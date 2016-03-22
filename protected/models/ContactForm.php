<?php///////////////////////////////////////////////////////////////////////////////// Licensed Source Code - Property of f-project.net//// © Copyright f-project.net 2013. All Rights Reserved.//////////////////////////////////////////////////////////////////////////////////* ****************************************************************************** This class is automatically generated and maintained by Gii.* Do not manually modify it.*******************************************************************************//** * ContactForm class. * ContactForm is the data structure for keeping * contact form data. It is used by the 'contact' action of 'SiteController'. */class ContactForm extends CFormModel{	public $name;	public $email;	public $subject;	public $body;	public $verifyCode;	/**	 * Declares the validation rules.	 */	public function rules()	{		return array(			// name, email, subject and body are required			array('name, email, subject, body', 'required'),			// email has to be a valid email address			array('email', 'email'),			// verifyCode needs to be entered correctly			array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),		);	}	/**	 * Declares customized attribute labels.	 * If not declared here, an attribute would have a label that is	 * the same as its name with the first letter in upper case.	 */	public function attributeLabels()	{		return array(			'verifyCode'=>'Verification Code',		);	}}