<?php
///////////////////////////////////////////////////////////////////////////////
// Licensed Source Code - Property of ProjectKit.net
//
// © Copyright ProjectKit.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////

Yii::import('application.components.*');
Yii::import('application.vendor.fproject.phpamf.fproject.*');

require_once 'Zend/Loader/Autoloader.php';
spl_autoload_register(array('Zend_Loader_Autoloader', 'autoload'));

require_once 'AmfSessionAuthStorage.php';
require_once 'AmfAuthAdapter.php';
require_once('Zend/Amf/Server.php');

/**
 * Basic Zend AMF server class
 * This class provides the basic structure for creating an AMF controller.
 * To use it you have to extend it and declare the servicesDir in which the
 * AMF services are placed. Then handle should be called. E.g.
 * $this->setServicesDir(
 * 
 * Porting, Coupling & Dependencies:
 * This class is coupled with Zend AMF. To port it copy Zend AMF library
 * directory in the protected/vendors/ directory and this file in the 
 * protected/components/ directory and you are done.
 *
 * @property string $servicesDir
 * @author Vassilis Papapanagiotou
 * @version 0.1
 * @package application.components
 */
abstract class AmfController extends Controller
{
	private $server;
	private $_servicesDir = '';
	private $_productionMode = false;
    private $_amfDiscoveryEnabled = false;

	/**
	 * @var array AMF class map
	 */
	public $classMap = [];

	public function __construct($id,$module=null)
	{
		parent::__construct($id,$module);
		$this->server = new Zend_Amf_Server();
        \fproject\amf\auth\Auth::getInstance()->setStorage(new AmfSessionAuthStorage());
	}

	public function getServicesDir()
	{
		return $this->_servicesDir;
	}

	public function setServicesDir($value)
	{
		if (is_dir($value)) {
			$this->_servicesDir = $value;
		} else {
			throw new CException("servicesDir defined as '{$value}' cannot be found");
		}
	}

	public function getProductionMode()
	{
		return $this->_productionMode;
	}

	public function setProductionMode($value)
	{
		$this->_productionMode = $value;
	}

    /**
     * @param boolean $value
     */
    public function setAmfDiscoveryEnabled($value)
    {
        $this->_amfDiscoveryEnabled = $value;
    }

    /**
     * @return boolean
     */
    public function getAmfDiscoveryEnabled()
    {
        return $this->_amfDiscoveryEnabled;
    }

	public function handle($servicesDir = NULL)
	{
		if ($servicesDir !== NULL) {
			$this->setServicesDir($servicesDir);
		}
		$this->server->addDirectory($this->_servicesDir);
		$this->server->setProduction($this->_productionMode);
        if(!$this->_productionMode && $this->_amfDiscoveryEnabled)
        {
            Yii::import("application.modules.amfGateway.components.AmfDiscoveryService",true);
			AmfDiscoveryService::$excludePaths = ['AmfDiscoveryService'];
			AmfDiscoveryService::$modelFolderPaths = [Yii::getPathOfAlias(Yii::app()->getModule('amfGateway')->voDirAlias)];
            AmfDiscoveryService::setConfiguration($this, $this->server);
        }
		if(!empty($this->classMap))
		{
			require_once('Zend/Amf/Parse/TypeLoader.php');
			Zend_Amf_Parse_TypeLoader::$classMap = array_merge(Zend_Amf_Parse_TypeLoader::$classMap, $this->classMap);
		}
        //$this->server->setAuth(new AmfAuthAdapter());

		echo $this->server->handle();
	}

}