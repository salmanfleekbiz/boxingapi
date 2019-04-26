<?php
//$params = array_merge(
    //require(__DIR__ . '/../../common/config/params.php'),
    //require(__DIR__ . '/../../common/config/params-local.php'),
    //require(__DIR__ . '/params.php'),
    //require(__DIR__ . '/params-local.php')
//);
//print_r(dirname(__DIR__)); exit;
return [
    'id' => 'app-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/v1',
            'class' => 'services\modules\v1\Module'
        ],
        'oauth2' => [
            'class' => 'filsh\yii2\oauth2server\Module',
            'tokenParamName' => 'accessToken',
            'tokenAccessLifetime' => 3600 * 24,
            'storageMap' => [
                'user_credentials' => 'services\modules\v1\models\User',
            ],
            'grantTypes' => [
                'user_credentials' => [
                    'class' => 'OAuth2\GrantType\UserCredentials',
                ],
                'refresh_token' => [
                    'class' => 'OAuth2\GrantType\RefreshToken',
                    'always_issue_new_refresh_token' => true
                ]
            ]
        ],

    ],
    'components' => [
        'user' => [
            'identityClass' => 'services\modules\v1\models\User',
            'enableAutoLogin' => false,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'class' => 'yii\web\UrlManager',
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/application',
                    'pluralize' => false,
                    'extraPatterns' => array(
                        'GET version' => 'app-version',
                    )
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/oauth2',
                    'pluralize' => false,
                    'extraPatterns' => array(
                        'POST token' => 'token',
                        'GET verification' => 'verification'
                    )
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/user',
                    'pluralize' => false,
                    'extraPatterns' => array(
                        'GET getuser/<id:\d+>' => 'getuser',
                        'POST usersignup' => 'usersignup',
                        'POST userlogin' => 'userlogin',
                        'POST userforget' => 'userforget',
                        'POST updateuserdata' => 'updateuserdata',
                        'POST deviceid' => 'deviceid',
                        'POST contactus' => 'contactus',
                        'POST allusers' => 'allusers'
                    )
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/Channel',
                    'pluralize' => false,
                    'extraPatterns' => array(
                        'POST' => 'addchannel',
                        'POST' => 'allchannel',
                        'GET channelget/<id:\d+>' => 'channelget',
                        'POST updatechaneldata' => 'updatechaneldata'
                    )
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/Tournament',
                    'pluralize' => false,
                    'extraPatterns' => array(
                        'POST' => 'addtournament',
                        'POST' => 'tournamentmatchlist',
                        'GET,POST' => 'upcommingevent',
                        'GET tournamentget/<id:\d+>' => 'tournamentget',
                        'POST' => 'updatetournamentdata',
                    )
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/Boxer',
                    'pluralize' => false,
                    'extraPatterns' => array(
                        'POST' => 'addboxer',
                        'POST' => 'alltournament',
                        'POST' => 'allboxers',
                        'GET boxerget/<id:\d+>' => 'boxerget',
                        'POST' => 'updateboxerdata'
                    )
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/Match',
                    'pluralize' => false,
                    'extraPatterns' => array(
                        'POST' => 'addmatch',
                        'POST' => 'allmatch',
                        'GET matchget/<id:\d+>' => 'matchget',
                        'POST' => 'updatematchdata',
                        'POST' => 'winnernames',
                        'POST' => 'matchshowbytournamentid',
                        'POST' => 'matchdelet',
                    )
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/Reminder',
                    'pluralize' => false,
                    'extraPatterns' => array(
                        'POST' => 'remindersubscriber',
                        'POST' => 'checkreminder',
                        'POST' => 'checkuserreminder',
                        'POST' => 'userdeviceids'
                    )
                ],
            ],
        ],
/*        'restComponent' => [
            'class' => 'services\modules\v1\components\RestComponent',
        ],*/
    ],
    //'params' => $params,
];



