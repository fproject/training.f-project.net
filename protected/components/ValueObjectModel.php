<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2013. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * The abstract base class for all AMF value-object model classes of this application.
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
abstract class ValueObjectModel implements JsonSerializable
{
    /** @var ValueObjectModel[] $_staticInstances */
    private static $_staticInstances = []; // class name => static instance

    /**
     * @var ActiveRecord[] the static AR model for this VO class
     */
    private static $_staticActiveRecords = [];

    /**
     * @var  bool $relationLoading indicate that all query without with() statement will use lazy loading
     */
    protected $relationLoading = false;

    /**
     * @var array $populatedRelations The relations of AR that is populated from VO via populateToAR() method.
     * This is an array of boolean values in following format:
     * ['relation1' => true, 'relation2' => true,...]
     */
    protected $populatedRelations = [];

    /**
     * @var ActiveRecord the AR model for this VO instance
     */
    protected $_activeRecord;

    /**
     * Constructor.
     * @param ActiveRecord $activeRecord the activeRecord dedicated fo this VO
     * @param bool $relationLoading
     */
    public function __construct($activeRecord=null, $relationLoading=false)
    {
        $this->relationLoading = $relationLoading;
        if(!is_null($activeRecord))
        {
            $this->_activeRecord = $activeRecord;
            $this->populateFromAR($activeRecord, $relationLoading);
        }
    }

    /**
     * Returns the static AR model of the specified VO model class.
     * @param ActiveRecord $activeRecord the The AR model for this value-object class
     * @param string|ValueObjectModel $className active record class name.
     * @return ValueObjectModel the static instance of this model
     */
    public static function staticInstance($activeRecord = null, $className = __CLASS__)
    {
        if (isset(self::$_staticInstances[$className]))
            return self::$_staticInstances[$className];
        else {
            if($className !== __CLASS__ && is_null($activeRecord))
                return $className::staticInstance();
            /** @var ValueObjectModel $model */
            $model = self::$_staticInstances[$className] = new $className($activeRecord);
            if(isset($activeRecord))
                self::$_staticActiveRecords[$className] = $activeRecord;
            return $model;
        }
    }

    /**
     * @param string $className
     * @return ActiveRecord the AR model for this VO model
     */
    public static function staticActiveRecord($className = __CLASS__)
    {
        //Create static record instance as needed
        if (!isset(self::$_staticActiveRecords[$className]))
            call_user_func(array($className, 'staticInstance'));

        return self::$_staticActiveRecords[$className];
    }

    /**
     * Find a single model object with the specified condition.
     * @param mixed $condition query condition or criteria.
     * If a string, it is treated as query condition (the WHERE clause);
     * If an array, it is treated as the initial values for constructing a {@link CDbCriteria} object;
     * Otherwise, it should be an instance of {@link CDbCriteria}.
     * @param array $params parameters to be bound to an SQL statement.
     * This is only used when the first parameter is a string (query condition).
     * In other cases, please use {@link CDbCriteria::params} to set parameters.
     * @return ValueObjectModel the model object found, converted to VO type. Null if no record is found.
     */
    public function find($condition = '', $params = [])
    {
        $this->checkRelationLoading($condition);

        $class = get_class($this);
        /** @var $rec ActiveRecord */
        $rec = self::staticActiveRecord($class)->find($condition, $params);

        return $this->recordToModel($rec, $class, $this->relationLoading);
    }

    /**
     * Finds a single active record that has the specified attribute values.
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param array $attributes list of attribute values (indexed by attribute names) that the active records should match.
     * An attribute value can be an array which will be used to generate an IN condition.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return ValueObjectModel the record found. Null if none is found.
     */
    public function findByAttributes($attributes,$condition='',$params=[])
    {
        $this->checkRelationLoading($condition);

        $class = get_class($this);
        /** @var $record ActiveRecord */
        $record = self::staticActiveRecord($class)->findByAttributes($attributes, $condition, $params);
        return $this->recordToModel($record, $class, $this->relationLoading);
    }

