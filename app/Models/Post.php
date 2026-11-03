<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasSeo;

    /** @var list<string> */
    protected $fillable = [
        'legacy_wp_id',
        'title',
        'slug',
        'path',
        'excerpt',
        'body',
        'featured_media_id',
        'author_id',
        'category_id',
        'status',
        'published_at',
        'reading_time',
        'focus_keyword',
        'secondary_keywords',
        'seo_score',
        'readability_score',
        'seo_analysis',
        'is_cornerstone',
        'show_toc',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'secondary_keywords' => 'array',
            'seo_analysis' => 'array',
            'is_cornerstone' => 'boolean',
            'show_toc' => 'boolean',
        ];
    }
}
