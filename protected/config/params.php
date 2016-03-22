<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2015. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////

return [
    //DateTime format use for DB
    //'dbDateTimeFormat'=>'Y-m-d H:i:s',

    //The method and password for data encryption
    'encryptionMethod' => 'aes256',

    // this URL is used to redirect after user logged in successfully
    'defaultLoginSuccessRoute' => 'site/index',

    // this is used in contact page
    'adminEmail' => 'admin@projectkit.net',

    'sendMailCallbackUrl' => "",

    //The folder to save attached files using AttachmentService
    'attachmentBaseUrl' => '/fattachments',

    //Last accessed application information
    'lastAppSetting' =>
        [
            'apps' =>
                [
                    'pk-home' => ['route' => 'site/index'],
                ],
            'routeMap' =>
                [
                    '*' => 'pk-home'
                ],
            'excludeRoutes' => ['/', '/index', 'site/', 'site/login', 'site/logout', 'file/download'],
        ],
];