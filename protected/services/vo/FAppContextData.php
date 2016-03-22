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

 * This is the f-project.net VO class associated with the model "AppContextData".

 */

class FAppContextData extends ValueObjectModel

{

    /**

     * Map the ActionScript class 'AppContextData' to this VO class:

     */

    public $_explicitType = 'FAppContextData';



    /** @var integer $id */

    public $id;

    /** @var SimpleXMLElement $xmlData */

    public $xmlData;

    /** @var FUser $loginUser */

    public $loginUser;



    /**

     * Returns the static model of this VO class.

     * @param AppContextData $activeRecord the The AR model for this VO class

     * @param string $className active record class name.

     * @return FAppContextData the static model class

    */

    public static function staticInstance($activeRecord = null, $className=__CLASS__)

    {

        if (is_null($activeRecord))

            $activeRecord = AppContextData::model();

        return parent::staticInstance($activeRecord, $className);

    }



    /**

     * Populate VO model properties from corresponding AR object.

     * @param AppContextData $ar the AR object

     * @param bool $relationLoading specify whether load relation field or not

     * @return void

     */

    protected function populateFromAR($ar, $relationLoading=true)

    {

        $this->id = $ar->id;
        $this->xmlData = is_null($ar->xmlData) ? null : Zend_Xml_Security::scan($ar->xmlData);


        if($relationLoading)

        {

			if($ar->hasRelated('loginUser'))
				$this->loginUser = FUser::staticInstance()->recordToModel($ar->loginUser, null, true);
			else
				$this->loginUser = null;
        }

        parent::populateFromAR($ar, $relationLoading);

    }



    /**

     * Populate VO model properties from corresponding AR object.

     * @param AppContextData $ar the AR object

     */

    protected function populateToAR($ar)

    {

        $ar->id = $this->id;
        $ar->xmlData = is_null($this->xmlData) ? null : $this->xmlData->saveXML();


        $ar->loginUserId = isset($this->loginUser) ? $this->loginUser->id : null;
    }



    /* ************************************************************************

     *

     * f-project.net implementation properties and methods are after this block

     *

     *********************************************************************** */



}