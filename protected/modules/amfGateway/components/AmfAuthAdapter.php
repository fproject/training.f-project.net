<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of ProjectKit.net
//
// © Copyright ProjectKit.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////

require_once 'AmfUserIdentity.php';

use fproject\amf\auth\AuthResult;

/**
 *
 * The AmfAuth class is used for AMF Service authentication.
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class AmfAuthAdapter extends \fproject\amf\auth\AuthAbstract{

    /**
     * Performs an authentication attempt
     *
     * @throws \fproject\amf\AmfException If authentication cannot be performed
     * @return AuthResult
     */
    public function authenticate()
    {
        $identity = new AmfUserIdentity($this->_username, $this->_password);

        if(is_null($this->_username) || $this->_username == '' || ctype_digit(strval($this->_username)))
        {
            $identity->token = $this->_password;
        }

        if($identity->authenticate())
        {
            $this->_username = $identity->username;
            $identity->role = AmfAcl::DEFAULT_LOGIN_ROLE;
            $code = AuthResult::SUCCESS;
            /** @var WebUser $wu */
            $wu = Yii::app()->user;
            $wu->setUsername($this->_username);
            $wu->setSub($identity->sub);
        }
        else
        {
            switch($identity->errorCode)
            {
                case AmfUserIdentity::ERROR_INVALID_TOKEN:
                case AmfUserIdentity::ERROR_PASSWORD_INVALID:
                    $code = AuthResult::FAILURE_CREDENTIAL_INVALID;
                    break;
                case AmfUserIdentity::ERROR_USERNAME_INVALID:
                    $code = AuthResult::FAILURE_IDENTITY_NOT_FOUND;
                    break;
                default:
                    $code = AuthResult::FAILURE;
            }
        }
        return new AuthResult($code, $identity);
    }

    /**
     * The Acl used to control AMF authorization
     * @return AmfAcl
     */
    public function getAcl()
    {
        require_once "AmfAcl.php";
        return AmfAcl::staticInstance();
    }
}