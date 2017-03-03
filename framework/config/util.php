<?php
if (!function_exists('debug')) {
    function debug($data, $_ = null)
    {
        $args = func_get_args();
        echo '<pre>';
        foreach ($args as $arg) {
            var_dump($arg);
        }
        echo '</pre>';
    }
}

if (!function_exists('getBacktrace')) {
    function getBacktrace(array $traces = null)
    {
        if (empty($traces)) {
            $traces = debug_backtrace();
            array_shift($traces);
        }
        $trace_array = '';

        foreach ($traces as $i => $trace) {
            $args_array = array();
            foreach ($trace['args'] as $arg) {
                $args_array[] = ("'" . \framework\library\String::truncateString($arg) . "'");
            }
            if (empty($args_array)) {
                $args_string = '';
            } else {
                $args_string = implode(', ', $args_array);
            }
            $file = isset($trace['file']) ? $trace['file'] : '';
            $line = isset($trace['line']) ? $trace['line'] : '';
            $function = isset($trace['function']) ? $trace['function'] : '';
            $class = isset($trace['class']) ? $trace['class'] : '';
            $type = isset($trace['type']) ? $trace['type'] : '';
            $trace_array[] = "#{$i} {$file}({$line}): {$class}{$type}{$function}({$args_string})";
        }

        return implode(PHP_EOL, $trace_array);
    }
}

if (!function_exists('baseClassName')) {
    function baseClassName($class_name)
    {
        $class_name = str_replace('\\', '/', $class_name);

        return basename($class_name);
    }
}

if (!function_exists('encodeJson')) {
    function encodeJson($mData)
    {
        return json_encode(encodeUtf8($mData));
    }
}

if (!function_exists('encodeUtf8')) {
    function encodeUtf8(&$mData)
    {
        if (is_array($mData) === true) {
            foreach ($mData as $sKey => $mVal) {
                $mData[$sKey] = encodeUtf8($mVal);
            }
        } else if (is_object($mData)) {
            $mData = (array)$mData;
            $mData = encodeUtf8($mData);
        } else {
            $mData = mb_convert_encoding($mData, 'UTF-8', 'UTF-8');
        }

        return $mData;
    }
}

if (!function_exists('emptyOr')) {
    /**
     * 전체 파라미터중 하나라도 empty하면 true 반환
     * @return bool
     */
    function emptyOr($mVar, $_ = null)
    {
        $aArgs = func_get_args();

        if (empty($aArgs) === true) {
            return true;
        }

        foreach ($aArgs as $mArg) {
            if (empty($mArg) === true) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('emptyAll')) {
    /**
     * 전체 파라미터가 empty할 경우만 true 반환
     * @return mixed
     */
    function emptyAll($mVar, $_ = null)
    {
        $aArgs = func_get_args();

        if (empty($aArgs) === true) {
            return true;
        }

        foreach ($aArgs as $mArg) {
            if (empty($mArg) === false) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('emptyOrArray')) {
    /**
     * 배열 원소중 하나라도 empty하면 true 반환
     * @return bool
     */
    function emptyOrArray(array $aArray = null)
    {
        if (empty($aArray) === true) {
            return true;
        }

        foreach ($aArray as $mEle) {
            if (empty($mEle) === true) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('emptyAllArray')) {
    /**
     * @param array|null $aArray
     * @return bool
     */
    function emptyAllArray(array $aArray = null)
    {
        if (empty($aArray) === true) {
            return true;
        }

        foreach ($aArray as $mEle) {
            if (empty($mEle) === false) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('isNotSetOr')) {
    /**
     * 전체 파라미터중 하나라도 isset이 아니면 true 반환
     * @return bool
     */
    function isNotSetOr($mVar, $_ = null)
    {
        $aArgs = func_get_args();

        if (empty($aArgs) === true) {
            return true;
        }

        foreach ($aArgs as $mArg) {
            if (isset($mArg) === false) {
                return true;
            }
        }

        return false;
    }
}
