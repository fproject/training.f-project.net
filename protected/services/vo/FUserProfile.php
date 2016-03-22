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

 * This is the f-project.net VO class associated with the model "UserProfile".

 */

class FUserProfile extends ValueObjectModel

{

    /**

     * Map the ActionScript class 'UserProfile' to this VO class:

     */

    public $_explicitType = 'FUserProfile';



    /** @var integer $userId */

    public $userId;

    /** @var string $firstName */

    public $firstName;

    /** @var string $lastName */

    public $lastName;

    /** @var FUser $user */

    public $user;



    /**

     * Returns the static model of this VO class.

     * @param UserProfile $activeRecord the The AR model for this VO class

     * @param string $className active record class name.

     * @return FUserProfile the static model class

    */

    public static function staticInstance($activeRecord = null, $className=__CLASS__)

    {

        if (is_null($activeRecord))

            $activeRecord = UserProfile::model();

        return parent::staticInstance($activeRecord, $className);

    }



    /**

     * Populate VO model properties from corresponding AR object.

     * @param UserProfile $ar the AR object

     * @param bool $relationLoading specify whether load relation field or not

     * @return void

     */

    protected function populateFromAR($ar, $relationLoading=true)

    {

        $this->userId = $ar->userId;
        $this->firstName = $ar->firstName;
        $this->lastName = $ar->lastName;


        if($relationLoading)

        {

			if($ar->hasRelated('user'))
				$this->user = FUser::staticInstance()->recordToModel($ar->user, null, true);
			else
				$this->user = null;
        }

        parent::populateFromAR($ar, $relationLoading);

    }



    /**

     * Populate VO model properties from corresponding AR object.

     * @param UserProfile $ar the AR object

     */

    protected function populateToAR($ar)

    {

        $ar->userId = $this->userId;
        $ar->firstName = $this->firstName;
        $ar->lastName = $this->lastName;


    }



    /* ************************************************************************

     *

     * f-project.net implementation properties and methods are after this block

     *

     *********************************************************************** */



}