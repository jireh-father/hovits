<?php
namespace middleware\service\contents\thumb;

use framework\library\Log;
use framework\library\sql_builder\SqlBuilder;
use middleware\exception\ImageException;
use middleware\library\Image;
use middleware\model\RealtimeBoxoffice;

class ThumbMaker
{
    public static function getLocalImagePath($image_url, $image_size, $image_type, $content_type, $content_id, $ext)
    {
        $file_name = md5($image_url . $image_size . $image_type) . '.' . $ext;

        return self::getImageDir($content_type, $content_id) . '/' . $file_name;
    }

    public static function getImageDir($content_type, $content_id)
    {
        $content_dir = substr($content_id, 0, 5);

        return PATH_IMAGE . "/{$content_type}/{$content_dir}/{$content_id}";
    }

    public static function getImageUri($image, $image_size_var)
    {
        if (empty($image) || empty($image_size_var)) {
            return null;
        }

        return empty($image[$image_size_var]) ? $image['image_url'] : self::getLocalImageUri($image, $image_size_var);

        //        return $image['image_url'];
    }

    public static function getLocalImageUri($image, $image_size_var)
    {
        $content_type = $image['content_type'];
        $content_id = $image['content_id'];
        $content_dir = substr($content_id, 0, 5);

        return "http://img.hovits.com/{$content_type}/{$content_dir}/{$content_id}/{$image[$image_size_var]}";
    }

    public static function makeSmallImagePoster($image_type, $image_list = null)
    {
        self::makeThumbImage(220, 314, $image_type, THUMB_KEY_SMALL_SIZE, $image_list);
    }

    public static function makeMidImagePoster($image_type, $image_list = null)
    {
        self::makeThumbImage(440, 628, $image_type, THUMB_KEY_MID_SIZE, $image_list);
    }

    public static function makeBigImagePoster($image_type, $image_list = null)
    {
        self::makeThumbImage(660, 942, $image_type, THUMB_KEY_BIG_SIZE, $image_list);
    }

    public static function makeMidQualityImage($image_type, $image_list = null)
    {
        self::makeStillCutImage($image_type, THUMB_KEY_MID_QUALITY, $image_list, array(40, 7));
    }

    public static function makeLowQualityImage($image_type, $image_list = null)
    {
        self::makeStillCutImage($image_type, THUMB_KEY_LOW_QUALITY, $image_list, array(10, 9));
    }

    public static function makeBoxOfficeThumbs()
    {
        $box_office_model = RealtimeBoxoffice::getInstance();

        $image_join = array(
            'realtime_boxoffice',
            SqlBuilder::join('image', 'realtime_boxoffice.movie_id = image.content_id')
        );

        $box_office_model->setTable($image_join);
        $main_images = $box_office_model->getList(
            array(
                'image_type'   => 'main',
                'content_type' => CONTENT_TYPE_MOVIE,
                SqlBuilder::isNull(THUMB_KEY_SMALL_SIZE)
            )
        );

        $box_office_model->setTable($image_join);

        $mid_still_cut_images = $box_office_model->getList(
            array(
                'image_type'   => 'still_cut',
                'content_type' => CONTENT_TYPE_MOVIE,
                SqlBuilder::isNull(THUMB_KEY_MID_QUALITY)
            )
        );

        $box_office_model->setTable($image_join);

        $low_still_cut_images = $box_office_model->getList(
            array(
                'image_type'   => 'still_cut',
                'content_type' => CONTENT_TYPE_MOVIE,
                SqlBuilder::isNull(THUMB_KEY_LOW_QUALITY)
            )
        );

        self::makeMidQualityImage('still_cut', $mid_still_cut_images);
        self::makeLowQualityImage('still_cut', $low_still_cut_images);

        self::makeSmallImagePoster('main', $main_images);
    }

