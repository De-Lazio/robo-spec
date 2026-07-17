<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Project resource storage
    |--------------------------------------------------------------------------
    |
    | Resources will be stored on Laravel's private local disk during the MVP.
    | The disk and maximum size are configured here so the resource domain can
    | evolve without scattering infrastructure choices through controllers.
    |
    */

    'resources' => [
        'disk' => env('ROBOFORGE_RESOURCE_DISK', 'local'),
        'max_upload_kb' => (int) env('ROBOFORGE_MAX_UPLOAD_KB', 25_600),
    ],
];
