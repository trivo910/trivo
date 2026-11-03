<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'legacy_wp_id',
        'name',
        'location',
    ];
}
