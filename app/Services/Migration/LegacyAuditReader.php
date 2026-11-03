<?php

namespace App\Services\Migration;

use Illuminate\Support\Facades\DB;

class LegacyAuditReader
{
    /** @return list<array{type: string, count: int}> */
    public function counts(): array
    {
        return DB::connection('legacy')->table('wp_posts')
            ->select('post_type')->selectRaw('COUNT(*) AS aggregate')
            ->groupBy('post_type')->orderBy('post_type')->get()
            ->map(fn (object $row): array => ['type' => (string) $row->post_type, 'count' => (int) $row->aggregate])
            ->values()->all();
    }
}
