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

 * This class is automatically generated and maintained by Gii, be careful

 * when modifying it.

 *

 * Your additional properties and methods should be placed at the bottom of

 * this class.

 *

 *****************************************************************************/

/**

 * This is the f-project.net VO class associated with the model "User".

 */

class FUser extends ValueObjectModel

{

    /**

     * Map the ActionScript class 'User' to this VO class:

     */

    public $_explicitType = 'FUser';



    /** @var integer $id */

    public $id;

    /** @var string $userName */

    public $userName;

    /** @var string $displayName */

    public $displayName;

    /** @var string $email */

    public $email;

    /** @var integer $status */

    public $status;

    /** @var bool $isSuperuser */

    public $isSuperuser;

    /** @var DateTime $lastLoginTime */

    public $lastLoginTime;

    /** @var FPersonalCalendar[] $userPersonalCalendars */

    public $userPersonalCalendars;

    /** @var FUserProfile $userProfile */

    public $userProfile;



    /**

     * Returns the static model of this VO class.

     * @param User $activeRecord the The AR model for this VO class

     * @param string $className active record class name.

     * @return FUser the static model class

    */

    public static function staticInstance($activeRecord = null, $className=__CLASS__)

    {

        if (is_null($activeRecord))

            $activeRecord = User::model();

        return parent::staticInstance($activeRecord, $className);

    }



    /**

     * Populate VO model properties from corresponding AR object.

     * @param User $ar the AR object

     * @param bool $relationLoading specify whether load relation field or not

     * @return void

     */

    protected function populateFromAR($ar, $relationLoading=true)

    {

        $this->id = $ar->id;
        $this->userName = $ar->userName;
        $this->displayName = $ar->displayName;
        $this->email = $ar->email;
        $this->status = $ar->status;
        $this->isSuperuser = (bool)$ar->isSuperuser;
        $this->lastLoginTime = is_null($ar->lastLoginTime) ? null : new DateTime($ar->lastLoginTime);


        if($relationLoading)

        {

			if($ar->hasRelated('userPersonalCalendars'))
				$this->userPersonalCalendars = FPersonalCalendar::staticInstance()->recordsToModels($ar->userPersonalCalendars, true);
			else
				$this->userPersonalCalendars = null;
			if($ar->hasRelated('userProfile'))
				$this->userProfile = FUserProfile::staticInstance()->recordToModel($ar->userProfile, null, true);
			else
				$this->userProfile = null;
        }

        parent::populateFromAR($ar, $relationLoading);

    }



    /**

     * Populate VO model properties from corresponding AR object.

     * @param User $ar the AR object

     */

    protected function populateToAR($ar)

    {

        $ar->id = $this->id;
        $ar->userName = $this->userName;
        $ar->displayName = $this->displayName;
        $ar->email = $this->email;
        $ar->status = $this->status;
        $ar->isSuperuser = $this->isSuperuser;
        $ar->lastLoginTime = is_null($this->lastLoginTime) ? null : $this->lastLoginTime->format(Yii::app()->params['dbDateTimeFormat']);


    }



    /* ************************************************************************

     *

     * f-project.net implementation properties and methods are after this block

     *

     *********************************************************************** */



}