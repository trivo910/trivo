<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasSeo;

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
