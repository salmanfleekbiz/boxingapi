<?php

#$config = [];

#if (!YII_ENV_TEST) {
// configuration adjustments for 'dev' environment
//    $config['bootstrap'][] = 'debug';
//    $config['modules']['debug'] = 'yii\debug\Module';
#}

return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=clients25s_boxingapp',
            'username' => 'clients25s',
            'password' => '45[^-;ZU~QUo',
            'charset' => 'utf8',
        ],
        'user' => [
            'class' => 'filsh\yii2\user\components\User',
        ],
        'mail' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' => false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'sclass.websitewelcome.com', //smtp.gmail.com
                'username' => 'admin@deliverychacha.com', //srashid@westagilelabs.com
                'password' => 'dcadmin!@#', //Srashid1@#$%
                'port' => '465', //465
                'encryption' => 'ssl', //ssl
            ],
        ],
    ],
    'modules' => [
        'user' => [
            'class' => 'filsh\yii2\user\Module',
            // set custom module properties here ...
        ],
    ]
];


#return $config;
