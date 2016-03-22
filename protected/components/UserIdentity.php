<?php
///////////////////////////////////////////////////////////////////////////////
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2013. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
use Firebase\JWT\JWT;
/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 *
 * @property string $sub the OAuth2 sub of logged in user.
 *
 */
class UserIdentity extends CUserIdentity
{
    const ERROR_USER_INACTIVATED=3;
    const ERROR_USER_BANNED=4;
    const ERROR_INVALID_TOKEN=5;
    const ERROR_CANT_LOAD_USER_INFO = 6;

    private $_id;

    /**
     * Get the ID of logged in user.
     * @return int the ID of logged in user
     */
    public function getId()
    {
        return $this->_id;
    }

    private $_sub;

    /**
     * Get the OAuth2 sub of logged in user.
     * @return int the Oauth2 sub of logged in user
     */
    public function getSub()
    {
        return $this->_sub;
    }

    /** @var $token string */
    public $token;

    /**
     * Authenticates a user.
     * In practical applications, this should be changed to authenticate
     * against some persistent user identity storage (e.g. database).
     * @return boolean whether authentication succeeds.
     */
    public function authenticate()
    {
        $this->errorCode = self::ERROR_INVALID_TOKEN;
        if(isset($this->token))
        {
            $payload = $this->verifyToken($this->token);
            if($payload != null)
            {
                $this->errorCode = self::ERROR_CANT_LOAD_USER_INFO;
                try {
                    $duration = property_exists($payload, 'exp') ?
                            JWT::$leeway + $payload->exp - time() : -1;
                    /** @var array $userInfo */
                    $userInfo = Yii::app()->authClient->getUserInfo($this->token, $duration);

                    if(!empty($userInfo) && isset($userInfo['sub']) && isset($userInfo['email']))
                    {
                        $this->_sub = $userInfo['sub'];

                        /** @var User $user */
                        $user = User::model()->findByOauthUserIdOrEmail($userInfo['email']);
                        if($user === null)
                            $user = User::model()->findByOauthUserIdOrEmail($userInfo['sub']);
                        //insert or update user model
                        if ($user === null) {
                            $user = new User();
                            $user->setIsNewRecord(true);
                        }

                        $user->sub = $userInfo['sub'];
                        $user->isSuperuser = isset($user->isSuperuser) ? $user->isSuperuser : 0;
                        $user->status = User::STATUS_ACTIVATED;
                        $user->email = $userInfo['email'];
                        $user->username = $user->displayName = isset($userInfo['name']) ? $userInfo['name'] : $user->email;
                        $user->lastLoginTime = date(DATE_ISO8601, time());
                        $user->save(false);

                        if ($user->status == User::STATUS_INACTIVATED && Yii::app()->userManager->allowInactivatedLogin == false)
                        {
                            $this->errorCode = self::ERROR_USER_INACTIVATED;
                        }
                        else if ($user->status == User::STATUS_BANNED)
                        {
                            $this->errorCode = self::ERROR_USER_BANNED;
                        }
                        else
                        {
                            $this->_id = $user->id;
                            $this->username = $user->username;
                            $this->setState('username', $this->username);

                            Yii::app()->user->setId($user->id); //hard code id

                            UserManager::setUser($user);

                            $this->setState('lastLoginTime', $user->lastLoginTime);

                            $this->errorCode = self::ERROR_NONE;
                            return true;
                        }
                    }
                }
                catch (Exception $e)
                {
                }
            }
        }
        return false;
    }

    /**
     * Verify a JWT issued by OAuth2 server
     * @param string $token
     * @return stdClass the payload data of JWT token
     */
    private function verifyToken($token)
    {
        $payload = null;
        $this->errorCode = self::ERROR_INVALID_TOKEN;
        if(!is_null($token))
        {
            try{
                $payload = Yii::app()->authClient->verifyAndDecodeToken($token);
                $this->errorCode = self::ERROR_NONE;
            }
            catch(Exception $e)
            {
                //Do nothing
            }
        }

        return $payload;
    }
}