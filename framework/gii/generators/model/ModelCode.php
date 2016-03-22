<?php

class ModelCode extends CCodeModel
{
	public $connectionId='db';
	public $tablePrefix;
	public $tableName;
	public $modelClass;
	public $modelPath='application.models';
	public $baseClass='ActiveRecord';//20131124 f-project.net Customized default to 'ActiveRecord'
	public $buildRelations=true;
	public $commentsAsLabels=false;

    //20131124 f-project.net Customized for VO generating
    public $voPath='application.services.vo';

    //20131124 f-project.net Customized for saving sticky bag
    public $stickyBag;

    //20130601 Added (Start) : Implement for f-project.net custom feature
    public $excludeColumns = 'createUserId, updateUserId, createTime, updateTime';
    public $voExcludeFields;
    public $ruleExcludeColumns = 'createUserId, updateUserId, createTime, updateTime, lastLoginTime';
    public $searchExcludeColumns = 'id, lastLoginTime';
    public $userDefinedTypeColumns;
    public $booleanColumns;
    public $customRules;
    public $customRelations;
    public $overrideRules=0;

    public $customLabels;

    public $dateTimeColumns=[];
    //20130601 Added (End)

    public $useILogicalDeletableModelInterface=false;

	public $useOptimisticLock=false;
	public $optimisticLockColumn="version";

	/**
	 * @var array list of candidate relation code. The array are indexed by AR class names and relation names.
	 * Each element represents the code of the one relation in one AR class.
	 */
	protected $relations;

	public function rules()
	{
		return array_merge(parent::rules(), array(
            array('tablePrefix, baseClass, tableName, modelClass, modelPath, connectionId', 'filter', 'filter'=>'trim'),
            array('connectionId, tableName, modelPath, baseClass', 'required'),
            array('tablePrefix, tableName, modelPath', 'match', 'pattern'=>'/^(\w+[\w\.]*|\*?|\w+\.\*)$/', 'message'=>'{attribute} should only contain word characters, dots, and an optional ending asterisk.'),
            array('connectionId', 'validateConnectionId', 'skipOnError'=>true),
            array('tableName', 'validateTableName', 'skipOnError'=>true),
            array('tablePrefix, modelClass', 'match', 'pattern'=>'/^[a-zA-Z_]\w*$/', 'message'=>'{attribute} should only contain word characters.'),
            array('baseClass', 'match', 'pattern'=>'/^[a-zA-Z_][\w\\\\]*$/', 'message'=>'{attribute} should only contain word characters and backslashes.'),
            array('modelPath', 'validateModelPath', 'skipOnError'=>true),
            array('baseClass, modelClass', 'validateReservedWord', 'skipOnError'=>true),
            array('baseClass', 'validateBaseClass', 'skipOnError'=>true),

            //20130602 Modified : : Implement for columns excluding feature
            array('userDefinedTypeColumns,booleanColumns,excludeColumns,voExcludeFields,ruleExcludeColumns,searchExcludeColumns,customRules,customRelations,overrideRules,buildRelations,customLabels,useILogicalDeletableModelInterface,useOptimisticLock,optimisticLockColumn', 'stickyBagValidator'),
			array('connectionId, tablePrefix, modelPath, baseClass, commentsAsLabels,stickyBag', 'sticky'),
		));
	}

	public function attributeLabels()
	{
		return array_merge(parent::attributeLabels(), array(
			'tablePrefix'=>'Table Prefix',
			'tableName'=>'Table Name',
			'modelPath'=>'Model Path',
			'modelClass'=>'Model Class',
			'baseClass'=>'Base Class',
			'buildRelations'=>'Build Relations',
            'commentsAsLabels'=>'Use Column Comments as Attribute Labels',
			'connectionId'=>'Database Connection',

            //20131124 f-project.net Customized for VO generating
            'voPath'=>'VO Path (f-project.net)',
            'userDefinedTypeColumns'=>'Object/User Type Column Definitions (f-project.net)',
            'booleanColumns'=>'Boolean Columns (f-project.net)',
            'excludeColumns'=>'Model Excluded Columns/Relations (f-project.net)',
            'voExcludeFields'=>'VO Excluded Fields (f-project.net)',
            'ruleExcludeColumns'=>'Validation & Rule Excluded Columns (f-project.net)',
            'searchExcludeColumns'=>'Model Search Excluded Columns (f-project.net)',
            'customRules'=>'Custom Validation & Rules (f-project.net)',
            'customRelations'=>'Custom Relations (f-project.net)',
            'customLabels'=>'Custom Column Labels (f-project.net)',
            'overrideRules'=>'Use custom rules() function:',

            'useILogicalDeletableModelInterface'=>'Use ILogicalDeletableModelInterface for VO class',
			'useOptimisticLock'=>'Implement Optimistic Locking using column:',
			'optimisticLockColumn'=>'Optimistic Lock Column',
		));
	}

