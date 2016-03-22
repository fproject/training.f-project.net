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

 * This is the model class for table "f_user_profile".

 *

 * The followings are the available columns in table 'f_user_profile':

 * @property integer $userId
 * @property string $firstName
 * @property string $lastName
 *

 * The followings are the available model relations:

 * @property User $user
 */

class UserProfile extends ActiveRecord
{

	/**

	 * @return string the associated database table name

	 */

	public function tableName()

	{

		return 'f_user_profile';

	}



	/**

	 * @return array relational rules.

	 */

	public function relations()

	{

		// @todo You may need to adjust the relation name and the related

		// class name for the relations automatically generated below.

		return array(

			'user' => array(self::BELONGS_TO, 'User', 'userId'),
		);

	}



	/**

	 * @return array customized attribute labels (name=>label)

	 */

	public function attributeLabels()

	{

		return array(

			'userId' => 'User',
			'firstName' => 'First Name',
			'lastName' => 'Last Name',
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



		$criteria->compare('firstName', $this->firstName,true);
		$criteria->compare('lastName', $this->lastName,true);


		return new CActiveDataProvider($this, array(

			'criteria'=>$criteria,

		));

	}



    /**

	 * Returns the static model of the specified AR class.

	 * Please note that you should have this exact method in all your CActiveRecord descendants!

	 * @param string $className active record class name.

	 * @return UserProfile the static model class

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

	 * @return array validation rules for model attributes.

	 */

	public function rules()

	{

		if (!$this->_rules)

        {

			$required = array();

			$numerical = array();

			$float = array();		

			$decimal = array();

			$rules = array();

			

			$model=$this->getFields();

			

			foreach ($model as $field)

            {

				if ($field->required==UserProfileField::REQUIRED_YES_NOT_SHOW_REG||$field->required==UserProfileField::REQUIRED_YES_SHOW_REG)

					array_push($required,$field->varName);

				if ($field->fieldType=='FLOAT')

					array_push($float,$field->varName);

				if ($field->fieldType=='DECIMAL')

					array_push($decimal,$field->varName);

				if ($field->fieldType=='INTEGER')

					array_push($numerical,$field->varName);

				if ($field->fieldType=='VARCHAR'||$field->fieldType=='TEXT')

                {

					$field_rule = array($field->varName, 'length', 'max'=>$field->fieldSize, 'min' => $field->fieldSizeMin);

					if ($field->errorMessage) $field_rule['message'] = $field->errorMessage;

					array_push($rules,$field_rule);

				}

				if ($field->otherValidator)

                {

					if (strpos($field->otherValidator,'{')===0)

                    {

						$validator = (array)CJavaScript::jsonDecode($field->otherValidator);

						foreach ($validator as $name=>$val)

                        {

							$field_rule = array($field->varName, $name);

							$field_rule = array_merge($field_rule,(array)$validator[$name]);

							if ($field->errorMessage) $field_rule['message'] = $field->errorMessage;

							array_push($rules,$field_rule);

						}

					}

                    else

                    {

						$field_rule = array($field->varName, $field->otherValidator);

						if ($field->errorMessage) $field_rule['message'] = $field->errorMessage;

						array_push($rules,$field_rule);

					}

				}

                elseif ($field->fieldType=='DATE')

                {

					$field_rule = array($field->varName, 'type', 'type' => 'date', 'dateFormat' => 'yyyy-mm-dd', 'allowEmpty'=>true);

					if ($field->errorMessage) $field_rule['message'] = $field->errorMessage;

					array_push($rules,$field_rule);

				}

				if ($field->match)

                {

					$field_rule = array($field->varName, 'match', 'pattern' => $field->match);

					if ($field->errorMessage) $field_rule['message'] = $field->errorMessage;

					array_push($rules,$field_rule);

				}

				if ($field->range)

                {

					$field_rule = array($field->varName, 'in', 'range' => self::rangeRules($field->range));

					if ($field->errorMessage) $field_rule['message'] = $field->errorMessage;

					array_push($rules,$field_rule);

				}

			}

			

			array_push($rules,array(implode(',',$required), 'required'));

			array_push($rules,array(implode(',',$numerical), 'numerical', 'integerOnly'=>true));

			array_push($rules,array(implode(',',$float), 'type', 'type'=>'float'));

			array_push($rules,array(implode(',',$decimal), 'match', 'pattern' => '/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/'));

			$this->_rules = $rules;

		}

		return $this->_rules;

	}



    private $_rules = array();



    /** @var bool Whether current user is being in registration mode */

    public $regMode = false;



	/**

     * Extends setAttributes to handle active date fields

     *

     * @param $values array

     * @param $safeOnly boolean

     */

    public function setAttributes($values,$safeOnly=true)

    {

        foreach ($this->widgetAttributes() as $fieldName=>$className)

        {

            if (isset($values[$fieldName])&&class_exists($className))

            {

                $class = new $className;

                $arr = $this->widgetParams($fieldName);

                if ($arr)

                {

                    $newParams = $class->params;

                    $arr = (array)CJavaScript::jsonDecode($arr);

                    foreach ($arr as $p=>$v)

                    {

                        if (isset($newParams[$p])) $newParams[$p] = $v;

                    }

                    $class->params = $newParams;

                }

                if (method_exists($class,'setAttributes'))

                {

                    $values[$fieldName] = $class->setAttributes($values[$fieldName],$this,$fieldName);

                }

            }

        }

        parent::setAttributes($values,$safeOnly);

    }



    public function widgetParams($fieldName) {

        $data = array();

        $model=$this->getFields();



        foreach ($model as $field)

        {

            if ($field->widget) $data[$field->varName]=$field->widgetparams;

        }

        return $data[$fieldName];

    }



    public function widgetAttributes()

    {

        $data = array();

        $model=$this->getFields();



        foreach ($model as $field)

        {

            if ($field->widget) $data[$field->varName]=$field->widget;

        }

        return $data;

    }



    private $ownerFields;

    private $registrationFields;



    /**

     * @return UserProfileField[]

     */

    public function getFields()

    {

        if ($this->regMode)

        {

            if (!$this->registrationFields)

                $this->registrationFields=UserProfileField::model()->forRegistration()->findAll();

            return $this->registrationFields;

        }

        else

        {

            if (!$this->ownerFields)

                $this->ownerFields=UserProfileField::model()->forOwner()->findAll();

            return $this->ownerFields;

        }

    }



    public static function range($str,$fieldValue=NULL)

    {

        $rules = explode(';',$str);

        $array = array();

        for ($i=0;$i<count($rules);$i++)

        {

            $item = explode("==",$rules[$i]);

            if (isset($item[0]))

                $array[$item[0]] = isset($item[1])? $item[1] : $item[0];

        }

        if (isset($fieldValue))

            if (isset($array[$fieldValue]))

                return $array[$fieldValue];

            else

                return '';

        else

            return $array;

    }



    private function rangeRules($str)

    {

        $rules = explode(';',$str);

        for ($i=0;$i<count($rules);$i++)

            $rules[$i] = current(explode("==",$rules[$i]));

        return $rules;

    }

}