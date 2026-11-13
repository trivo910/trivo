<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $legacy_wp_id
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string $label
 * @property string $url
 * @property string|null $target
 * @property int $sort
 */
class MenuItem extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'legacy_wp_id',
        'menu_id',
        'parent_id',
        'label',
        'url',
        'target',
        'sort',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['menu_id' => 'integer', 'parent_id' => 'integer', 'sort' => 'integer'];
    }
}
