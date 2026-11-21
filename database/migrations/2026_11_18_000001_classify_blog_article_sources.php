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
            $table->string('content_kind', 24)->default('article')->index();
            $table->string('body_format', 16)->default('legacy');
        });
        // These imported case studies share the legacy posts table but are not blog articles.
        DB::table('posts')->where(function ($query): void {
            $query->where('path', 'like', '/portfolio-source/%')->orWhereIn('path', [
                '/luhaif-perfumes', '/mumbai-darshan', '/sell-sold', '/professional-frameworks',
                '/arihant-panel-fittings', '/al-bushra-t-and-t', '/stocksovet', '/rashidmiftahi',
                '/areebatourism', '/sanjari-recycling', '/holidayaapkliye', '/villaplanet', '/bradymorris', '/miftahi-shop',
            ]);
        })->update(['content_kind' => 'portfolio_source']);
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table): void {
            $table->dropColumn(['content_kind', 'body_format']);
        });
    }
};
