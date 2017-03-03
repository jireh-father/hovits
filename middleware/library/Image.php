<?php
namespace middleware\library;

use framework\library\Log;
use framework\library\Time;
use middleware\exception\ImageException;

class Image
{
    const DEFAULT_QUALITY_JPEG = 100;
    const DEFAULT_QUALITY_PNG = 0;

    public static $image_type_map = array(
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png'
    );

    public static function resizeImage($image_url, $dest_path, $width, $height, $quality = null)
    {
        $image_resource = self::loadImage($image_url);

        $image_resource = self::_resizeImage($image_resource, $image_url, $width, $height);

        return self::_writeImage($image_resource, $dest_path, $image_url, $quality);
    }

    public static function downloadImage($image_url, $dest_path = null)
    {
        if (empty($dest_path)) {
            $dest_path = PATH_TEMP . '/image/' . uniqid() . basename($image_url);
        }

        if (!is_dir(dirname($dest_path))) {
            mkdir(dirname($dest_path), 0777, true);
        }

        $ret = file_put_contents($dest_path, fopen($image_url, 'r'));
        if ($ret === false) {
            return false;
        }

        return $dest_path;
    }

    public static function resizeImageK($image_url, $dest_path, $width, $height, $quality = null)
    {
        $ret = self::downloadImage($image_url, $dest_path);
        if (!$ret) {
            return false;
        }

        chdir(PATH_IMAGICK);

        $quality_str = '';
        if (!empty($quality)) {
            $quality_str = "-quality $quality";
        }

        $cmd = "convert {$dest_path} -resize {$width}x{$height} {$quality_str} {$dest_path}";
        Log::info('이미지 리사이즈 실행', $cmd);
        exec($cmd);

        return true;
    }

    public static function resizeImageByWidthK($image_url, $dest_path, $width_limit, $quality = null)
    {
        $ret = self::downloadImage($image_url, $dest_path);
        if (!$ret) {
            return false;
        }

        chdir(PATH_IMAGICK);

        $quality_str = '';
        if (!empty($quality)) {
            $quality_str = "-quality $quality";
        }

        $cmd = "convert {$dest_path} -resize {$width_limit}x {$quality_str} {$dest_path}";
        Log::info('이미지 리사이즈 by width 실행', $cmd);
        exec($cmd);

        return true;
    }

    public static function resizeImageByWidth($image_url, $dest_path, $width_limit, $quality = null, $image_resource = null)
    {
        if (empty($image_resource)) {
            $image_resource = self::loadImage($image_url);
        }

        //width 비율에 맞게 height limit 구하는 계산식
        list($width, $height) = getimagesize($image_url);
        if ($width <= $width_limit) {
            return self::_writeImage($image_resource, $dest_path, $image_url, $quality);
        }

        $height_limit = $height - ($height * (($width - $width_limit) / $width));
        if ($height_limit < 1) {
            $height_limit = 1;
        }

        $image_resource = self::_resizeImage($image_resource, $image_url, $width_limit, $height_limit);

        return self::_writeImage($image_resource, $dest_path, $image_url, $quality);
    }

    public static function changeQuality($image_url, $dest_path, $quality)
    {
        $image_resource = self::loadImage($image_url);

        self::_writeImage($image_resource, $dest_path, $image_url, $quality);
    }

    public static function getImageTypeString($image_url)
    {
        $image_type = exif_imagetype($image_url);
        if (empty(self::$image_type_map[$image_type])) {
            return null;
        }

        return self::$image_type_map[$image_type];
    }

    public static function filterGaussianBlur($image_path, $blur_cnt = 3)
    {
        chdir(PATH_IMAGICK);

        return exec("convert {$image_path} -blur 0x{$blur_cnt} {$image_path}");
    }

    public static function loadImage($image_url)
    {
        $dir = dirname($image_url);
        $file = rawurlencode(basename($image_url));
        $image_resource = imagecreatefromstring(file_get_contents("{$dir}/{$file}"));

        if ($image_resource === false) {
            throw new ImageException('fail to create image from file', array($image_url, error_get_last()));
        }

        return $image_resource;
    }

    public static function getImageType($image_url)
    {
        return exif_imagetype($image_url);
    }

    private static function _writeImage($image_resource, $file_path, $image_url, $quality = null)
    {
        if (empty($image_resource) === true || empty($file_path) === true) {
            throw new ImageException('required parameter is empty', array($image_resource, $file_path));
        }

        $image_type = exif_imagetype($image_url);
        if ($image_type === false) {
            throw new ImageException('이미지 타입 얻어오기 실패', $image_url);
        }

        if (!is_dir(dirname($file_path))) {
            mkdir(dirname($file_path), 0777, true);
        }

        switch ($image_type) {
            case IMAGETYPE_GIF:
                $bRet = imagegif($image_resource, $file_path);
                break;
            case IMAGETYPE_JPEG:
                if ($quality === null) {
                    $bRet = imagejpeg($image_resource, $file_path, self::DEFAULT_QUALITY_JPEG);
                } else {
                    $bRet = imagejpeg($image_resource, $file_path, $quality);
                }
                break;
            case IMAGETYPE_PNG:
                if ($quality === null) {
                    $bRet = imagepng($image_resource, $file_path, self::DEFAULT_QUALITY_PNG);
                } else {
                    $bRet = imagepng($image_resource, $file_path, $quality);
                }
                break;
            default:
                throw new ImageException('image type is invalid', array($image_url, $image_type));
        }

        if ($bRet === false) {
            throw new ImageException('fail to put image', $file_path);
        }
        imagedestroy($image_resource);

        return true;
    }

    private static function _resizeImage($image_resource, $image_url, $width_limit, $height_limit)
    {
        list($width, $height) = getimagesize($image_url);
        if ($width === false) {
            throw new ImageException('fail to get image size', $image_resource);
        }
        // Load
        $resize_image_resource = imagecreatetruecolor($width_limit, $height_limit);

        if ($resize_image_resource === false) {
            throw new ImageException('fail to create black image', array($width_limit, $height_limit));
        }
        // Resize
        $ret = imagecopyresampled($resize_image_resource, $image_resource, 0, 0, 0, 0, $width_limit, $height_limit, $width, $height);
        if ($ret === false) {
            throw new ImageException('fail to resize image', compact('width_limit', 'height_limit', 'width', 'height'));
        }

        return $resize_image_resource;
    }
}