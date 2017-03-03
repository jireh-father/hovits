<?php
namespace framework\library;

class String
{
    public static function toUnderscores($camelCase)
    {
        if (empty($camelCase)) {
            return '';
        }
        $encodeUnderscores = '';

        for ($i = 0; $i < strlen($camelCase); $i++) {
            $asciiValue = ord($camelCase[$i]);
            if ($asciiValue >= 65 && $asciiValue <= 90) {
                if ($i > 0) {
                    $encodeUnderscores .= '_';
                }
                $encodeUnderscores .= strtolower($camelCase[$i]);
            } else {
                $encodeUnderscores .= $camelCase[$i];
            }
        }

        return $encodeUnderscores;
    }

    public static function toCamelCase($underscores)
    {
        if (empty($underscores)) {
            return '';
        }
        $encodeCamelCase = strtoupper($underscores[0]);;

        for ($i = 1; $i < strlen($underscores); $i++) {
            if ($underscores[$i] == '_') {
                $i++;
                $encodeCamelCase .= strtoupper($underscores[$i]);
            } else {
                $encodeCamelCase .= $underscores[$i];
            }
        }

        return $encodeCamelCase;
    }

    public static function truncateString($string, $limit = 10, $over_flow_str = '...')
    {
        if (is_array($string)) {
            return 'Array';
        }
        if (is_object($string)) {
            return 'Object';
        }

        if (is_string($string) && strlen($string) > $limit) {
            return substr($string, 0, $limit) . $over_flow_str;
        }

        return $string;
    }

    public static function has($big_string, $small_string, $percent_limit = null)
    {
        if (empty($percent_limit)) {
            return strpos($big_string, $small_string) !== false;
        } elseif (strpos($big_string, $small_string) !== false) {
            $big_string_length = self::charLength($big_string);
            $small_string_length = self::charLength($small_string);

            return ($small_string_length / $big_string_length * 100) >= $percent_limit;
        }
    }

    public static function cutTail($string, $length)
    {
        return substr($string, 0, (-$length));
    }

    public static function cutHead($string, $length)
    {
        return substr($string, $length);
    }

    public static function cutBothSide($string, $head_length, $tail_length)
    {
        $ret = self::cutHead($string, $head_length);

        return self::cutTail($ret, $tail_length);
    }

    public static function explodeStrLen($string, $str_len)
    {
        if (strlen($string) < $str_len) {
            return array($string);
        }

        $list = array();
        while (strlen($string) >= $str_len) {
            $list[] = substr($string, 0, $str_len);
            $string = substr($string, $str_len);
        }

        if (strlen($string) > 0) {
            $list[] = $string;
        }

        return $list;
    }

    public static function getHead($string, $str_len)
    {
        return substr($string, 0, $str_len);
    }

    public static function cutFirstOccur($string, $search)
    {
        $pos = strpos($string, $search);
        if ($pos < 0) {
            return $string;
        }

        return substr($string, 0, $pos);
    }

    public static function getTail($string, $cnt)
    {
        return substr($string, -$cnt);
    }

    public static function getLastChar($string)
    {
        return $string[strlen($string) - 1];
    }

    public static function stripAllWhiteSpaces($string, $strip_char = ' ')
    {
        return trim(preg_replace('/\s+/', $strip_char, $string));
    }

    public static function explodeTrim($delimiter, $string, $del_empty = false)
    {
        $exploded = explode($delimiter, $string);
        $ret = array();
        foreach ($exploded as $item) {
            $trim_item = trim($item);
            if (!$del_empty) {
                $ret[] = $trim_item;
            } else {
                if (!empty($trim_item)) {
                    $ret[] = $trim_item;
                }
            }
        }

        return $ret;
    }

    public static function isDateTimeString($string)
    {
        return preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/', $string) === 1;
    }

    public static function isDateString($string)
    {
        return preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $string) === 1;
    }

    public static function isYearString($string)
    {
        return preg_match('/^[0-9]{4}$/', $string) === 1;
    }

    public static function strToDate($string_date, $format = 'Y-m-d')
    {
        return date($format, strtotime($string_date));
    }

    public static function extractNumbers($string)
    {
        if (empty($string)) {
            return null;
        }

        if (preg_match_all('/[0-9]+/', $string, $match) > 0) {
            return implode('', $match[0]);
        } else {
            return null;
        }
    }

    public static function charLength($string)
    {
        return strlen(utf8_decode($string));
    }

    public static function getSimilarPercent($s1, $s2)
    {
        similar_text($s1, $s2, $percent);

        return $percent;
    }

    public static function stripMultiSlash($str)
    {
        return preg_replace('/\/{2,}/', '/', $str);
    }
}