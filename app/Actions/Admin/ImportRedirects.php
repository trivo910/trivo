<?php

namespace App\Actions\Admin;

use App\Models\Redirect;
use App\Rules\RedirectSource;
use App\Rules\RedirectTarget;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ImportRedirects
{
    public function handle(UploadedFile $file): int
    {
        Gate::authorize('create', Redirect::class);
        $stream = fopen($file->getRealPath(), 'r');
        if ($stream === false) {
            throw ValidationException::withMessages(['csv' => 'Cannot read this CSV.']);
        }
        try {
            $headers = fgetcsv($stream, 0, ',', '"', '');
            if ($headers !== false) {
                $headers[0] = ltrim((string) $headers[0], "\xEF\xBB\xBF");
            }
            if ($headers === false || count($headers) !== 3 || count(array_unique($headers)) !== 3
                || array_diff(['from_path', 'to_url', 'status_code'], $headers)) {
                throw ValidationException::withMessages(['csv' => 'Use exactly these headers: from_path,to_url,status_code.']);
            }
            $records = [];
            $seen = [];
            while (($row = fgetcsv($stream, 0, ',', '"', '')) !== false) {
                if ($row === [null]) {
                    continue;
                }
                if (count($records) >= 1000 || count($row) !== 3) {
                    throw ValidationException::withMessages(['csv' => 'CSV must contain at most 1,000 rows with three columns each.']);
                }
                $values = array_combine($headers, $row);
                $values['to_url'] = $values['to_url'] === '' ? null : $values['to_url'];
                $validator = Validator::make($values, [
                    'from_path' => ['required', 'string', 'max:768', new RedirectSource],
                    'to_url' => ['nullable', 'required_unless:status_code,410', 'string', 'max:768', new RedirectTarget],
                    'status_code' => ['required', 'integer', Rule::in([301, 302, 410])],
                ]);
                if ($validator->fails() || isset($seen[$values['from_path']])) {
                    throw ValidationException::withMessages(['csv' => 'Invalid or duplicate redirect on row '.(count($records) + 2).'. No redirects were imported.']);
                }
                $seen[$values['from_path']] = true;
                $records[] = $validator->validated();
            }
        } finally {
            fclose($stream);
        }

        return DB::transaction(function () use ($records): int {
            foreach ($records as $values) {
                $existing = Redirect::query()->where('from_path', $values['from_path'])->first();
                app(SaveRedirect::class)->handle($values, $existing);
            }

            return count($records);
        });
    }
}
