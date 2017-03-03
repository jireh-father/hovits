<?php

namespace framework\library;

class Redirect
{
    public static function redirect($url, $msg = null)
    {
        $location_replace = "location.replace('{$url}');";
        if (!empty($msg)) {
            $alert_msg = "alert('" . str_replace(array("\n", "'"), array('\\n', '\''), $msg) . "');";
            $redirect_js = $alert_msg . $location_replace;
        } else {
            $redirect_js = $location_replace;
        }
        $redirect_js = Javascript::wrapJsScriptTag($redirect_js);
        echo $redirect_js;
        exit;
    }

    public static function back($msg = null)
    {
        $back_js = "history.back();";

        if (!empty($msg)) {
            $back_js = "alert('{$msg}');" . $back_js;
        }

        $back_js = Javascript::wrapJsScriptTag($back_js);
        echo $back_js;
        exit;
    }
}
