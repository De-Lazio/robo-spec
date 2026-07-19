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

        // Extension whitelist per resource kind. Anything not listed here is
        // rejected outright, which incidentally blocks executables/scripts.
        'allowed_extensions' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
            'document' => ['pdf', 'doc', 'docx', 'odt', 'txt'],
            'code' => ['py', 'ino', 'cpp', 'c', 'h', 'hpp', 'js', 'ts', 'json', 'yaml', 'yml', 'sh'],
            'cad' => ['stl', 'step', 'stp', 'iges', 'igs', 'sldprt', 'sldasm', 'f3d', 'gltf', 'glb'],
            'schema' => ['dxf', 'fzz', 'brd', 'sch', 'kicad_pcb', 'kicad_sch'],
            'archive' => ['zip', 'rar', '7z', 'tar', 'gz'],
            'other' => ['csv', 'md'],
        ],

        // Kinds whose real (finfo-detected) MIME type is cross-checked against
        // a list of expected prefixes. CAD/code/schema formats are too
        // MIME-ambiguous for finfo to whitelist reliably, so they rely on the
        // extension list above only.
        'strict_mime_kinds' => [
            'image' => ['image/'],
            'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.oasis.opendocument.text', 'text/plain'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Project invitations
    |--------------------------------------------------------------------------
    |
    | Invitation links expire after this many days, after which the invitee
    | must be re-invited before they can join the project.
    |
    */

    'invitations' => [
        'expires_after_days' => (int) env('ROBOFORGE_INVITATION_EXPIRES_DAYS', 7),
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization invitations
    |--------------------------------------------------------------------------
    |
    | Kept separate from the project invitation TTL above so the two can
    | diverge later without cross-affecting each other.
    |
    */

    'organizations' => [
        'invitations' => [
            'expires_after_days' => (int) env('ROBOFORGE_ORGANIZATION_INVITATION_EXPIRES_DAYS', 7),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Component library
    |--------------------------------------------------------------------------
    |
    | Datasheets are admin-only uploads (PDF only), so they share the same
    | private disk and default size limit as project resources rather than
    | introducing a separate storage surface.
    |
    */

    'components' => [
        'datasheet' => [
            'max_upload_kb' => (int) env('ROBOFORGE_MAX_UPLOAD_KB', 25_600),
        ],
    ],
];
