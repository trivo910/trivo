<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
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
}
