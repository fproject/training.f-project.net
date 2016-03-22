<?php
///////////////////////////////////////////////////////////////////////////////

//

// Licensed Source Code - Property of f-project.net

//

// © Copyright f-project.net 2014. All Rights Reserved.

//

///////////////////////////////////////////////////////////////////////////////

/* ****************************************************************************

 *

 * This class is automatically generated and maintained by Gii.

 * Do not manually modify any line of the generated code.

 *

 * Your additional properties and methods must be placed at the bottom of

 * this class.

 *

 *****************************************************************************/

/**

 * This is the model class for table "f_user".

 *

 * The followings are the available columns in table 'f_user':

 * @property integer $id
 * @property string $userName
 * @property string $displayName
 * @property string $email
 * @property string $password
 * @property string $activateKey
 * @property integer $status
 * @property integer $isSuperuser
 * @property string $lastLoginTime
 * @property string $createTime
 * @property integer $createUserId
 * @property string $updateTime
 * @property integer $updateUserId
 *

 * The followings are the available model relations:

 * @property AppContextData[] $loginUserAppContextDatas
 * @property CalendarItem[] $userCalendarItems
 * @property Gcm[] $userGcms
 * @property Issue[] $requesterIssues
 * @property Issue[] $ownerIssues
 * @property PersonalCalendar[] $userPersonalCalendars
 * @property Post[] $createUserPosts
 * @property Post[] $targetUserPosts
 * @property Post[] $updateUserPosts
 * @property Resource[] $userResources
 * @property UserProfile $userProfile
 * @property ProjectResourceAssignment[] $projectAssignments
 */

class User extends ActiveRecord
{

	/**

	 * @return string the associated database table name

	 */

	public function tableName()

	{

		return 'f_user';

	}



	/**

	 * @return array validation rules for model attributes.

	 */

	public function rules()

	{

		// @todo You should only define rules for those attributes that

		// will receive user inputs.

		return array(

			array('userName, displayName, email, password', 'required'),
			array('status, isSuperuser, createUserId, updateUserId', 'numerical', 'integerOnly'=>true),
			array('userName, displayName, email, password, activateKey', 'length', 'max'=>255),
			array('createTime, updateTime', 'safe'),
			// The following rules are f-project.net custom rules defined by using Gii.

			array('userName', 'unique', 'message' => "This user's name already exists."),
			array('userName',  'match',  'pattern' => '/^[A-Za-z0-9_]+$/u', 'message' => "Incorrect symbols (A-z0-9)."),
			array('userName', 'length', 'min' => 3,'message' => "Incorrect username (length between 3 and 255 characters)."),
			array('email', 'email'),
			array('email', 'unique', 'message' => "This user's email address already exists."),
			// The following rule is used by search().

			// @todo Please remove those attributes that should not be searched.

			array('userName, displayName, email, activateKey, status, isSuperuser, lastLoginTime, createTime, createUserId, updateTime, updateUserId', 'safe', 'on'=>'search'),

		);

	}



	/**

	 * @return array relational rules.

	 */

	public function relations()

	{

		// @todo You may need to adjust the relation name and the related

		// class name for the relations automatically generated below.

		return array(

			'loginUserAppContextDatas' => array(self::HAS_MANY, 'AppContextData', 'loginUserId'),
			'userCalendarItems' => array(self::HAS_MANY, 'CalendarItem', 'userId'),
			'userGcms' => array(self::HAS_MANY, 'Gcm', 'userId'),
			'requesterIssues' => array(self::HAS_MANY, 'Issue', 'requesterId'),
			'ownerIssues' => array(self::HAS_MANY, 'Issue', 'ownerId'),
			'userPersonalCalendars' => array(self::HAS_MANY, 'PersonalCalendar', 'userId'),
			'createUserPosts' => array(self::HAS_MANY, 'Post', 'createUserId'),
			'targetUserPosts' => array(self::HAS_MANY, 'Post', 'targetUserId'),
			'updateUserPosts' => array(self::HAS_MANY, 'Post', 'updateUserId'),
			'userResources' => array(self::HAS_MANY, 'Resource', 'userId'),
			'userProfile' => array(self::HAS_ONE, 'UserProfile', 'userId'),
			'projectAssignments' => array(self::HAS_MANY, 'ProjectResourceAssignment', 'resourceId'),
		);

	}



	/**

	 * @return array customized attribute labels (name=>label)

	 */

	public function attributeLabels()

	{

		return array(

			'id' => 'ID',
			'userName' => 'User Name',
			'displayName' => 'Display Name',
			'email' => 'Email',
			'password' => 'Password',
			'activateKey' => 'Activate Key',
			'status' => 'Status',
			'isSuperuser' => 'Is Superuser',
			'lastLoginTime' => 'Last Login Time',
			'createTime' => 'Create Time',
			'createUserId' => 'Create User',
			'updateTime' => 'Update Time',
			'updateUserId' => 'Update User',
		);

	}



