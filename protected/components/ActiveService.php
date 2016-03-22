<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2015. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * ActiveService implements a common set of actions for accessing to a value object model.
 *
 * The model class should be specified via [[modelClass]], which must inherit [[ValueObjectModel]].
 * By default, the following actions are supported:
 *
 * - `find`: find a list of models
 * - `findOne`: return the details of a model
 * - `save`: create/update a new model
 * - `batchSave`: batch create/update an array of models
 * - `remove`: delete an existing model
 * - `batchRemove`: delete an array of existing models
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class ActiveService
{
    /**
     * @var string the model class name. This property must be set.
     */
    protected $modelClass = null;

    /**
     * @var array The condition map contains pre-defined SQL conditions and expand (relation) definition for client query
     * For example:
     * [
     *    'findByUser_condition'=>'name LIKE :userName',
     *    'findByUser_expand_resource'=>['select'=>false,'condition'=>'resource.userId=:userId'],
     * ]
     * The conditionMap also can be created by inline function
     */
    protected $conditionMap = [];

    /**
     * @var array|boolean the page size limits. The 'min' element stands for the minimal page size, and the 'max'
     * for the maximal page size.
     */
    protected $pageSizeLimit = ['min'=>1, 'max'=>50];

    /**
     * Find a VO model instance by ID
     * @param mixed $id primary key value or a composite value of primary key
     * @param mixed $extraParams Additional parameter. For example, this parameter may contains 'expand'
     * key, which indicates the relation fields to be loaded together with this model using ActiveRecord->with(...)
     * @return ValueObjectModel
     * @see ValueObjectModel::findByPk()
     */
    public function findOne($id, $extraParams=null)
    {
        $params = $this->getParams($extraParams);

        $expand = $this->getExpand($extraParams, $params);

        /* @var $modelClass ValueObjectModel */
        $modelClass = $this->getModelClass();
        if(is_object($id))
            $id = (array)$id;

        if(method_exists($this, 'beforeFindOneCallback'))
            $this->{'beforeFindOneCallback'}($id, $extraParams);

        if(is_subclass_of($modelClass, 'fproject\common\ILogicalDeletableModel'))
        {
            $condition = new CDbCriteria();
            /** @var \fproject\common\ILogicalDeletableModel $modelClass */
            $this->setLogicalDeleteCondition($modelClass, $condition);
        }
        else
        {
            $condition = '';
        }

        if(isset($expand))
            $return = $modelClass::staticInstance()->with($expand)->findByPk($id, $condition);
        else
            $return = $modelClass::staticInstance()->findByPk($id, $condition);

        if(method_exists($this, 'afterFindOneCallback'))
            $this->{'afterFindOneCallback'}($id, $extraParams, $return);

        return $return;
    }

    /**
     * Find VO model instances by search criteria and paging information
     * @param mixed $criteria
     * @param mixed $page
     * @param mixed $perPage
     * @param string $sort
     * @param mixed $extraParams Additional parameter. For example, this parameter may contains 'expand'
     * key, which indicates the relation fields to be loaded together with this model using ActiveRecord->with(...)
     * @return PaginationResult
     * @throws CHttpException
     * @see ValueObjectModel::findAll()
     */
    public function find($criteria, $page=null, $perPage=null, $sort=null, $extraParams=null)
    {
        if(is_object($criteria))
            $criteria = (array)$criteria;

        $params = $this->getParams($criteria);
        if(!isset($params))
            $params = $this->getParams($extraParams);

        $conditionKeys = $this->getConditionKeys($criteria);
        if(!isset($conditionKeys))
            $conditionKeys = $this->getConditionKeys($extraParams);

        if(isset($conditionKeys))
        {
            $c = $this->getConditionMapItem($conditionKeys, $params);
            if(isset($c))
            {
                if($c instanceof CDbCriteria)
                    $condition = $c;
                elseif(isset($c['condition']) || isset($c['with']) || isset($c['expand']))
                {
                    if(isset($c['with']))
                    {
                        $with = $c['with'];
                        unset($c['with']);
                    }
                    if(isset($c['expand']))
                    {
                        $with = $c['expand'];
                        unset($c['expand']);
                    }
                    $condition = new CDbCriteria($c);
                    if(isset($with))
                        $condition->mergeWith(['with'=>$with]);
                }
                else
                    $condition = new CDbCriteria(['condition'=>$c]);
            }
            else
            {
                throw new CHttpException(400,"Condition definition(s) not found: ".implode(',', $conditionKeys));
            }
            $callbacks = $this->getFindCallbacks($conditionKeys);
        }

        if(!isset($condition))
            $condition = new CDbCriteria;

        if(isset($params))
        {
            foreach($params as $k=>$v)
            {
                if(!isset($condition->params[$k]) && is_scalar($v))
                {
                    if(strpos($condition->condition,$k) !== false)
                        $condition->params[$k] = $v;
                }
            }
        }

        if(isset($criteria['pagination']))
        {
            $pagination = $criteria['pagination'];
            if(is_object($pagination))
                $pagination = (array)$pagination;
        }
        if(!isset($pagination) || !is_array($pagination))
            $pagination = $criteria;

        if(is_numeric($perPage))
            $perPage = (int)$perPage;
        elseif(isset($pagination['perPage']) && is_numeric($pagination['perPage']))
            $perPage = (int)$pagination['perPage'];
        else
            $perPage = CPagination::DEFAULT_PAGE_SIZE;

        if($perPage < $this->pageSizeLimit['min'])
            $perPage = $this->pageSizeLimit['min'];
        elseif($perPage > $this->pageSizeLimit['max'])
            $perPage = $this->pageSizeLimit['max'];

        $condition->limit = $perPage;

        if(is_numeric($page))
            $page = (int)$page;
        elseif(isset($pagination['page']) && is_numeric($pagination['page']))
            $page = (int)$pagination['page'];
        else
            $page = 1;

        $condition->offset = $perPage * ($page - 1);

        if(isset($criteria['distinct']))
            $condition->distinct = $criteria['distinct'];
        if((!isset($sort) || $sort=='') && isset($criteria['sort']))
            $sort = $criteria['sort'];

        /* @var ValueObjectModel $modelClass */
        $modelClass = $this->getModelClass();

        if(isset($sort) && $sort !== '')
        {
            $sortObj = new Sort($modelClass, ['sort'=>$sort]);
            $condition->order = $sortObj->getOrderBy($condition);
        }

        $expand = $this->getExpand($criteria, $params);
        if(!isset($expand))
            $expand = $this->getExpand($extraParams, $params);

        if(isset($callbacks) && isset($callbacks['before']))
            $this->{$callbacks['before']}($params);

        if(is_subclass_of($modelClass, 'fproject\common\ILogicalDeletableModel'))
        {
            /** @var \fproject\common\ILogicalDeletableModel $modelClass */
            $this->setLogicalDeleteCondition($modelClass, $condition);
        }

        if(isset($expand))
            $items = $modelClass::staticInstance()->with($expand)->findAll($condition);
        else
            $items = $modelClass::staticInstance()->findAll($condition);

        if(isset($callbacks) && isset($callbacks['after']))
            $this->{$callbacks['after']}($params, $items);

        return new PaginationResult($page, $perPage, $items, $modelClass::staticInstance()->count($condition));
    }

    protected function getModelClass()
    {
        if(is_null($this->modelClass))
            throw new Exception('Model class not set.');
        return $this->modelClass;
    }

    protected function convertSource($source)
    {
        if(count($source) == 1)
        {
            $first = reset($source);
            if(is_object($first) || (is_array($first) && !is_string(key($source))))
                $source = (array)$first;
        }
        return $source;
    }

    protected function getParams($source)
    {
        if(isset($source) && is_array($source))
        {
            $source = $this->convertSource($source);
            if(isset($source['params']))
            {
                $params = $source['params'];
                if(is_object($params))
                    $params = (array)$params;
                return $params;
            }
        }
        return null;
    }

    protected function getConditionKeys($source)
    {
        if(isset($source) && is_array($source))
        {
            $source = $this->convertSource($source);
            if(isset($source['condition']))
            {
                $c = $source['condition'];
                $keys = [$c];
                if(substr($c, -9) !== 'Condition')
                    $keys[] = $c.'Condition';
                return $keys;
            }
        }
        return null;
    }

    /**
     * @param $source
     * @param $params
     * @return array|null
     */
    protected function getExpand($source, $params)
    {
        if(is_object($source))
            $source = (array)$source;

        if(isset($source) && is_array($source))
        {
            $source = $this->convertSource($source);
            if(isset($source['expand']))
            {
                $expand = $source['expand'];
                if(!is_null($expand))
                    return $this->convertSource($this->parseExpand($expand, $params));
            }
        }
        return null;
    }

    protected function parseExpand($expand, $params)
    {
        if(is_string($expand))
            $expand = explode(',', $expand);
        if(is_array($expand))
        {
            foreach($expand as $key=>$value)
            {
                if(!is_string($value))
                    unset($expand[$key]);
                else
                    $expand[$key] = $this->getConditionMapItem($value, $params);
            }
            return $this->convertSource($expand);
        }
        return null;
    }

    protected function getConditionMapItem($keys, $params)
    {
        if(!is_array($keys))
            $keys = [$keys];
        foreach($keys as $key)
        {
            if(strlen($key) > 0 && $key[0] == '@')
                $methodName = substr($key, 1);
            if(isset($methodName) && method_exists($this, $methodName))
                return $this->{$methodName}($params);
            elseif(isset($this->conditionMap[$key]))
                return $this->conditionMap[$key];
        }
        return null;
    }

    /**
     * @param \fproject\common\ILogicalDeletableModel $modelClass
     * @param CDbCriteria $condition
     */
    protected function setLogicalDeleteCondition($modelClass, $condition)
    {
        if(is_subclass_of($modelClass, 'fproject\common\ILogicalDeletableModel'))
        {
            /** @var CDbCriteria|array $logicalDeletedCriteria */
            $logicalDeletedCriteria = $modelClass::getIsNotDeletedCriteria();

            if(is_array($logicalDeletedCriteria))
            {
                if(isset($logicalDeletedCriteria['condition']))
                    $ldc = $logicalDeletedCriteria['condition'];
                elseif(count($logicalDeletedCriteria) > 0)
                    $ldc = reset($logicalDeletedCriteria);
                if(!empty($ldc))
                {
                    $condition->addCondition($ldc);
                }
                $ldp = $this->getParams($logicalDeletedCriteria);
                if(!empty($ldp))
                {
                    $condition->params = array_merge($condition->params, $ldp);
                }
            }
            elseif($logicalDeletedCriteria instanceof CDbCriteria)
            {
                $condition->addCondition($logicalDeletedCriteria->condition);
                $condition->params = array_merge($condition->params, $logicalDeletedCriteria->params);
            }
        }
    }

    protected function getFindCallbacks($keys)
    {
        if(!is_array($keys))
            $keys = [$keys];
        $callbacks = [];
        foreach($keys as $key)
        {
            if(strlen($key) > 0 && $key[0] == '@')
                $key = substr($key, 1);
            $l = strlen($key);
            if($l > 9 && strpos($key,'Condition',$l - 9) !== false)
                $key = substr($key, 0, $l - 9);

            $beforeCallbackName = $key."BeforeCallback";
            $afterCallbackName = $key."AfterCallback";

            if(!empty($beforeCallbackName) && method_exists($this, $beforeCallbackName))
                $callbacks['before'] = $beforeCallbackName;
            if(!empty($afterCallbackName) && method_exists($this, $afterCallbackName))
                $callbacks['after'] = $afterCallbackName;
        }
        return $callbacks;
    }

    /**
     * Save a VO model object
     * @param ValueObjectModel $object the VO to be saved
     * @param array $attributes list of attributes that need to be saved.
     * If this parameter is null, all attributes that are loaded from DB excepts for relations, will be saved.
     * @return mixed
     * @see ValueObjectModel::save()
     */
    public function save($object,$attributes=null)
    {
        if(method_exists($this, 'beforeSaveCallback'))
            $this->{'beforeSaveCallback'}($object, $attributes);

        $return = $object->save($attributes);

        if(method_exists($this, 'afterSaveCallback'))
            $this->{'afterSaveCallback'}($object, $attributes, $return);

        return $return;
    }

    /**
     * Save an array of VO objects
     * @param array $objects an array of records to be saved
     * @param array $attributes list of attributes that need to be saved.
     * If this parameter is null, all attributes that are loaded from DB excepts for relations, will be saved.
     * @return array the result object represents batch-saving information
     * @see ValueObjectModel::saveMultiple()
     */
    public function batchSave($objects,$attributes=null)
    {
        /* @var $modelClass ValueObjectModel */
        $modelClass = $this->getModelClass();

        if(method_exists($this, 'beforeBatchSaveCallback'))
            $this->{'beforeBatchSaveCallback'}($objects, $attributes);

        $return = $modelClass::staticInstance()->saveMultiple($objects, $attributes);

        if(method_exists($this, 'afterBatchSaveCallback'))
            $this->{'afterBatchSaveCallback'}($objects, $attributes, $return);

        return $return;
    }

    /**
     * Delete an array of records
     * @param array $items an array of records or ID of records to be deleted
     * @return int
     * @see ValueObjectModel::deleteByPk()
     */
    public function batchRemove($items)
    {
        $pks = [];

        if(is_array($items))
        {
            foreach($items as $key=>$item)
            {
                if($item instanceof ValueObjectModel)
                {
                    $s = $item->getActiveRecord()->getTableSchema()->primaryKey;
                    if(is_string($s))
                    {
                        $pkNames = [$s];
                    }
                    else
                        $pkNames = $s;

                    $pk = [];
                    $isPkOk = true;
                    foreach ($pkNames as $pkName)
                    {
                        if(property_exists($item, $pkName))
                        {
                            $pk[$pkName] = $item->{$pkName};
                        }
                        else
                        {
                            $isPkOk = false;
                            Yii::trace("Primary Key '".$pkName."' does not exist.");
                        }
                    }

                    if($isPkOk)
                        $pks[] = is_string($s) ? $pk[$s] : $pk;
                }
                elseif(is_string($item))
                {
                    $pks[] = $item;
                }
                else
                {
                    if(is_object($item))
                        $item = (array)$item;
                    $pks[] = $item;
                }
            }
        }

        if(empty($pks))
            return 0;

        /* @var $modelClass ValueObjectModel */
        $modelClass = $this->getModelClass();

        if(method_exists($this, 'beforeBatchRemoveCallback'))
            $this->{'beforeBatchRemoveCallback'}($items);

        $model = $modelClass::staticInstance();

        if($model instanceof \fproject\common\ILogicalDeletableModel)
        {
            if($model->isLogicalDeletable())
                $return = $model->logicalDeleteByPk($pks);
        }

        if(!isset($return))
        {
            $return = $model->deleteByPk($pks);
        }

        //$return = $modelClass::staticInstance()->deleteByPk($items);

        if(method_exists($this, 'afterBatchRemoveCallback'))
            $this->{'afterBatchRemoveCallback'}($items, $return);

        return $return;
    }

    /**
     * @param mixed $id
     * @return int
     * @see ValueObjectModel::deleteByPk()
     */
    public function remove($id)
    {
        if(is_object($id))
            $id = (array)$id;
        /* @var $modelClass ValueObjectModel */
        $modelClass = $this->getModelClass();

        if(method_exists($this, 'beforeRemoveCallback'))
            $this->{'beforeRemoveCallback'}($id);

        $model = $modelClass::staticInstance();

        if($model instanceof \fproject\common\ILogicalDeletableModel)
        {
            /** @var \fproject\common\ILogicalDeletableModel $ldm */
            $ldm = $model;
            if($ldm->isLogicalDeletable())
                $return = $ldm->logicalDeleteByPk($id);
        }

        if(!isset($return))
        {
            $return = $model->deleteByPk($id);
        }

        if(method_exists($this, 'afterRemoveCallback'))
            $this->{'afterRemoveCallback'}($id, $return);

        return $return;
    }

    /**
     * Default condition to find all models by a list of primary keys
     * Use '@findAllByIdsCondition' as the key for client-side condition.
     * Note: this condition cannot be used with composite primary key
     * @param $params
     * @return CDbCriteria
     * @throws Exception
     */
    protected function findAllByIdsCondition($params)
    {
        /* @var $modelClass ValueObjectModel */
        $modelClass = $this->getModelClass();
        $pk = $modelClass::staticInstance()->getActiveRecord()->primaryKey;
        if(is_array($pk))
            throw new Exception("Cannot use '@findAllByIdsCondition' with composite primary key");

        $condition = new CDbCriteria;
        $condition->addInCondition($pk, $params[':ids']);
        return $condition;
    }

    private static $_staticInstances = []; // class name => static instance

    /**
     * Returns the static instance of this Service class.
     * @param string $className active service class name that extends this class.
     * @return ActiveService the static model class
     */
    public static function staticInstance($className=__CLASS__)
    {
        if (isset(self::$_staticInstances[$className]))
            return self::$_staticInstances[$className];
        else {
            /** @var ActiveService $className */
            if($className !== __CLASS__)
                return $className::staticInstance();
            /**
             * @var ActiveService $service
             * @var string $className
             *
             * */
            $service = self::$_staticInstances[$className] = new $className();
            return $service;
        }
    }
}