<?php
namespace framework\library;

class FileCache
{
    const CACHE_FOREVER = 'forever';

    private static function _getCachePath()
    {
        return PATH_CACHE . '/file/';
    }

    private static function _isExpired($sExpireTime)
    {
        if ($sExpireTime === self::CACHE_FOREVER) {
            return false;
        }
        $oNow = new \DateTime('now');
        $oExpireTime = new \DateTime($sExpireTime);

        return $oExpireTime < $oNow;
    }

    private static function _createExpireTime($iExpireSec = null)
    {
        if ($iExpireSec === null || is_int($iExpireSec) === false) {
            return self::CACHE_FOREVER . PHP_EOL;
        }
        $iTimeStamp = mktime(date("H"), date("i"), date("s") + $iExpireSec, date("m"), date("d"), date("Y"));

        return date('Y/m/d H:i:s', $iTimeStamp) . PHP_EOL;
    }

    private static function _convertDataString($mData)
    {
        if (is_object($mData) === true) {
            $mData = (array)$mData;
        }

        if (is_array($mData)) {
            return encodeJson($mData);
        } else {
            return $mData;
        }
    }

    public static function get($sKey)
    {
        $sCachePath = self::_getCachePath();

        $sFileName = self::_filterFileName($sKey);
        $sFullPath = $sCachePath . $sFileName;
        if (is_file($sFullPath) === true) {
            $handle = @fopen($sFullPath, "r");
            if (is_resource($handle) === true) {
                $sExpireTime = fgets($handle);
                if ($sExpireTime === false) {
                    return null;
                }
                //만료일이 지났으면 파일을 지우고 null 리턴
                if (self::_isExpired(trim($sExpireTime)) === true) {
                    if (is_file($sFullPath) === true) {
                        @unlink($sFullPath);
                    }

                    return null;
                }
                $sData = '';
                while (($buffer = fgets($handle)) !== false) {
                    $sData .= $buffer;
                }
                $sData = trim($sData);

                if (feof($handle) === false) {
                    return null;
                }
                fclose($handle);

                return $sData;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    private static function _filterFileName($file_name)
    {
        return $file_name;
        $path = pathinfo($file_name);

        $split_file_name = self::_splitFileName($path['basename']);

        if ($path['dirname'] === '.') {
            return $split_file_name;
        } else {
            return $path['dirname'] . '/' . $split_file_name;
        }
    }

    private static function _splitFileName($file_name)
    {
        return implode('/', str_split($file_name));
    }

    public static function set($sKey, $mData, $iExpireSec = null)
    {
        $sCachePath = self::_getCachePath();

        $sFileName = self::_filterFileName($sKey);
        $sFullPath = $sCachePath . $sFileName;
        if (is_file($sFullPath)) {
            @unlink($sFullPath);
        }

        $sDir = dirname($sFullPath);
        if (is_dir($sDir) === false) {
            $bRet = mkdir($sDir, 0777, true);
            if ($bRet === false) {
                return false;
            }
        }

        $handle = @fopen($sFullPath, "w");
        if (is_resource($handle) === true) {
            $sExpireTime = self::_createExpireTime($iExpireSec);
            $sData = self::_convertDataString($mData);
            if (fwrite($handle, $sExpireTime . $sData) === false) {
                return false;
            }

            fclose($handle);

            return true;
        } else {
            return false;
        }
    }

    public static function remove($mKey)
    {
        $sCachePath = self::_getCachePath();

        if (is_string($mKey) === true) {
            $sCachePath = ($sCachePath . $mKey);
        } else {
            if (is_array($mKey) === true) {
                for ($i = 0; $i < count($mKey); $i++) {
                    $sCachePath .= ($mKey[$i] . DIRECTORY_SEPARATOR);
                }
                if (empty($mKey) === false) {
                    $sCachePath = substr($sCachePath, 0, -1);
                }
            } else {
                return false;
            }
        }

        if (is_file($sCachePath) === true) {
            return @unlink($sCachePath);
        } elseif (is_dir($sCachePath) === true) {
            File::removeDir($sCachePath);
        } else {
            return false;
        }

        return true;
    }
}
