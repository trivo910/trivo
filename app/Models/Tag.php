<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasSeo;

    /** @var list<string> */
    protected $fillable = [
        'legacy_wp_id',
        'name',
        'slug',
        'path',
        'description',
        'parent_id',
    ];
}
