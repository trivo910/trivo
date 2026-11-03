<?php

return [
    'disk' => 'public',
    'staging_disk' => 'local',
    'max_bytes' => 8 * 1024 * 1024,
    'max_pixels' => 8_000_000,
    'max_dimension' => 6000,
    'widths' => [480, 768, 1200, 1920],
    'webp_quality' => 82,
    'avif_quality' => 55,
];
