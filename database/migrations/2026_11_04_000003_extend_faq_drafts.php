<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->unsignedInteger('content_version')->default(1);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('publication_locked_at')->nullable();
            $table->softDeletes();
        });
        DB::table('faqs')->whereIn('status', ['published', 'scheduled'])->update(['publication_locked_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('faqs', fn (Blueprint $table) => $table->dropColumn(['content_version', 'published_at', 'publication_locked_at', 'deleted_at']));
    }
};
