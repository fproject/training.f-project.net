<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * This is the abstract base class for all active form classes of this application.
 * It is a customized model based on Yii CActiveForm class.
 * All active form classes of this application should extend from this class.
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class ActiveForm extends CActiveForm
{

	public $disableAjaxValidationAttributes = array();
	
	public function run()
	{
		foreach ($this->attributes as $attr => $item) {
            if (in_array($attr,$this->disableAjaxValidationAttributes))
                $this->attributes[$attr]['enableAjaxValidation'] = false;
		}
		parent::run();
	}
}

