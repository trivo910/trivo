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
            $table->string('archived_path', 768)->nullable();
            $table->string('path', 768)->nullable()->change();
        });

        DB::table('pages')->whereNotNull('deleted_at')->whereNull('archived_path')
            ->whereNotNull('path')->update(['archived_path' => DB::raw('path')]);
        DB::table('pages')->whereNotNull('deleted_at')->update(['path' => null]);
    }

    public function down(): void
    {
        $conflict = DB::table('pages as archived')
            ->join('pages as active', function ($join) {
                $join->on('archived.archived_path', '=', 'active.path')
                    ->whereColumn('archived.id', '!=', 'active.id');
            })
            ->whereNotNull('archived.deleted_at')
            ->whereNotNull('archived.archived_path')
            ->exists();

        if ($conflict) {
            throw new RuntimeException('Cannot restore archived page paths while an active page uses one.');
        }

        DB::table('pages')->whereNotNull('deleted_at')->whereNotNull('archived_path')
            ->update(['path' => DB::raw('archived_path')]);

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('archived_path');
            $table->string('path', 768)->nullable(false)->change();
        });
    }
};
