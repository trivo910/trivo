<?php

namespace App\Console\Commands;

use App\Services\Content\PublishDuePortfolio;
use Illuminate\Console\Command;

class PublishPortfolio extends Command
{
    protected $signature = 'trivo:publish-portfolio {--limit=50 : Maximum due portfolio entries per run (1-100)}';

    protected $description = 'Validate and publish a bounded batch of due scheduled portfolio entries';

    public function handle(PublishDuePortfolio $publisher): int
    {
        $limit = filter_var($this->option('limit'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100]]);
        if ($limit === false) {
            $this->error('Limit must be an integer between 1 and 100.');

            return self::INVALID;
        }
        $counts = $publisher->handle($limit);
        $this->info('Published: '.$counts['published'].'. Blocked: '.$counts['blocked'].'.');

        return self::SUCCESS;
    }
}
