<?php
namespace middleware\service\contents;

abstract class CgvContents extends Contents
{
    const CONTENT_VENDOR = CONTENTS_PROVIDER_CGV;

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
        if (!empty($movie['title_aka'])) {
            $titles[] = $movie['title_aka'];
        }

        return $titles;
    }


}