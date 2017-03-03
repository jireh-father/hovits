<?php
namespace middleware\library;

use framework\exception\LibraryException;
use framework\library\Log;

class Curl
{
    private static $headers = null;
    private static $options = null;
    private static $connect_timeout = null;
    private static $read_timeout = null;

    public static function setHeaders($headers)
    {
        self::$headers = $headers;
    }

    public static function setOptions($options)
    {
        self::$options = $options;
    }

    public static function setConnectTimeout($connect_timeout)
    {
        self::$connect_timeout = $connect_timeout;
    }

    public static function setReadTimeout($read_timeout)
    {
        self::$read_timeout = $read_timeout;
    }

    public static function post($url, $params = null, $retry = 1, $headers = null, $timeout = 30, $connect_timeout = 5, $options = null)
    {
        if ($retry < 1) {
            throw new LibraryException('retry 수가 1보다 작음');
        }
        $ch = curl_init();

        self::_setDefaultOption($ch, $url, $timeout, $connect_timeout);
        curl_setopt($ch, CURLOPT_POST, true);

        self::_setHeader($ch, $headers);
        self::_setOption($ch, $options);

        if (!empty($params)) {
            $param_query = self::_filterParams($params);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param_query);
        }

        list($ret, $error, $error_no) = self::_exec($ch, $retry);

        if ($ret === false) {
            throw new LibraryException('curl post 호출 실패', compact('url', 'params', 'headers', 'timeout', 'options', 'error', 'error_no'));
        }

        return $ret;
    }


    public static function get($url, $params = null, $retry = 1, $headers = null, $timeout = 30, $connect_timeout = 5, $options = null)
    {
        if ($retry < 1) {
            throw new LibraryException('retry 수가 1보다 작음');
        }
        $ch = curl_init();

        if (!empty($params)) {
            $param_query = self::_filterParams($params);
            $url .= "?{$param_query}";
        }

        self::_setDefaultOption($ch, $url, $timeout, $connect_timeout);
        self::_setHeader($ch, $headers);
        self::_setOption($ch, $options);

        list($ret, $error, $error_no) = self::_exec($ch, $retry);

        if ($ret === false) {
            throw new LibraryException('curl get 호출 실패', compact('url', 'params', 'headers', 'timeout', 'options', 'error', 'error_no'));
        }

        return $ret;
    }

    private static function _filterParams($params)
    {
        if (is_array($params)) {
            $param_query = http_build_query($params);
        } else {
            $param_query = $params;
        }

        return $param_query;
    }

    private static function _setDefaultOption($ch, $url, $timeout, $connect_timeout)
    {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if (self::$connect_timeout !== null) {
            $connect_timeout = self::$connect_timeout;
        }

        if (self::$read_timeout !== null) {
            $timeout = self::$read_timeout;
        }
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $connect_timeout);
        //        curl_setopt($ch, CURLOPT_HEADER, false);
    }

    private static function _exec($ch, $retry)
    {
        for ($i = 0; $i < $retry; $i++) {
            $output = @curl_exec($ch);
            if (empty($output)) {
                continue;
            }

            if ($output !== false) {
                break;
            }
        }

        $error = null;
        $error_no = null;
        if ($output === false) {
            $error = curl_error($ch);
            $error_no = curl_errno($ch);
        }

        curl_close($ch);

        self::init();

        return array($output, $error, $error_no);
    }

    public static function init()
    {
        self::$connect_timeout = null;
        self::$read_timeout = null;
        self::$headers = null;
        self::$options = null;
    }

    private static function _setOption($ch, $options)
    {
        if (empty($options) && !empty(self::$options)) {
            $options = self::$options;
        }

        if (!empty($options)) {
            foreach ($options as $option_key => $option_val) {
                curl_setopt($ch, $option_key, $option_val);
            }
        }
    }

    private static function _setHeader($ch, $headers)
    {
        if (empty($headers) && !empty(self::$headers)) {
            $headers = self::$headers;
        }

        if (empty($headers)) {
            return;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
}