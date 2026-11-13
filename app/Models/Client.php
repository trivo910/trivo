<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $legacy_wp_id
 * @property string $title
 * @property string|null $path
 * @property string $status
 * @property int $sort
 * @property int|null $media_id
 * @property int $content_version
 * @property array<array-key, mixed>|null $blocks
 * @property Carbon|null $published_at
 * @property Carbon|null $publication_locked_at
 */
class Client extends Model
{
    use HasSeo;
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'legacy_wp_id',
        'title',
        'path',
        'body',
        'blocks',
        'details',
        'media_id',
        'status',
        'sort',
        'content_version',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'details' => 'array',
            'published_at' => 'datetime',
            'publication_locked_at' => 'datetime',
        ];
    }
}