    /**
     * Find all data model objects with the specified condition.
     * @param mixed $condition query condition or criteria.
     * If a string, it is treated as query condition (the WHERE clause);
     * If an array, it is treated as the initial values for constructing a {@link CDbCriteria} object;
     * Otherwise, it should be an instance of {@link CDbCriteria}.
     * @param array $params parameters to be bound to an SQL statement.
     * This is only used when the first parameter is a string (query condition).
     * In other cases, please use {@link CDbCriteria::params} to set parameters.
     * @return ValueObjectModel[] the search result models as value-objects.
     */
    public function findAll($condition = '', $params = [])
    {
        $this->checkRelationLoading($condition);

        /** @var $recs ActiveRecord[] */
        $recs = $this->getActiveRecord()->findAll($condition, $params);
        return $this->recordsToModels($recs, $this->relationLoading);
    }

    /**
     * Search and return model objects as value-objects.
     * The search condition is based on properties of current VO model.
     * @param array $attributes list of attribute values (indexed by attribute names)
     * that the active records should match.
     * An attribute value can be an array which will be used to generate an IN condition.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return ValueObjectModel[] the search result models as value-objects.
     * An empty array is returned if none is found.
     */
    public function findAllByAttributes($attributes, $condition = '', $params = [])
    {
        $this->checkRelationLoading($condition);

        /** @var $records ActiveRecord[] */
        $records = $this->getActiveRecord()->findAllByAttributes($attributes, $condition, $params);
        return $this->recordsToModels($records, $this->relationLoading);
    }

    /**
     * Finds a data model object with the specified primary key.
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param mixed $pk primary key value(s). Use array for multiple primary keys.
     * For composite key, each key value must be an array (column name=>column value).
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return ValueObjectModel the model object found. Null if none is found.
     */
    public function findByPk($pk, $condition = '', $params = [])
    {
        $this->checkRelationLoading($condition);

        $class = get_class($this);
        /** @var $rec ActiveRecord */
        $rec = self::staticActiveRecord($class)->findByPk($pk, $condition, $params);
        return $this->recordToModel($rec, $class, $this->relationLoading);
    }

    /**
     * Finds all data models with the specified primary keys.
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param mixed $pk primary key value(s). Use array for multiple primary keys.
     * For composite key, each key value must be an array (column name=>column value).
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return ValueObjectModel[] the search result models as value-objects. An empty array is returned if none is found.
     */
    public function findAllByPk($pk, $condition='', $params=[])
    {
        $this->checkRelationLoading($condition);

        /** @var $records ActiveRecord[] */
        $records = $this->getActiveRecord()->findAllByPk($pk, $condition, $params);
        return $this->recordsToModels($records, $this->relationLoading);
    }

    /**
     * Finds the number of model objects satisfying the specified query condition.
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return string the number of model objects satisfying the specified query condition.
     * Note: type is string to keep max. precision.
     */
    public function count($condition='', $params=[])
    {
        return $this->getActiveRecord()->count($condition, $params);
    }

    /**
     * Finds the number of model objects that have the specified attribute values.
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param array $attributes list of attribute values (indexed by attribute names) that the active records should match.
     * An attribute value can be an array which will be used to generate an IN condition.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return string the number of model objects satisfying the specified query condition.
     * Note: type is string to keep max. precision.
     * @since 1.1.4
     */
    public function countByAttributes($attributes, $condition='', $params=[])
    {
        return $this->getActiveRecord()->countByAttributes($attributes,$condition, $params);
    }

    /**
     * Checks whether there is model object satisfying the specified condition.
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return boolean whether there is row satisfying the specified condition.
     */
    public function exists($condition='', $params=[])
    {
        return $this->getActiveRecord()->exists($condition, $params);
    }

