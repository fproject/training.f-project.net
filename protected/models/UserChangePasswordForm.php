<?php
/**
 * UserChangePasswordForm is the data structure for keeping user change password form data.
 * It is used by the 'changePassword' and 'recovery' actions of UserController.
 */
class UserChangePasswordForm extends CFormModel {
	public $oldPassword;
	public $password;
	public $verifyPassword;
	
	public function rules() {
		return array(
			array('password, verifyPassword', 'required'),
			array('oldPassword, password, verifyPassword', 'length', 'max'=>128, 'min' => 4,'message' => "Incorrect password (minimal length 4 characters)."),
			array('verifyPassword', 'compare', 'compareAttribute'=>'password', 'message' => "Retype Password is incorrect."),
		);
	}

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'oldPassword'=>"Old Password",
			'password'=>"New Password",
			'verifyPassword'=>"Retype New Password",
		);
	}
}