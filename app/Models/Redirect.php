<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'from_path',
        'to_url',
        'status_code',
        'hits',
        'last_hit_at',
        'is_regex',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'last_hit_at' => 'datetime',
            'is_regex' => 'boolean',
        ];
    }
}
