<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2013. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * The Database Helper class
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class DbHelper {
    /**
     * @return CDbConnection
     */
    private static function db()
    {
        return Yii::app()->db;
    }

    /**
     * Insert a list of data to a table.
     * This method could be used to achieve better performance during insertion of the large
     * amount of data into the database table.
     *
     * For example,
     *
     * ~~~
     * DbHelper::insertMultiple('user', [
     *     ['name' => 'Tom', 'age' => 30],
     *     ['name' => 'Jane', 'age' => 20],
     *     ['name' => 'Linda', 'age' => 25],
     * ]);
     * ~~~
     *
     * @param string|CDbTableSchema $table the table schema ({@link CDbTableSchema}) or the table name (string).
     * @param array $data list data to be inserted, each value should be an array in format (column name=>column value).
     * If a key is not a valid column name, the corresponding value will be ignored.
     * @return int number of rows inserted.
     */
    public static function insertMultiple($table, $data)
    {
        Yii::trace('insertMultiple()','application.DbHelper');
        $command = self::db()->commandBuilder->createMultipleInsertCommand($table, $data);
        return $command->execute();
    }

    /**
     * Update a list of data to a table.
     * This method could be used to achieve better performance during updating of the large
     * amount of data to the database table.
     *
     * For example,
     *
     * ~~~
     * DbHelper::updateMultiple('user', [
     *     ['id' => 1, 'name' => 'Tom', 'age' => 30],
     *     ['id' => 2, 'name' => 'Jane', 'age' => 20],
     *     ['id' => 3, 'name' => 'Linda', 'age' => 25],
     * ],
     * 'id');
     * ~~~
     *
     * @param string|CDbTableSchema $table the table schema ({@link CDbTableSchema}) or the table name (string).
     * @param array $data list data to be updated, each value should be an array in format (column name=>column value).
     * If a key is not a valid column name, the corresponding value will be ignored.
     * @param string|array $searchColumnNames A name or an array of column names that used as searching condition to update.
     * This will usually be the primary key(s)
     * @param array $searchColumnValues An array of column values that used as searching condition to update.
     * This will usually be the primary key values. If this parameter is null, the primary keys will be get from
     * the corresponding field in records of $data array.
     * @return int number of rows updated.
     */
    public static function updateMultiple($table, $data, $searchColumnNames, $searchColumnValues=null)
    {
        Yii::trace('updateMultiple()','application.DbHelper');
        $command = self::createMultipleUpdateCommand($table, $data, $searchColumnNames, $searchColumnValues);
        return $command->execute();
    }

    /**
     * Creates a multiple INSERT command with ON DUPLICATE KEY UPDATE statement.
     * This method compose the SQL expression via given part templates, providing ability to adjust
     * command for different SQL syntax.
     * @param mixed $table the table schema ({@link CDbTableSchema}) or the table name (string).
     * @param array $data list data to be saved, each value should be an array in format (column name=>column value).
     * If a key is not a valid column name, the corresponding value will be ignored.
     * @param string|array $searchColumnNames A name or an array of column names that used as searching condition to update.
     * This will usually be the primary key(s)
     * @param array $searchColumnValues An array of column values that used as searching condition to update.
     * This will usually be the primary key values. If this parameter is null, the primary keys will be get from
     * the corresponding field in records of $data array.
     * @param array $templates templates for the SQL parts.
     * @throws CDbException
     * @return CDbCommand multiple insert command
     */
    private static function createMultipleUpdateCommand($table, $data, $searchColumnNames, $searchColumnValues=null, array $templates=[])
    {
        $templates=array_merge(
            [
                'rowUpdateStatement'=>'UPDATE {{tableName}} SET {{columnNameValuePairs}} WHERE {{rowUpdateCondition}}',
                'columnAssignValue'=>'{{column}}={{value}}',
                'columnValueGlue'=>', ',
                'rowUpdateConditionExpression'=>'{{pkName}}={{pkValue}}',
                'rowUpdateConditionJoin'=>' AND ',
                'rowUpdateStatementGlue'=>'; ',
            ],
            $templates
        );
        if(is_string($table) && ($table=self::db()->schema->getTable($tableName=$table))===null)
            throw new CDbException(Yii::t('yii','Table "{table}" does not exist.', ['{table}'=>$tableName]));
        $tableName=self::db()->commandBuilder->getDbConnection()->quoteTableName($table->name);
        $params=[];
        $quoteColumnNames=[];

        $columns=[];

        foreach($data as $rowData)
        {
            foreach($rowData as $columnName=>$columnValue)
            {
                if(!in_array($columnName,$columns,true))
                    if($table->getColumn($columnName)!==null)
                        $columns[]=$columnName;

            }
        }

        foreach($columns as $name)
            $quoteColumnNames[$name]=self::db()->commandBuilder->getDbConnection()->quoteColumnName($name);

        $rowUpdateStatements=[];

        // Map PK name to column name ignoring case-sensitive
        $pkToColumnName=[];

        foreach($data as $rowKey=>$rowData)
        {
            $hasPKValues = !empty($searchColumnValues) && !empty($searchColumnValues[$rowKey]);

            if($hasPKValues)
            {
                $pkValue = $searchColumnValues[$rowKey];
                if(is_array($pkValue))
                {
                    foreach($pkValue as $n=>$v)
                    {
                        foreach($searchColumnNames as $pk)
                        {
                            if (strcasecmp($n, $pk) == 0)
                            {
                                $params[':k_'.$n.'_'.$rowKey] = $v;
                                $pkToColumnName[$pk]=$n;
                                continue;
                            }
                        }
                    }
                }
            }

            $columnNameValuePairs=[];
            foreach($rowData as $columnName=>$columnValue)
            {
                $isPK = false;
                if(is_array($searchColumnNames))
                {
                    foreach($searchColumnNames as $pk)
                    {
                        if (strcasecmp($columnName, $pk) == 0)
                        {
                            $params[':'.$columnName.'_'.$rowKey] = $columnValue;
                            $pkToColumnName[$pk]=$columnName;
                            $isPK = true;
                            break;
                        }
                    }
                }
                else if (strcasecmp($columnName, $searchColumnNames) == 0)
                {
                    $params[':'.$columnName.'_'.$rowKey] = $columnValue;
                    $pkToColumnName[$searchColumnNames]=$columnName;
                    $isPK = true;
                }

                if(!$isPK || $hasPKValues)
                {
                    /** @var CDbColumnSchema $column */
                    $column=$table->getColumn($columnName);
                    $paramValuePlaceHolder=':'.$columnName.'_'.$rowKey;
                    $params[$paramValuePlaceHolder]=$column->typecast($columnValue);

                    $columnNameValuePairs[]=strtr($templates['columnAssignValue'],
                        [
                            '{{column}}'=>$quoteColumnNames[$columnName],
                            '{{value}}'=>$paramValuePlaceHolder,
                        ]);
                }
            }

            //Skip all rows that don't have primary key value;
            if(is_array($searchColumnNames))
            {
                $rowUpdateCondition = '';
                foreach($searchColumnNames as $pk)
                {
                    if(!isset($pkToColumnName[$pk]))
                        continue;

                    $pkValuePlaceHolder = $hasPKValues ? ':k_'.$pkToColumnName[$pk].'_'.$rowKey : ':'.$pkToColumnName[$pk].'_'.$rowKey;

                    if($rowUpdateCondition != '')
                        $rowUpdateCondition = $rowUpdateCondition.$templates['rowUpdateConditionJoin'];
                    $rowUpdateCondition = $rowUpdateCondition.strtr($templates['rowUpdateConditionExpression'],
                            [
                                '{{pkName}}'=>$pk,
                                '{{pkValue}}'=>$pkValuePlaceHolder,
                            ]);
                }
            }
            else
            {
                if(!isset($pkToColumnName[$searchColumnNames]))
                    continue;

                $pkValuePlaceHolder = $hasPKValues ? ':k_'.$pkToColumnName[$searchColumnNames].'_'.$rowKey : ':'.$pkToColumnName[$searchColumnNames].'_'.$rowKey;

                $rowUpdateCondition = strtr($templates['rowUpdateConditionExpression'],
                    [
                        '{{pkName}}'=>$searchColumnNames,
                        '{{pkValue}}'=>$pkValuePlaceHolder,
                    ]);
            }

            $rowUpdateStatements[]=strtr($templates['rowUpdateStatement'],
                [
                    '{{tableName}}'=>$tableName,
                    '{{columnNameValuePairs}}'=>implode($templates['columnValueGlue'],$columnNameValuePairs),
                    '{{rowUpdateCondition}}'=>$rowUpdateCondition,
                ]);
        }

        $sql=implode($templates['rowUpdateStatementGlue'], $rowUpdateStatements);

        //Must ensure Yii::app()->db->emulatePrepare is set to TRUE;
        $command=self::db()->commandBuilder->getDbConnection()->createCommand($sql);

        foreach($params as $name=>$value)
            $command->bindValue($name,$value);

        return $command;
    }
}