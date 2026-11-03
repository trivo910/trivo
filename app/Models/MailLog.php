<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'lead_id',
        'status',
        'error_code',
        'sent_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }
}