	public function requiredTemplates()
	{
		return array(
			'model.php',
		);
	}

	public function init()
	{
		if(Yii::app()->{$this->connectionId}===null)
			throw new CHttpException(500,'A valid database connection is required to run this generator.');
		$this->tablePrefix=Yii::app()->{$this->connectionId}->tablePrefix;
		parent::init();
	}

    //20130601 Modified : Implement for columns excluding feature
	public function prepare()
	{
		if(($pos=strrpos($this->tableName,'.'))!==false)
		{
			$schema=substr($this->tableName,0,$pos);
			$tableName=substr($this->tableName,$pos+1);
		}
		else
		{
			$schema='';
			$tableName=$this->tableName;
		}
		if($tableName[strlen($tableName)-1]==='*')
		{
            /** @var CDbTableSchema[] $tables */
			$tables=Yii::app()->{$this->connectionId}->schema->getTables($schema);
			if($this->tablePrefix!='')
			{
				foreach($tables as $i=>$table)
				{
					if(strpos($table->name,$this->tablePrefix)!==0)
						unset($tables[$i]);
				}
			}
		}
		else
			$tables=array($this->getTableSchema($this->tableName));

		$this->files=array();
		$templatePath=$this->templatePath;
		$this->relations=$this->generateRelations();


		foreach($tables as $table)
		{
			$tableName=$this->removePrefix($table->name);
			$className=$this->generateClassName($table->name);

            //20130601 Added : Implement for columns excluding feature
            $columns = $this->getOutputColumns($table);

            $modelPath = Yii::getPathOfAlias($this->modelPath).'/'.$className.'.php';
            $this->_userCode = $this->getUserCode($modelPath);
            $voPath = Yii::getPathOfAlias($this->voPath).'/F'.$className.'.php';
            $this->_voUserCode = $this->getUserCode($voPath);
            //20130604 Added (End)

			$params=array(
				'tableName'=>$schema==='' ? $tableName : $schema.'.'.$tableName,
				'modelClass'=>$className,

                //20130601 Modified : Implement for columns excluding feature
				/*'columns'=>$table->columns,*/
                'columns'=>$columns,

				'labels'=>$this->generateLabels($table),
				'rules'=>$this->generateRules($table),

                //20131124 apply excluded columns
				//'relations'=>isset($this->relations[$className]) ? $this->relations[$className] : array(),
                'relations'=>$this->getOutputRelations($className),

				'connectionId'=>$this->connectionId,

                //20130601 Added (Start) : f-project.net customisation
                'searchColumns'=>$this->getSearchColumns($table),
                'overrideRules'=>$this->overrideRules,
                'customRules'=>$this->parseCustomRules($this->customRules),
                'userCode'=>$this->_userCode,
                'voUserCode'=>$this->_voUserCode,
                'userDefinedTypeColumns'=>$this->parseUserDefinedTypeColumns($this->userDefinedTypeColumns),
                //20130601 Added (End)

                //20131124 f-project.net added for VO generating
                'voFields'=>$this->getVoFields($columns, $this->getOutputRelations($className)),

                //20140426 f-project.net added for VO generating
                'derivedExcludeFields'=>$this->getDerivedExcludeFields($className),
                'primaryKey'=>$table->primaryKey,
                'isRelationTable'=>$this->isRelationTable($table),

                'useILogicalDeletableModelInterface'=>$this->useILogicalDeletableModelInterface,
				'useOptimisticLock'=>$this->useOptimisticLock,
				'optimisticLockColumn'=>$this->optimisticLockColumn,
			);

            $this->files[]=new CCodeFile(
                $modelPath,
                $this->render($templatePath.'/model.php', $params)
            );

            //20131124 f-project.net Customized for VO generating
            $this->files[]=new CCodeFile(
                $voPath,
                $this->render($templatePath.'/vo.php', $params)
            );
		}
	}

    //20130601 Added (Start) : Implement for f-project.net custom feature
    private $_userCode = '';
    private $_voUserCode = '';

    private function parseColumnNames($s)
    {
        $s = str_replace(array("\r", "\n", "\t"), '', $s);
        $cols = array();
        $a = explode(',', $s);
        foreach ($a as $item) {
            $cols[$item] = $item;
        }
        return $cols;
    }