    /**
     * Specifies which related objects should be eagerly loaded.
     * This method takes variable number of parameters. Each parameter specifies
     * the name of a relation or child-relation. For example,
     * <pre>
     * // find all posts together with their author and comments
     * Post::staticInstance()->with('author','comments')->findAll();
     * // find all posts together with their author and the author's profile
     * Post::staticInstance()->with('author','author.profile')->findAll();
     * </pre>
     * The relations should be declared in {@link relations()}.
     *
     * By default, the options specified in {@link relations()} will be used
     * to do relational query. In order to customize the options on the fly,
     * we should pass an array parameter to the with() method. The array keys
     * are relation names, and the array values are the corresponding query options.
     * For example,
     * <pre>
     * Post::staticInstance()->with(array(
     *     'author'=>array('select'=>'id, name'),
     *     'comments'=>array('condition'=>'approved=1', 'order'=>'create_time'),
     * ))->findAll();
     * </pre>
     *
     * @return ValueObjectModel this VO itself.
     */
    public function with()
    {
        $this->relationLoading = true;
        $withParams = func_get_args();
        call_user_func_array(array($this->getActiveRecord(), 'with'), $withParams);
        return $this;
    }

    /**
     * Inserts a row into the table based on this active record attributes.
     * If the table's primary key is auto-incremental and is null before insertion,
     * it will be populated with the actual value after insertion.
     * Note, validation is not performed in this method. You may call {@link validate} to perform the validation.
     * After the record is inserted to DB successfully, its {@link isNewRecord} property will be set false,
     * and its {@link scenario} property will be set to be 'update'.
     * @param array $attributes list of attributes that need to be saved. Defaults to null,
     * meaning all attributes that are loaded from DB will be saved.
     * @return boolean whether the attributes are valid and the record is inserted successfully.
     * @throws CDbException if the row is not a new record
     */
    public function insert($attributes=null)
    {
        $ar = $this->getActiveRecord(true);
        $b = $ar->insert($attributes);

        if($b)
            $this->afterSave($ar, 'add', $attributes);

        return $b;
    }

    /**
     * Insert a list of data to a table.
     * This method could be used to achieve better performance during insertion of the large
     * amount of data into the database table.
     * @param array $data list data to be inserted, each value should be an array in format (column name=>column value).
     * If a key is not a valid column name, the corresponding value will be ignored.
     * @return int number of rows inserted.
     */
    /*public function insertMultiple(array $data)
    {
        return DbHelper::insertMultiple($this->getActiveRecord()->tableName(), $data);
    }*/

    /**
     * Updates records with the specified primary key(s).
     * See {@link find()} for detailed explanation about $condition and $params.
     * Note, the attributes are not checked for safety and validation is NOT performed.
     * @param mixed $pk primary key value(s). Use array for multiple primary keys. For composite key,
     * each key value must be an array (column name=>column value).
     * @param array $attributes list of attributes (name=>$value) to be updated
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return integer the number of rows being updated
     */
    public function updateByPk($pk, $attributes, $condition='', $params=[])
    {
        $logicalDeleting = ($this instanceof \fproject\common\ILogicalDeletableModel && $this->isLogicalDeleting());

        $ar = $this->getActiveRecord();

        if($logicalDeleting)
            $ar->{'optimisticLockColumn'} = null;

        $updateResult = $this->getActiveRecord()->updateByPk($pk, $attributes, $condition, $params);
        if ($logicalDeleting)
        {
            $pk = $this->convertPrimaryKey($pk);
            $this->afterDelete($this->getActiveRecord(), $pk);
        }
        return $updateResult;
    }

    /**
     * Updates records with the specified condition.
     * See {@link find()} for detailed explanation about $condition and $params.
     * Note, the attributes are not checked for safety and no validation is done.
     * @param array $attributes list of attributes (name=>$value) to be updated
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return integer the number of rows being updated
     */
    public function updateAll($attributes, $condition='', $params=[])
    {
        return $this->getActiveRecord()->updateAll($attributes, $condition, $params);
    }

    /**
     * Updates the row represented by this model object.
     * All loaded attributes will be saved to the database.
     * Note, validation is not performed in this method. You may call {@link validate} to perform the validation.
     * @param array $attributes list of attributes that need to be saved. Defaults to null,
     * meaning all attributes that are loaded from DB will be saved.
     * @return boolean whether the update is successful
     * @throws CDbException if the record is new
     */
    public function update($attributes=null)
    {
        $ar = $this->getActiveRecord(true);
        $ar->setIsNewRecord(false);
        $b = $ar->update($attributes);

        if($b)
            $this->afterSave($ar, 'update', $attributes);

        return $b;
    }

