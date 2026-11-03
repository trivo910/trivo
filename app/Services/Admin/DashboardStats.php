<?php

namespace App\Services\Admin;

use App\Models\Lead;
use App\Models\NotFoundLog;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;

class DashboardStats
{
    /** @return array<string, int|null> */
    public function forUser(User $user): array
    {
        return [
            'posts' => $user->can('posts.view') ? Post::query()->count() : null,
            'pages' => $user->can('pages.view') ? Page::query()->count() : null,
            'leads' => $user->can('leads.view') ? Lead::query()->count() : null,
            'not_found' => $user->can('redirects.manage') ? (int) NotFoundLog::query()->sum('count') : null,
            'seo' => $user->can('posts.view') ? (($score = Post::query()->where('status', 'published')->avg('seo_score')) === null ? null : (int) round($score)) : null,
        ];
    }
}
