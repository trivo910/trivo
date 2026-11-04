<?php

namespace App\Services\Content;

class PublishDueServices
{
    /** @return array{published: int, blocked: int} */
    public function handle(int $limit = 50): array
    {
        return app(PublishDueContent::class)->handle('services', $limit);
    }
}
