<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'service',
        'message',
        'source_url',
        'utm',
        'ip_hash',
        'status',
        'notes',
        'handled_by',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'utm' => 'array',
        ];
    }
}
