<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasSeo;

    /** @var list<string> */
    protected $fillable = [
        'legacy_wp_id',
        'title',
        'path',
        'template',
        'blocks',
        'status',
        'published_at',
        'parent_id',
        'sort',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'blocks' => 'array',
            'published_at' => 'datetime',
        ];
    }
}