    /**
     * Saves the current model.
     *
     * The model is inserted as a row into the database table if its primary key field
     * is null (usually the case when the record is created using the 'new'
     * operator). Otherwise, it will be used to update the corresponding row in the table
     * (usually the case if the record is obtained using one of those 'find' methods.)
     *
     * Validation will be performed before saving the record. If the validation fails,
     * the record will not be saved. You can call {@link getErrors()} to retrieve the
     * validation errors.
     *
     * If the record is saved via insertion, and if its primary key is auto-incremental,
     * the primary key will be populated with the automatically generated value.
     *
     * @param array $attributes list of attributes that need to be saved.
     * If this parameter is null, all attributes that are loaded from DB excepts for relations, will be saved.
     *
     * If value of first element of this array equals to '*', then all attributes of the model
     * that are not relation-attribute will be saved, and all attributes from the second element
     * will be considered as relation names to be saved.
     * For example:
     * ```
     * userService.save(model, ['&#42;', 'userProfile', 'resources'])
     * ```
     *
     * If value of first element does not equal to '*', then all attributes in the list
     * will be saved regardless they are relation or not.
     * For example:
     * ```php
     * userService.save(model, ['name', 'birthDay', 'age'])
     * ```
     *
     * @throws CDbException
     * @return mixed If the record is saved via insertion, and if its primary key is auto-incremental,
     * the primary key will be returned.
     * If the record is saved via updating, it returns a bool value that indicates whether the updating action is success or not.
     */
    public function save($attributes=null)
    {
        $ar = $this->getActiveRecord(true);
        $this->removeTemporaryId($ar);
        $emptyPK = $this->getFirstValueNotSetPK($ar);
        $saveRelNames = [];
        $arRelations = $ar->relations();
        if(is_null($attributes) || $attributes[0] == '*')
        {
            $arAttributes = null;
            if(isset($attributes))
            {
                array_splice($attributes, 0, 1);
                foreach($attributes as $rel)
                {
                    if(array_key_exists($rel, $arRelations) && array_key_exists($rel,$this->populatedRelations) && $this->populatedRelations[$rel])
                    {
                        $saveRelNames[] = $rel;
                    }
                }
            }
        }
        else
        {
            $arAttributes = [];

            foreach($attributes as $attribute)
            {
                if(array_key_exists($attribute, $arRelations))
                {
                    if(array_key_exists($attribute,$this->populatedRelations) && $this->populatedRelations[$attribute])
                    {
                        $saveRelNames[] = $attribute;
                    }
                }
                else
                {
                    $arAttributes[] = $attribute;
                }
            }
        }

        $action = 'add';

        if(isset($arAttributes) && count($arAttributes) == 0)
        {
            $b = true;
        }
        elseif(isset($emptyPK) || ($this instanceof \fproject\common\IUpdatableKeyModel && empty($this->getOldKey())))
        {
            if($ar->insert($arAttributes))
            {
                if(isset($emptyPK))
                    $emptyPkValue = $this->$emptyPK = $ar->$emptyPK;
                $b = true;
            } else
                $b = false;
        }
        else
        {
            if($this instanceof \fproject\common\IUpdatableKeyModel)
            {
                $ar->setOldPrimaryKey($this->getOldKey());
            }
            $ar->setIsNewRecord(false);
            $b = $ar->update($arAttributes);
            $action = 'update';
        }

        foreach($saveRelNames as $relName)
        {
            if(isset($emptyPK))
                $mode = self::SAVE_MODE_INSERT_ALL;
            else
                $mode = self::SAVE_MODE_AUTO;
            $this->saveRelation($relName, null, $mode);
        }

        if($b)
        {
            if(($versionCol = $ar->optimisticLock()) != null)
            {
                $this->$versionCol = $ar->$versionCol;
            }
            $this->afterSave($ar, $action, $attributes);
        }

        return isset($emptyPkValue) ? $emptyPkValue : $b;
    }

