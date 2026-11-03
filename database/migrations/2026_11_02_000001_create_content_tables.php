<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_wp_id')->nullable()->unique();
            $table->string('disk')->default('public');
            $table->string('path', 768)->unique();
            $table->string('original_name');
            $table->string('mime', 100);
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->text('alt')->nullable();
            $table->text('title')->nullable();
            $table->text('caption')->nullable();
            $table->json('variants')->nullable();
            $table->char('sha256', 64)->index();
            $table->timestamps();
        });

        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_wp_id')->nullable()->unique();
            $table->string('name');
            $table->string('path', 768)->unique();
            $table->text('bio')->nullable();
            $table->foreignId('avatar_id')->nullable()->constrained('media')->nullOnDelete();
            $table->json('socials')->nullable();
            $this->seoColumns($table);
            $table->timestamps();
        });

        foreach (['categories', 'tags'] as $name) {
            Schema::create($name, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('legacy_wp_id')->nullable()->unique();
                $table->string('name');
                $table->string('slug');
                $table->string('path', 768)->unique();
                $table->text('description')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $this->seoColumns($table);
                $table->timestamps();
            });
        }

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_wp_id')->nullable()->unique();
            $table->string('title');
            $table->string('path', 768)->unique();
            $table->string('template')->default('page');
            $table->json('blocks')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->foreignId('parent_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->unsignedInteger('sort')->default(0);
            $this->seoColumns($table);
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_wp_id')->nullable()->unique();
            $table->string('title');
            $table->string('slug');
            $table->string('path', 768)->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedSmallInteger('reading_time')->default(0);
            $table->string('focus_keyword')->nullable()->index();
            $table->json('secondary_keywords')->nullable();
            $table->unsignedTinyInteger('seo_score')->default(0);
            $table->unsignedTinyInteger('readability_score')->default(0);
            $table->json('seo_analysis')->nullable();
            $table->boolean('is_cornerstone')->default(false);
            $table->boolean('show_toc')->default(true);
            $this->seoColumns($table);
            $table->timestamps();
        });

        Schema::create('post_tag', function (Blueprint $table) {
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['post_id', 'tag_id']);
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_wp_id')->nullable()->unique();
            $table->string('name');
            $table->string('location')->unique();
            $table->timestamps();
        });
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legacy_wp_id')->nullable()->unique();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->nullOnDelete();
            $table->string('label');
            $table->text('url');
            $table->string('target', 20)->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        foreach (['services', 'portfolio_items', 'testimonials', 'faqs', 'clients'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                $table->unsignedBigInteger('legacy_wp_id')->nullable()->unique();
                $table->string('title');
                $table->string('path', 768)->nullable()->unique();
                $table->longText('body')->nullable();
                $table->json('blocks')->nullable();
                $table->json('details')->nullable();
                $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
                $table->string('status', 20)->default('draft')->index();
                $table->unsignedInteger('sort')->default(0);
                if (in_array($name, ['services', 'portfolio_items'], true)) {
                    $this->seoColumns($table);
                }
                $table->timestamps();
            });
        }

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 191)->unique();
            $table->longText('value')->nullable();
            $table->string('group', 50)->index();
            $table->timestamps();
        });
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path', 768)->unique();
            $table->text('to_url')->nullable();
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->unsignedBigInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->boolean('is_regex')->default(false);
            $table->timestamps();
        });
        Schema::create('not_found_logs', function (Blueprint $table) {
            $table->id();
            $table->string('path', 768)->unique();
            $table->text('referrer')->nullable();
            $table->char('ua_hash', 64)->nullable();
            $table->unsignedBigInteger('count')->default(1);
            $table->timestamp('last_seen');
        });
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->string('company')->nullable();
            $table->string('service')->nullable();
            $table->text('message')->nullable();
            $table->text('source_url')->nullable();
            $table->json('utm')->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->string('status', 30)->default('new')->index();
            $table->text('notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
        Schema::create('mail_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->index();
            $table->string('error_code')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
        Schema::create('revisions', function (Blueprint $table) {
            $table->id();
            $table->morphs('revisionable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('snapshot');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['revisions', 'mail_logs', 'leads', 'not_found_logs', 'redirects', 'settings',
            'clients', 'faqs', 'testimonials', 'portfolio_items', 'services', 'menu_items', 'menus',
            'post_tag', 'posts', 'pages', 'tags', 'categories', 'authors', 'media'] as $name) {
            Schema::dropIfExists($name);
        }
    }

    private function seoColumns(Blueprint $table): void
    {
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
    }
};