    private function parseUserDefinedTypeColumns($s)
    {
        if(is_null($s) || $s == '')
            return [];

        $s = '['.str_replace(array("\r", "\n", "\t"), '', $s).']';

        $cols = [];

        $v = eval('return '.$s.";");

        foreach ($v as $colName=>$col)
        {
            $colObj = new stdClass();
            $colObj->name = $colName;

            if(is_array($col))
            {
                if(isset($col['type']))
                    $colObj->type = $col['type'];
                if(isset($col['arType']))
                {
                    $colObj->arType = $col['arType'];
                }

                if(isset($col['fromAR']))
                    $colObj->fromAR=str_replace('{'.$colName.'}','$ar->'.$colName,$col['fromAR']);
				if(isset($col['deriveFrom']))
					$colObj->deriveFrom=str_replace('{'.$colName.'}','$ar->'.$colName,$col['deriveFrom']);
                if(isset($col['toAR']))
                    $colObj->toAR=str_replace('{'.$colName.'}','$this->'.$colName,$col['toAR']);
                if(isset($col['autoValue']))
                    $colObj->autoValue=$col['autoValue'];
            }
            else
            {
                $colObj->type = $col;
            }

            if(!property_exists($colObj, 'fromAR') && property_exists($colObj, 'type'))
            {
                $colObj->fromAR='new '.$colObj->type.'($ar->'.$colName.')';
            }

            if(property_exists($colObj, 'type') && $colObj->type == 'DateTime' && !property_exists($colObj, 'toAR'))
            {
                $colObj->toAR='$this->'.$colName.'->format(DATE_ISO8601)';
            }

            if(!property_exists($colObj, 'autoValue'))
                $colObj->autoValue=false;

            $cols[$colName] = $colObj;
        }

        return $cols;
    }

    private function parseCustomLabels($s)
    {
        if(is_null($s) || $s == '')
            return [];

        $s = '['.str_replace(array("\r", "\n", "\t"), '', $s).']';

        $cols = [];

        $v = eval('return '.$s.";");

        foreach ($v as $colName=>$label)
        {
            $colObj = new stdClass();
            $colObj->name = $colName;
            $colObj->label = $label;
            $cols[$colName] = $colObj;
        }

        return $cols;
    }

    private function parseCustomRelations($s)
    {
        if(is_null($s) || $s == '')
            return [];

        $s = '['.str_replace(array("\r", "\n", "\t"), '', $s).']';

        $v = eval('return '.$s.";");

        return $v;
    }

    private function parseCustomRules($s)
    {
        if($this->overrideRules != 0)
            return '';

        $s = rtrim($s, ',');
        $s = str_replace(array("\r", "\n", "\t"), ' ', $s);
        $s = str_replace("  ", ' ', $s);
        $a = explode('),', $s);
        $rules = array();
        foreach ($a as $item) {
            $item = trim($item);
            if (substr($item, 0, 6) !== 'array(')
                continue;
            if (substr($item, -1) !== ')')
                $item = $item . ')';

            if (strpos($item, '","')) {
                $b = explode('","', $item);
                $delimiter = '", "';
            } elseif (strpos($item, '", "')) {
                $b = explode('", "', $item);
                $delimiter = '", "';
            } elseif (stripos($item, "','")) {
                $b = explode("','", $item);
                $delimiter = "', '";
            } elseif (stripos($item, "', '")) {
                $b = explode("', '", $item);
                $delimiter = "', '";
            } else
                continue;
            $ss = str_replace(',', ', ', $b[0]);
            for ($i = 1; $i < count($b); $i++) {
                $ss = $ss . $delimiter . $b[$i];
            }
            $rules[] = $ss;
        }

        return $rules;
    }

    /**
     * Get columns for generating output Model code
     * @param CDbTableSchema$table the related table
     * @return array
     *
     * @author NguyenBS
     */
    private function getOutputColumns($table)
    {
        $_excludeColumns = $this->parseColumnNames($this->excludeColumns);
        $columns = array();
        foreach ($table->columns as $column) {
            if (!array_key_exists($column->name, $_excludeColumns))
                $columns[$column->name] = $column;
        }
        return $columns;
    }

    /**
     * Get columns for generating search in output Model code
     * @param $table
     * @return array
     *
     * @author NguyenBS
     */
    private function getSearchColumns($table)
    {
        $_searchExcludeColumns = $this->parseColumnNames($this->searchExcludeColumns);
        $search = array();
        foreach ($table->columns as $column) {
            if ($column->autoIncrement || array_key_exists($column->name, $_searchExcludeColumns))
                continue;

            $search[$column->name] = $column;
        }
        return $search;
    }

