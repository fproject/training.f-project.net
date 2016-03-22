<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * The File Helper class
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class FileHelper {
    private static $_appBasePath;

    public static function getAppBasePath()
    {
        if(is_null(self::$_appBasePath))
            self::$_appBasePath = dirname(Yii::app()->request->scriptFile);
        return self::$_appBasePath;
    }

    private static $_attachmentBasePath;

    public static function getAttachmentBasePath()
    {
        if(is_null(self::$_attachmentBasePath))
            self::$_attachmentBasePath = self::getAppBasePath().Yii::app()->params['attachmentBaseUrl'];
        return self::$_attachmentBasePath;
    }

    private static $_flexResourceBasePath;

    public static function getFlexResourceBasePath()
    {
        if(is_null(self::$_flexResourceBasePath))
            self::$_flexResourceBasePath = self::getAppBasePath().Yii::app()->params['flexResourceBaseUrl'];
        return self::$_flexResourceBasePath;
    }
} 