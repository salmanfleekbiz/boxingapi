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
       'mailer' => [
        'class' => 'yii\swiftmailer\Mailer',
		'viewPath' => '@common/mail',
        'useFileTransport' => false,
			'transport' => [
				'class' => 'Swift_SmtpTransport',
				'host' => 'smtp.mandrillapp.com',  
				'username' => 'srizvi@csquareonline.com',
				'password' => '0qN-j-nIFMNoEGwbDeucsg',
				'port' => '587', 
				#'encryption' => 'tls', 
			],
		],
    ],
];


#return $config;
