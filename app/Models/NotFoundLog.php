<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotFoundLog extends Model
{
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['path', 'referrer', 'ua_hash', 'count', 'last_seen'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['last_seen' => 'datetime'];
    }
}