    /**
     * Create an instance of AR, populate with field values of this model
     * @param bool $useInstanceRecord if false, the static active record will be used.
     * Otherwise, the instance-dedicated active record will be used.
     * @return ActiveRecord
     */
    public function getActiveRecord($useInstanceRecord = false)
    {
        $class = get_class($this);
        if($useInstanceRecord)
        {
            if(is_null($this->_activeRecord))
            {
                $class = get_class(self::staticActiveRecord($class));
                $this->_activeRecord = new $class();
            }
            $this->populateToAR($this->_activeRecord);
            return $this->_activeRecord;
        } else {
            return self::staticActiveRecord($class);
        }
    }

    /**
     * Populate VO model properties from corresponding AR object.
     * @param ActiveRecord $ar the AR object
     * @param bool $relationLoading specify whether load relation field or not
     * @return void
     */
    protected function populateFromAR($ar, $relationLoading=true)
    {
        $this->relationLoading = false;
    }

    /**
     * Populate VO model properties to corresponding AR object.
     * @param ActiveRecord $ar the AR object
     */
    protected abstract function populateToAR($ar);

    /**
     * Convert an active record to a model
     * @param ActiveRecord $record
     * @param string $className the model class name
     * @param bool $relationLoading specify whether load relation field or not
     * @return ValueObjectModel the model that is constructed from active record
     */
    protected function recordToModel($record, $className = null, $relationLoading=true)
    {
        if(is_null($record))
            return null;
        if(is_null($className))
        {
            $className = get_class($this);
        }

        /** @var $model ValueObjectModel*/
        $model = new $className();
        $model->populateFromAR($record, $relationLoading);
        return $model;
    }

    /**
     * Convert an active array of records to an array of models
     * @param ActiveRecord[] $records
     * @param bool $relationLoading specify whether load relation field or not
     * @return ValueObjectModel[]
     */
    protected function recordsToModels($records, $relationLoading=true)
    {
        if(is_null($records))
            return null;
        $class = get_class($this);
        $models = [];
        foreach ($records as $rec) {
            $models[] = $this->recordToModel($rec, $class, $relationLoading);
        }
        return $models;
    }

    private function checkRelationLoading($condition)
    {
        if($condition instanceof CDbCriteria && isset($condition->with))
            $this->relationLoading = true;
    }

    /**
     * @param ActiveRecord $ar
     */
    private function removeTemporaryId($ar)
    {
        $primaryKey = $ar->getTableSchema()->primaryKey;
        if(is_string($primaryKey))
        {
            $keyVal = $ar->getAttribute($primaryKey);
            if(!is_numeric($keyVal))
                $ar->setAttribute($primaryKey, null);
        }
    }

    /**
     * Return the first PK name that value of corresponding field in this VO is not set
     * @param ActiveRecord $ar
     * @return null|string
     */
    private function getFirstValueNotSetPK($ar)
    {
        $primaryKey = $ar->getTableSchema()->primaryKey;
        if(is_string($primaryKey))
        {
            if(!is_numeric($ar->getAttribute($primaryKey)))
                return $primaryKey;
        }
        elseif(is_array($primaryKey))
        {
            foreach($primaryKey as $pk)
            {
                if (!is_numeric($ar->getAttribute($pk)))
                {
                    return $pk;
                }
            }
        }

        return null;
    }

    /**
     * Return all PK names
     * @param ActiveRecord $ar
     * @return null|array
     */
    private function getAllPKNames($ar)
    {
        $primaryKey = $ar->getTableSchema()->primaryKey;
        if(is_string($primaryKey))
        {
            return [$primaryKey];
        }
        elseif(is_array($primaryKey))
        {
            return $primaryKey;
        }
        return null;
    }

    const SAVE_MODE_AUTO = 0;
    const SAVE_MODE_INSERT_ALL = 1;
    const SAVE_MODE_UPDATE_ALL = 2;

