<?php
namespace framework\exception;

use framework\library\Log;

class DatabaseException extends LogException
{
    public function __construct($message, $data = null, $log_type = null)
    {
        Log::disableDb();
        parent::__construct($message, $data, $log_type, 1);
        Log::restoreDisableDb();
    }
}