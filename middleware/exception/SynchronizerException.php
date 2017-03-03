<?php
namespace middleware\exception;

use framework\exception\LogException;

class SynchronizerException extends LogException
{
    private static $content_type = null;
    private static $content_id = null;
    private static $content_vendor = null;

    public static function setContentType($content_type)
    {
        self::$content_type = $content_type;
    }

    public static function setContentVendor($content_vendor)
    {
        self::$content_vendor = $content_vendor;
    }

    public static function setContentId($content_id)
    {
        self::$content_id = $content_id;
    }

    public function __construct($message, $data = null, $type = null)
    {
        if (!empty(self::$content_type)) {
            if (!is_array($data)) {
                $data = (array)$data;
            }
            $data['content_type'] = self::$content_type;
        }

        if (!empty(self::$content_id)) {
            if (!is_array($data)) {
                $data = (array)$data;
            }
            $data['content_id'] = self::$content_id;
        }

        if (!empty(self::$content_vendor)) {
            if (!is_array($data)) {
                $data = (array)$data;
            }
            $data['content_vendor'] = self::$content_vendor;
        }

        parent::__construct($message, $data, $type, 1);
    }
}