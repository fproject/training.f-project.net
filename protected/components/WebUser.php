<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////

/**
 *
 * The WebUser class is used for user authentication.
 * @property string $sub
 * @property string $token
 * @property string $email
 * @property string $username
 * @property string $createTime
 * @property string $lastLoginTime
 * @property stdClass $lastApp
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class WebUser extends CWebUser{
    /**
     * @var int the number of seconds in which the token will expire. Default is one day.
     */
    public $tokenExpire=86400;

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->getState('__token');
    }

    public function getRole()
    {
        return $this->getState('__role');
    }

    public function getSub()
    {
        return $this->getState('__sub');
    }

    public function setSub($value)
    {
        $this->setState('__sub', $value);
    }

    public function setUsername($value)
    {
        $this->setState('username', $value);
    }

    /**
     * Logs in a user.
     *
     * The user identity information will be saved in storage that is
     * persistent during the user session. By default, the storage is simply
     * the session storage. If the duration parameter is greater than 0,
     * a cookie will be sent to prepare for cookie-based login in future.
     *
     * Note, you have to set {@link allowAutoLogin} to true
     * if you want to allow user to be authenticated based on the cookie information.
     *
     * @param UserIdentity $identity the user identity (which should already be authenticated)
     * @param integer $duration number of seconds that the user can remain in logged-in status. Defaults to 0, meaning login till the user closes the browser.
     * If greater than 0, cookie-based login will be used. In this case, {@link allowAutoLogin}
     * must be set true, otherwise an exception will be thrown.
     * @return boolean whether the user is logged in
     */
    public function login($identity,$duration=0)
    {
        if(parent::login($identity, $duration))
        {
            $this->setState('__token', $identity->token);
            $this->setState('__sub', $identity->sub);
            return true;
        }
        else
            return false;
    }

    public function afterLogin($fromCookie)
    {
        parent::afterLogin($fromCookie);
        $this->updateSession();
    }

    public function updateSession() {
        /** @var User $user */
        $user = UserManager::getUser($this->id);

        $userAttributes = CMap::mergeArray(
            [
                'email'=>$user->email,
                'username'=>$user->username,
                'createTime'=>$user->createTime,
                'lastLoginTime'=>$user->lastLoginTime,
            ],
            isset($user->userProfile) ? $user->userProfile->getAttributes() :[],
            isset($user->jsonData) && property_exists($user->jsonData, 'lastApp') ? ['lastApp' => $user->jsonData->lastApp] :[]);

        foreach ($userAttributes as $attrName=>$attrValue) {
            $this->setState($attrName,$attrValue);
        }
    }

    public function getAppContext()
    {
        return AppContextData::model()->findByAttributes(array('loginUserId'=>$this->id));
    }
} 