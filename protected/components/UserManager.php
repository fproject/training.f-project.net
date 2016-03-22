<?php
///////////////////////////////////////////////////////////////////////////////
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////

/**
 * Class UserManager.
 */
class UserManager extends CComponent{
    /**
     * @var int
     * @desc items on page
     */
    public $userPageSize = 10;

    /**
     * @var int
     * @desc items on page
     */
    public $fieldsPageSize = 10;

    /**
     * @var boolean
     * @desc use email for activation user account
     */
    public $sendActivationMail=true;

    /**
     * @var boolean
     * @desc allow auth for is not active user
     */
    public $allowInactivatedLogin=false;

    /**
     * @var boolean
     * @desc activate user on registration (only $sendActivationMail = false)
     */
    public $activateAfterRegister=false;

    /**
     * @var boolean
     * @desc login after registration (need allowInactivatedLogin or activateAfterRegister = true)
     */
    public $autoLogin=true;

    public $registrationUrl = array("/user/registration");
    public $recoveryUrl = array("/user/recovery");
    public $profileUrl = array("/user/profile");

    public $fieldsMessage = '';

    /**
     * @var array
     * @desc User model relation from other models
     * @see http://www.yiiframework.com/doc/guide/database.arr
     */
    public $relations = array();

    /**
     * @var array
     * @desc Profile model relation from other models
     */
    public $profileRelations = array();

    /**
     * @var boolean
     */
    //public $cacheEnable = false;

    public $tableUsers = '{{f_user}}';
    public $tableProfiles = '{{f_user_profile}}';
    public $tableProfileFields = '{{f_user_profiles_field}}';

    static private $_usersCache=array();
    static private $_loginUserIsAdmin;
    static private $_allAdmins;

    public function init()
    {
        // this method is called when the module is being created
        // you may place code here to customize the module or the application
    }

    /**
     * Return admin status.
     * @return boolean
     */
    public static function loginUserIsAdmin()
    {
        if(Yii::app()->user->isGuest)
            return false;
        else
        {
            if (!isset(self::$_loginUserIsAdmin))
            {
                if(self::getUser()->isSuperuser)
                    self::$_loginUserIsAdmin = true;
                else
                    self::$_loginUserIsAdmin = false;
            }
            return self::$_loginUserIsAdmin;
        }
    }

    /**
     * Return all admins.
     * @return array Superusers names
     */
    public static function getAllAdmins()
    {
        if (!self::$_allAdmins)
        {
            $admins = User::model()->activated()->superuser()->findAll();
            $return_name = array();
            /** @var User $admin */
            foreach ($admins as $admin)
                array_push($return_name,$admin->username);
            self::$_allAdmins = ($return_name)?$return_name:array('');
        }
        return self::$_allAdmins;
    }


    /**
     * Set a user to memory cache.
     * @param User $user
     */
    public static function setUser($user)
    {
        $user->removeUnsafeAttributes();
        self::$_usersCache[$user->id] = self::$_usersCache[$user->username] = self::$_usersCache[$user->email] = $user;
    }

    /**
     * Return safe user data.
     * @param mixed $idUsernameOrEmail
     * @param bool $clearCache
     * @return User
     */
    public static function getUser($idUsernameOrEmail=null,$clearCache=false)
    {
        if (is_null($idUsernameOrEmail) && !Yii::app()->user->isGuest)
            $idUsernameOrEmail = Yii::app()->user->id;
        if (!is_null($idUsernameOrEmail))
        {
            if (!isset(self::$_usersCache[$idUsernameOrEmail])||$clearCache)
            {
                /** @var User $user */
                $user = User::model()->with('userProfile')->findByIdNameOrEmail($idUsernameOrEmail);
                if(isset($user))
                    self::setUser($user);
            }
            if (isset(self::$_usersCache[$idUsernameOrEmail]))
                return self::$_usersCache[$idUsernameOrEmail];
        }
        return null;
    }

    /**
     * Truy vấn thông tin vể service config cho user.
     * Các user khác nhau của F-Project có thể được cấp quyền truy cập tới các địa chỉ server khác nhau.
     * Tất cả các thông tin về service config được lưu trong thẻ XML <ServiceConfig/>
     * Để xác định destination của một service, cần look-up theo trường XML <Name> của service đó
     * - Nếu tồn tại một phần tử <Service/> có <Name/> như service name (hay còn gọi là service source) thì
     * destination của service là giá trị định nghĩa trong thẻ <Destination/>
     * - Nếu không tồn tại phần tử như thế, destination sẽ lấy theo giá trị của thẻ <DefaultDestination/>
     *
     * Trong ví dụ sau, riêng IssueService sẽ được đặt destination là "issue-blazeds":
     * <ServerConfig>
     *   <DefaultDestination>pk-main</DefaultDestination>
     *   <RemoteObject>
     *     <Name>IssueService</Name>
     *     <Destination>pk-http</Destination>
     *     <RPCImpl>net.fproject.rpc.JSONRemoteObject</RPCImpl>
     *   </RemoteObject>
     * </ServerConfig>
     *
     * @param mixed $userId
     * @return string a text represents an XML fragment of the user service config
     */
    public function getUserServiceConfig($userId)
    {
        $xmlData =
            '<ServerConfig>
                <DefaultDestination>pk-main</DefaultDestination>
                <RemoteObject><Name>IssueService</Name><Destination>pk-issue</Destination></RemoteObject>
            </ServerConfig>';
        return Zend_Xml_Security::scan($xmlData);
    }

    /**
     * @param $password
     * @return string
     */
    public static function generateActivationKey($password)
    {
        return CPasswordHelper::hashPassword(microtime().$password);
    }

    /**
     * @param $key
     * @param $originalKey
     * @return bool
     */
    public static function validateActivationKey($key, $originalKey)
    {
        return $key === $originalKey;
    }

    public static function getUserLastAppUrl()
    {
        /** @var WebUser $user */
        $user = Yii::app()->user;

        if(isset($user->lastApp))
        {
            $appId = $user->lastApp->id;
            return URLHelper::getLastAppUrl($appId);
        }
        return URLHelper::getLastAppUrl();
    }

    public static function setUserLastAppFromRoute($route)
    {
        $appId = URLHelper::getLastAppIdFromRoute($route);
        if(!is_null($appId))
        {
            /** @var WebUser $wUser */
            $wUser = Yii::app()->user;

            if(!isset($wUser->lastApp) || $appId !== $wUser->lastApp->id)
            {
                $user = self::getUser();

                if(!is_null($user))
                {
                    if(!isset($user->jsonData))
                        $user->jsonData = new stdClass();
                    if(!isset($user->jsonData->lastApp))
                        $user->jsonData->lastApp = new stdClass();
                    $user->jsonData->lastApp->id = $appId;
                    $user->save(false, ['jsonData']);
                    if(!isset($wUser->lastApp))
                        $wUser->setState('lastApp', new stdClass);
                    $wUser->lastApp->id = $appId;
                }
            }
        }
    }
} 