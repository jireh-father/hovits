<?php
namespace exception;

use framework\exception\LogException;

class HovitsException extends LogException
{
    public function __construct($message, $data = null, $type = null)
    {
        parent::__construct($message, $data, $type, 1);
    }
}