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
            'dsn' => 'mysql:host=localhost;dbname=boxing_app',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
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
];


#return $config;
