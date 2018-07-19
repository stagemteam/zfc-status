<?php
namespace Popov\ZfcStatus;

return [
    'default' => [
        'assets' => [
            //'@grid_css',
            '@status_js',
        ],
    ],

    'modules' => [
        __NAMESPACE__ => [
            'root_path' => __DIR__ . '/../view/assets',
            'collections' => [
                'status_js' => [
                    'assets' => [
                        'js/status-button.js',
                    ],
                ],
            ],
        ],
    ],
];