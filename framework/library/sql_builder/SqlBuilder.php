<?php
namespace framework\library\sql_builder;

use framework\library\FileCache;
use framework\library\Log;
use framework\library\sql_builder\element\clause\SqlDelete;
use framework\library\sql_builder\element\clause\SqlInsert;
use framework\library\sql_builder\element\clause\SqlSelect;
use framework\library\sql_builder\element\clause\SqlUpdate;
use framework\library\sql_builder\element\expr\SqlDateRange;
use framework\library\sql_builder\element\expr\SqlExpr;
use framework\library\sql_builder\element\expr\SqlIn;
use framework\library\sql_builder\element\expr\SqlJoin;
use framework\library\sql_builder\element\expr\SqlPlainExpr;
use framework\library\sql_builder\element\SqlElement;
use framework\library\sql_builder\element\value\SqlPlainString;
use framework\library\sql_builder\element\value\SqlWildcard;
use framework\library\sql_builder\element\wrapper\SqlAndWhere;
use framework\library\sql_builder\element\wrapper\SqlOrWhere;
use framework\library\sql_builder\element\wrapper\SqlSubQuery;

class SqlBuilder
{
    const SQL_TYPE_SELECT = 'SELECT';
    const SQL_TYPE_INSERT = 'INSERT';
    const SQL_TYPE_UPDATE = 'UPDATE';
    const SQL_TYPE_DELETE = 'DELETE';

    const EXPR_DEFAULT = 1;
    const EXPR_DATE_FROM = '_dateFrom';
    const EXPR_DATE_TO = '_dateTo';
    const EXPR_DATE_RANGE = '_dateRange';
    const EXPR_LIKE_BOTH_WILDCARD = '_likeBothWildcard';
    private static $is_insert_ignore = false;

    private static $is_began = false;

    private static $sql_type = null;

    private static $is_select_values = false;

    /**
     * @var SqlElement
     */
    private static $builder_data = null;

    private static $query_string = null;

    private static $select_data = null;
    private static $from_data = null;
    private static $where_data = null;
    private static $group_by_data = null;
    private static $having_data = null;
    private static $order_by_data = null;
    private static $limit_data = null;
    private static $insert_data = null;
    private static $into_data = null;
    private static $values_data = null;
    private static $update_data = null;
    private static $set_data = null;
    private static $delete_data = null;
    private static $using_data = null;

    private static function _init()
    {
        self::$is_began = true;

        self::$is_select_values = false;

        self::$query_string = null;
        self::$builder_data = null;

        self::$select_data = null;
        self::$from_data = null;
        self::$where_data = null;
        self::$group_by_data = null;
        self::$having_data = null;
        self::$order_by_data = null;
        self::$limit_data = null;
        self::$insert_data = null;
        self::$into_data = null;
        self::$values_data = null;
        self::$update_data = null;
        self::$set_data = null;
        self::$delete_data = null;
        self::$using_data = null;
    }

    private static function _setBegin($sql_type)
    {
        if (self::_isBegan()) {
            return false;
        }
        self::$sql_type = $sql_type;
        self::_init();

        return true;
    }

    public static function beginSelect()
    {
        return self::_setBegin(self::SQL_TYPE_SELECT);
    }

    public static function beginInsert()
    {
        return self::_setBegin(self::SQL_TYPE_INSERT);
    }

    public static function beginUpdate()
    {
        return self::_setBegin(self::SQL_TYPE_UPDATE);
    }

    public static function beginDelete()
    {
        return self::_setBegin(self::SQL_TYPE_DELETE);
    }

    public static function end()
    {
        if (self::_isEnd() === true) {
            return false;
        }
        self::$is_began = false;

        self::$builder_data = self::_makeBuilderData();

        if (\Config::$ENABLE_SQL_BUILDER_CACHE === true && self::$sql_type == self::SQL_TYPE_SELECT) {
            $key = md5(serialize(self::$builder_data));
            $cache_data = @json_decode(FileCache::get($key), true);
            if (empty($cache_data)) {
                $query = self::$builder_data->parse();
                $values = self::$builder_data->getValues();
                $cache_data = compact('query', 'values');
                FileCache::set($key, $cache_data);
            }
            self::$query_string = $cache_data['query'];
            $values = $cache_data['values'];
        } else {
            self::$query_string = self::$builder_data->parse();
            $values = self::$builder_data->getValues();
        }
        self::$builder_data->initValues();
        self::$sql_type = null;

        Log::disableDb();
        Log::info('SqlBuilder로 생성한 쿼리', array(self::$query_string, $values));
        Log::restoreDisableDb();

        return array(self::$query_string, $values);
    }

    private static function _isBegan()
    {
        return self::$is_began;
    }

    private static function _isEnd()
    {
        return !self::$is_began;
    }

    /**
     * @return SqlElement
     */
    private static function _makeBuilderData()
    {
        switch (self::$sql_type) {
            case self::SQL_TYPE_SELECT:
                return new SqlSelect(
                    self::$from_data,
                    self::$where_data,
                    self::$order_by_data,
                    self::$limit_data,
                    self::$select_data,
                    self::$group_by_data,
                    self::$having_data
                );
            case self::SQL_TYPE_UPDATE:
                return new SqlUpdate(self::$update_data, self::$set_data, self::$where_data, self::$order_by_data, self::$limit_data);
            case self::SQL_TYPE_INSERT:
                if (self::$is_select_values) {
                    $values_data = new SqlSelect(
                        self::$from_data,
                        self::$where_data,
                        self::$order_by_data,
                        self::$limit_data,
                        self::$select_data,
                        self::$group_by_data,
                        self::$having_data
                    );
                } else {
                    $values_data = self::$values_data;
                }

                return new SqlInsert(self::$insert_data, self::$into_data, $values_data, self::$is_select_values, self::$is_insert_ignore);
            case self::SQL_TYPE_DELETE:
                return new SqlDelete(self::$delete_data, self::$where_data, self::$using_data, self::$order_by_data, self::$limit_data);
        }

        return null;
    }

