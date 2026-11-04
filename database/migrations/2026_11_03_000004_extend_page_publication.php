<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->timestamp('publication_locked_at')->nullable();
            $table->string('publication_error', 40)->nullable();
        });
        DB::table('pages')->where(function ($query) {
            $query->whereIn('status', ['published', 'scheduled'])->orWhereNotNull('published_at');
        })->update(['publication_locked_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('pages', fn (Blueprint $table) => $table->dropColumn(['publication_locked_at', 'publication_error']));
    }
};
