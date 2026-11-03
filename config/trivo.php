<?php

return [
    'admin_path' => env('ADMIN_PATH', ''),
    'csp_enforce' => (bool) env('CSP_ENFORCE', false),
    'canonical_url' => env('CANONICAL_URL', 'https://trivo.in'),
    'admin_allowed_ips' => array_values(array_filter(explode(',', (string) env('ADMIN_ALLOWED_IPS', '')))),
];
