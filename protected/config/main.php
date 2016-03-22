<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2015. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.

$params = require(__DIR__ . '/params.php');

$db = require(__DIR__ . '/db.php');

$config = [
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'F-Project',

    // preloading 'log' component
    'preload' => [
        'log',
        'zend'],

    // autoloading model and component classes
    'import' => [
        'application.models.*',
        'application.components.*',
        'application.components.services.*',
    ],

    'modules' => [
        'amfGateway' => [
            'servicesDirAlias' => 'application.services.amf',
            'productionMode' => false,
            'amfDiscoveryEnabled' => true,
            'classMap' => [
                'net.fproject.calendar.WorkCalendar' => 'fproject\calendar\WorkCalendar',
                'net.fproject.calendar.Period' => 'fproject\calendar\Period',
                'net.fproject.calendar.WorkShift' => 'fproject\calendar\WorkShift',
                'net.fproject.calendar.WeekDay' => 'fproject\calendar\WeekDay',
            ]
        ],
    ],

    // application components
    'components' => [
        'user' => [
            'class' => 'WebUser',
            // enable cookie-based authentication
            'allowAutoLogin' => true,

            //The number of seconds in which the user token will expire. Default is one day.
            'tokenExpire' => 86400,
        ],

        // uncomment the following to enable URLs in path-format
        'urlManager' => [
            'showScriptName' => false,
            /*'urlFormat'=>'path',
            'rules'=>array(
                '<controller:\w+>/<id:\d+>'=>'<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
                '<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
            ),*/
        ],

        'db' => $db,

        'errorHandler' => [
            // use 'site/error' action to display errors
            'errorAction' => 'site/error',
        ],

        'log' => [
            'class' => 'CLogRouter',
            'routes' => [
                [
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning, trace',//'levels'=>'trace, info, error, warning, application',
                    //'showInFireBug'=>true, //firebug only - turn off otherwise
                    'maxFileSize' => 1024,//maximum log file size in kilo-bytes (KB).
                ],

                // uncomment the following to show log messages on web pages
                /*
                array(
                    'class'=>'CWebLogRoute',
                ),
                */
            ],
        ],

        'cache' => [
            'class' => 'system.caching.CDbCache',
            'connectionID' => 'db',
            'cacheTableName' => 'f_cache',
        ],
        /*'cache' => [
            'class' => 'system.caching.CApcCache',
            'useApcu' => true,
        ],*/

        //UserManager component
        'userManager' => [
            'class' => 'UserManager',

            # send activation email
            'sendActivationMail' => true,

            # allow access for non-activated users
            'allowInactivatedLogin' => false,

            # activate user on registration (only sendActivationMail = false)
            'activateAfterRegister' => false,

            # automatically login from registration
            'autoLogin' => true,

            # registration path
            'registrationUrl' => array('/user/registration'),

            # recovery password path
            'recoveryUrl' => array('/user/recovery'),
        ],
    ],

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => $params,//The param values are defined in params.php

    //Tạm thời hard code timezone. Giá trị này cần được set sau khi user login vào hệ thống, dựa theo
    //setting của từng user khác nhau sẽ có timezone khác nhau
    'timeZone' => 'Asia/Ho_Chi_Minh',
    'language' => 'en_us'
];

if (defined('YII_ENV_DEV') && YII_ENV_DEV) {
    $config['modules']['gii'] =
        [
            'class' => 'system.gii.GiiModule',
            'password' => 'pk_12345',
            // If removed, Gii defaults to localhost only. Edit carefully to taste.
            'ipFilters' => ['127.0.0.1', '::1'],
        ];
}

return $config;