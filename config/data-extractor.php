<?php

// config for NaimSolong/DataExtractor

use App\Models\User;

return [
    'is_enabled' => env('DATA_EXTRACTOR_ENABLED', false),

    'options' => [
        [
            'name' => 'Default',
            'description' => 'Extra all user data',
            'format' => 'sql',
            'source' => 'default',
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
];
