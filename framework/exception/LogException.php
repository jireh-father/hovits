<?php
namespace framework\exception;

use framework\library\Log;
use framework\library\Time;

class LogException extends \Exception
{
    const DEFAULT_LOG_STACK_IDX = 4;

    public function __construct($message, $data = null, $type = null, $extend_cnt = 0)
    {
        parent::__construct($message);
        if (empty($type) === true) {
            $type = baseClassName(get_called_class());
        }

        $this->log($message, $data, $type, $extend_cnt);
    }

    private function log($message, $data, $type, $extend_cnt)
    {
        $log_data = array(
            'execution_time' => Time::getExecutionTime(),
            'file'           => $this->getFile(),
            'line'           => $this->getLine(),
            'trace'          => $this->getTraceAsString()
        );

        if (!empty($data)) {
            $log_data['data'] = $data;
        }

        Log::setLogType($type);
        Log::error($message, $log_data, self::DEFAULT_LOG_STACK_IDX + $extend_cnt);
        Log::restoreLogType();
    }
}