<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->unsignedInteger('content_version')->default(1);
            $table->softDeletes();
        });
        Schema::table('menu_items', fn (Blueprint $table) => $table->softDeletes());
    }

    public function down(): void
    {
        Schema::table('menu_items', fn (Blueprint $table) => $table->dropSoftDeletes());
        Schema::table('menus', fn (Blueprint $table) => $table->dropColumn(['content_version', 'deleted_at']));
    }
};
