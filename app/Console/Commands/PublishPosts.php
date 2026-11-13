<?php

namespace App\Console\Commands;

use App\Services\Content\PublishDuePosts;
use Illuminate\Console\Command;

class PublishPosts extends Command
{
    protected $signature = 'trivo:publish-posts {--limit=50 : Maximum due posts per run (1-100)}';

    protected $description = 'Validate and publish a bounded batch of due scheduled posts';

    public function handle(PublishDuePosts $publisher): int
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
