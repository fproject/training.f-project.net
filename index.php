<?php

// change the following paths if necessary
$yiiBase=dirname(__FILE__).DIRECTORY_SEPARATOR.'framework'.DIRECTORY_SEPARATOR.'YiiBase.php';
$yii=dirname(__FILE__).DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'yii.php';
$config=dirname(__FILE__).DIRECTORY_SEPARATOR.'protected'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'main.php';

// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);

// remove the following lines when in production mode
defined('YII_ENV_DEV') or define('YII_ENV_DEV', true);

// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);

require_once($yiiBase);

require_once($yii);

require_once(__DIR__ . '/protected/vendor/autoload.php');

Yii::setPathOfAlias('fproject',Yii::getPathOfAlias('application.vendors.fproject.amqp-helper.fproject'));

Yii::createWebApplication($config)->run();
