<?php
/**
 * This is the template for generating the VO class of a specified table.
 * @var ModelCode $this: the ModelCode object
 * @var string $tableName: the table name for this class (prefix is already removed if necessary)
 * @var string $modelClass: the model class name
 * @var array $columns: list of table columns (name=>CDbColumnSchema)
 * @var array $labels: list of attribute labels (name=>label)
 * @var array $rules: list of validation rules
 * @var array $relations: list of relations (name=>relation declaration)
 * @var array $voFields: list of VO fields
 * @var bool $isRelationTable: indicates that the current model class is generated for a relation table
 * @var array $userDefinedTypeColumns: list of user-defined columns
 * @var array $derivedExcludeFields: list of fields that are derived excluded
 * @var string $userCode: the user customized code.
 * @var int $overrideRules: Indicates that should use custom rules() function
 * @var array $customRules: list of user-defined validation rules
 * @var array $searchColumns: list of user-defined search columns
 * @var array $connectionId: DB connection's ID
 * @var boolean $useILogicalDeletableModelInterface: whether this class should implement fproject\common\ILogicalDeletableModelInterface
 * @var string $optimisticLockColumn the column used for optimistic locking
 * @var boolean $useOptimisticLock whether the class should implement optimistic locking
 * @var string|array $primaryKey the primary key name(s)
 *
 */
?>
<?php echo "<?php\n"; ?>
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net <?php echo date("Y"); ?>. All Rights Reserved.
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
 * This is the model class for table "<?php echo $tableName; ?>".
 *
 * The followings are the available columns in table '<?php echo $tableName; ?>':
<?php foreach($columns as $column): ?>
 * @property <?php  if(array_key_exists($column->name,$userDefinedTypeColumns) && property_exists($userDefinedTypeColumns[$column->name], 'arType'))
                    {
                        echo $userDefinedTypeColumns[$column->name]->arType.' $'.$column->name."\n";
                    }
                    else
                    {
                        echo $column->type.' $'.$column->name."\n";
                    }
    ?>
<?php endforeach; ?>
<?php if(!empty($relations)): ?>
 *
 * The followings are the available model relations:
<?php foreach($relations as $name=>$relation): ?>
 * @property <?php
	if (preg_match(ModelCode::RELATION_REGEX, $relation, $matches))
    {
        $relationType = $matches[1];
        $relationModel = $matches[2];

        switch($relationType){
            case 'HAS_ONE': {
				$relationType = $relationModel;
				break;
			}
            case 'BELONGS_TO': {
				$relationType = $relationModel;
				break;
			}
            case 'HAS_MANY': {
				$relationType = $relationModel . '[]';
				break;
			}
            case 'MANY_MANY': {
				$relationType = $relationModel . '[]';
				break;
			}
            default: {
				if(array_key_exists($name,$userDefinedTypeColumns) && property_exists($userDefinedTypeColumns[$name], 'arType'))
					$relationType = $userDefinedTypeColumns[$name]->arType;
				else
					$relationType = 'mixed';
				break;
			}
        }

		echo $relationType . ' $' . $name . "\n";
	}
    ?>
<?php endforeach; ?>
<?php endif; ?>
 */
class <?php echo $modelClass; ?> extends <?php echo $this->baseClass."\n"; ?>
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '<?php echo $tableName; ?>';
	}
<?php if($overrideRules == 0):?>

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// @todo You should only define rules for those attributes that
		// will receive user inputs.
		return array(
<?php foreach($rules as $rule): ?>
			<?php echo $rule.",\n"; ?>
<?php endforeach; ?>
<?php if($customRules != []):?>
			// The following rules are f-project.net custom rules defined by using Gii.
<?php foreach($customRules as $rule): ?>
			<?php echo $rule.",\n"; ?>
<?php endforeach; ?>
<?php endif?>
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('<?php echo implode(', ', array_keys($searchColumns)); ?>', 'safe', 'on'=>'search'),
		);
	}
<?php endif?>

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// @todo You may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
<?php foreach($relations as $name=>$relation): ?>
			<?php echo "'$name' => $relation,\n"; ?>
<?php endforeach; ?>
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
<?php foreach($labels as $name=>$label): ?>
			<?php echo "'$name' => '$label',\n"; ?>
<?php endforeach; ?>
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

<?php
foreach($searchColumns as $name=>$column)
{
	if($column->type==='string')
	{
		echo "\t\t\$criteria->compare('$name', \$this->$name,true);\n";
	}
	else
	{
		echo "\t\t\$criteria->compare('$name', \$this->$name);\n";
	}
}
?>

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

<?php if($useOptimisticLock):?>
	public $optimisticLockColumn = '<?php echo $optimisticLockColumn ?>';

	/**
	 * @inheritdoc
	 */
	public function optimisticLock()
	{
		return $this->optimisticLockColumn;
	}

<?php endif?>
<?php if($connectionId!='db'):?>
    /**
	 * @return CDbConnection the database connection used for this class
	 */
    public function getDbConnection()
    {
		return Yii::app()-><?php echo $connectionId ?>;
    }

<?php endif?>
    /**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return <?php echo $modelClass; ?> the static model class
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

<?php if(strcmp($userCode,'')):?>
<?php echo "\r\n\t".$userCode; ?>
<?php endif?>

}