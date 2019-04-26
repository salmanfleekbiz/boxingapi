<?php
// return [
//     'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
//     'language' => 'pt-PT',
//     'components' => [
//         'cache' => [
//             'class' => 'yii\caching\FileCache',
//         ],
//         'i18n' => [
// 	        'translations' => [
// 	            'frontend*' => [
// 	                'class' => 'yii\i18n\PhpMessageSource',
// 	                'basePath' => '@common/messages',
// 	            ],
// 	            'backend*' => [
// 	                'class' => 'yii\i18n\PhpMessageSource',
// 	                'basePath' => '@common/messages',
// 	            ],
// 	        ],
//     	],
//     ],
// ];

return [
    'language' => 'EN-US',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'i18n' => [
            'translations' => [
                'frontend*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '/var/www/html/yii-application/backend/messages',
                ],
                'backend*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '/var/www/html/yii-application/backend/messages',
                ],
            ],
        ],
    ],
];
