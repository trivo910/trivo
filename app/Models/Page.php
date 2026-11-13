<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $canonical_url
 * @property Carbon|null $published_at
 * @property bool $noindex
 * @property bool $nofollow
 * @property list<array<string, mixed>>|null $blocks
 */
class Page extends Model
{
    use HasSeo;
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'legacy_wp_id',
        'title',
        'path',
        'template',
        'blocks',
        'status',
        'published_at',
        'parent_id',
        'sort',
        'content_version',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'published_at' => 'datetime',
            'publication_locked_at' => 'datetime',
        ];
    }
}
