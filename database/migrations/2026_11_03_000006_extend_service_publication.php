<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('publication_locked_at')->nullable();
            $table->string('publication_error', 40)->nullable();
        });
        DB::table('services')->whereIn('status', ['published', 'scheduled'])->update(['publication_locked_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('services', fn (Blueprint $table) => $table->dropColumn(['published_at', 'publication_locked_at', 'publication_error']));
    }
};
