<?php
///////////////////////////////////////////////////////////////////////////////
// Licensed Source Code - Property of ProjectKit.net
//
// © Copyright ProjectKit.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
class DefaultController extends AmfController
{
	public function actionIndex() {
		Yii::import('application.services.vo.*');
		$this->productionMode = Yii::app()->getModule('amfGateway')->productionMode;
        $this->amfDiscoveryEnabled = Yii::app()->getModule('amfGateway')->amfDiscoveryEnabled;
		$this->classMap = Yii::app()->getModule('amfGateway')->classMap;
		$servicesFolder = Yii::getPathOfAlias(
			Yii::app()->getModule('amfGateway')->servicesDirAlias);
		$this->handle($servicesFolder);
		return $servicesFolder;
	}	
}