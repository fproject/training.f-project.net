<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2013. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * This is the abstract base class for all controller classes of this application.
 * It is a customized controller based on Yii CController class.
 * All controller classes of this application should extend from this class.
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
abstract class Controller extends CController
{
    /* ************************************************************************
     *
     * Gii Generated properties and methods
     *
     *********************************************************************** */

	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/commonLayout';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

    const MODEL_ID_ISSUE = 0;

    /* ************************************************************************
     *
     * f-project.net implementation properties and methods
     *
     *********************************************************************** */

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return [
            ['allow', // allow everyone to use Captcha controller
                'actions' => ['captcha'],
                'users' => ['*']
            ],
            ['allow', // allow admin user to perform 'admin' and 'delete' actions
                'controllers' => ['user'],
                'actions' => ['registration','activation','recovery'],
                'users' => ['*'],
            ],
            ['allow', // allow authenticated user to open main application
                'controllers' => ['mainApp'],
                'actions' => ['index','open'],
                'users' => ['@'],
            ],
            ['allow', // allow authenticated user to open main application
                'controllers' => ['file'],
                'actions' => ['download','flexResource'],
                'users' => ['@'],
            ],
            ['allow', // allow authenticated user to perform 'create' and 'update' actions
                'controllers' => ['project'],
                'actions' => ['index', 'view'],
                'users' => ['@'],
            ],
            ['allow', // allow authenticated user to perform 'create' and 'update' actions
                'controllers' => ['issue'],
                'actions' => ['index', 'view', 'create', 'update','assignUser'],
                'users' => ['@'],
            ],
            ['allow', // allow admin user to perform 'admin' and 'delete' actions
                'controllers' => ['issue', 'project', 'user', 'rights'],
                'actions' => ['index','view','create','update','admin','delete','install','authItem','profile','assignUser'],
                'users' => ['admin'],
            ],
            ['allow', // allow every user to perform update ther profile, password
                'controllers' => ['user'],
                'actions' => ['profile','update','changePassword'],
                'users' => ['@'],
            ],
            ['deny', // deny all users
                'controllers' => ['mainApp', 'issue', 'project', 'user','rights','file'],
                'users' => ['*'],
            ],
        ];
    }

    /** @inheritdoc */
    public function runAction($action)
    {
        parent::runAction($action);
        UserManager::setUserLastAppFromRoute($this->getUniqueId().'/'.$action->id);
    }

    public function urlFromViewAssets($relativeUrl)
    {
        return Yii::app()->assetManager->getPublishedUrl($this->viewPath.DIRECTORY_SEPARATOR.'assets').'/'.$relativeUrl;
    }

    public function registerViewAssets($cssAndJs=[])
    {
        $publishedUrl = Yii::app()->assetManager->publish($this->viewPath.DIRECTORY_SEPARATOR.'assets');
        if(isset($cssAndJs['css']))
            Yii::app()->clientScript->registerCssFile($publishedUrl.'/'.$cssAndJs['css']);

        if(isset($cssAndJs['js']))
        {
            if(is_array($cssAndJs['js']))
            {
                $url = $cssAndJs['js']['url'];
                $position = isset($cssAndJs['js']['position']) ? $cssAndJs['js']['position'] : CClientScript::POS_END;
            }
            else
            {
                $url = $cssAndJs['js'];
                $position = CClientScript::POS_END;
            }
            Yii::app()->clientScript->registerScriptFile($publishedUrl.'/'.$url, $position);
        }
    }
}