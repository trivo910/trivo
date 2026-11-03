<?php

namespace App\Models;

use App\Models\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasSeo;

    /** @var list<string> */
    protected $fillable = [
        'legacy_wp_id',
        'name',
        'path',
        'bio',
        'avatar_id',
        'socials',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'socials' => 'array',
        ];
    }
}
