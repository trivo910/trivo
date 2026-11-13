<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['faqs', 'testimonials', 'clients'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->text('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->text('canonical_url')->nullable();
                $table->text('robots')->nullable();
                $table->text('og_title')->nullable();
                $table->text('og_description')->nullable();
                $table->foreignId('og_image_id')->nullable()->constrained('media')->nullOnDelete();
                $table->text('twitter_title')->nullable();
                $table->text('twitter_description')->nullable();
                $table->foreignId('twitter_image_id')->nullable()->constrained('media')->nullOnDelete();
                $table->string('schema_type', 50)->nullable();
                $table->json('schema_json')->nullable();
                $table->boolean('noindex')->default(false);
                $table->boolean('nofollow')->default(false);
                $table->boolean('sitemap_include')->default(true);
                $table->decimal('sitemap_priority', 2, 1)->nullable();
            });
        }
    }

    public function down(): void
    {
        $columns = ['seo_title', 'seo_description', 'canonical_url', 'robots', 'og_title', 'og_description', 'og_image_id', 'twitter_title', 'twitter_description', 'twitter_image_id', 'schema_type', 'schema_json', 'noindex', 'nofollow', 'sitemap_include', 'sitemap_priority'];
        foreach (['faqs', 'testimonials', 'clients'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
