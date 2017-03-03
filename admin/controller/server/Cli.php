<?php
namespace controller\server;

use controller\AdminBase;
use framework\library\Log;
use middleware\library\Shell;

class Cli extends AdminBase
{
    private static $command_list = array(
        'ls /home/jireh/public_html/temp/crawling/kofic/movie',
        'nohup php /home/jireh/public_html/admin/console/kofic_crawler.php > /dev/null &',
        "pkill -f '/opt/lampstack-5.4.24-0/php/bin/php.bin /home/jireh/public_html/admin/console/kofic_crawler.php'",
        "ps -ef | grep php",
        "ls -Rl /home/jireh/public_html/temp/crawling/kofic/movie | grep ^- | wc -l"
    );

    public function index()
    {
        $this->addJs('util');
        $this->addJs('server/cli');

        $this->setViewData(array('command_list' => self::$command_list));
    }

    public function exec()
    {
        $params = $this->getParams();
        if (empty($params['command']) === true) {
            $this->ajaxFail('command 없음');
        }
        $type = $params['type'];

        Log::info('명령 실행', $params);

        if ($type === SERVER_CMD_TYPE_CLI) {
            Shell::exec($params['command'], $output);
            Log::info('cli 실행 결과', $output);
            $output = implode(PHP_EOL, $output);
        } elseif ($type === SERVER_CMD_TYPE_PHP) {
            $output = eval($params['command']);
            Log::info('php code 실행 결과', $output);
            $eol = PHP_EOL;
            $output = "[::PHP CODE::]{$eol}{$params['command']}{$eol}{$eol}[::RESULT::]{$eol}{$output}";
        } elseif ($type === SERVER_CMD_TYPE_FUNC) {
            $function_name = $params['command'];
            if (empty($params['function_type']) === true) {
                $output = call_user_func($function_name);
            } elseif ($params['function_type'] === '->') {
                $class_method = explode('->', $function_name);
                $class = $class_method[0];
                $method = $class_method[1];
                $object = new $class();
                $output = $object->$method();
            } elseif ($params['function_type'] === '::') {
                $class_method = explode('::', $function_name);
                $class = $class_method[0];
                $method = $class_method[1];
                $output = $class::$method();
            }
        }

        $this->ajaxSuccess('성공', $output);
    }
}