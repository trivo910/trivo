<?php

namespace App\Console\Commands;

use App\Services\Migration\LegacyAuditReader;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

class LegacyReport extends Command
{
    protected $signature = 'trivo:legacy-report';

    protected $description = 'Read published-content counts from the isolated, read-only audit connection';

    public function handle(LegacyAuditReader $reader): int
    {
        try {
            $this->table(['Public content type', 'Count'], $reader->counts());
        } catch (QueryException) {
            $this->error('Cannot read the isolated legacy source. Check LEGACY_DB settings without sharing credentials.');

            return self::FAILURE;
        }

        $this->info('This sanitized audit subset excludes legacy users, sessions and form submissions.');

        return self::SUCCESS;
    }
}
