<?php
namespace framework\base;

use framework\exception\DatabaseException;
use framework\library\ArrayUtil;
use framework\library\Database;
use framework\library\sql_builder\element\clause\SqlSelect;
use framework\library\sql_builder\SqlBuilder;
use framework\library\String;

class Model
{
    const PFX_CUSTOM_CACHE_KEY = '__custom_cache_key__';

    /**
     * @var Model[]
     */
    private static $instances = array();

    /**
     * @var Database
     */
    protected $db;

    protected $table;

    protected $select_columns;

    private function __construct($dsn = null, $custom_cache_key = null)
    {
        $this->db = Database::getInstance($dsn, $custom_cache_key);

        $className = baseClassName(get_class($this));

        $this->table = String::toUnderscores($className);
    }

    /**
     * @param null $dsn
     * @param null $custom_cache_key
     * @return Model
     */
    public static function getInstance($dsn = null, $custom_cache_key = null)
    {
        if (empty($dsn)) {
            $dsn = \Config::$DEFAULT_DSN;
        }
        $class_name = get_called_class();
        if (!empty($custom_cache_key)) {
            $dsn_key = md5(self::PFX_CUSTOM_CACHE_KEY . $custom_cache_key);
        } else {
            $dsn_key = md5(serialize($dsn) . $class_name);
        }

        if (empty(self::$instances[$dsn_key])) {
            self::$instances[$dsn_key] = new $class_name($dsn, $custom_cache_key);
        }

        return self::$instances[$dsn_key];
    }

    private function _getByParams(
        $where = null,
        $order_by = null,
        $limit = null,
        $from = null,
        $select_columns = null,
        $group_by = null,
        $having = null,
        $is_fetch_assoc = true,
        $is_fetch_all = true,
        $is_fetch_column = false,
        $is_fetch_unique = false
    )
    {
        SqlBuilder::beginSelect();
        SqlBuilder::select($this->_getSelectColumns($select_columns));
        SqlBuilder::from($this->_getFrom($from));
        SqlBuilder::where($where);
        SqlBuilder::orderBy($order_by);
        SqlBuilder::limit($limit);
        SqlBuilder::groupBy($group_by);
        SqlBuilder::having($having);
        list($query, $values) = SqlBuilder::end();

        $ret = $this->_get($query, $is_fetch_assoc, $is_fetch_all, $is_fetch_column, $is_fetch_unique, $values);
        if (is_array($ret) && empty($ret)) {
            return null;
        }

        return $ret;
    }

    private function _get($query, $is_fetch_assoc = true, $is_fetch_all = true, $is_fetch_column = false, $is_fetch_unique = false, $values = null)
    {
        $ret = $this->db->query($query, $values);
        if ($ret === false) {
            return null;
        }
        $fetch_mode = $this->_makeFetchMode($is_fetch_assoc, $is_fetch_column, $is_fetch_unique);
        if ($is_fetch_all) {
            $ret = $this->db->fetchAll($fetch_mode);
        } else {
            $ret = $this->db->fetch($fetch_mode);
        }

        return $ret;
    }

    private function _makeFetchMode($is_fetch_assoc, $is_fetch_column, $is_fetch_unique)
    {
        if ($is_fetch_assoc) {
            $fetch_mode = \PDO::FETCH_ASSOC;
        } else {
            $fetch_mode = \PDO::FETCH_NUM;
        }

        if ($is_fetch_column) {
            $fetch_mode = $fetch_mode | \PDO::FETCH_COLUMN;
        }

        if ($is_fetch_unique) {
            $fetch_mode = $fetch_mode | \PDO::FETCH_UNIQUE;
        }

        return $fetch_mode;
    }

    private function _getFrom($from)
    {
        if (empty($from)) {
            $table = $this->table;
        } else {
            $table = $from;
        }

        return $table;
    }

    private function _getSelectColumns($select_columns)
    {
        if (!empty($this->select_columns)) {
            $select_columns = $this->select_columns;
        }

        return $select_columns;
    }

    public function begin()
    {
        $this->db->begin();
    }

    public function commit()
    {
        $this->db->commit();
    }