    /**
     * Get user defined code.
     * @param string $path the file path to source code PHP script
     * @return string
     */
    private function getUserCode($path)
    {
        $userCode = '';
        if ($path !== null && is_file($path)) {
            $oldCode = file_get_contents($path);
            $toFind = '* f-project.net implementation properties and methods are after this block';
            $i = stripos($oldCode, $toFind);
            if ($i) {
                $finisher = '*********************************************************************** */';
                $j = stripos($oldCode, $finisher, $i);
                if (!$j) {
                    $j = $i;
                    $finisher = $toFind;
                }
                $userCode = trim(substr($oldCode, $j + strlen($finisher)));
                if ($userCode === '}')
                    $userCode = '';
                else {
                    $j = strrpos($userCode, '}');
                    if ($j) {
                        $userCode = trim(substr($userCode, 0, $j));
                    }
                }
            }
        }

        return $userCode;
    }

    //20130601 Added (End)

	public function validateTableName($attribute,$params)
	{
		if($this->hasErrors())
			return;

		$invalidTables=array();
		$invalidColumns=array();

		if($this->tableName[strlen($this->tableName)-1]==='*')
		{
			if(($pos=strrpos($this->tableName,'.'))!==false)
				$schema=substr($this->tableName,0,$pos);
			else
				$schema='';

			$this->modelClass='';
			$tables=Yii::app()->{$this->connectionId}->schema->getTables($schema);
			foreach($tables as $table)
			{
				if($this->tablePrefix=='' || strpos($table->name,$this->tablePrefix)===0)
				{
					if(in_array(strtolower($table->name),self::$keywords))
						$invalidTables[]=$table->name;
					if(($invalidColumn=$this->checkColumns($table))!==null)
						$invalidColumns[]=$invalidColumn;
				}
			}
		}
		else
		{
			if(($table=$this->getTableSchema($this->tableName))===null)
				$this->addError('tableName',"Table '{$this->tableName}' does not exist.");
			if($this->modelClass==='')
				$this->addError('modelClass','Model Class cannot be blank.');

			if(!$this->hasErrors($attribute) && ($invalidColumn=$this->checkColumns($table))!==null)
					$invalidColumns[]=$invalidColumn;
		}

		if($invalidTables!=array())
			$this->addError('tableName', 'Model class cannot take a reserved PHP keyword! Table name: '.implode(', ', $invalidTables).".");
		if($invalidColumns!=array())
			$this->addError('tableName', 'Column names that does not follow PHP variable naming convention: '.implode(', ', $invalidColumns).".");
	}

