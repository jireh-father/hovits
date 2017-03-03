<?php
namespace framework\library;

class Time
{
    const TIME_FORMAT_YMD = 'Y-m-d';
    const TIME_FORMAT_YMD_PLAIN = 'Ymd';
    const TIME_FORMAT_YMDHIS_PLAIN = 'YmdHis';
    const TIME_FORMAT_YMDHIS = 'Y-m-d H:i:s';

    private static $STOP_WATCH_BEGIN_TIME;

    public static function getDate($format)
    {
        return date($format);
    }

    public static function YmdHis()
    {
        return date(self::TIME_FORMAT_YMDHIS);
    }

    public static function Ymd()
    {
        return date(self::TIME_FORMAT_YMD);
    }

    public static function YmdPlain()
    {
        return date(self::TIME_FORMAT_YMD_PLAIN);
    }

    public static function YmdHisPlain()
    {
        return date(self::TIME_FORMAT_YMDHIS_PLAIN);
    }

    public static function getMicroTimeString()
    {
        list($usec, $sec) = explode(" ", microtime());

        return date("YmdHis", $sec) . $usec;
    }

    public static function getRequestTime()
    {
        return $_SERVER['REQUEST_TIME'];
    }

    public static function getRequestTimeFloat()
    {
        return $_SERVER['REQUEST_TIME_FLOAT'];
    }

    public static function getExecutionTime()
    {
        return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
    }

    public static function beginStopWatch($adding_time = null)
    {
        self::$STOP_WATCH_BEGIN_TIME = microtime(true);
        if (!empty($adding_time)) {
            self::$STOP_WATCH_BEGIN_TIME -= $adding_time;
        }
    }

    public static function endStopWatch()
    {
        return microtime(true) - self::$STOP_WATCH_BEGIN_TIME;
    }

    public static function extractDateStrings($string_date, $date_glue = '-')
    {
        $ret = preg_match_all('/([0-9]{4}[.-]?[0-9]{2}[.-]?[0-9]{2})/', $string_date, $matches);
        if ($ret === false || empty($matches[0])) {
            return null;
        }
        $dates = array();
        foreach ($matches[0] as $date) {
            $tmp = str_replace(array('.', '-'), array($date_glue, $date_glue), $date);
            if (strlen($tmp) !== 10 && !empty($date_glue)) {
                $tmp = date("Y{$date_glue}m{$date_glue}d", strtotime($tmp));
            }
            $dates[] = $tmp;
        }

        return $dates;
    }

    public static function extractDateString($string_date, $date_glue = '-')
    {
        $dates = self::extractDateStrings($string_date, $date_glue);
        if (empty($dates)) {
            return null;
        }

        return $dates[0];
    }

    public static function filterYmdDate($string_date, $date_glue = '-')
    {
        $ret = preg_match('/[0-9]{4}' . $date_glue . '[0-9]{2}' . $date_glue . '[0-9]{2}/', $string_date, $matches);
        if ($ret !== 1) {
            return null;
        }

        return $matches[0];
    }

    public static function filterYmDate($string_date, $date_glue = '-')
    {
        $ret = preg_match('/[0-9]{4}' . $date_glue . '[0-9]{2}/', $string_date, $matches);
        if ($ret !== 1) {
            return null;
        }

        return $matches[0];
    }

    public static function filterYearDate($string_date)
    {
        $ret = preg_match('/[0-9]{4}/', $string_date, $matches);
        if ($ret !== 1) {
            return null;
        }

        return $matches[0];
    }

    public static function subMonths($months = 1, $string_date = null, $string_format = 'Y-m-d')
    {
        $date = new \DateTime($string_date);
        $date->sub(new \DateInterval("P{$months}M"));

        return $date->format($string_format);
    }

    public static function subDays($days = 1, $string_date = null, $string_format = 'Y-m-d')
    {
        $date = new \DateTime($string_date);
        $date->sub(new \DateInterval("P{$days}D"));

        return $date->format($string_format);
    }

    public static function addDays($days = 1, $string_date = null, $string_format = 'Y-m-d')
    {
        $date = new \DateTime($string_date);
        $date->add(new \DateInterval("P{$days}D"));

        return $date->format($string_format);
    }

    public static function diffDays($date_str1, $date_str2, $has_minus = false)
    {
        if (emptyOr($date_str1, $date_str2)) {
            return null;
        }

        $datetime1 = date_create($date_str1);
        $datetime2 = date_create($date_str2);

        if (emptyOr($date_str1, $date_str2)) {
            return null;
        }

        $interval = date_diff($datetime1, $datetime2);
        if (empty($interval) === true) {
            return null;
        }

        if ($has_minus) {
            if ($date_str1 < $date_str2) {
                return (int)$interval->format('-%a');
            } else {
                return (int)$interval->format('%a');
            }
        } else {
            return (int)$interval->format('%a');
        }
    }
}