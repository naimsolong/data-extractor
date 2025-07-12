<?php

// config for NaimSolong/DataExtractor

use Illuminate\Foundation\Auth\User;

return [
    'is_enabled' => env('DATA_EXTRACTOR_ENABLED', false),

    'options' => [
        [
            'name' => 'Default',
            'description' => 'Extra all user data',
            'format' => 'sql',
            'source' => 'default',
            'export' => 'default',
        ],
    ],

    'source' => [
        'default' => [
            'connection' => 'mysql',
            'model' => User::class,
            'relationships' => [
                'mainProfile',
            ],
        ],
    ],

    'export' => [
        'default' => [
            'file_name' => 'data-extractor',
            'file_path' => 'data-extractor',
            'disk' => 'local',
        ],
    ],
];
