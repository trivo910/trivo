<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // These imported portfolio design drafts were stored as published WordPress posts.
        // Preserve their bodies and URLs, but do not list them as editorial articles.
        DB::table('posts')->whereNotNull('legacy_wp_id')->whereIn('path', [
            '/new-layout', '/new-landing-2', '/new-landing-3',
        ])->update(['content_kind' => 'portfolio_source']);
    }

    public function down(): void
    {
        DB::table('posts')->whereNotNull('legacy_wp_id')->whereIn('path', [
            '/new-layout', '/new-landing-2', '/new-landing-3',
        ])->where('content_kind', 'portfolio_source')->update(['content_kind' => 'article']);
    }
};