	/**

	 * Retrieves a list of models based on the current search/filter conditions.

	 *

	 * Typical usecase:

	 * - Initialize the model fields with values from filter form.

	 * - Execute this method to get CActiveDataProvider instance which will filter

	 * models according to data in model fields.

	 * - Pass data provider to CGridView, CListView or any similar widget.

	 *

	 * @return CActiveDataProvider the data provider that can return the models

	 * based on the search/filter conditions.

	 */

	public function search()

	{

		// @todo Please modify the following code to remove attributes that should not be searched.



		$criteria=new CDbCriteria;



		$criteria->compare('userName', $this->userName,true);
		$criteria->compare('displayName', $this->displayName,true);
		$criteria->compare('email', $this->email,true);
		$criteria->compare('activateKey', $this->activateKey,true);
		$criteria->compare('status', $this->status);
		$criteria->compare('isSuperuser', $this->isSuperuser);
		$criteria->compare('lastLoginTime', $this->lastLoginTime,true);
		$criteria->compare('createTime', $this->createTime,true);
		$criteria->compare('createUserId', $this->createUserId);
		$criteria->compare('updateTime', $this->updateTime,true);
		$criteria->compare('updateUserId', $this->updateUserId);


		return new CActiveDataProvider($this, array(

			'criteria'=>$criteria,

		));

	}



    /**

	 * Returns the static model of the specified AR class.

	 * Please note that you should have this exact method in all your CActiveRecord descendants!

	 * @param string $className active record class name.

	 * @return User the static model class

	 */

	public static function model($className=__CLASS__)

	{

		return parent::model($className);

	}



	/* ************************************************************************

	 *

	 * f-project.net implementation properties and methods are after this block

	 *

	 *********************************************************************** */





	const STATUS_INACTIVATED=0;

    const STATUS_BANNED=-1;

    const STATUS_ACTIVATED=1;



	/**

     * Apply a hash on the password before we store it in the database

     */

    protected function afterValidate()

    {

        if (!$this->hasErrors())

            $this->password = CPasswordHelper::hashPassword($this->password);

        parent::afterValidate();

    }



    /**

     * Checks if the given password is correct.

     * @param string $password the password to be validated

     * @return boolean whether the password is valid

     */

    public function validatePassword($password)

    {

        //return Bcrypt::password_verify($password, $this->password);

        return CPasswordHelper::verifyPassword($password, $this->password);

    }



    /**

     * Search DB to find a user by account name or email.

     * @param string $nameOrEmail the account userName or email

     * @return User

     */

    public function findByNameOrEmail($nameOrEmail)

    {

        return $this->find('LOWER(userName)=:noe OR LOWER(email)=:noe',array(':noe'=>strtolower($nameOrEmail)));

    }



    public function scopes()

    {

        return array(

            'activated'=>array(

                'condition'=>'status='.self::STATUS_ACTIVATED,

            ),

            'inactivated'=>array(

                'condition'=>'status='.self::STATUS_INACTIVATED,

            ),

            'banned'=>array(

                'condition'=>'status='.self::STATUS_BANNED,

            ),

            'superuser'=>array(

                'condition'=>'isSuperuser=1',

            ),

            'unsafe'=>array(

                'select' => 'id, userName, displayName, password, activateKey, email, createTime, lastLoginTime, isSuperuser, status',

            ),

        );

    }



    public function removeUnsafeAttributes()

    {

        $this->password=null;

        $this->activateKey=null;

    }



    public function defaultScope()

    {

        return array(

            'select' => 'id, userName, displayName, email, createTime, lastLoginTime, isSuperuser, status',

        );

    }



    public static function itemAlias($type,$code=NULL)

    {

        $items = array(

            'UserStatus' => array(

                self::STATUS_INACTIVATED => 'Not activated',

                self::STATUS_ACTIVATED => 'Activated',

                self::STATUS_BANNED => 'Banned',

            ),

            'AdminStatus' => array(

                '0' => 'No',

                '1' => 'Yes',

            ),

        );

        if (isset($code))

            return isset($items[$type][$code]) ? $items[$type][$code] : false;

        else

            return isset($items[$type]) ? $items[$type] : false;

    }



    private $safeAttributes=['id', 'userName', 'displayName', 'email', 'createTime', 'lastLoginTime', 'isSuperuser', 'status'];



    /**

     * Safely update a user

     * @param $runValidation

     * @return bool

     */

    public function safeUpdate($runValidation)

    {

        return $this->save($runValidation, $this->safeAttributes);

    }



    /**

     * Validate this model ignoring unsafe attributes

     * @param bool $clearErrors

     * @return bool

     */

    public function validateIgnoreUnsafe($clearErrors=true)

    {

        return $this->validate($this->safeAttributes);

    }

}