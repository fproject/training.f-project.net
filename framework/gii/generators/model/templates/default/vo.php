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
 * @var string $voUserCode: the user customized code.
 * @var boolean $useILogicalDeletableModelInterface: whether this class should implement fproject\common\ILogicalDeletableModelInterface
 * @var string $optimisticLockColumn the column used for optimistic locking
 * @var boolean $useOptimisticLock whether the class should implement optimistic locking
 * @var string|array $primaryKey the primary key name(s)
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
 * This class is automatically generated and maintained by Gii, be careful
 * when modifying it.
 *
 * Your additional properties and methods should be placed at the bottom of
 * this class.
 *
 *****************************************************************************/
/**
 * This is the f-project.net VO class associated with the model "<?php echo $modelClass; ?>".
 */
class <?php echo 'F'.$modelClass; ?> extends ValueObjectModel<?php
$interfaces = [];
if($this->useILogicalDeletableModelInterface)
    $interfaces[] = '\fproject\common\ILogicalDeletableModel';
if($isRelationTable)
    $interfaces[] = '\fproject\common\IUpdatableKeyModel';
$impl = "\n";
if(count($interfaces) > 0)
    $impl = ' implements '.implode(', ', $interfaces).$impl;

echo $impl; ?>
{
    /**
     * Map the ActionScript class that has alias '<?php echo 'F'.$modelClass; ?>' to this VO class:
     */
    public $_explicitType = '<?php echo 'F'.$modelClass; ?>';

<?php foreach($voFields as $name=>$type): ?>
    /** @var <?php
    if(array_key_exists($name,$userDefinedTypeColumns) && property_exists($userDefinedTypeColumns[$name], 'type'))
        $relationType = $userDefinedTypeColumns[$name]->type;
    else
        $relationType = str_replace('{R}', '', $type);
    echo $relationType.' $'.$name;
    ?> */
    public $<?php echo $name.";\n\n"; ?>
<?php endforeach; ?>
<?php if($isRelationTable && is_array($primaryKey)) :?>
    /**
     * @var mixed $oldKey The old composite primary key
     */
    public $oldKey;

    /**
     * @inheritdoc
     */
    public function getOldKey()
    {
        return (array)$this->oldKey;
    }
<?php endif?>

    /**
     * Returns the static model of this VO class.
     * @param <?php echo $modelClass; ?> $activeRecord the The AR model for this VO class
     * @param string|ValueObjectModel $className active record class name.
     * @return F<?php echo $modelClass; ?> the static model class
    */
    public static function staticInstance($activeRecord = null, $className=__CLASS__)
    {
        if (is_null($activeRecord))
            $activeRecord = <?php echo $modelClass; ?>::model();
        return parent::staticInstance($activeRecord, $className);
    }

    /**
     * Populate VO model properties from corresponding AR object.
     * @param <?php echo $modelClass; ?> $ar the AR object
     * @param bool $relationLoading specify whether load relation field or not
     * @return void
     */
    protected function populateFromAR($ar, $relationLoading=true)
    {
<?php
    $keyValues = [];
    if($isRelationTable && is_array($primaryKey))
    {
        foreach ($primaryKey as $name)
        {
            $keyValues[] = "\t\t\t'$name' => \$ar->$name";
        }
        if(count($keyValues) > 0)
        {
            echo "\t\t\$this->oldKey = (object)[\n".implode(",\n", $keyValues)."\n\t\t];\n";
        }
    }?>
<?php foreach($voFields as $name=>$type): ?>
<?php if(substr($type,0, 3) != '{R}') :?>
        $this-><?php echo $name; ?> = <?php
        echo $this->generateArFieldToVoFieldCode($name, $type, $userDefinedTypeColumns) ?>
<?php endif?>
<?php endforeach; ?>

<?php if($this->hasRelation($voFields)) :?>
        if($relationLoading)
        {
<?php foreach($voFields as $name=>$type): ?>
<?php
            if(substr($type,0, 3) == '{R}')
            {
                echo $this->generateArRelationToVoFieldCode($name, $type, $userDefinedTypeColumns, 3);
            }?><?php endforeach; ?>
        }
        parent::populateFromAR($ar, $relationLoading);
<?php endif?>
    }

    /**
     * Populate VO model properties from corresponding AR object.
     * @param <?php echo $modelClass; ?> $ar the AR object
     */
    protected function populateToAR($ar)
    {
<?php foreach($voFields as $name=>$type): ?>
<?php if(substr($type,0, 3) != '{R}') :?>
        <?php
        if(array_key_exists($name,$userDefinedTypeColumns) && property_exists($userDefinedTypeColumns[$name],'toAR'))
        {
            echo '$ar->'.$name.' = is_null($this->'.$name.') ? null : '.$userDefinedTypeColumns[$name]->toAR.";\n";
        }
        else
        {
            echo '$ar->'.$name.' = $this->'.$name.";\n";
        }
        ?>
<?php endif?>
<?php endforeach; ?>

<?php foreach($derivedExcludeFields as $relName=>$relField): ?>
        <?php
        if(array_key_exists($relName, $relations))
        {
            echo "if(!is_null(\$this->$relName) && property_exists(\$this->$relName, 'id'))\n".
                "\t\t{\n\t\t\t\$ar->$relField = \$this->$relName"."->id;\n\t\t\t\$this->populatedRelations['$relName'] = true;\n\t\t}\n";
        }
        else
        {
            echo "\$ar->$relField = isset(\$this->$relName) && property_exists(\$this->$relName, 'id') ? \$this->$relName."."->id : null;\n";
        }
        ?>
<?php endforeach; ?>
    }
<?php

    $autoFields = [];
    foreach($userDefinedTypeColumns as $field)
    {
        if(array_key_exists($field->name, $voFields) && $field->autoValue)
        {
            $autoFields[]= $field;
        }
    }

    if(!empty($autoFields))
    {
        echo "\n\t/** @inheritdoc */\n\tprotected function afterSave(\$ar, \$action, \$attributeNames=null, \$insertModels=null, \$updateModels=null)\n";
        echo "\t{\n";
        echo "\t\tparent::afterSave(\$ar, \$action, \$attributeNames, \$insertModels, \$updateModels);\n";
        foreach($autoFields as $field)
        {
            echo "\t\t\$this->".$field->name." = is_null(\$this->_activeRecord->".$field->name.") ? null : ".str_replace('$ar->','$this->_activeRecord->',$field->fromAR).";\n";
        }

        echo "\t}\n";
    }

    if(!empty($this->dateTimeColumns))
    {
        echo "\n\t/** @inheritdoc */\n\tpublic function jsonSerialize()\n";
        echo "\t{\n";
        echo "\t\t\$a = (array)\$this;\n";
        foreach($this->dateTimeColumns as $colName)
        {
            echo "\t\t\$a['".$colName."'] = is_null(\$this->".$colName.") ? null : \$this->".$colName."->format(DATE_ISO8601);\n";
        }
        echo "\t\treturn \$a;\n";
        echo "\t}\n";
    }
?>

    /* ************************************************************************
     *
     * f-project.net implementation properties and methods are after this block
     *
     *********************************************************************** */
<?php if(strcmp($voUserCode,'')):?>
    <?php echo "\r\n\t".$voUserCode; ?>
<?php endif?>

}