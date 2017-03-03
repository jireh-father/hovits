<?php
namespace middleware\service\contents;

abstract class WatchaContents extends Contents
{
    const CONTENT_VENDOR = CONTENTS_PROVIDER_WATCHA;

    public function __construct($content_type)
    {
        parent::__construct($content_type, self::CONTENT_VENDOR);
    }

    public function getTitles($movie)
    {
        if (empty($movie)) {
            return null;
        }
        $titles = array($movie['title']);
//        if (!empty($movie['title_eng'])) {
        //            $titles[] = $movie['title_eng'];
        //        }

        return $titles;
    }

}