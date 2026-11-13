<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $legacy_wp_id
 * @property string $name
 * @property string $slug
 * @property string $path
 * @property string|null $description
 * @property int|null $parent_id
 * @property int $content_version
 */
class Tag extends Model
{
    use HasSeo;
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'legacy_wp_id',
        'name',
        'slug',
        'path',
        'description',
        'parent_id',
        'content_version',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['parent_id' => 'integer', 'content_version' => 'integer'];
    }
}
