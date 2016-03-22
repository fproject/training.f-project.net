<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2013. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * The URL helper class
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class URLHelper {
    /**
     * Return URL for lastApp from its ID
     * @param string|null $appId
     * @return string
     */
    public static function getLastAppUrl($appId=null)
    {
        if(!is_null($appId) && isset(Yii::app()->params['lastAppSetting']))
        {
            $lastAppSetting = Yii::app()->params['lastAppSetting'];
            if(is_array($lastAppSetting) && isset($lastAppSetting['apps']) && isset($lastAppSetting['apps'][$appId]))
            {
                $app = $lastAppSetting['apps'][$appId];
                if(isset($app['route']))
                    return Yii::app()->createUrl($app['route']);
            }
        }

        return self::getDefaultLoginSuccessRouteUrl();
    }

    public static function getLastAppIdFromRoute($route)
    {
        if(isset(Yii::app()->params['lastAppSetting']))
        {
            $lastAppSetting = Yii::app()->params['lastAppSetting'];
            if(is_array($lastAppSetting) && isset($lastAppSetting['routeMap']))
            {
                $excludeRoutes = isset($lastAppSetting['excludeRoutes']) ? $lastAppSetting['excludeRoutes'] : [];
                $routeMap = $lastAppSetting['routeMap'];
                if(!in_array($route, $excludeRoutes))
                {
                    if(isset($routeMap[$route]))
                        return $routeMap[$route];
                    elseif(isset($routeMap['*']))
                        return $routeMap['*'];
                }
            }
        }
        return null;
    }

    public static function getDefaultLoginSuccessRouteUrl()
    {
        return Yii::app()->createUrl(Yii::app()->params['defaultLoginSuccessRoute']);
    }

    private static function getActionUrl($route, $params=[])
    {
        return Yii::app()->createAbsoluteUrl($route, $params);
    }
    public static function getActionUrlMap()
    {
        return [
            'logout' => self::getActionUrl('site/logout'),
            'userProfile' => self::getActionUrl('user/profile'),
            'userChangePassword' => self::getActionUrl('user/changePassword'),
            'forum' => self::getActionUrl('forum'),
            'fileDownload' => self::getActionUrl('file/download',['fileId'=>'{0}']),
        ];
    }
}