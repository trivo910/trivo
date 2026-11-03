<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /** @var list<string> */
    protected $hidden = ['value'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'value' => 'encrypted:array',
        ];
    }
}
