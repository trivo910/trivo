<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
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
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'details' => 'array',
        ];
    }
}
