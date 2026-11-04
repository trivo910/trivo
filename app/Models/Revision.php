<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** @property array<string, mixed>|null $snapshot */
class Revision extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'revisionable_type',
        'revisionable_id',
        'user_id',
        'snapshot',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
        ];
    }
}
