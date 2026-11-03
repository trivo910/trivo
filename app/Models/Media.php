<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'legacy_wp_id',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'width',
        'height',
        'alt',
        'title',
        'caption',
        'variants',
        'sha256',
        'source_sha256', 'source_mime', 'processing_status', 'error_code', 'folder', 'uploaded_by',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'variants' => 'array',
        ];
    }

    public function isLegacy(): bool
    {
        return $this->legacy_wp_id !== null || str_starts_with(ltrim($this->path, '/'), 'wp-content/uploads/');
    }
}
