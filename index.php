<?php

require('common/config/bootstrap.php');	/* configurations based on environment */

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', $env);

require('vendor/autoload.php');
require('vendor/yiisoft/yii2/Yii.php');
require('common/config/aliases.php');

$config = yii\helpers\ArrayHelper::merge(
    require('common/config/main.php'),
    require('common/config/main-local.php'),
    require('services/config/main.php'),
    require($path)
);


$application = new yii\web\Application($config);
$application->run();