    /**
     * Save a list of data to a table, each row data may be inserted or updated depend on its existence.
     * This method could be used to achieve better performance during insertion/update of the large
     * amount of data to the database table.
     * @param ValueObjectModel[] $models list of models to be saved.
     * If a key is not a valid column name, the corresponding value will be ignored.
     * @param array $attributeNames name list of attributes that need to be update. Defaults to null,
     * meaning all fields of corresponding active record will be saved.
     * This parameter is ignored in the case of insertion
     * @param int $mode the save mode flag.
     * If this flag value is set to 0, any model that have a PK value is NULL will be inserted, otherwise it will be update.
     * If this flag value is set to 1, all models will be inserted regardless to PK values.
     * If this flag value is set to 2, all models will be updated regardless to PK values
     * @param array $returnModels An associative array contains two element:
     * ```php
     *      [
     *      'inserted' => [<array of inserted models with ID populated>],
     *      'updated' => [<array of updated models>]
     *      ]
     * ```
     * @return stdClass An instance of stdClass that has of two fields:
     * - The 'lastId' field is the last model ID (auto-incremental primary key) inserted.
     * - The 'insertCount' is the number of rows inserted.
     * - The 'updateCount' is the number of rows updated.
     */
    public function saveMultiple($models, $attributeNames=null, $mode=self::SAVE_MODE_AUTO, &$returnModels=null)
    {
        if(is_null($models))
            return null;

        $ar = $this->getActiveRecord();
        $pkNames = $this->getAllPKNames($ar);
        $updateModels=[];
        $insertModels = [];
        if($mode==self::SAVE_MODE_INSERT_ALL)
        {
            $insertModels = $models;
        }
        elseif($mode==self::SAVE_MODE_UPDATE_ALL)
        {
            $updateModels = $models;
        }
        else
        {
            foreach ($models as $model)
            {
                if($model instanceof \fproject\common\IUpdatableKeyModel)
                {
                    $inserting = empty($model->getOldKey());
                }
                else
                {
                    $inserting = false;
                    foreach($pkNames as $pkName)
                    {
                        if(!property_exists($model,$pkName) || is_null($model->$pkName) || !is_numeric($model->$pkName))
                        {
                            $inserting = true;
                            if(property_exists($model,$pkName) && !is_numeric($model->$pkName))
                            {
                                $model->$pkName = null;
                            }
                            break;
                        }
                    }
                }

                if($inserting)
                    $insertModels[] = $model;
                else
                    $updateModels[] = $model;
            }
        }

        $returnInfo = new stdClass();

        if(($cnt = count($updateModels)) > 0)
        {
            $columns = $pkNames;
            $columnValues = [];

            if(($versionCol=$ar->optimisticLock()) != null && $ar->hasAttribute($versionCol))
                $columns[] = $versionCol;
            else
                $versionCol = null;

            $data = $this->modelsToDataArray($ar, $updateModels, $attributeNames, $columns, $columnValues, $versionCol);

            DbHelper::updateMultiple($ar->getTableSchema(), $data, $pkNames, count($columnValues) > 0 ? $columnValues : null);

            if($versionCol != null)
            {
                foreach($updateModels as $model)
                {
                    $model->$versionCol = 1 + $model->$versionCol;
                }
            }

            if(isset($returnModels))
                $returnModels['updated'] = $updateModels;
            $returnInfo->updateCount = $cnt;
        }
        if(count($insertModels) > 0)
        {
            $data = $this->modelsToDataArray($ar, $insertModels);
            $insertedCount = DbHelper::insertMultiple($ar->getTableSchema(), $data);
            $lastPk = Yii::app()->db->commandBuilder->getLastInsertID($ar->getTableSchema());

            $returnInfo->lastId = isset($lastPk) ? $lastPk + $insertedCount - 1 : null;
            $returnInfo->insertCount = $insertedCount;

            if(isset($pkNames) && isset($returnInfo->lastId) && isset($returnInfo->insertCount))
            {
                $this->populateIds($insertModels, $pkNames, $returnInfo->lastId, $returnInfo->insertCount);
                if (isset($returnModels))
                    $returnModels['inserted'] = $insertModels;
            }
        }

        $this->afterSave($ar, 'batchSave', $attributeNames, $insertModels, $updateModels);

        return $returnInfo;
    }

    /**
     * Populate auto-increment IDs back to models after batch-inserting
     * @param array $insertModels
     * @param array $pkNames
     * @param mixed $lastPk
     * @param int $insertedCount
     */
    private function populateIds($insertModels, $pkNames, $lastPk, $insertedCount)
    {
        if (is_null($pkNames) || is_null($lastPk) || is_null($insertedCount))
            return;
        while($insertedCount > 0 && $insertedCount <= count($insertModels))
        {
            $insertedCount--;
            foreach($pkNames as $pkName)
            {
                $insertModels[$insertedCount]->$pkName = $lastPk;
            }
            $lastPk = intval($lastPk) - 1;
        }
    }

