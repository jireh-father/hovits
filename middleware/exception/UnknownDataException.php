<?php
namespace middleware\exception;

use framework\exception\LogException;

class UnknownDataException extends LogException
{
    public function __construct($message, $data = null, $type = null)
    {
        parent::__construct($message, $data, $type, 1);
    }
}