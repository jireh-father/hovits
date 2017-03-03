<?php
namespace framework\library;

class Css
{
    public static function wrapStyleTag($css_code)
    {
        if (empty($css_code)) {
            return null;
        }

        return "<style>{$css_code}</style>";
    }

    public static function wrapCssLinkTag($css_link)
    {
        if (empty($css_link)) {
            return null;
        }

        return '<link rel="stylesheet" type="text/css" href="' . $css_link . '" charset="utf-8">';
    }
}