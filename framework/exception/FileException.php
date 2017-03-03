<?php
namespace framework\exception;

use framework\library\Log;

class FileException extends LogException
{
    public function __construct($message, $data = null, $log_type = null)
    {
        Log::disableFile();
        parent::__construct($message, $data, $log_type, 1);
        Log::restoreDisableFile();
    }
}