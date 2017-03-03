<?php
namespace middleware\service\contents;

abstract class NaverContents extends Contents
{
    const CONTENT_VENDOR = CONTENTS_PROVIDER_NAVER;

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
        if (!empty($movie['title_eng'])) {
            $titles[] = $movie['title_eng'];
        }

        return $titles;
    }

    public function getMakingCountries($movie)
    {
        if (empty($movie) || empty($movie['making_country'])) {
            return null;
        }

        $making_countries = json_decode($movie['making_country'], true);
        if (empty($making_countries)) {
            return null;
        }

        return $making_countries[0];
    }
}