<?php
/**
 * CFlexWidget class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CFlexWidget embeds a Flex 3.x application into a page.
 *
 * To use CFlexWidget, set {@link name} to be the Flex application name
 * (without the .swf suffix), and set {@link baseUrl} to be URL (without the ending slash)
 * of the directory containing the SWF file of the Flex application.
 *
 * @property string $flashVarsAsString The flash parameter string.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>, Bui Sy Nguyen <nguyenbs@gmail.com>
 * @package system.web.widgets
 * @since 1.0
 */
class CFlexWidget extends CWidget
{
	/**
	 * @var string name of the Flex application.
	 * This should be the SWF file name without the ".swf" suffix.
	 */
	public $name;
	/**
	 * @var string the base URL of the Flex application.
	 * This refers to the URL of the directory containing the SWF file.
	 */
	public $baseUrl;

    /**
     * @var string the base URL of the Flex modules.
     * This refers to the URL of the directory containing the module SWF file.
     * @since 1.1.14 (f-project.net)
     */
    public $moduleBaseUrl;

    /**
     * @var string the base URL of the Flex RSLs.
     * This refers to the URL of the directory containing the module SWF file.
     * @since 1.1.14 (f-project.net)
     */
    public $rslBaseUrl;

	/**
	 * @var string width of the application region. Defaults to 450.
	 */
	public $width='100%';
	/**
	 * @var string height of the application region. Defaults to 300.
	 */
	public $height='100%';
	/**
	 * @var string quality of the animation. Defaults to 'high'.
	 */
	public $quality='high';
	/**
	 * @var string background color of the application region. Defaults to '#FFFFFF', meaning white.
	 */
	public $bgColor='#FFFFFF';
	/**
	 * @var string align of the application region. Defaults to 'middle'.
	 */
	public $align='middle';
	/**
	 * @var string the access method of the script. Defaults to 'sameDomain'.
	 */
	public $allowScriptAccess='sameDomain';

    /**
     * @var boolean whether to allow running the Flash in full screen mode. Defaults to false.
     * (f-project.net)
     */
    public $allowWsBridge=false;

	/**
	 * @var boolean whether to allow running the Flash in full screen mode. Defaults to false.
	 * @since 1.1.1
	 */
	public $allowFullScreen=false;
    /**
     * @var boolean whether to allow running the Flash in full screen mode. Defaults to false.
     * @since 1.1.14 (f-project.net)
     */
    public $allowFullScreenInteractive=false;
	/**
	 * @var string the HTML content to be displayed if Flash player is not installed.
	 */
	public $altHtmlContent;
	/**
	 * @var boolean whether history should be enabled. Defaults to true.
	 */
	public $enableHistory=true;
    /**
     * @var boolean whether history should be enabled. Defaults to true.
     */
    public $flashVersion='11.3.0';
	/**
	 * @var array parameters to be passed to the Flex application.
	 */
	public $flashVars=array();

	/**
	 * Renders the widget.
	 */
	public function run()
	{
		if(empty($this->name))
			throw new CException(Yii::t('yii','CFlexWidget.name cannot be empty.'));
		if(empty($this->baseUrl))
			throw new CException(Yii::t('yii','CFlexWidget.baseUrl cannot be empty.'));
		if($this->altHtmlContent===null)
			$this->altHtmlContent=Yii::t('yii','This content requires the <a href="http://www.adobe.com/go/getflash/">Adobe Flash Player</a>.');

		$this->registerClientScript();

		$this->render('flexWidget');
	}

	/**
	 * Registers the needed CSS and JavaScript.
	 */
	public function registerClientScript()
	{
        /** @var $cs CClientScript */
		$cs=Yii::app()->getClientScript();

        //20130610: Replaced script file by swfobject.js for displaying Flex 4 and Flash Player 11+
		$cs->registerScriptFile($this->baseUrl.'/swfobject.js');
        if($this->allowWsBridge)
            $cs->registerScriptFile($this->baseUrl.'/ws_bridge.js');

		if($this->enableHistory)
		{
			$cs->registerCssFile($this->baseUrl.'/history/history.css');
			$cs->registerScriptFile($this->baseUrl.'/history/history.js');
		}
	}

	/**
	 * Generates the properly quoted flash parameter string.
	 * @return string the flash parameter string.
	 */
	public function getFlashVarsAsString()
	{
		$params=array();
		foreach($this->flashVars as $k=>$v)
        {
            if(!is_string($v))
                $v = json_encode($v);
            $params[] = $k.':"'.urlencode($v).'"';
        }

        if(!array_key_exists('baseUrl', $this->flashVars))
            $params[] = 'baseUrl:"'.urlencode($this->baseUrl).'"';

        if(!array_key_exists('moduleBaseUrl', $this->flashVars))
            $params[] = 'moduleBaseUrl:"'.urlencode($this->moduleBaseUrl).'"';

        if(!array_key_exists('rslBaseUrl', $this->flashVars))
            $params[] = 'rslBaseUrl:"'.urlencode($this->rslBaseUrl).'"';

        if(!array_key_exists('locale', $this->flashVars))
            $params[] = 'locale:"'.urlencode(Yii::app()->locale->id).'"';

		return implode(',',$params);
	}
}