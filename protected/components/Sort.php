<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2013. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////

/**
 * Sort represents information relevant to sorting.
 *
 * When data needs to be sorted according to one or several attributes,
 * we can use Sort to represent the sorting information and generate
 * appropriate ORDER BY clause
 *
 * Sort is designed to be used together with {@link ValueObjectModel}.
 * When creating a Sort instance, you need to specify {@link modelClass}.
 * You can use Sort to generate ORDER BY clause by calling {@link getOrderBy}.
 *
 * In order to prevent SQL injection attacks, Sort ensures that only valid model attributes
 * can be sorted. This is determined based on {@link modelClass} and {@link attributes}.
 * When {@link attributes} is not set, all attributes belonging to {@link modelClass}
 * can be sorted. When {@link attributes} is set, only those attributes declared in the property
 * can be sorted.
 *
 * By configuring {@link attributes}, one can perform more complex sorts that may
 * consist of things like compound attributes (e.g. sort based on the combination of
 * first name and last name of users).
 *
 * The property {@link attributes} should be an array of key-value pairs, where the keys
 * represent the attribute names, while the values represent the virtual attribute definitions.
 * For more details, please check the documentation about {@link attributes}.
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class Sort
{
    /**
     * @var boolean whether the sorting can be applied to multiple attributes simultaneously.
     * Defaults to false, which means each time the data can only be sorted by one attribute.
     */
    public $enableMultiSort = false;
    /**
     * @var array list of attributes that are allowed to be sorted. Its syntax can be
     * described using the following example:
     *
     * ```php
     * [
     *     'age',
     *     'name' => [
     *         'asc' => ['first_name' => SORT_ASC, 'last_name' => SORT_ASC],
     *         'desc' => ['first_name' => SORT_DESC, 'last_name' => SORT_DESC],
     *         'default' => SORT_DESC,
     *         'label' => 'Name',
     *     ],
     * ]
     * ```
     *
     * In the above, two attributes are declared: "age" and "name". The "age" attribute is
     * a simple attribute which is equivalent to the following:
     *
     * ```php
     * 'age' => [
     *     'asc' => ['age' => SORT_ASC],
     *     'desc' => ['age' => SORT_DESC],
     *     'default' => SORT_ASC,
     *     'label' => Inflector::camel2words('age'),
     * ]
     * ```
     *
     * The "name" attribute is a composite attribute:
     *
     * - The "name" key represents the attribute name which will appear in the URLs leading
     *   to sort actions.
     * - The "asc" and "desc" elements specify how to sort by the attribute in ascending
     *   and descending orders, respectively. Their values represent the actual columns and
     *   the directions by which the data should be sorted by.
     * - The "default" element specifies by which direction the attribute should be sorted
     *   if it is not currently sorted (the default value is ascending order).
     * - The "label" element specifies what label should be used when calling [[link()]] to create
     *   a sort link. If not set, [[Inflector::camel2words()]] will be called to get a label.
     *   Note that it will not be HTML-encoded.
     *
     * Note that if the Sort object is already created, you can only use the full format
     * to configure every attribute. Each attribute must include these elements: `asc` and `desc`.
     */
    public $attributes = [];
    /**
     * @var string the name of the parameter that specifies which attributes to be sorted
     * in which direction. Defaults to 'sort'.
     * @see params
     */
    public $sortParam = 'sort';
    /**
     * @var array the order that should be used when the current request does not specify any order.
     * The array keys are attribute names and the array values are the corresponding sort directions. For example,
     *
     * ```php
     * [
     *     'name' => SORT_ASC,
     *     'created_at' => SORT_DESC,
     * ]
     * ```
     *
     * @see attributeOrders
     */
    public $defaultOrder;

    /**
     * @var string the character used to separate different attributes that need to be sorted by.
     */
    public $separator = ',';
    /**
     * @var array parameters (name => value) that should be used to obtain the current sort directions
     * and to create new sort URLs. If not set, $_GET will be used instead.
     *
     * In order to add hash to all links use `array_merge($_GET, ['#' => 'my-hash'])`.
     *
     * The array element indexed by [[sortParam]] is considered to be the current sort directions.
     * If the element does not exist, the [[defaultOrder|default order]] will be used.
     *
     * @see sortParam
     * @see defaultOrder
     */
    public $params;

    /**
     * Constructor.
     * @param string $modelClass the class name of data models that need to be sorted.
     * This should be a child class of {@link ValueObjectModel}.
     * @param string $params an array contains a field with name equals to $this->sortVar
     * that is a sort-specification string
     */
    public function __construct($modelClass=null, $params=null)
    {
        $this->modelClass = $modelClass;
        $this->params = $params;
    }

    private $_model;
	/**
	 * Given active record class name returns new model instance.
	 *
	 * @param ValueObjectModel $className active record class name.
	 * @return ActiveRecord active record model instance.
	 *
	 */
	protected function getModel($className=null)
	{
        if(is_null($className))
            return $this->_model;
        return $this->_model = $className::staticInstance()->getActiveRecord();
	}

    private $_initialized = false;
    private $_prefix;
    /**
     * Normalizes the attributes property.
     */
    protected function init()
    {
        if($this->_initialized)
            return;

        if($this->attributes!==array())
        {
            $this->normalizeAttributes();
            $this->_initialized = true;
            return;
        }
        elseif(is_null($this->modelClass))
            return;
        $attributes = [];
        $modelAttributes=$this->getModel($this->modelClass)->attributeNames();
        $this->_prefix = $this->getModel($this->modelClass)->tableAlias;
        foreach ($modelAttributes as $attribute) {
            $attribute = $this->_prefix.'.'.$attribute;
            $attributes[$attribute] = [
                'asc' => [$attribute => SORT_ASC],
                'desc' => [$attribute => SORT_DESC],
            ];
        }
        $this->attributes = $attributes;
        $this->_initialized = true;
    }

    protected function normalizeAttributes()
    {
        $attributes = [];
        foreach ($this->attributes as $name => $attribute) {
            if (!is_array($attribute)) {
                $attributes[$attribute] = [
                    'asc' => [$attribute => SORT_ASC],
                    'desc' => [$attribute => SORT_DESC],
                ];
            } elseif (!isset($attribute['asc'], $attribute['desc'])) {
                $attributes[$name] = array_merge([
                    'asc' => [$name => SORT_ASC],
                    'desc' => [$name => SORT_DESC],
                ], $attribute);
            } else {
                $attributes[$name] = $attribute;
            }
        }
        $this->attributes = $attributes;
    }

    /**
     * Normalizes format of ORDER BY data
     *
     * @param array|string $columns
     * @return array
     */
    protected function normalizeOrderBy($columns)
    {
        if (is_array($columns)) {
            return $columns;
        } else {
            $columns = preg_split('/\s*,\s*/', trim($columns), -1, PREG_SPLIT_NO_EMPTY);
            $result = [];
            foreach ($columns as $column) {
                if (preg_match('/^(.*?)\s+(asc|desc)$/i', $column, $matches)) {
                    $result[$matches[1]] = strcasecmp($matches[2], 'desc') ? SORT_ASC : SORT_DESC;
                } else {
                    $result[$column] = SORT_ASC;
                }
            }
            return $result;
        }
    }

    /**
     * @param CDbCriteria $criteria the query criteria
     * @param boolean $recalculate whether to recalculate the sort directions
     * @return string the order-by columns represented by this sort object.
     * This can be put in the ORDER BY clause of a SQL statement.
     */
    public function getOrderBy($criteria = null, $recalculate = false)
    {
        return $this->buildOrderBy($this->getOrders($recalculate));
    }

    /**
     * @param array $columns
     * @return string the ORDER BY clause built from [[Query::$orderBy]].
     */
    private function buildOrderBy($columns)
    {
        if (empty($columns)) {
            return '';
        }
        $orders = [];
        foreach ($columns as $name => $direction) {
            $orders[] = $this->getModel()->dbConnection->quoteColumnName($name) . ($direction === SORT_DESC ? ' DESC' : '');
        }

        return implode(', ', $orders);
    }

    /**
     * Returns the columns and their corresponding sort directions.
     * @param boolean $recalculate whether to recalculate the sort directions
     * @return array the columns (keys) and their corresponding sort directions (values).
     */
    public function getOrders($recalculate = false)
    {
        $this->init();
        $attributeOrders = $this->getAttributeOrders($recalculate);
        $orders = [];
        foreach ($attributeOrders as $attribute => $direction) {
            $definition = $this->attributes[$attribute];
            $columns = $definition[$direction === SORT_ASC ? 'asc' : 'desc'];
            foreach ($columns as $name => $dir) {
                $orders[$name] = $dir;
            }
        }

        return $orders;
    }

    /**
     * @var array the currently requested sort order as computed by [[getAttributeOrders]].
     */
    private $_attributeOrders;

    /**
     * Returns the currently requested sort information.
     * @param boolean $recalculate whether to recalculate the sort directions
     * @return array sort directions indexed by attribute names.
     * Sort direction can be either `SORT_ASC` for ascending order or
     * `SORT_DESC` for descending order.
     */
    public function getAttributeOrders($recalculate = false)
    {
        if ($this->_attributeOrders === null || $recalculate) {
            $this->_attributeOrders = [];
            $params = $this->params;
            if (isset($params[$this->sortParam]) && is_scalar($params[$this->sortParam])) {
                $attributes = explode($this->separator, $params[$this->sortParam]);
                foreach ($attributes as $attribute) {
                    $descending = false;
                    if (strncmp($attribute, '-', 1) === 0) {
                        $descending = true;
                        $attribute = substr($attribute, 1);
                    }

                    if (isset($this->attributes[$attribute])) {
                        $this->_attributeOrders[$attribute] = $descending ? SORT_DESC : SORT_ASC;
                        if (!$this->enableMultiSort) {
                            return $this->_attributeOrders;
                        }
                    }
                }
            }
            if (empty($this->_attributeOrders) && is_array($this->defaultOrder)) {
                $this->_attributeOrders = $this->defaultOrder;
            }
        }

        return $this->_attributeOrders;
    }

    /**
     * Returns the sort direction of the specified attribute in the current request.
     * @param string $attribute the attribute name
     * @return boolean|null Sort direction of the attribute. Can be either `SORT_ASC`
     * for ascending order or `SORT_DESC` for descending order. Null is returned
     * if the attribute is invalid or does not need to be sorted.
     */
    public function getAttributeOrder($attribute)
    {
        $orders = $this->getAttributeOrders();

        return isset($orders[$attribute]) ? $orders[$attribute] : null;
    }

    /**
     * Creates the sort variable for the specified attribute.
     * The newly created sort variable can be used to create a URL that will lead to
     * sorting by the specified attribute.
     * @param string $attribute the attribute name
     * @return string the value of the sort variable
     * @throws Exception if the specified attribute is not defined in [[attributes]]
     */
    public function createSortParam($attribute)
    {
        if (!isset($this->attributes[$attribute])) {
            throw new Exception("Unknown attribute: $attribute");
        }
        $definition = $this->attributes[$attribute];
        $directions = $this->getAttributeOrders();
        if (isset($directions[$attribute])) {
            $direction = $directions[$attribute] === SORT_DESC ? SORT_ASC : SORT_DESC;
            unset($directions[$attribute]);
        } else {
            $direction = isset($definition['default']) ? $definition['default'] : SORT_ASC;
        }

        if ($this->enableMultiSort) {
            $directions = array_merge([$attribute => $direction], $directions);
        } else {
            $directions = [$attribute => $direction];
        }

        $sorts = [];
        foreach ($directions as $attribute => $direction) {
            $sorts[] = $direction === SORT_DESC ? '-' . $attribute : $attribute;
        }

        return implode($this->separator, $sorts);
    }

    /**
     * Returns a value indicating whether the sort definition supports sorting by the named attribute.
     * @param string $name the attribute name
     * @return boolean whether the sort definition supports sorting by the named attribute.
     */
    public function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }
}
