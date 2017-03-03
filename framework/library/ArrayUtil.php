<?php
namespace framework\library;

class ArrayUtil
{
    public static function mergeArray($array1, $array2 = null, $_ = null)
    {
        $arg_cnt = func_num_args();
        if ($arg_cnt < 2) {
            return null;
        }
        $args = func_get_args();
        $merged_array = array();
        foreach ($args as $arg) {
            if (!is_array($arg)) {
                $arg = (array)$arg;
            }
            $merged_array = array_merge($merged_array, $arg);
        }

        return $merged_array;
    }

    private static function _mergeAssocArrayRecur($array1, $array2)
    {
        $result = array();
        foreach ($array1 as $key1 => $val1) {
            if (isset($array2[$key1])) {
                if (self::isAssoc($val1) || self::isAssoc($array2[$key1])) {
                    $result[$key1] = self::_mergeAssocArrayRecur($val1, $array2[$key1]);
                } else {
                    $result[$key1] = array_merge($val1, $array2[$key1]);
                }
                unset($array2[$key1]);
            } else {
                $result[$key1] = $val1;
            }
        }

        if (empty($array2)) {
            return $result;
        }

        foreach ($array2 as $key2 => $val2) {
            $result[$key2] = $val2;
        }

        return $result;
    }

    public static function mergeAssocArrayRecur($array1, $array2 = null, $_ = null)
    {
        $arg_cnt = func_num_args();
        if ($arg_cnt < 2) {
            return null;
        }
        $args = func_get_args();
        $merged_array = array();
        foreach ($args as $arg) {
            if (!is_array($arg)) {
                $arg = (array)$arg;
            }
            $merged_array = self::_mergeAssocArrayRecur($merged_array, $arg);
        }

        return $merged_array;
    }

    public static function stripNull(array &$array)
    {
        foreach ($array as $key => $item) {
            if ($item === null) {
                unset($array[$key]);
            }
        }
    }

    public static function isNumeric(array $array)
    {
        $keys = array_keys($array);
        foreach ($keys as $key) {
            if (is_int($key) === false) {
                return false;
            }
        }

        return true;
    }

    public static function isAssoc(array $array)
    {
        return ($array !== array_values($array));
    }

    public static function isRows(array $data, $is_rigorous = false)
    {
        if (empty($data) || !isset($data[0]) || !is_array($data[0]) || !self::isNumeric($data)) {
            return false;
        }

        if ($is_rigorous === true) {
            $keys = array_keys($data[0]);
            foreach ($data as $element) {
                if (!self::isAssoc($element)) {
                    return false;
                }
                if ($keys !== array_keys($element)) {
                    return false;
                }
            }

            return true;
        } else {
            return self::isAssoc($data[0]);
        }
    }

    public static function isNumericRows(array $data, $is_rigorous = false)
    {
        if (empty($data) || !isset($data[0]) || !is_array($data[0]) || !self::isNumeric($data)) {
            return false;
        }

        if ($is_rigorous === true) {
            $cnt = count($data[0]);
            foreach ($data as $element) {
                if (!self::isNumeric($element)) {
                    return false;
                }
                if ($cnt !== count($element)) {
                    return false;
                }
            }

            return true;
        } else {
            return self::isNumeric($data[0]);
        }
    }

    public static function isColumns(array $data, $is_rigorous = false)
    {
        if (empty($data) || self::isNumeric($data) || !is_array(reset($data))) {
            return false;
        }

        if ($is_rigorous === true) {
            $first_key = key($data);
            $cnt = count($data[$first_key]);
            foreach ($data as $element) {
                if (!self::isNumeric($element)) {
                    return false;
                }
                if ($cnt != count($element)) {
                    return false;
                }
            }

            return true;
        } else {
            $first_key = key($data);

            return self::isNumeric($data[$first_key]);
        }
    }

    public static function columnsToRows(array $columns_array)
    {
        if (!self::isColumns($columns_array)) {
            return false;
        }

        $value_cnt = count(reset($columns_array));
        $rows_array = array();
        for ($i = 0; $i < $value_cnt; $i++) {
            $tmp = array();
            foreach ($columns_array as $key => $values) {
                $tmp[$key] = $values[$i];
            }
            $rows_array[$i] = $tmp;
        }

        return $rows_array;
    }

    public static function columnsToValueRows(array $columns_array, array $same_value_array = null)
    {
        if (!self::isColumns($columns_array)) {
            return false;
        }
        $value_cnt = count(reset($columns_array));
        $value_rows = array();
        for ($i = 0; $i < $value_cnt; $i++) {
            $tmp = array();
            foreach ($columns_array as $values) {
                $tmp[] = $values[$i];
            }
            $value_rows[$i] = empty($same_value_array) ? $tmp : array_merge($tmp, $same_value_array);
        }

        return $value_rows;
    }

    public static function toMap($key, array $rows, $is_unique = true, $column_name = null)
    {
        if (empty($rows) || empty($key)) {
            return false;
        }
        $map = array();
        foreach ($rows as $row) {
            if (empty($row[$key])) {
                continue;
            }
            if (empty($column_name)) {
                $ret_row = $row;
            } else {
                $ret_row = $row[$column_name];
            }
            if ($is_unique) {
                $map[$row[$key]] = $ret_row;
            } else {
                if (empty($map[$row[$key]])) {
                    $map[$row[$key]] = array();
                }
                $map[$row[$key]][] = $ret_row;
            }
        }

        return $map;
    }

    public static function isEmpty(array $array)
    {
        if (empty($array)) {
            return true;
        }

        foreach ($array as $val) {
            if (is_array($val)) {
                $ret = self::isEmpty($val);
                if ($ret === false) {
                    return false;
                }
            } else {
                if (isset($val)) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function getArrayColumn($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();

        if ($argc < 2) {

            return null;
        }

        if (!is_array($params[0])) {

            return null;
        }

        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {

            return false;
        }

        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {

            return false;
        }

        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string)$params[1] : null;

        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int)$params[2];
            } else {
                $paramsIndexKey = (string)$params[2];
            }
        }

        $resultArray = array();

        foreach ($paramsInput as $row) {

            $key = $value = null;
            $keySet = $valueSet = false;

            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string)$row[$paramsIndexKey];
            }

            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }

        }

        return $resultArray;
    }

    public static function toAssoc($arr)
    {
        if (empty($arr)) {
            return null;
        }
        $ret = array();
        foreach ($arr as $val) {
            $ret[$val] = $val;
        }

        return $ret;
    }

    public static function buildMatrix($list, $first_col, $second_col, $value_col = null, $value = null)
    {
        $matrix_list = array();
        $last_id = 0;
        foreach ($list as $row) {
            $first_id = $row[$first_col];
            $second_id = $row[$second_col];
            if ($first_id != $last_id) {
                $matrix_list[$first_id] = array();
                $last_id = $first_id;
            }
            if (!empty($value_col)) {
                $val = $row[$value_col];
            } else {
                $val = ($value === null ? 1 : $value);
            }

            $matrix_list[$first_id][$second_id] = $val;
        }

        return $matrix_list;
    }

    public static function toStringElements($arr)
    {
        if (empty($arr)) {
            return null;
        }

        foreach ($arr as $key => $val) {
            if (is_integer($val)) {
                $arr[$key] = "{$val}";
            }
        }

        return $arr;
    }
}