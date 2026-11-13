<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $status
 * @property string|null $notes
 * @property int|null $handled_by
 * @property int $content_version
 */
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
        'content_version',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'utm' => 'array',
            'content_version' => 'integer',
            'handled_by' => 'integer',
        ];
    }
}
