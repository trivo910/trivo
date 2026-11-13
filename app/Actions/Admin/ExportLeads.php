<?php

namespace App\Actions\Admin;

use App\Models\Lead;
use App\Services\Leads\LeadInbox;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportLeads
{
    /** @param array<string, mixed> $filters */
    public function handle(array $filters): StreamedResponse
    {
        Gate::authorize('export', Lead::class);
        $inbox = app(LeadInbox::class);
        $fields = ['id', 'name', 'email', 'phone', 'company', 'service', 'message', 'status', 'source_url', 'created_at', 'updated_at'];
        $query = $inbox->query($filters)->orderByDesc('id')->limit(LeadInbox::EXPORT_LIMIT)->select($fields);
        activity('leads')->causedBy(auth()->user())->event('leads_exported')->log('leads_exported');

        return response()->streamDownload(function () use ($query, $fields, $inbox) {
            $stream = fopen('php://output', 'wb');
            if ($stream === false) {
                throw new \RuntimeException('Unable to open export stream.');
            }
            try {
                fwrite($stream, "\xEF\xBB\xBF");
                fputcsv($stream, $fields, ',', '"', '');
                foreach ($query->cursor() as $lead) {
                    $row = [];
                    foreach ($fields as $field) {
                        $value = $lead->getAttribute($field);
                        $row[] = $inbox->csvCell($value === null ? null : (string) $value);
                    }
                    fputcsv($stream, $row, ',', '"', '');
                }
            } finally {
                fclose($stream);
            }
        }, 'leads-'.now('UTC')->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private', 'X-Export-Limit' => (string) LeadInbox::EXPORT_LIMIT]);
    }
}