    /**
     * Remove a set of models
     * @param array $items list of models to be deleted.
     * If a key is not a valid column name, the corresponding value will be ignored.
     * @return int the number of rows deleted
     */
    public function deleteMultiple($items)
    {
        if(is_array($items) && count($items) > 0)
        {
            $pks=[];
            $ar = $this->getActiveRecord();
            foreach($items as $item)
            {
                if(is_array($item) || is_object($item))
                {
                    $pkNames = $this->getAllPKNames($ar);
                    $pk = [];
                    foreach($pkNames as $pkName)
                    {
                        if(is_array($item) && isset($item[$pkName]))
                            $pk[$pkName] = $item[$pkName];
                        elseif(property_exists($item,$pkName))
                            $pk[$pkName] = $item->$pkName;
                    }
                }
                else
                {
                    $pks[] = $item;
                }
            }
            $n = $ar->deleteByPk($pks);
            if($n > 0)
                $this->afterDelete($ar, $pks);
        }
        return 0;
    }

    /**
     * Convert an array of models to an array of data used for {@link saveMultiple()} method
     * @param ActiveRecord $record
     * @param ValueObjectModel[] $models
     * @param array $attributeNames name list of attributes that need to be saved. Defaults to null,
     * meaning all fields of corresponding active record will be saved.
     * @param mixed $pkNames Name or an array of names of primary key(s)
     * @param array $pkValues the output PK values for every models that instance of fproject\common\IUpdatableKeyModel
     * @param null $versionColumn the column used for optimistic locking
     * @return array the converted data
     */
    protected function modelsToDataArray($record, $models, $attributeNames = null,
                                         $pkNames = null, &$pkValues = null,
                                         $versionColumn = null)
    {
        $data = [];

        if(is_null($attributeNames))
        {
            $attributeNames = array_keys($record->attributes);
        }
        else
        {
            if(is_array($pkNames))
            {
                foreach($pkNames as $pk)
                {
                    if(!is_null($pk) && !StringHelper::inArray($pk, $attributeNames, false))
                    {
                        $attributeNames[] = $pk;
                    }
                }
            }
            else if(!is_null($pkNames) && !StringHelper::inArray($pkNames, $attributeNames, false))
            {
                $attributeNames[] = $pkNames;
            }
        }

        $i = 0;

        foreach ($models as $model)
        {
            $item = [];
            $record->unsetAttributes();
            $model->populateToAR($record);
            foreach ($attributeNames as $attName)
            {
                if ($record->hasAttribute($attName))
                {
                    $columnName = $record->getTableSchema()->getColumn($attName)->name;
                    $item[$columnName] = $record->prepareSavingAttribute($attName);
                    if(strcasecmp($versionColumn, $attName) == 0)
                        $item[$columnName] = 1 + $item[$columnName];
                }
            }
            $data[] = $item;
            if(!is_null($pkValues))
            {
                if($model instanceof \fproject\common\IUpdatableKeyModel)
                {
                    $oldKey = $model->getOldKey();
                    if(!empty($oldKey))
                    {
                        $pkValues[$i] = $oldKey;
                    }
                }
            }

            $i++;
        }

        return $data;
    }

    /**
     * Deletes the row corresponding to this active record.
     * @throws CDbException if the record is new
     * @return boolean whether the deletion is successful.
     */
    public function delete()
    {
        $ar = $this->getActiveRecord(true);
        $ar->setIsNewRecord(false);
        if($ar->delete())
        {
            $this->afterDelete($ar);
        }
    }

    /**
     * Deletes rows with the specified primary key.
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param mixed $pk primary key value(s). Use array for multiple primary keys. For composite key, each key value must be an array (column name=>column value).
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return integer the number of rows deleted
     */
    public function deleteByPk($pk,$condition='',$params=[])
    {
        $n = $this->getActiveRecord()->deleteByPk($pk, $condition, $params);
        if($n > 0)
        {
            $pk = $this->convertPrimaryKey($pk);

            $this->afterDelete($this->getActiveRecord(), $pk);
        }
    }

