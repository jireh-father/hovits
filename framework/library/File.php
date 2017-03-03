<?php
namespace framework\library;

use framework\exception\FileException;

class File
{
    private static $file_handler = null;
    private static $file_path = null;

    /**
     * Open file.
     * @param $path
     * @return null|resource
     * @throws FileException
     */
    public static function openFile($path)
    {
        if (!empty(self::$file_handler)) {
            self::closeFile();
        }

        //Check whether the directory of file exists.
        $dir = dirname($path);
        if (!file_exists($dir)) {
            if (mkdir($dir, 0777, true) === false) {
                throw new FileException('Fail to make directories!', array_merge(array('dir_path' => $dir), error_get_last()));
            }
        }

        //Check whether the file or it's directory is writable.
        if (file_exists($path)) {
            // Check whether the file is writable.
            if (!is_writable($path)) {
                throw new FileException('The file is not writable!', $path);
            }
        } else {
            //Check whether the directory of file is writable.
            if (!is_writable($dir)) {
                throw new FileException('The directory of the file is not writable!', $dir);
            }
        }

        //Open the file.
        $file_handler = fopen($path, "a");
        if ($file_handler === false) {
            throw new FileException('Fail to open the file', array_merge(array('path' => $path), error_get_last()));
        }

        self::$file_handler = $file_handler;
        self::$file_path = $path;

        return self::$file_handler;
    }

    public static function closeFile()
    {
        if (fclose(self::$file_handler) === false) {
            throw new FileException('Fail to close file handler', array_merge(array('file_path' => self::$file_path), error_get_last()));
        }
        self::$file_handler = null;
        self::$file_path = null;

        return true;
    }

    public static function append($contents)
    {
        if (empty(self::$file_handler)) {
            throw new FileException('File handler is empty.');
        }

        if (fwrite(self::$file_handler, $contents) === false) {
            throw new FileException('Fail fwrite.', array_merge(array('contents' => $contents), error_get_last()));
        }

        return true;
    }

    public static function appendToFile($path, $contents)
    {
        self::openFile($path);
        $ret = self::append($contents);
        self::closeFile();

        return $ret;
    }

    public static function writeToFile($path, $contents)
    {
        if (is_file($path)) {
            unlink($path);
        }
        self::openFile($path);
        $ret = self::append($contents);
        self::closeFile();

        return $ret;
    }

    public static function removeDir($dir)
    {
        $files = scandir($dir);
        unset($files[0]);
        unset($files[1]);
        foreach ($files as $file) {
            $full_path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_file($full_path)) {
                @unlink($full_path);
            } elseif (is_dir($full_path)) {
                self::removeDir($full_path);
            }
        }
        @rmdir($dir);
    }

    public static function getFilePaths($dir_path, $is_recursive = false)
    {
        if (empty($dir_path)) {
            return false;
        }
        $file_paths = array();
        $files = scandir($dir_path);
        foreach ($files as $path) {
            if ($path == '.' || $path == '..') {
                continue;
            }
            $full_path = "{$dir_path}/$path";
            if (is_dir($full_path)) {
                if ($is_recursive === true) {
                    $file_paths = array_merge($file_paths, self::getFilePaths($full_path, $is_recursive));
                }
            } else {
                $file_paths[] = $full_path;
            }
        }

        return $file_paths;
    }

    public static function getFirstFile($dir_path)
    {
        $dir = opendir($dir_path);
        while (false !== ($file = readdir($dir))) {
            if ($file != '.' && $file != '..') {
                return $file;
            }
        }

        return null;
    }

    public static function getFileContents($file_path)
    {
        if (!is_file($file_path)) {
            return false;
        }

        return file_get_contents($file_path);
    }

    public static function getFileList($dir, $is_full_path = false)
    {
        if (!is_dir($dir)) {
            return null;
        }

        $files = scandir($dir);
        $ret_files = array();
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || is_dir("{$dir}/{$file}")) {
                continue;
            }
            if ($is_full_path === true) {
                $ret_files[] = "{$dir}/{$file}";
            } else {
                $ret_files[] = $file;
            }
        }

        return $ret_files;
    }

    public static function getDirList($dir, $is_full_path = false)
    {
        if (!is_dir($dir)) {
            return null;
        }

        $files = scandir($dir);
        $ret_files = array();
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || is_file("{$dir}/{$file}")) {
                continue;
            }
            if ($is_full_path === true) {
                $ret_files[] = "{$dir}/{$file}";
            } else {
                $ret_files[] = $file;
            }
        }

        return $ret_files;
    }

    public static function getList($dir, $is_full_path = false)
    {
        if (!is_dir($dir)) {
            return null;
        }

        $files = scandir($dir);
        $ret_files = array();
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if ($is_full_path === true) {
                $ret_files[] = "{$dir}/{$file}";
            } else {
                $ret_files[] = $file;
            }
        }

        return $ret_files;
    }

    public static function move($source, $dest)
    {
        $ret = self::copy($source, $dest);
        if ($ret === false) {
            return false;
        }

        $ret = unlink($source);

        if ($ret === false) {
            Log::warning('삭제 실패', $source);
        }

        return $ret;
    }

    public static function copy($source, $dest)
    {
        if (!is_file($source)) {
            Log::warning('source 없는 파일', $source);

            return false;
        }

        if (!is_dir(dirname($dest))) {
            $ret = mkdir(dirname($dest), 0777, true);
            if ($ret === false) {
                Log::warning('디렉토리 생성 실패', dirname($dest));

                return false;
            }
        }

        if (is_file($dest)) {
            unlink($dest);
        }

        $ret = copy($source, $dest);
        if ($ret === false || !is_file($dest)) {
            Log::warning('카피 실패', array($source, $dest));
        }

        return $ret;
    }
}
