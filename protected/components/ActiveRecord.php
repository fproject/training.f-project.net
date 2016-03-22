<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2013. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * This is the abstract base class for all AR model classes of this application.
 * It is a customized model based on Yii CActiveRecord class.
 * All model classes of this application should extend from this class.
 * make it available for label displaying in forms and views.
 *
 * @property mixed|string jsonData
 * @property mixed|string createUserId
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */

abstract class ActiveRecord extends CActiveRecord
{
    /**
     * @inheritdoc
     *
     * Override the behaviors() method of CModel class for timestamp audition
     *
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $timestampBehavior = [];

        if($this->hasAttribute('createTime'))
            $timestampBehavior['createAttribute'] = 'createTime';
        elseif($this->hasAttribute('createDate'))
            $timestampBehavior['createAttribute'] = 'createDate';
        else
            $timestampBehavior['createAttribute'] = null;

        if($this->hasAttribute('updateTime'))
            $timestampBehavior['updateAttribute'] = 'updateTime';
        elseif($this->hasAttribute('updateDate'))
            $timestampBehavior['updateAttribute'] = 'updateDate';
        else
            $timestampBehavior['updateAttribute'] = null;

        if(!empty($timestampBehavior) &&
            (isset($timestampBehavior['createAttribute']) || isset($timestampBehavior['updateAttribute'])))
        {
            $timestampBehavior['class'] = 'zii.behaviors.CTimestampBehavior';
            $behaviors = array_merge($behaviors,['CTimestampBehavior' => $timestampBehavior]);
        }

        return $behaviors;
    }


    /**
     * @inheritdoc
     *
     * Convert JSON data to string before execute finding
     *
     */
    protected function beforeFind()
    {
        if ($this->hasAttribute('jsonData') && !is_string($this->jsonData))
            $this->jsonData =json_encode($this->jsonData);
        parent::beforeFind();
    }

    /**
     * @inheritdoc
     *
     * Convert string to JSON data after execute finding
     *
     */
    protected function afterFind()
    {
        if ($this->hasAttribute('jsonData') && !is_null($this->jsonData))
            $this->jsonData =json_decode($this->jsonData);
        parent::afterFind();
    }

    /**
     * @inheritdoc
     *
     * Prepares createUserId and updateUserId and jsonData attributes before saving.
     *
     */
    protected function beforeSave()
    {
        if($this->isNewRecord)
        {
            if ($this->hasAttribute('createUserId'))
                $this->createUserId = $this->prepareSavingAttribute('createUserId');
            if ($this->hasAttribute('updateUserId'))
                $this->createUserId = $this->prepareSavingAttribute('updateUserId');
        }

        if ($this->hasAttribute('jsonData') && !is_string($this->jsonData))
            $this->jsonData = $this->prepareSavingAttribute('jsonData');

        return parent::beforeSave();
    }

    public function prepareSavingAttribute($attributeName)
    {
        if(($attributeName === 'createUserId' || $attributeName === 'updateUserId') && null !== Yii::app()->user)
            return Yii::app()->user->id;
        elseif($attributeName === 'jsonData')
            return json_encode($this->{$attributeName});
        return $this->{$attributeName};
    }

    /**
     * @inheritdoc
     */
    protected function afterSave()
    {
        if ($this->hasAttribute('jsonData') && !is_null($this->jsonData))
        {
            $this->jsonData =json_decode($this->jsonData);
        }

        $timeAuditFields = ['createTime','updateTime','createDate','updateDate'];

        foreach($timeAuditFields as $field)
        {
            if ($this->hasAttribute($field))
            {
                //Sau khi save, tạm lấy thời gian của PHP server điền vào các trường auto-time.
                //Thực tế trong bản ghi CSDL các trường này đã được tự động điền theo giờ của DB server khi thực hiện lệnh save().
                //Ưu điểm là không phải truy vấn lại DB để lấy giá trị, nhược điểm là có sai số.
                $this->setAttribute($field, date(DATE_ISO8601, time()));
            }
        }
        parent::afterSave();
    }

    /**
     * Returns the name of the column that stores the lock version for implementing optimistic locking.
     *
     * Optimistic locking allows multiple users to access the same record for edits and avoids
     * potential conflicts. In case when a user attempts to save the record upon some staled data
     * (because another user has modified the data), a [[StaleObjectException]] exception will be thrown,
     * and the update or deletion is skipped.
     *
     * Optimistic locking is only supported by [[update()]] and [[delete()]].
     *
     * To use Optimistic locking:
     *
     * 1. Create a column to store the version number of each row. The column type should be `BIGINT DEFAULT 0`.
     *    Override this method to return the name of this column.
     * 2. Add a `required` validation rule for the version column to ensure the version value is submitted.
     * 3. In the Web form that collects the user input, add a hidden field that stores
     *    the lock version of the recording being updated.
     * 4. In the controller action that does the data updating, try to catch the [[StaleObjectException]]
     *    and implement necessary business logic (e.g. merging the changes, prompting stated data)
     *    to resolve the conflict.
     *
     * @return string the column name that stores the lock version of a table row.
     * If null is returned (default implemented), optimistic locking will not be supported.
     */
    public function optimisticLock()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function insert($attributes=null)
    {
        $versionColumn = $this->optimisticLock();
        if ($versionColumn !== null && $this->hasAttribute($versionColumn))
            $this->$versionColumn = 0;

        return parent::insert($attributes);
    }

    /**
     * @inheritdoc
     */
    public function deleteByPk($pk, $condition='', $params=array())
    {
        Yii::trace(get_class($this).'.deleteByPk()','system.db.ar.CActiveRecord');
        $builder=$this->getCommandBuilder();
        $criteria=$builder->createPkCriteria($this->getTableSchema(),$pk,$condition,$params);
        $versionColumn = $this->optimisticLock();
        if ($versionColumn !== null) {
            if(is_array($pk))
            {
                $indices = array_keys($pk);
                if($indices[0] == 0)
                    $versionColumn = null;
            }
            if(!$this->hasAttribute($versionColumn))
                $versionColumn = null;
            if($versionColumn !== null)
                $criteria->addColumnCondition([$versionColumn => $this->$versionColumn]);
        }
        $command=$builder->createDeleteCommand($this->getTableSchema(),$criteria);
        $result = $command->execute();
        if ($versionColumn !== null && !$result) {
            throw new StaleObjectException('The object being deleted is outdated.');
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function updateByPk($pk,$attributes,$condition='',$params=array())
    {
        Yii::trace(get_class($this).'.updateByPk()','system.db.ar.CActiveRecord');
        $builder=$this->getCommandBuilder();
        $table=$this->getTableSchema();
        $criteria=$builder->createPkCriteria($table,$pk,$condition,$params);

        $versionColumn = $this->optimisticLock();

        if($versionColumn !== null) {
            if(!$this->hasAttribute($versionColumn))
            {
                $versionColumn = null;
            }
            else
            {
                $criteria->addColumnCondition([$versionColumn => $this->$versionColumn]);
                $attributes[$versionColumn] = $this->$versionColumn + 1;
            }
        }

        $command=$builder->createUpdateCommand($table,$attributes,$criteria);
        $result = $command->execute();
        if($versionColumn !== null) {
            if(!$result)
                throw new StaleObjectException('The object being updated is outdated.');
            else
                $this->$versionColumn = $this->$versionColumn + 1;
        }

        return $result;
    }
}