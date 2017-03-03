<?php
namespace middleware\library;

class Value
{
    private static $value_container = array();
    private static $current_key;

    public static function setValue($value, $key)
    {
        if (empty($value) || empty($key)) {
            return false;
        }

        if (!is_array($value)) {
            $value = (array)$value;
        }

        self::$value_container[$key] = $value;

        return true;
    }

    public static function setKey($key)
    {
        if (empty($key)) {
            return false;
        }
        self::$current_key = $key;
    }

    public static function get($key, $container_key = null)
    {
        if (empty($key)) {
            return null;
        }
        if (empty($container_key)) {
            $container_key = self::$current_key;
        }

        if (isset(self::$value_container[$container_key][$key])) {
            return self::$value_container[$container_key][$key];
        }

        return null;
    }

    public static function pr()
    {
        if (empty($key)) {
            return;
        }
        if (empty($container_key)) {
            $container_key = self::$current_key;
        }

        if (isset(self::$value_container[$container_key][$key])) {
            echo self::$value_container[$container_key][$key];
        }
    }
}
