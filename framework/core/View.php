<?php
namespace framework\core;

use framework\exception\SystemException;

class View
{
    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function block($path)
    {
        $app_path = PATH_APP_BLOCK . '/' . $path . '.php';
        if(is_file($app_path)){
            return $app_path;
        }
        $mw_path = PATH_MW_BLOCK . '/' . $path . '.php';
        if (is_file($mw_path)) {
            return $mw_path;
        }

        return null;
    }

    public function renderTwig($path, $data, $is_print = true, $is_app_view = true)
    {
        $dir_path = $is_app_view === true ? dirname(PATH_APP_VIEW) : dirname(PATH_MW_VIEW);
        $loader = new \Twig_Loader_Filesystem($dir_path);
        $twig = new \Twig_Environment(
            $loader, array(
                'debug' => \Config::$ENABLE_DEBUG_TWIG
            )
        );

        $response_html = $twig->render($path, $data);
        if ($is_print === true) {
            echo $response_html;
        } else {
            return $response_html;
        }
    }

    public function renderPhp($path, $data, $is_print = true)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $$k = $v;
            }
        }

        if (!file_exists($path)) {
            throw new SystemException('뷰 파일이 없습니다.', $path);
        }

        ob_start();

        include $path;

        if ($is_print === false) {
            $buffer = ob_get_contents();
            ob_end_clean();

            return $buffer;
        }

        ob_end_flush();
    }
}