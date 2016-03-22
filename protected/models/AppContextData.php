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

 * This is the model class for table "f_app_context_data".

 *

 * The followings are the available columns in table 'f_app_context_data':

 * @property integer $id
 * @property integer $loginUserId
 * @property string $xmlData
 *

 * The followings are the available model relations:

 * @property User $loginUser
 */

class AppContextData extends ActiveRecord
{

	/**

	 * @return string the associated database table name

	 */

	public function tableName()

	{

		return 'f_app_context_data';

	}



	/**

	 * @return array validation rules for model attributes.

	 */

	public function rules()

	{

		// @todo You should only define rules for those attributes that

		// will receive user inputs.

		return array(

			array('loginUserId', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().

			// @todo Please remove those attributes that should not be searched.

			array('loginUserId, xmlData', 'safe', 'on'=>'search'),

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

			'loginUser' => array(self::BELONGS_TO, 'User', 'loginUserId'),
		);

	}



	/**

	 * @return array customized attribute labels (name=>label)

	 */

	public function attributeLabels()

	{

		return array(

			'id' => 'ID',
			'loginUserId' => 'Login User',
			'xmlData' => 'Xml Data',
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



		$criteria->compare('loginUserId', $this->loginUserId);
		$criteria->compare('xmlData', $this->xmlData,true);


		return new CActiveDataProvider($this, array(

			'criteria'=>$criteria,

		));

	}



    /**

	 * Returns the static model of the specified AR class.

	 * Please note that you should have this exact method in all your CActiveRecord descendants!

	 * @param string $className active record class name.

	 * @return AppContextData the static model class

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





	/**

     * Returns the working project ID of current login user.

     * @return string

     */

    public function getWorkingProjectId()

    {

        if(!is_null($this->xmlData))

        {

            $xml = new SimpleXMLElement($this->xmlData);

            return $xml->WorkingProjectId;

        }

        else

        {

            return null;

        }

    }

}