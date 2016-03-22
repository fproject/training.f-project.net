<?php
/**
 * UserRegistrationForm class.
 * UserRegistrationForm is the data structure for keeping
 * user registration form data. It is used by the 'registration' action of 'UserController'.
 */
class UserRegistrationForm extends User {
	public $verifyPassword;
	public $verifyCode;
	
	public function rules() {
		$rules = array(
			array('userName, password, verifyPassword, email', 'required'),
			array('userName', 'length', 'max'=>20, 'min' => 3,'message' => "Incorrect username (length between 3 and 20 characters)."),
			array('password', 'length', 'max'=>128, 'min' => 4,'message' => "Incorrect password (minimal length 4 symbols)."),
			array('email', 'email'),
			array('userName', 'unique', 'message' => "This user's name already exists."),
			array('email', 'unique', 'message' => "This user's email address already exists."),
			array('verifyPassword', 'verifyPasswordValidator', 'message' => "Retype Password is incorrect."),
			array('userName', 'match', 'pattern' => '/^[A-Za-z0-9_]+$/u','message' => "Incorrect symbols (A-z0-9)."),
		);

		if (!(isset($_POST['ajax']) && $_POST['ajax']==='registration-form')) {
			array_push($rules,array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()));
		}

		return $rules;
	}

    public function verifyPasswordValidator($attribute,$params)
    {
        return $this->validatePassword($this->verifyPassword);
    }
}