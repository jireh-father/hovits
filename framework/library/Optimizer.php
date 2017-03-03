<?php
namespace framework\library;

class Optimizer
{
    public static function optimizeJs($js_link)
    {
        if (empty($js_link)) {
            return null;
        }

        $dir_list = array(PATH_APP_JS, PATH_APP_RES_LIB);
        if (\Config::$ENABLE_MIDDLEWARE === true) {
            $dir_list[] = PATH_MW_JS;
            $dir_list[] = PATH_MW_RES_LIB;
        }

        $file_paths = self::_filterFilePaths($js_link, $dir_list, 'js');
        if (empty($file_paths)) {
            return null;
        }

        return self::_optimizeJs(array_keys($file_paths), array_values($file_paths));
    }

    private static function _filterFilePaths($files, $dir_list, $ext)
    {
        $file_paths = array();
        foreach ($files as $file) {
            foreach ($dir_list as $dir) {
                $file_path = "{$dir}/{$file}.{$ext}";
                if (is_file($file_path) === true) {
                    $file_paths[$file] = $dir;
                    continue;
                }
            }
        }

        return $file_paths;
    }

    private static function _optimizeJs($js_list, $dir_list)
    {
        $cache_file_name = self::_getCacheFileName($js_list, $dir_list, 'js') . '.js';
        if (self::_isReadableCache($cache_file_name, 'js') === false) {
            $js_code = self::_getJs($js_list, $dir_list);
            $optimized_code = self::_minifyJs($js_code);
            if (empty($optimized_code)) {
                return null;
            }
            File::writeToFile(PATH_CACHE . '/js/' . $cache_file_name, $optimized_code);
        }

        return $cache_file_name;
    }

    public static function optimizeJsCode($js_code)
    {
        $js_code_ret = self::_getJsCode($js_code);

        return $js_code_ret;
    }

    private static function _getCacheFileName($file_list, $dir_list, $ext)
    {
        $iLast = self::_getLastMtime($file_list, $dir_list, $ext);
        if ($iLast === 0) {
            return null;
        }
        $sFilename = implode(',', $file_list);

        return sha1($sFilename) . '_' . $iLast;
    }

    private static function _getLastMtime($aFile, $dir_list, $ext)
    {
        $iLast = 0;
        foreach ($aFile as $i => $f) {
            if (strpos(strtolower($f), 'http') === 0) {
                continue;
            }
            $file_path = $dir_list[$i] . DIRECTORY_SEPARATOR . $f . ".$ext";
            $mtime = filemtime($file_path);
            if ($mtime === false) {
                continue;
            }
            if ($iLast < $mtime) {
                $iLast = $mtime;
            }
        }

        return $iLast;
    }

    private static function _minifyJs($js_code)
    {
        return \JSMin::minify($js_code);
    }

    private static function _getJsCode($list)
    {
        if (empty($list)) {
            return '';
        }

        return implode(PHP_EOL, $list);
    }

    private static function _getJs($list, $dir_list)
    {
        if (empty($list)) {
            return '';
        }
        $js_code = '';
        foreach ($list as $i => $js_path) {
            $content = self::_getJsFileContent($js_path, $dir_list[$i]);
            if (!empty($content)) {
                $js_code .= $content;
            }
        }

        return $js_code;
    }

    private static function _getJsFileContent($js_path, $dir)
    {
        if (($local_path = self::_isReadableJs($js_path, $dir)) !== false) {
            return file_get_contents($local_path);
        } else {
            return null;
        }
    }

    private static function _isReadableJs($js_path, $dir)
    {
        if (is_readable($dir . DIRECTORY_SEPARATOR . $js_path . '.js') === true) {
            return $dir . DIRECTORY_SEPARATOR . $js_path . '.js';
        }

        return false;
    }

    private static function _isReadableCache($cache_file_name, $ext)
    {
        return is_readable(PATH_CACHE . DIRECTORY_SEPARATOR . $ext . DIRECTORY_SEPARATOR . $cache_file_name);
    }

    private static function _isReadableCss($css_path, $dir)
    {
        if (is_readable($dir . DIRECTORY_SEPARATOR . $css_path . '.css') === true) {
            return $dir . DIRECTORY_SEPARATOR . $css_path . '.css';
        }

        return false;
    }

    public static function optimizeCss($css_list)
    {
        if (empty($css_list)) {
            return null;
        }

        $dir_list = array(PATH_APP_CSS, PATH_APP_RES_LIB);
        if (\Config::$ENABLE_MIDDLEWARE === true) {
            $dir_list[] = PATH_MW_CSS;
            $dir_list[] = PATH_MW_RES_LIB;
        }

        $file_paths = self::_filterFilePaths($css_list, $dir_list, 'css');
        if (empty($file_paths)) {
            return null;
        }

        return self::_optimizeCss(array_keys($file_paths), array_values($file_paths));
    }

    private static function _optimizeCss($css_list, $dir_list)
    {
        $cache_file_name = self::_getCacheFileName($css_list, $dir_list, 'css') . '.css';
        if (self::_isReadableCache($cache_file_name, 'css') === false) {
            $css_code = self::_getCss($css_list, $dir_list);
            $optimized_code = self::_minifyCss($css_code);
            if (empty($optimized_code)) {
                return null;
            }
            File::writeToFile(PATH_CACHE . '/css/' . $cache_file_name, $optimized_code);
        }

        return $cache_file_name;
    }

    public static function optimizeCssCode($css_code)
    {
        return self::_getCssCode($css_code);
    }

    private static function _minifyCss($css_code)
    {
        return \Minify_CSS::minify($css_code);
    }

    private static function _getCssCode($list)
    {
        if (empty($list)) {
            return '';
        }

        return implode(PHP_EOL, $list);
    }

    private static function _getCss($list, $dir_list)
    {
        if (empty($list)) {
            return '';
        }

        $css_code = '';
        foreach ($list as $i => $css_path) {
            $content = self::_getCssFileContent($css_path, $dir_list[$i]);
            if (!empty($content)) {
                $css_code .= $content;
            }
        }

        return $css_code;
    }

    private static function _getCssFileContent($css_path, $dir)
    {
        if (($local_path = self::_isReadableCss($css_path, $dir)) !== false) {
            return file_get_contents($local_path);
        }

        return null;
    }
}