	/*
	 * Check that all database field names conform to PHP variable naming rules
	 * For example mysql allows field name like "2011aa", but PHP does not allow variable like "$model->2011aa"
	 * @param CDbTableSchema $table the table schema object
	 * @return string the invalid table column name. Null if no error.
	 */
	public function checkColumns($table)
	{
		foreach($table->columns as $column)
		{
			if(!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/',$column->name))
				return $table->name.'.'.$column->name;
		}
        return null;
	}

	public function validateModelPath($attribute,$params)
	{
		if(Yii::getPathOfAlias($this->modelPath)===false)
			$this->addError('modelPath','Model Path must be a valid path alias.');
	}

	public function validateBaseClass($attribute,$params)
	{
		$class=@Yii::import($this->baseClass,true);
		if(!is_string($class) || !$this->classExists($class))
			$this->addError('baseClass', "Class '{$this->baseClass}' does not exist or has syntax error.");
		elseif($class!=='CActiveRecord' && !is_subclass_of($class,'CActiveRecord'))
			$this->addError('baseClass', "'{$this->model}' must extend from CActiveRecord.");
	}

    /**
     * @param $tableName
     * @return CDbTableSchema
     */
    public function getTableSchema($tableName)
	{
		$connection=Yii::app()->{$this->connectionId};
		return $connection->getSchema()->getTable($tableName, $connection->schemaCachingDuration!==0);
	}

	public function generateLabels($table)
	{
        $labels=array();
        $customLabels = $this->parseCustomLabels($this->customLabels);
        foreach($table->columns as $column)
        {
            if(array_key_exists($column->name,$customLabels))
                $labels[$column->name]=$customLabels[$column->name]->label;
            else if($this->commentsAsLabels && $column->comment)
                $labels[$column->name]=$column->comment;
            else
            {
                $label=ucwords(trim(strtolower(str_replace(array('-','_'),' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $column->name)))));
                $label=preg_replace('/\s+/',' ',$label);
                if(strcasecmp(substr($label,-3),' id')===0)
                    $label=substr($label,0,-3);
                if($label==='Id')
                    $label='ID';
                $label=str_replace("'","\\'",$label);
                $labels[$column->name]=$label;
            }
        }
        return $labels;
	}

	public function generateRules($table)
	{
        //20130602 Added (Start) : Implement for columns excluding feature
        $_ruleExcludeColumns = $this->parseColumnNames($this->ruleExcludeColumns);
        $userDefinedCols = $this->parseUserDefinedTypeColumns($this->userDefinedTypeColumns);
        //20130602 Added (End)

		$rules=array();
		$required=array();
		$integers=array();
		$numerical=array();
		$length=array();
		$safe=array();

		foreach($table->columns as $column)
		{
            //20130602 Modified : Implement for columns excluding feature
			/*if($column->autoIncrement)
				continue;*/
            if($column->autoIncrement || array_key_exists($column->name, $_ruleExcludeColumns))
                continue;

			$r=!$column->allowNull && $column->defaultValue===null;
			if($r)
				$required[]=$column->name;
			if($column->type==='integer')
				$integers[]=$column->name;
			elseif($column->type==='double')
				$numerical[]=$column->name;
			elseif($column->type==='string' && $column->size>0)
				$length[$column->size][]=$column->name;

            //20130602 Modified : Implement for columns excluding feature
            //elseif(!$column->isPrimaryKey && !$r)
			elseif(!$column->isPrimaryKey && !$r && !array_key_exists($column->name, $userDefinedCols))
				$safe[]=$column->name;
		}
		if($required!==array())
			$rules[]="array('".implode(', ',$required)."', 'required')";
		if($integers!==array())
			$rules[]="array('".implode(', ',$integers)."', 'numerical', 'integerOnly'=>true)";
		if($numerical!==array())
			$rules[]="array('".implode(', ',$numerical)."', 'numerical')";
		if($length!==array())
		{
			foreach($length as $len=>$cols)
				$rules[]="array('".implode(', ',$cols)."', 'length', 'max'=>$len)";
		}
		if($safe!==array())
			$rules[]="array('".implode(', ',$safe)."', 'safe')";

		return $rules;
	}

	public function getRelations($className)
	{
		return isset($this->relations[$className]) ? $this->relations[$className] : array();
	}

	protected function removePrefix($tableName,$addBrackets=true)
	{
		if($addBrackets && Yii::app()->{$this->connectionId}->tablePrefix=='')
			return $tableName;
		$prefix=$this->tablePrefix!='' ? $this->tablePrefix : Yii::app()->{$this->connectionId}->tablePrefix;
		if($prefix!='')
		{
			if($addBrackets && Yii::app()->{$this->connectionId}->tablePrefix!='')
			{
				$prefix=Yii::app()->{$this->connectionId}->tablePrefix;
				$lb='{{';
				$rb='}}';
			}
			else
				$lb=$rb='';
			if(($pos=strrpos($tableName,'.'))!==false)
			{
				$schema=substr($tableName,0,$pos);
				$name=substr($tableName,$pos+1);
				if(strpos($name,$prefix)===0)
					return $schema.'.'.$lb.substr($name,strlen($prefix)).$rb;
			}
			elseif(strpos($tableName,$prefix)===0)
				return $lb.substr($tableName,strlen($prefix)).$rb;
		}
		return $tableName;
	}

	protected function generateRelations()
	{
		if(!$this->buildRelations)
			return array();
		$relations=array();
		foreach(Yii::app()->{$this->connectionId}->schema->getTables() as $table)
		{
			if($this->tablePrefix!='' && strpos($table->name,$this->tablePrefix)!==0)
				continue;
			$tableName=$table->name;

            $isRelationTable = $this->isRelationTable($table);
			if ($isRelationTable)
			{
				$pks=$table->primaryKey;
				$fks=$table->foreignKeys;

				$table0=$fks[$pks[0]][0];
				$table1=$fks[$pks[1]][0];
				$className0=$this->generateClassName($table0);
				$className1=$this->generateClassName($table1);

                //20140111 removed
				//$unprefixedTableName=$this->removePrefix($tableName);

				$relationName=$this->generateRelationName($table0, $table1, true);

                //20140111 modified
				//$relations[$className0][$relationName]="array(self::MANY_MANY, '$className1', '$unprefixedTableName($pks[0], $pks[1])')";
                $relations[$className0][$relationName]="array(self::MANY_MANY, '$className1', '$tableName($pks[0], $pks[1])')";

				$relationName=$this->generateRelationName($table1, $table0, true);

				$i=1;
				$rawName=$relationName;
				while(isset($relations[$className1][$relationName]))
					$relationName=$rawName.$i++;

                //20140111 modified
				//$relations[$className1][$relationName]="array(self::MANY_MANY, '$className0', '$unprefixedTableName($pks[1], $pks[0])')";
                $relations[$className1][$relationName]="array(self::MANY_MANY, '$className0', '$tableName($pks[1], $pks[0])')";
			}
            //20140111 modified
			/*else
			{*/
            $className=$this->generateClassName($tableName);
            foreach ($table->foreignKeys as $fkName => $fkEntry)
            {
                // Put table and key name in variables for easier reading
                $refTable=$fkEntry[0]; // Table name that current fk references to
                $refKey=$fkEntry[1];   // Key in that table being referenced
                $refClassName=$this->generateClassName($refTable);

                // Add relation for this table
                $relationName=$this->generateRelationName($tableName, $fkName, false);

                //20140111 modified
                if(!$isRelationTable)
                    $relations[$className][$relationName]="array(self::BELONGS_TO, '$refClassName', '$fkName')";

                // Add relation for the referenced table
                $relationType=$table->primaryKey === $fkName ? 'HAS_ONE' : 'HAS_MANY';
                $relationName=$this->generateRelationName($refTable, $this->removePrefix($tableName,false), $relationType==='HAS_MANY');

                //20130602 Added (Start) : f-project.net customisation
                //To make the relation name is more understandable

                if(strcasecmp($relationType,'HAS_MANY')==0 && !$isRelationTable)
                {
                    if(strcasecmp(substr($fkName,-2),'id')===0 && strcasecmp($fkName,'id'))
                        $pref=rtrim(substr($fkName, 0, -2),'_');
                    else
                        $pref=$fkName;

                    if(strcmp($fkName,$relationName))
                        $relationName = $pref.ucfirst($relationName);
                }
                //20130602 Added (End)

                $i=1;
                $rawName=$relationName;
                while(isset($relations[$refClassName][$relationName]))
                    $relationName=$rawName.($i++);

                $relations[$refClassName][$relationName]="array(self::$relationType, '$className', '$fkName')";
            }
            //20140111 modified
			//}
		}
		return $relations;
	}

	/**
	 * Checks if the given table is a "many to many" pivot table.
	 * Their PK has 2 fields, and both of those fields are also FK to other separate tables.
	 * @param CDbTableSchema $table table to inspect
	 * @return boolean true if table matches description of helpter table.
	 */
	protected function isRelationTable($table)
	{
		$pk=$table->primaryKey;
		return (count($pk) === 2 // we want 2 columns
			&& isset($table->foreignKeys[$pk[0]]) // pk column 1 is also a foreign key
			&& isset($table->foreignKeys[$pk[1]]) // pk column 2 is also a foriegn key
			&& $table->foreignKeys[$pk[0]][0] !== $table->foreignKeys[$pk[1]][0]); // and the foreign keys point different tables
	}

	protected function generateClassName($tableName)
	{
		if($this->tableName===$tableName || ($pos=strrpos($this->tableName,'.'))!==false && substr($this->tableName,$pos+1)===$tableName)
			return $this->modelClass;

		$tableName=$this->removePrefix($tableName,false);
        if(($pos=strpos($tableName,'.'))!==false) // remove schema part (e.g. remove 'public2.' from 'public2.post')
            $tableName=substr($tableName,$pos+1);
		$className='';
		foreach(explode('_',$tableName) as $name)
		{
			if($name!=='')
				$className.=ucfirst($name);
		}
		return $className;
	}

	/**
	 * Generate a name for use as a relation name (inside relations() function in a model).
	 * @param string $tableName the name of the table to hold the relation
	 * @param string $fkName the foreign key name
	 * @param boolean $multiple whether the relation would contain multiple objects
	 * @return string the relation name
	 */
	protected function generateRelationName($tableName, $fkName, $multiple)
	{
		if(strcasecmp(substr($fkName,-2),'id')===0 && strcasecmp($fkName,'id'))
			$relationName=rtrim(substr($fkName, 0, -2),'_');
		else
			$relationName=$fkName;
		$relationName[0]=strtolower($relationName);

        //20130530 NguyenBS modified
		/*if($multiple)
			$relationName=$this->pluralize($relationName);*/
        if($multiple)
            $relationName=$this->removePrefix($this->pluralize($relationName),false);

		$names=preg_split('/_+/',$relationName,-1,PREG_SPLIT_NO_EMPTY);
		if(empty($names)) return $relationName;  // unlikely
		for($name=$names[0], $i=1;$i<count($names);++$i)
			$name.=ucfirst($names[$i]);

		$rawName=$name;
		$table=Yii::app()->{$this->connectionId}->schema->getTable($tableName);
		$i=0;
		while(isset($table->columns[$name]))
			$name=$rawName.($i++);

		return $name;
	}

	public function validateConnectionId($attribute, $params)
	{
		if(Yii::app()->hasComponent($this->connectionId)===false || !(Yii::app()->getComponent($this->connectionId) instanceof CDbConnection))
			$this->addError('connectionId','A valid database connection is required to run this generator.');
	}

    //20131124 f-project.net Customized for VO generating
    public function getVoFields($columns, $relations)
    {
        $userDefCols = $this->parseUserDefinedTypeColumns($this->userDefinedTypeColumns);
        $_voExcludeFields = $this->parseColumnNames($this->voExcludeFields);
        $booleanCols = $this->parseColumnNames($this->booleanColumns);
        $voFields = array();
        foreach($columns as $column)
        {
            if (array_key_exists($column->name, $_voExcludeFields))
                continue;

            if(array_key_exists($column->name, $userDefCols) && property_exists($userDefCols[$column->name], 'type'))
                $voFields[$column->name] = $userDefCols[$column->name]->type;
            else if(array_key_exists($column->name, $userDefCols) && property_exists($userDefCols[$column->name], 'arType'))
                $voFields[$column->name] = $userDefCols[$column->name]->arType;
            else if(array_key_exists($column->name, $booleanCols))
                $voFields[$column->name] = 'bool';
            else
                $voFields[$column->name] = $column->type;

            if(array_key_exists($column->name, $userDefCols))
            {
                $uCol = $userDefCols[$column->name];
                if(property_exists($uCol,'type') && $uCol->type === 'DateTime')
                    $this->dateTimeColumns[$column->name] = $column->name;
            }
        }

        foreach($relations as $name=>$relation)
        {
            if (array_key_exists($name, $_voExcludeFields))
                continue;

            $voFields[$name] = '{R}F'.$this->getRelationType($relation);
        }

        $customRels = $this->parseCustomRelations($this->customRelations);
        foreach($customRels as $name=>$relation)
        {
            if (array_key_exists($name, $_voExcludeFields))
                continue;
            $voFields[$name] = '{R}F'.$this->getRelationType($relation);
        }

        return $voFields;
    }

    const RELATION_REGEX = "~^array\(self::([^,]+), '([^']+)', ('([^']+)'|array\(.+\)).*\)$~";

    public function getRelationType($relation)
    {
        if (preg_match(self::RELATION_REGEX, $relation, $matches))
        {
            $relationType = $matches[1];
            $relationModel = $matches[2];

            switch($relationType){
                case 'HAS_ONE':
                    $type = $relationModel;
                    break;
                case 'BELONGS_TO':
                    $type = $relationModel;
                    break;
                case 'HAS_MANY':
                    $type = $relationModel.'[]';
                    break;
                case 'MANY_MANY':
                    $type = $relationModel.'[]';
                    break;
                default:
                    $type = 'mixed';
            }

            return $type;
        }
        throw new Exception("Invalid parameter for getRelationType(): $relation");
    }

    /**
     * The "stickyBagValidator" validator.
     * This validator does not really validate the attributes.
     * It actually saves the attribute value in a file to make it sticky.
     * @param string $attribute the attribute to be validated
     * @param array $params the validation parameters
     */
    public function stickyBagValidator($attribute,$params)
    {
        if(!$this->hasErrors())
        {
            $stickyBagJson = json_decode($this->stickyBag);
            if(property_exists($stickyBagJson,$this->tableName))
            {
                $stickyBagTable = $stickyBagJson->{$this->tableName};
                $stickyBagTable->$attribute = $this->$attribute;
            }
            else
            {
                $stickyBagTable = new StdClass();
            }

            $stickyBagJson->{$this->tableName} = $stickyBagTable;
            $this->stickyBag = json_encode($stickyBagJson);
        }
    }

    //20140114 NguyenBS Added
    private function getOutputRelations($className)
    {
        $_excludeColumns = $this->parseColumnNames($this->excludeColumns);
        $rels = array();
        if(isset($this->relations[$className]))
        {
            foreach ($this->relations[$className] as $relName=>$rel)
            {
                if (!array_key_exists($relName, $_excludeColumns))
                    $rels[$relName]= $rel;
            }
        }

        $customRels = $this->parseCustomRelations($this->customRelations);
        foreach($customRels as $relName=>$rel)
        {
            $rels[$relName] = $rel;
        }
        return $rels;
    }

    //20140114 NguyenBS Added
    public function hasRelation($fields)
    {
        foreach($fields as $name=>$type)
        {
            if(substr($type,0, 3) == '{R}')
            {
                return true;
            }
        }
        return false;
    }

    public function getDerivedExcludeFields($modelClass)
    {
        $rels = array();
        $excludeVoFields = $this->parseColumnNames($this->voExcludeFields);
        $relations = $this->parseCustomRelations($this->customRelations);
        if(isset($this->relations[$modelClass]))
        {
            $relations = array_merge($this->relations[$modelClass], $relations);
        }

        if(isset($relations))
        {
            foreach ($relations as $relName=>$rel)
            {
                if (preg_match(self::RELATION_REGEX, $rel, $matches))
                {
                    $relationType = $matches[1];
                    if($relationType == 'BELONGS_TO' || $relationType == 'HAS_ONE')
                    {
                        $relationField = $matches[4];
                        if(array_key_exists($relationField, $excludeVoFields))
                        {
                            $rels[$relName] = $relationField;
                        }
                    }
                }
            }
        }

        return $rels;
    }

    public function generateArFieldToVoFieldCode($name, $type, $userDefinedTypeColumns)
    {
        if(array_key_exists($name,$userDefinedTypeColumns) && property_exists($userDefinedTypeColumns[$name], 'fromAR'))
        {
            return 'is_null($ar->'.$name.') ? null : '.$userDefinedTypeColumns[$name]->fromAR.";\n";
        }
		elseif(array_key_exists($name,$userDefinedTypeColumns) && property_exists($userDefinedTypeColumns[$name], 'deriveFrom'))
		{
			return '$ar->'.$userDefinedTypeColumns[$name]->deriveFrom.";\n";
		}
        elseif(strcmp($type,'bool') == 0)
        {
            return '(bool)$ar->'.$name.";\n";
        }
        elseif(substr($type, -2) == '[]')
        {
            return $type.'::staticInstance()->recordsToModels($ar->'.$name.");\n";
        }
        else
        {
            return '$ar->'.$name.";\n";
        }
    }

    public function generateArRelationToVoFieldCode($name, $type, $userDefinedTypeColumns,$tabs)
    {
        $tabStr = str_repeat("\t", $tabs);
        if((substr($type, -2)) == '[]')
        {
            return $tabStr.'if($ar->hasRelated(\''.$name.'\'))'."\n".
            "$tabStr\t".'$this->'.$name.' = '.substr(str_replace('{R}', '', $type), 0, -2).'::staticInstance()->recordsToModels($ar->'.$name.", true);\n".
            $tabStr."else\n".
            "$tabStr\t".'$this->'.$name." = null;\n";
        }
        else
        {
            if(array_key_exists($name,$userDefinedTypeColumns) && property_exists($userDefinedTypeColumns[$name], 'fromAR'))
            {
                if(property_exists($userDefinedTypeColumns[$name], 'type'))
                    $vtype=$userDefinedTypeColumns[$name]->{'type'};
                else
                    $vtype = $type;
                if(strcmp($vtype,'bool') == 0)
                {
                    $defaultValue = 'false';
                }
                elseif(strcmp($vtype,'int') == 0)
                {
                    $defaultValue = '0';
                }
                else
                {
                    $defaultValue = 'null';
                }

                return $tabStr.'if($ar->hasRelated(\''.$name.'\'))'."\n".
                "$tabStr\t".'$this->'.$name.' = $ar->'.$name.";\n".
                $tabStr."else\n".
                "$tabStr\t".'$this->'.$name." = $defaultValue;\n";
            }
            else
            {
                return $tabStr.'if($ar->hasRelated(\''.$name.'\'))'."\n".
                "$tabStr\t".'$this->'.$name.' = '.str_replace('{R}', '', $type).'::staticInstance()->recordToModel($ar->'.$name.", null, true);\n".
                $tabStr."else\n".
                "$tabStr\t".'$this->'.$name." = null;\n";
            }
        }
    }
}
