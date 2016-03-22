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

 * This is the model class for table "f_user_profile_field".

 *

 * The followings are the available columns in table 'f_user_profile_field':

 * @property integer $id
 * @property string $varName
 * @property string $title
 * @property string $fieldType
 * @property integer $fieldSize
 * @property integer $fieldSizeMin
 * @property integer $required
 * @property string $match
 * @property string $errorMessage
 * @property string $otherValidator
 * @property string $range
 * @property string $default
 * @property string $widget
 * @property string $widgetParams
 * @property integer $position
 * @property integer $visible
 */

class UserProfileField extends ActiveRecord
{

	/**

	 * @return string the associated database table name

	 */

	public function tableName()

	{

		return 'f_user_profile_field';

	}



	/**

	 * @return array validation rules for model attributes.

	 */

	public function rules()

	{

		// @todo You should only define rules for those attributes that

		// will receive user inputs.

		return array(

			array('varName, title, fieldType', 'required'),
			array('fieldSize, fieldSizeMin, required, position, visible', 'numerical', 'integerOnly'=>true),
			array('varName, fieldType', 'length', 'max'=>50),
			array('title, match, errorMessage, default, widget', 'length', 'max'=>255),
			array('otherValidator, range, widgetParams', 'safe'),
			// The following rule is used by search().

			// @todo Please remove those attributes that should not be searched.

			array('varName, title, fieldType, fieldSize, fieldSizeMin, required, match, errorMessage, otherValidator, range, default, widget, widgetParams, position, visible', 'safe', 'on'=>'search'),

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
			'varName' => 'Var Name',
			'title' => 'Title',
			'fieldType' => 'Field Type',
			'fieldSize' => 'Field Size',
			'fieldSizeMin' => 'Field Size Min',
			'required' => 'Required',
			'match' => 'Match',
			'errorMessage' => 'Error Message',
			'otherValidator' => 'Other Validator',
			'range' => 'Range',
			'default' => 'Default',
			'widget' => 'Widget',
			'widgetParams' => 'Widget Params',
			'position' => 'Position',
			'visible' => 'Visible',
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



		$criteria->compare('varName', $this->varName,true);
		$criteria->compare('title', $this->title,true);
		$criteria->compare('fieldType', $this->fieldType,true);
		$criteria->compare('fieldSize', $this->fieldSize);
		$criteria->compare('fieldSizeMin', $this->fieldSizeMin);
		$criteria->compare('required', $this->required);
		$criteria->compare('match', $this->match,true);
		$criteria->compare('errorMessage', $this->errorMessage,true);
		$criteria->compare('otherValidator', $this->otherValidator,true);
		$criteria->compare('range', $this->range,true);
		$criteria->compare('default', $this->default,true);
		$criteria->compare('widget', $this->widget,true);
		$criteria->compare('widgetParams', $this->widgetParams,true);
		$criteria->compare('position', $this->position);
		$criteria->compare('visible', $this->visible);


		return new CActiveDataProvider($this, array(

			'criteria'=>$criteria,

		));

	}



    /**

	 * Returns the static model of the specified AR class.

	 * Please note that you should have this exact method in all your CActiveRecord descendants!

	 * @param string $className active record class name.

	 * @return UserProfileField the static model class

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





	const VISIBLE_ALL=3;

    const VISIBLE_REGISTER_USER=2;

    const VISIBLE_ONLY_OWNER=1;

    const VISIBLE_NO=0;



    const REQUIRED_NO = 0;

    const REQUIRED_YES_SHOW_REG = 1;

    const REQUIRED_NO_SHOW_REG = 2;

    const REQUIRED_YES_NOT_SHOW_REG = 3;



    public function scopes()

    {

        return array(

            'forAll'=>array(

                'condition'=>'visible='.self::VISIBLE_ALL,

                'order'=>'position',

            ),

            'forUser'=>array(

                'condition'=>'visible>='.self::VISIBLE_REGISTER_USER,

                'order'=>'position',

            ),

            'forOwner'=>array(

                'condition'=>'visible>='.self::VISIBLE_ONLY_OWNER,

                'order'=>'position',

            ),

            'forRegistration'=>array(

                'condition'=>'required='.self::REQUIRED_NO_SHOW_REG.' OR required='.self::REQUIRED_YES_SHOW_REG,

                'order'=>'position',

            ),

            'sort'=>array(

                'order'=>'position',

            ),

        );

    }



    /**

     * @param $model

     * @return bool formated value (string)

     */

    public function widgetView($model) {

        if ($this->widget && class_exists($this->widget)) {

            $widgetClass = new $this->widget;



            $arr = $this->widgetparams;

            if ($arr) {

                $newParams = $widgetClass->params;

                $arr = (array)CJavaScript::jsonDecode($arr);

                foreach ($arr as $p=>$v) {

                    if (isset($newParams[$p])) $newParams[$p] = $v;

                }

                $widgetClass->params = $newParams;

            }



            if (method_exists($widgetClass,'viewAttribute')) {

                return $widgetClass->viewAttribute($model,$this);

            }

        }

        return false;

    }



    public function widgetEdit($model,$params=array()) {

        if ($this->widget && class_exists($this->widget)) {

            $widgetClass = new $this->widget;



            $arr = $this->widgetparams;

            if ($arr) {

                $newParams = $widgetClass->params;

                $arr = (array)CJavaScript::jsonDecode($arr);

                foreach ($arr as $p=>$v) {

                    if (isset($newParams[$p])) $newParams[$p] = $v;

                }

                $widgetClass->params = $newParams;

            }



            if (method_exists($widgetClass,'editAttribute')) {

                return $widgetClass->editAttribute($model,$this,$params);

            }

        }

        return false;

    }



    public static function itemAlias($type,$code=NULL) {

        $items = array(

            'field_type' => array(

                'INTEGER' => 'INTEGER',

                'VARCHAR' => 'VARCHAR',

                'TEXT'=> 'TEXT',

                'DATE'=> 'DATE',

                'FLOAT'=> 'FLOAT',

                'DECIMAL'=> 'DECIMAL',

                'BOOL'=> 'BOOL',

                'BLOB'=> 'BLOB',

                'BINARY'=> 'BINARY',

            ),

            'required' => array(

                self::REQUIRED_NO => 'No',

                self::REQUIRED_NO_SHOW_REG => 'No, but show on registration form',

                self::REQUIRED_YES_SHOW_REG => 'Yes and show on registration form',

                self::REQUIRED_YES_NOT_SHOW_REG => 'Yes',

            ),

            'visible' => array(

                self::VISIBLE_ALL => 'For all',

                self::VISIBLE_REGISTER_USER => 'Registered users',

                self::VISIBLE_ONLY_OWNER => 'Only owner',

                self::VISIBLE_NO => 'Hidden',

            ),

        );

        if (isset($code))

            return isset($items[$type][$code]) ? $items[$type][$code] : false;

        else

            return isset($items[$type]) ? $items[$type] : false;

    }

}