    public function rollBack()
    {
        $this->db->rollBack();
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function setSelectColumns($select_columns)
    {
        $this->select_columns = $select_columns;
    }

    public function getList($where = null, $order_by = null, $limit = null, $from = null, $select_columns = '*', $group_by = null, $having = null)
    {
        return $this->_getByParams($where, $order_by, $limit, $from, $select_columns, $group_by, $having);
    }

    public function getNumericList(
        $where = null,
        $order_by = null,
        $limit = null,
        $from = null,
        $select_columns = '*',
        $group_by = null,
        $having = null
    )
    {
        return $this->_getByParams($where, $order_by, $limit, $from, $select_columns, $group_by, $having, false);
    }

    public function getListQuery($query, $is_fetch_assoc = true)
    {
        return $this->_get($query, $is_fetch_assoc);
    }

    public function getMap(
        $key_column,
        $where = null,
        $order_by = null,
        $limit = null,
        $from = null,
        $select_columns = '*',
        $group_by = null,
        $having = null
    )
    {
        $ret = $this->_getByParams($where, $order_by, $limit, $from, $select_columns, $group_by, $having);
        if (empty($ret)) {
            return null;
        }

        return ArrayUtil::toMap($key_column, $ret);
    }

    public function getMapValues(
        $key_column,
        $value_column,
        $where = null,
        $order_by = null,
        $limit = null,
        $from = null,
        $group_by = null,
        $having = null
    )
    {
        return $this->_getByParams($where, $order_by, $limit, $from, array($key_column, $value_column), $group_by, $having, false, false, true, true);
    }

    public function getMultiMapValues(
        $key_column,
        $value_column,
        $where = null,
        $order_by = null,
        $limit = null,
        $from = null,
        $group_by = null,
        $having = null
    )
    {
        $ret = $this->_getByParams($where, $order_by, $limit, $from, array($key_column, $value_column), $group_by, $having);
        if (empty($ret)) {
            return null;
        }

        return ArrayUtil::toMap($key_column, $ret, false, $value_column);
    }

    public function getMultiMap(
        $key_column,
        $where = null,
        $order_by = null,
        $limit = null,
        $from = null,
        $select_columns = '*',
        $group_by = null,
        $having = null
    )
    {
        $ret = $this->_getByParams($where, $order_by, $limit, $from, $select_columns, $group_by, $having);
        if (empty($ret)) {
            return null;
        }

        return ArrayUtil::toMap($key_column, $ret, false);
    }

    public function getNumericMap(
        $key_column,
        $where = null,
        $order_by = null,
        $limit = null,
        $from = null,
        $select_columns = '*',
        $group_by = null,
        $having = null
    )
    {
        $ret = $this->_getByParams($where, $order_by, $limit, $from, $select_columns, $group_by, $having, false);
        if (empty($ret)) {
            return null;
        }

        return ArrayUtil::toMap($key_column, $ret);
    }

    public function getNumericMultiMap(
        $key_column,
        $where = null,
        $order_by = null,
        $limit = null,
        $from = null,
        $select_columns = '*',
        $group_by = null,
        $having = null
    )
    {
        $ret = $this->_getByParams($where, $order_by, $limit, $from, $select_columns, $group_by, $having, false);
        if (empty($ret)) {
            return null;
        }

        return ArrayUtil::toMap($key_column, $ret, false);
    }

    public function getMapQuery($key_column, $query, $is_multi_values = false, $is_fetch_assoc = true)
    {
        $ret = $this->_get($query, $is_fetch_assoc);

        if (empty($ret)) {
            return null;
        }

        return ArrayUtil::toMap($key_column, $ret, !$is_multi_values);
    }

    public function getRow($where = null, $order_by = null, $from = null, $select_columns = '*', $group_by = null, $having = null)
    {
        $result = $this->_getByParams($where, $order_by, '1', $from, $select_columns, $group_by, $having, true, false);
        if (empty($result)) {
            return null;
        }

        return $result[0];
    }

    public function getNumericRow(
        $where = null,
        $order_by = null,
        $from = null,
        $select_columns = '*',
        $group_by = null,
        $having = null
    )
    {
        $result = $this->_getByParams($where, $order_by, '1', $from, $select_columns, $group_by, $having, false, false);
        if (empty($result)) {
            return null;
        }

        return $result[0];
    }

    public function getRowQuery($query, $is_fetch_assoc = true)
    {
        return $this->_get($query, $is_fetch_assoc, false);
    }

    public function getValues(
        $key_column,
        $where = null,
        $order_by = null,
        $limit = null,
        $from = null,
        $group_by = null,
        $having = null
    )
    {
        return $this->_getByParams($where, $order_by, $limit, $from, $key_column, $group_by, $having, false, true, true);
    }

    public function getGroupValues($key_column, $is_sort = true)
    {
        if ($is_sort === true) {
            $order_by = $key_column;
        } else {
            $order_by = null;
        }

        return $this->_getByParams(null, $order_by, null, null, $key_column, $key_column, null, false, true, true);
    }

    public function getValuesQuery($query)
    {
        return $this->_get($query, false, true, true);
    }

    public function get(
        $key_column,
        $where = null,
        $order_by = null,
        $limit = null,
        $from = null,
        $group_by = null,
        $having = null
    )
    {
        $ret = $this->_getByParams($where, $order_by, $limit, $from, $key_column, $group_by, $having, false, false, true);
        if (!empty($ret)) {
            return $ret[0];
        }

        return $ret;
    }

    public function getQuery($query)
    {
        return $this->_get($query, false, false, true);
    }

    public function getRowCount($where = null, $counting_column = '*', $from = null, $group_by = null, $having = null)
    {
        $ret = $this->_getByParams($where, null, null, $from, "count({$counting_column})", $group_by, $having, false, false, true);

        return empty($ret) ? 0 : $ret[0];
    }

    public function getResultRowCount(
        $where = null,
        $order_by = null,
        $limit = null,
        $from = null,
        $select_columns = '*',
        $group_by = null,
        $having = null
    )
    {
        return count($this->_getByParams($where, $order_by, $limit, $from, $select_columns, $group_by, $having));
    }

    public function getResultRowCountQuery($query)
    {
        return count($this->_get($query));
    }

    public function exist($where = null, $select_columns = '*', $from = null, $group_by = null, $having = null)
    {
        $ret = $this->_getByParams($where, null, '1', $from, $select_columns, $group_by, $having, true, false);

        return !empty($ret);
    }

    public function existColumn($target_column, $where = null, $select_columns = '*', $from = null, $group_by = null, $having = null)
    {
        $ret = $this->_getByParams($where, null, '1', $from, $select_columns, $group_by, $having, true, false);

        if (empty($ret)) {
            return false;
        }

        return !empty($ret[0][$target_column]);
    }

    public function existQuery($query)
    {
        $ret = $this->_get($query, true, false);

        return !empty($ret);
    }

    private function _filterNullValue(array &$data)
    {
        foreach ($data as $key => $item) {
            if ($item === null) {
                $data[$key] = SqlBuilder::plainExpr($key, 'null');
            }
        }
    }

    public function add(array $data, $ignore_null_value = true, array $key_list = null, SqlSelect $select = null)
    {
        if (empty($data)) {
            throw new DatabaseException('data is empty.');
        }

        if ($ignore_null_value === true) {
            ArrayUtil::stripNull($data);
        } else {
            self::_filterNullValue($data);
        }

        SqlBuilder::beginInsert();
        if (empty($select)) {
            if (ArrayUtil::isNumeric($data)) {
                if (empty($key_list)) {
                    throw new DatabaseException('data key가 없습니다.');
                }
                SqlBuilder::insert($this->table, $key_list);
                SqlBuilder::values($data);
            } else {
                SqlBuilder::insert($this->table, ArrayUtil::mergeArray(array_keys($data), $key_list));
                SqlBuilder::values(array_values($data));
            }
        } else {
            if (empty($key_list)) {
                throw new DatabaseException('select 이용한 insert는 key_list 가 필수');
            }
            SqlBuilder::insert($this->table, $key_list);
            SqlBuilder::values($select, true);
        }

        list($query, $values) = SqlBuilder::end();

        return $this->db->query($query, $values);
    }

    public function addExist(array $data, $exist_where, $ignore_null_value = true, array $key_list = null, SqlSelect $select = null)
    {
        if ($this->exist($exist_where) === true) {
            return true;
        }

        return $this->add($data, $ignore_null_value, $key_list, $select);
    }

    /**
     * @param array $data (numeric array, rows array, numeric rows array, columns array)
     * @param array $same_value_array
     * @param bool $is_ignore_insert
     * @param array $key_list
     * @return bool
     * @throws DatabaseException
     */
    public function addList(array $data, array $same_value_array = null, $is_ignore_insert = false, array $key_list = null)
    {
        if (empty($data)) {
            throw new DatabaseException('data is empty.');
        }
        $values = array();

        if (ArrayUtil::isRows($data)) {
            //rows array
            if (ArrayUtil::isAssoc($same_value_array)) {
                $keys = ArrayUtil::mergeArray(array_keys($data[0]), array_keys($same_value_array));
            } else {
                $keys = array_keys($data[0]);
            }
            $keys = ArrayUtil::mergeArray($keys, $key_list);
            foreach ($data as $item) {
                if (empty($same_value_array)) {
                    $values[] = array_values($item);
                } else {
                    $values[] = ArrayUtil::mergeArray(array_values($item), array_values($same_value_array));
                }
            }
        } elseif (ArrayUtil::isNumericRows($data)) {
            //numeric rows array
            if (empty($key_list)) {
                throw new DatabaseException('numeric rows는 key list가 필수');
            }
            if (ArrayUtil::isAssoc($same_value_array)) {
                $keys = ArrayUtil::mergeArray($key_list, array_keys($same_value_array));
            } else {
                $keys = $key_list;
            }
            foreach ($data as $item) {
                if (empty($same_value_array)) {
                    $values[] = $item;
                } else {
                    $values[] = ArrayUtil::mergeArray($item, array_values($same_value_array));
                }
            }
        } elseif (ArrayUtil::isColumns($data)) {
            //columns array
            $keys = array_keys($data);
            if (ArrayUtil::isAssoc($same_value_array)) {
                $keys = ArrayUtil::mergeArray($keys, array_keys($same_value_array));
            }
            if (!empty($key_list)) {
                $keys = ArrayUtil::mergeArray($keys, $key_list);
            }
            $values = ArrayUtil::columnsToValueRows($data, array_values($same_value_array));
        } elseif (ArrayUtil::isNumeric($data)) {
            //numeric array
            if (ArrayUtil::isAssoc($same_value_array)) {
                $keys = ArrayUtil::mergeArray($key_list, array_keys($same_value_array));
            } else {
                $keys = $key_list;
            }
            foreach ($data as $value) {
                $values[] = ArrayUtil::mergeArray($value, array_values($same_value_array));
            }
        } else {
            throw new DatabaseException('data 값 이상', $data);
        }

        SqlBuilder::beginInsert();
        SqlBuilder::insert($this->table, $keys, $is_ignore_insert);
        SqlBuilder::values($values);
        list($query, $values) = SqlBuilder::end();

        return $this->db->query($query, $values);
    }

    public function modify($data, $where = null, $ignore_null_value = true, $order_by = null, $limit = null)
    {
        if (empty($data)) {
            throw new DatabaseException('data is empty.');
        }

        if ($ignore_null_value === false) {
            self::_filterNullValue($data);
        }
        SqlBuilder::beginUpdate();
        SqlBuilder::update($this->table);
        SqlBuilder::set($data);
        SqlBuilder::where($where);
        SqlBuilder::orderBy($order_by);
        SqlBuilder::limit($limit);
        list($query, $values) = SqlBuilder::end();

        return $this->db->query($query, $values);
    }

    public function modifyList($data, $where, $is_force = false)
    {
        if (empty($data)) {
            throw new DatabaseException('data is empty.');
        }
        if (count($data) !== count($where)) {
            throw new DatabaseException('데이터와 where 갯수 다름', array('data_size' => count($data), 'where_size' => count($where)));
        }

        foreach ($data as $key => $value) {
            $ret = $this->modify($value, $where[$key]);
            if ($ret === false && $is_force === false) {
                throw new DatabaseException('modify 실패', $data);
            }
        }

        return true;
    }

    public function modifyInverseList($data, $where, $is_force = false)
    {
        if (empty($data)) {
            throw new DatabaseException('data is empty.');
        }
        if (count($data) !== count($where)) {
            throw new DatabaseException('데이터와 where 갯수 다름', array('data_size' => count($data), 'where_size' => count($where)));
        }

        $data = ArrayUtil::columnsToRows($data);
        $where = ArrayUtil::columnsToRows($where);

        return $this->modifyList($data, $where, $is_force);
    }

    public function set($data, $where)
    {
        if (empty($data)) {
            throw new DatabaseException('data is empty.');
        }
        $exist = $this->exist($where);
        if ($exist === true) {
            return $this->modify($data, $where);
        } else {
            return $this->add(array_merge($data, $where));
        }
    }

    public function setList($data, $where, $is_force = false)
    {
        if (empty($data)) {
            throw new DatabaseException('data is empty.');
        }
        if (count($data) !== count($where)) {
            throw new DatabaseException('데이터와 where 갯수 다름', array('data_size' => count($data), 'where_size' => count($where)));
        }

        foreach ($data as $key => $value) {
            $ret = $this->set($value, $where[$key]);
            if ($ret === false && $is_force === false) {
                throw new DatabaseException('set 실패', $data);
            }
        }

        return true;
    }

    public function setInverseList($data, $where, $is_force = false)
    {
        if (empty($data)) {
            throw new DatabaseException('data is empty.');
        }
        if (count($data) !== count($where)) {
            throw new DatabaseException('데이터와 where 갯수 다름', array('data_size' => count($data), 'where_size' => count($where)));
        }

        $data = ArrayUtil::columnsToRows($data);
        $where = ArrayUtil::columnsToRows($where);

        return $this->setList($data, $where, $is_force);
    }

    public function remove($where = null, $using = null, $order_by = null, $limit = null)
    {
        SqlBuilder::beginDelete();
        SqlBuilder::delete($this->table);
        SqlBuilder::where($where);
        SqlBuilder::using($using);
        SqlBuilder::orderBy($order_by);
        SqlBuilder::limit($limit);
        list($query, $values) = SqlBuilder::end();

        return $this->db->query($query, $values);
    }

    public function closeConnection()
    {
        $this->db->closeConnection();
        $this->db = null;
    }

    public function getLastInsertId($name = null)
    {
        return $this->db->getLastInsertId($name);
    }

    public function getAffectedRowCount()
    {
        return $this->db->getRowCount();
    }

    public function getLastErrorInfo()
    {
        return $this->db->getLastErrorInfo();
    }
}