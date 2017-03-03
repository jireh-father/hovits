<?php
/**
 * @param $e \Exception
 */
function __exceptionHandler($e)
{
    if (!$e instanceof \framework\exception\LogException) {
        $log_data = array(
            'execution_time' => \framework\library\Time::getExecutionTime(),
            'file'           => $e->getFile(),
            'line'           => $e->getLine(),
            'trace'          => $e->getTraceAsString()
        );
        if (PHP_WEB === true) {
            \framework\library\Log::setLogType('http request');
        } else {
            \framework\library\Log::setLogType('cli execution');
        }
        \framework\library\Log::error($e->getMessage(), $log_data);
        \framework\library\Log::restoreLogType();
    }
}

function __shutdownHandler()
{
    $error = error_get_last();
    if (empty($error)) {
        return;
    }

    $iErrorCondition = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;

    if (($error['type'] & $iErrorCondition) === 1) {
        $error['execution_time'] = \framework\library\Time::getExecutionTime();
        if (PHP_WEB === true) {
            \framework\library\Log::setLogType('http request');
        } else {
            \framework\library\Log::setLogType('cli execution');
        }

        \framework\library\Log::error($error['message'], $error);
        \framework\library\Log::restoreLogType();
    }
}