    private function convertPrimaryKey($pk)
    {
        if(!is_array($pk))
        {
            $keyName = (string)$this->_activeRecord->getTableSchema()->primaryKey;
            $pk = [$keyName=>$pk];
        }
        return $pk;
    }

    /**
     * Deletes rows with the specified condition.
     * See {@link find()} for detailed explanation about $condition and $params.
     * @param mixed $condition query condition or criteria.
     * @param array $params parameters to be bound to an SQL statement.
     * @return integer the number of rows deleted
     */
    public function deleteAll($condition='',$params=array())
    {
        $n = $this->getActiveRecord()->deleteAll($condition, $params);
        if($n > 0)
            $this->afterDelete($this->getActiveRecord(), $condition);
    }

    /**
     * Save a relation field of this VO
     * @param string $relationName the name of relation
     * @param array $relationAttributeNames the attribute name of the relation class that will be saved
     * @param int $mode save mode.
     * @return array|bool|null
     */
    public function saveRelation($relationName,$relationAttributeNames=null, $mode=self::SAVE_MODE_AUTO)
    {
        $relation = $this->{$relationName};
        if(isset($relation))
        {
            $ar = $this->getActiveRecord(true);
            $primaryKey = $ar->getPrimaryKey();
            $activeRel = $ar->getActiveRelation($relationName);
            $b = !is_array($primaryKey) && isset($activeRel);

            /** @var ValueObjectModel $relationObject */
            if(is_array($relation))
            {
                if($b)
                {
                    /** @var ValueObjectModel $r */
                    foreach($relation as $r)
                    {
                        if(property_exists($r, $activeRel->foreignKey) && !is_numeric($r->{$activeRel->foreignKey}))
                            $r->{$activeRel->foreignKey} = $primaryKey;
                    }
                }

                $relationObject = $relation[0];
                return $relationObject->saveMultiple($relation, $relationAttributeNames, $mode);
            }
            else
            {
                $relationObject = $relation;
                if(property_exists($relationObject, $activeRel->foreignKey) && !is_numeric($relationObject->{$activeRel->foreignKey}))
                    $relationObject->{$activeRel->foreignKey} = $primaryKey;

                return $relationObject->save($relationAttributeNames);
            }
        }
        return null;
    }

    /**
     * This method is invoked after saving a record successfully.
     * The default implementation does nothing.
     * You may override this method to do postprocessing after record saving.
     *
     * @param ActiveRecord $ar the instance of active record used to execute DB saving
     * @param string $action the action, the possible values: "add", "update", "save"
     * @param array $attributeNames the attributes the saving attribute list
     * @param ValueObjectModel[] $insertModels the array of inserted models
     * @param ValueObjectModel[] $updateModels the array of updated models
     */
    protected function afterSave($ar, $action, $attributeNames=null, $insertModels=null, $updateModels=null)
    {
        if($this->activityNoticeEnabled)
            Yii::app()->activityNoticeManager->noticeAfterModelAction($this, $ar, $action, $attributeNames, $insertModels, $updateModels);
    }

    /**
     * This method is invoked after saving a record successfully.
     * The default implementation does nothing.
     * You may override this method to do postprocessing after record saving.
     *
     * @param ActiveRecord $ar the instance of active record used to execute DB saving
     * @param mixed $deletedData the array of inserted models
     */
    protected function afterDelete($ar, $deletedData=null)
    {
        if($this->activityNoticeEnabled)
            Yii::app()->activityNoticeManager->noticeAfterModelAction($this, $ar, 'delete', null, $deletedData);
    }

    /** @inheritdoc */
    public function jsonSerialize() {
        return $this;
    }

    protected $activityNoticeEnabled = false;

    /**
     * Set value for $activityNoticeDisabled flag
     * @param bool $value the value to of $activityNoticeDisabled flag
     */
    public function setActivityNoticeEnabled($value)
    {
        $this->activityNoticeEnabled = $value;
    }
}