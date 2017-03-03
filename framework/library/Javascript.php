<?php
namespace framework\library;

class Javascript
{
    public static function wrapJsScriptTag($js_code_or_src, $is_src = false)
    {
        if (empty($js_code_or_src)) {
            return null;
        }
        if ($is_src === true) {
            return '<script type="text/javascript" src="' . $js_code_or_src . '"></script>';
        } else {
            return "<script type='text/javascript'>{$js_code_or_src}</script>";
        }
    }
}