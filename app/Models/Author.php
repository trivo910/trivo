<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string|null $seo_title
 * @property string|null $seo_description
 * @property string|null $canonical_url
 */
class Author extends Model
{
    use HasSeo;
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'legacy_wp_id',
        'name',
        'path',
        'bio',
        'avatar_id',
        'socials',
        'content_version',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'socials' => 'array',
            'content_version' => 'integer',
        ];
    }
}
