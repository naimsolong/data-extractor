<?php

// config for NaimSolong/DataExtractor

use Illuminate\Foundation\Auth\User;

return [
    'allow_production' => env('DATA_EXTRACTOR_PRODUCTION', false),

    'instructions' => [
        [
            'name' => 'Default',
            'description' => 'Extra all user data',
            'source' => 'default',
            'export' => [
                'format' => 'sql',
                'file_name' => 'data-extractor',
                'file_path' => 'data-extractor',
                'disk' => 'local',
            ],
        ]
    ],

    'source' => [
        'default' => [
            'connection' => 'mysql',
            'model' => User::class,
            'relationships' => [
                'mainProfile',
            ],
        ]
    ]
];
