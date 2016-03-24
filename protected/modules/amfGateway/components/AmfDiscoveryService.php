<?php

use fproject\amf\discovery\DiscoveryService;
use fproject\amf\discovery\ClassFindInfo;

/**
 *  This file is part of amfPHP
 *
 * LICENSE
 *
 * This source file is subject to the license that is bundled
 * with this package in the file license.txt.
 * @package Amfphp_Plugins_Discovery
 */

/**
 * Analyses existing services. Warning: if 2 or more services have the same name, t-only one will appear in the returned data,
 * as it is an associative array using the service name as key. 
 * @package Amfphp_Plugins_Discovery
 * @author Ariel Sommeria-Klein
 */
class AmfDiscoveryService extends DiscoveryService{
    /**
     * @param AmfController $controller
     * @param Zend_Amf_Server $server
     */
    public static function setConfiguration($controller, $server)
    {
        $discoveryPath = Yii::getPathOfAlias("application.modules.amfGateway.components.AmfDiscoveryService");
        self::$serviceFolderPaths = [$controller->servicesDir];
        self::$classNames2ClassFindInfo["AmfDiscoveryService"] = new ClassFindInfo($discoveryPath, 'AmfDiscoveryService');
        $server->setClass("AmfDiscoveryService");
    }
}

?>
