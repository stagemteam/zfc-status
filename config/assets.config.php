<?php
namespace Agere\Status;

return [
    'default' => [
        'assets' => [
            //'@cartDefault_css',
            '@statusDefault_js',
        ],
        'options' => [
            'mixin' => true,
        ],
    ],

    'modules' => [
        __NAMESPACE__ => [
            'root_path' => __DIR__ . '/../view/assets',
            'collections' => [
                'statusDefault_js' => [
                    'assets' => [
                        'js/status-button.js',
                    ],
                ],
            ],
        ],
    ],
];