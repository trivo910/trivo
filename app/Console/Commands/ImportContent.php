<?php

namespace App\Console\Commands;

use App\Services\Migration\LegacyContentImporter;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

class ImportContent extends Command
{
    protected $signature = 'trivo:import-content {--dry-run : Report the import without changing the local database}';

    protected $description = 'Import published pages and posts from the sanitized legacy source';

    public function handle(LegacyContentImporter $importer): int
    {
        try {
            $result = $importer->import((bool) $this->option('dry-run'));
        } catch (QueryException) {
            $this->error('Could not read the legacy source or write the local database. Check database configuration without sharing credentials.');

            return self::FAILURE;
        } catch (\RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->table(['Result', 'Count'], collect($result)->map(fn (int $count, string $key): array => [$key, $count])->values()->all());
        $this->info($this->option('dry-run') ? 'Dry run complete; no content was written.' : 'Content import complete.');
        if ($result['missing_map'] > 0) {
            $this->warn('Some published source records have no verified URL-MAP entry and were not imported.');
        }
        if ($result['url_conflicts'] > 0) {
            $this->warn('A source post sharing a live page URL was retained as a draft for review.');
        }

        return $result['missing_map'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
