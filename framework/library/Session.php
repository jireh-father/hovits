<?php
namespace framework\library;

class Session
{
    public static function startSession()
    {
        $sSessionId = session_id();
        if (empty($sSessionId) === true) {
            session_start();
        }
    }

    public static function get($key)
    {
        self::startSession();

        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }

        return null;
    }

    public static function is($key)
    {
        self::startSession();

        return !empty($_SESSION[$key]);
    }

    public static function set($key, $value)
    {
        self::startSession();
        $_SESSION[$key] = $value;
    }

    public static function destroy()
    {
        self::startSession();

        session_destroy();
        $_SESSION = null;
    }
}