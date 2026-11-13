<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['categories', 'tags'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->unsignedInteger('content_version')->default(1);
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach (['categories', 'tags'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn(['content_version', 'deleted_at']));
        }
    }
};