    public static function makeStillCutImage($image_type, $image_size_var, $image_list = null, $qualities = null)
    {
        $image_model = \middleware\model\Image::getInstance();
        if (empty($image_list)) {
            $image_list = $image_model->getList(array('image_type' => $image_type, SqlBuilder::isNull($image_size_var)));
        }
        if (empty($image_list)) {
            throw new ImageException('썸네일 이미지파일 없음');
        }
        $total = count($image_list);
        Log::info('이미지 퀄리티 변경 시작', array($total, $image_type, $image_size_var));
        foreach ($image_list as $i => $image) {
            try {
                $image_file_type = Image::getImageType($image['image_url']);
                if ($image_file_type === IMAGETYPE_JPEG) {
                    $ext = 'jpg';
                    $quality = $qualities[0];
                } elseif ($image_file_type === IMAGETYPE_PNG) {
                    $ext = 'png';
                    $quality = $qualities[1];
                } elseif ($image_file_type === IMAGETYPE_GIF) {
                    $ext = 'jpg';
                    $quality = $qualities[0];
                } elseif ($image_file_type === IMAGETYPE_BMP) {
                    $ext = 'jpg';
                    $quality = $qualities[0];
                } else {
                    throw new ImageException('이미지파일 타입 이상함', array($image_file_type, $image));
                }

                $count = "{$i}/{$total}";
                $image_path = self::getLocalImagePath($image['image_url'], $image_size_var, $image['image_type'], $image['content_type'], $image['content_id'], $ext);

                Image::resizeImageByWidthK($image['image_url'], $image_path, 1280, $quality);

                if (!is_file($image_path)) {
                    throw new ImageException('이미지 퀄리티 변경 실패', array($image_path, $image, $count, $image_type, $image_size_var, $quality));
                }

                Image::filterGaussianBlur($image_path);

                $file_name = basename($image_path);

                $ret = $image_model->modify(array($image_size_var => $file_name), array('image_id' => $image['image_id']));
                if ($ret === false) {
                    if (is_file($image_path)) {
                        unlink($image_path);
                    }
                    Log::error('이미지 퀄리티 변경 db 업데이트 실패', array($image_size_var, $file_name, $image_path, $image, $count, $image_type, $quality));
                } else {
                    Log::info('이미지 퀄리티 변경 완료', array($image_path, $image, $count, $image_type, $image_size_var, $quality));
                }
            } catch (\Exception $e) {
                Log::error('이미지 퀄리티 변경 실패', array($count, $image_type, $image_size_var, $quality, $e->getMessage()));
                continue;
            }
        }
        Log::info('이미지 퀄리티 변경 완료', $count);
    }

    public static function makeThumbImage($width, $height, $image_type, $image_size_var, $image_list = null)
    {
        $image_model = \middleware\model\Image::getInstance();
        if (empty($image_list)) {
            $image_list = $image_model->getList(array('image_type' => $image_type, SqlBuilder::isNull($image_size_var)));
        }
        if (empty($image_list)) {
            throw new ImageException('썸네일 이미지파일 없음');
        }
        $total = count($image_list);
        Log::info('이미지 썸네일 생성 시작', array('total' => $total));
        foreach ($image_list as $i => $image) {
            try {
                $count = "{$i}/{$total}";
                $image_file_type = Image::getImageType($image['image_url']);
                if ($image_file_type === IMAGETYPE_JPEG) {
                    $ext = 'jpg';
                } elseif ($image_file_type === IMAGETYPE_PNG) {
                    $ext = 'png';
                } elseif ($image_file_type === IMAGETYPE_GIF) {
                    $ext = 'gif';
                } elseif ($image_file_type === IMAGETYPE_GIF) {
                    $ext = 'bmp';
                } else {
                    throw new ImageException('이미지파일 타입 이상함', array($image['content_id'], $image_file_type));
                }
                $image_path = self::getLocalImagePath($image['image_url'], $image_size_var, $image['image_type'], $image['content_type'], $image['content_id'], $ext);
                Image::resizeImageK($image['image_url'], $image_path, $width, $height);

                if (!is_file($image_path)) {
                    throw new ImageException('이미지 리사이징 실패', array($image_path, $image, $count));
                }

                $file_name = basename($image_path);

                $ret = $image_model->modify(array($image_size_var => $file_name), array('image_id' => $image['image_id']));
                if ($ret === false) {
                    if (is_file($image_path)) {
                        unlink($image_path);
                    }
                    Log::error('db 업데이트 실패', array($image_size_var, $file_name, $image_path, $image, $count));
                } else {
                    Log::info('이미지 썸네일 생성 완료', array($image['content_id'], $image_path, $image, $count));
                }
            } catch (\Exception $e) {
                Log::error('이미지 썸네일 생성 실패', array($image['content_id'], $count, $e));
                continue;
            }
        }
        Log::info('이미지 썸네일 생성 종료', $count);
    }
}