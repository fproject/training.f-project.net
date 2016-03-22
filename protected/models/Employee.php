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

 * This is the model class for table "employee".

 *

 * The followings are the available columns in table 'employee':

 * @property integer $id
 * @property string $name
 * @property integer $age
 * @property integer $gender
 */

class Employee extends ActiveRecord
{

	/**

	 * @return string the associated database table name

	 */

	public function tableName()

	{

		return 'employee';

	}



	/**

	 * @return array validation rules for model attributes.

	 */

	public function rules()

	{

		// @todo You should only define rules for those attributes that

		// will receive user inputs.

		return array(

			array('id, name', 'required'),
			array('id, age, gender', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
			// The following rule is used by search().

			// @todo Please remove those attributes that should not be searched.

			array('id, name, age, gender', 'safe', 'on'=>'search'),

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

		);

	}



	/**

	 * @return array customized attribute labels (name=>label)

	 */

	public function attributeLabels()

	{

		return array(

			'id' => 'ID',
			'name' => 'Name',
			'age' => 'Age',
			'gender' => 'Gender',
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



		$criteria->compare('id', $this->id);
		$criteria->compare('name', $this->name,true);
		$criteria->compare('age', $this->age);
		$criteria->compare('gender', $this->gender);


		return new CActiveDataProvider($this, array(

			'criteria'=>$criteria,

		));

	}



    /**

	 * Returns the static model of the specified AR class.

	 * Please note that you should have this exact method in all your CActiveRecord descendants!

	 * @param string $className active record class name.

	 * @return Employee the static model class

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





}