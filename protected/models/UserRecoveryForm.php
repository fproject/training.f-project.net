<?php

/**
 * UserRecoveryForm class.
 * UserRecoveryForm is the data structure for keeping
 * user recovery form data. It is used by the 'recovery' action of 'UserController'.
 */
class UserRecoveryForm extends CFormModel {
	public $accountNameOrEmail, $userId;
    public $verifyCode;

	/**
	 * Declares the validation rules.
	 * The rules state that userName and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
        $rules = array(
			// userName and password are required
			array('accountNameOrEmail', 'required'),
			array('accountNameOrEmail', 'match', 'pattern' => '/^[A-Za-z0-9@.-\s,]+$/u','message' => "Incorrect symbols (A-z0-9)."),
			// password needs to be authenticated
			array('accountNameOrEmail', 'checkExists'),
		);

        if (!(isset($_POST['ajax']) && $_POST['ajax']==='recovery-form')) {
            array_push($rules,array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()));
        }

        return $rules;
	}
	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'accountNameOrEmail'=>"Username or Email",
		);
	}
	
	public function checkExists($attribute,$params)
    {
		if(!$this->hasErrors())  // we only want to authenticate when no input errors
		{
            $user=User::model()->findByNameOrEmail($this->accountNameOrEmail);
            if ($user)
            {
                $this->userId=$user->id;
            }
            else
            {
                if (strpos($this->accountNameOrEmail,"@"))
                {
                    $this->addError("accountNameOrEmail","Email is incorrect.");
                }
                else
                {
                    $this->addError("accountNameOrEmail","Username is incorrect.");
                }
            }
		}
	}
	
}