    public static function getBuilderData()
    {
        return self::$builder_data;
    }

    public static function getQueryString()
    {
        return self::$query_string;
    }

    private static function _setData(&$source, $data)
    {
        if (self::_isEnd() || empty($data)) {
            return false;
        }

        if (empty($source) !== true) {
            $source = array_merge((array)$source, (array)$data);
        } else {
            $source = $data;
        }

        return true;
    }

    public static function select($data = '*')
    {
        return self::_setData(self::$select_data, $data, __FUNCTION__);
    }

    public static function from($data)
    {
        return self::_setData(self::$from_data, $data, __FUNCTION__);
    }

    public static function where($data)
    {
        return self::_setData(self::$where_data, $data, __FUNCTION__);
    }

    public static function groupBy($data)
    {
        return self::_setData(self::$group_by_data, $data, __FUNCTION__);
    }

    public static function having($data)
    {
        return self::_setData(self::$having_data, $data, __FUNCTION__);
    }

    public static function orderBy($data)
    {
        return self::_setData(self::$order_by_data, $data, __FUNCTION__);
    }

    public static function limit($data)
    {
        return self::_setData(self::$limit_data, $data, __FUNCTION__);
    }

    public static function insert($table, $columns, $insert_ignore = false)
    {
        if (self::_isEnd()) {
            return false;
        }

        self::$insert_data = $table;
        self::$into_data = $columns;
        self::$is_insert_ignore = $insert_ignore;

        return true;
    }

    public static function values($data, $is_select_values = false)
    {
        self::$is_select_values = $is_select_values;

        return self::_setData(self::$values_data, $data, __FUNCTION__);
    }

    public static function update($data)
    {
        return self::_setData(self::$update_data, $data, __FUNCTION__);
    }

    public static function set($data)
    {
        return self::_setData(self::$set_data, $data, __FUNCTION__);
    }

    public static function delete($data)
    {
        return self::_setData(self::$delete_data, $data, __FUNCTION__);
    }

    public static function using($data)
    {
        return self::_setData(self::$using_data, $data, __FUNCTION__);
    }

    public static function expr($column, $value, $operator = '=', $is_wrap = false, $wrapper_as = null)
    {
        return new SqlExpr($column, $value, $operator, $is_wrap, $wrapper_as);
    }

    public static function in($in_data, $column = null)
    {
        return new SqlIn($in_data, $column);
    }

    public static function join($join_table, $on_expr = null, $join_type = ' JOIN')
    {
        return new SqlJoin($join_table, $on_expr, $join_type);
    }

    public static function plainExpr($column, $value, $operator = '=', $is_wrap = false, $wrapper_as = null)
    {
        return new SqlPlainExpr($column, $value, $operator, $is_wrap, $wrapper_as);
    }

    public static function isNull($column)
    {
        return new SqlPlainExpr($column, 'null', 'is');
    }

    public static function isNotNull($column)
    {
        return new SqlPlainExpr($column, 'null', 'is not');
    }

    public static function plainString($in_data)
    {
        return new SqlPlainString($in_data);
    }

    public static function andWhere($data)
    {
        return new SqlAndWhere($data);
    }

    public static function orWhere($data)
    {
        return new SqlOrWhere($data);
    }

    public static function subQuery($as_name, $from, $data = '*', $where = null, $order_by = null, $limit = null, $group_by = null, $having = null)
    {
        return new SqlSubQuery($as_name, $from, $data, $where, $order_by, $limit, $group_by, $having);
    }

    public static function dateRange($column, $from = null, $to = null)
    {
        return new SqlDateRange($column, $from, $to);
    }

    public static function wildcard($key, $value, $location = SqlWildcard::WILDCARD_LOCATION_BOTH, $wildcard_type = '%')
    {
        return new SqlWildcard($key, $value, $location, $wildcard_type);
    }

    private static function _dateFrom($key, $val)
    {
        return SqlBuilder::expr($key, $val, '>=');
    }

    private static function _dateTo($key, $val)
    {
        return SqlBuilder::expr($key, $val, '<=');
    }

    private static function _dateRange($key, $val)
    {
        if (empty($val[0]) && empty($val[1])) {
            return null;
        }

        return SqlBuilder::dateRange($key, $val[0], $val[1]);
    }

    private static function _likeBothWildcard($key, $val)
    {
        return SqlBuilder::wildcard($key, $val);
    }

    public static function autoBuild(array $params, array $expr_map, $is_ignore_empty = false)
    {
        $builder_data = array();
        foreach ($params as $key => $val) {
            if (empty($val) && $is_ignore_empty) {
                continue;
            }
            $expr_type = isset($expr_map[$key]) ? $expr_map[$key] : self::EXPR_DEFAULT;
            if ($expr_type == self::EXPR_DEFAULT) {
                $builder_data[$key] = $val;
            } else {
                $sql_data = self::$expr_type($key, $val);
                if (!empty($sql_data)) {
                    $builder_data[] = $sql_data;
                }
            }
        }

        return $builder_data;
    }
}