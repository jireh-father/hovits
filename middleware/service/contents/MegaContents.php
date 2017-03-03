<?php
namespace middleware\service\contents;

abstract class MegaContents extends Contents
{
    const CONTENT_VENDOR = CONTENTS_PROVIDER_MEGA;

    public function __construct($content_type)
    {
        parent::__construct($content_type, self::CONTENT_VENDOR);
    }
}