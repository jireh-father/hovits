<?php
namespace framework\exception;

class LibraryException extends LogException
{
    public function __construct($message, $data = null, $log_type = null)
    {
        parent::__construct($message, $data, $log_type, 1);
    }
}