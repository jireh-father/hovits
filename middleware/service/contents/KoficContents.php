<?php
namespace middleware\service\contents;

abstract class KoficContents extends Contents
{
    const CONTENT_VENDOR = CONTENTS_PROVIDER_KOFIC;

    public function __construct($content_type)
    {
        parent::__construct($content_type, self::CONTENT_VENDOR);
    }

    public function getMovieActorPath($content_id, $use_backup = false)
    {
        $content_type = CONTENT_TYPE_MOVIE;
        $content_dir = substr($content_id, 0, 5);

        $path = PATH_CRAWLING . "/kofic/{$content_type}/{$content_dir}/actor/{$content_id}";
        if ($use_backup === true) {
            if (!is_file($path)) {
                $path = $this->getBackupMovieActorPath($content_id);
                if (!is_file($path)) {
                    $path = null;
                }
            }
        }

        return $path;
    }

    public function getMovieStaffPath($content_id, $use_backup = false)
    {
        $content_type = CONTENT_TYPE_MOVIE;
        $content_dir = substr($content_id, 0, 5);

        $path = PATH_CRAWLING . "/kofic/{$content_type}/{$content_dir}/staff/{$content_id}";
        if ($use_backup === true) {
            if (!is_file($path)) {
                $path = $this->getBackupMovieStaffPath($content_id);
                if (!is_file($path)) {
                    $path = null;
                }
            }
        }

        return $path;
    }

    public function getBackupMovieActorPath($content_id)
    {
        $content_type = CONTENT_TYPE_MOVIE;
        $content_dir = substr($content_id, 0, 5);

        return PATH_CRAWLING . "/kofic/backup/{$content_type}/{$content_dir}/actor/{$content_id}";
    }

    public function getBackupMovieStaffPath($content_id)
    {
        $content_type = CONTENT_TYPE_MOVIE;
        $content_dir = substr($content_id, 0, 5);

        return PATH_CRAWLING . "/kofic/backup/{$content_type}/{$content_dir}/staff/{$content_id}";
    }

    public function getRealTimeBoxOfficePath($now)
    {
        return PATH_CRAWLING . "/kofic/realtime_boxoffice/{$now}";
    }

    public function getBackupRealTimeBoxOfficePath($now)
    {
        return PATH_CRAWLING . "/kofic/backup/realtime_boxoffice/{$now}";
    }

    public function getBoxOfficeExcelDirPath()
    {
        return PATH_CRAWLING . "/" . CONTENTS_PROVIDER_KOFIC . "/box_office";
    }

    public function getBoxOfficeExcelPath($date)
    {
        return PATH_CRAWLING . "/" . CONTENTS_PROVIDER_KOFIC . "/box_office/" . substr($date, 0, 7) . "/$date.xls";
    }

    public function getBackupBoxOfficeExcelPath($date)
    {
        return PATH_CRAWLING . "/" . CONTENTS_PROVIDER_KOFIC . "/backup/box_office/" . substr($date, 0, 7) . "/$date.xls";
    }

    public function isBoxOfficeExcelFile($date, $check_backup = false)
    {
        if ($check_backup) {
            return is_file($this->getBoxOfficeExcelPath($date)) || is_file($this->getBackupBoxOfficeExcelPath($date));
        } else {
            return is_file($this->getBoxOfficeExcelPath($date));
        }
    }
}