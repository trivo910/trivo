<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'legacy_wp_id',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'width',
        'height',
        'alt',
        'title',
        'caption',
        'variants',
        'sha256',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'variants' => 'array',
        ];
    }
}
