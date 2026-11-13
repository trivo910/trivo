<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property array<string, array{status: string, weight: int}>|null $seo_analysis
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $canonical_url
 * @property Carbon|null $published_at
 */
class Post extends Model
{
    use HasSeo;
    use SoftDeletes;

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
        'content_version',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'publication_locked_at' => 'datetime',
            'secondary_keywords' => 'array',
            'seo_analysis' => 'array',
            'is_cornerstone' => 'boolean',
            'show_toc' => 'boolean',
            'content_version' => 'integer',
        ];
    }

    /** @return BelongsToMany<Tag, $this> */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
