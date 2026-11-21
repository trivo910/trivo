<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->boolean('show_toc')->default(false)->change();
        });
        // The imported articles have no contents panel in the reference design.
        DB::table('posts')->where('content_kind', 'article')->where('body_format', 'legacy')
            ->where('show_toc', true)->update(['show_toc' => false,
                'content_version' => DB::raw('content_version + 1'), 'updated_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->boolean('show_toc')->default(true)->change();
        });
        // Do not overwrite editors' saved choices when rolling back.
    }
};
