<?php

namespace App\Services\Redirects;

use App\Support\RedirectPaths;
use Illuminate\Validation\ValidationException;

class RedirectGraph
{
    /** @param array<string, array{to_url: ?string, status_code: int}> $nodes
     * @return array<string, array{to_url: ?string, status_code: int}>
     */
    public function flatten(array $nodes): array
    {
        $result = $nodes;
        foreach ($nodes as $source => $node) {
            if ($node['status_code'] === 410) {
                $result[$source]['to_url'] = null;

                continue;
            }
            $target = RedirectPaths::target((string) $node['to_url']);
            $seen = [$source => true];
            while ($target !== null) {
                $path = (string) parse_url($target, PHP_URL_PATH);
                if (isset($seen[$path])) {
                    throw ValidationException::withMessages(['to_url' => 'This redirect would create a loop.']);
                }
                if (! isset($nodes[$path])) {
                    break;
                }
                $seen[$path] = true;
                if ($nodes[$path]['status_code'] === 410) {
                    throw ValidationException::withMessages(['to_url' => 'A redirect cannot point at a removed (410) path.']);
                }
                $target = RedirectPaths::target((string) $nodes[$path]['to_url']);
            }
            if ($target === null) {
                throw ValidationException::withMessages(['to_url' => 'The destination is not a valid internal URL.']);
            }
            $result[$source]['to_url'] = $target;
        }

        return $result;
    }
}
