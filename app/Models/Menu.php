<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $legacy_wp_id
 * @property string $name
 * @property string $location
 * @property int $content_version
 */
class Menu extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'legacy_wp_id',
        'name',
        'location',
        'content_version',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['content_version' => 'integer'];
    }
}
