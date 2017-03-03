<?php
namespace controller\contents\naver;

class IdMapChecker extends \controller\contents\cgv\IdMapChecker
{
    public function movie()
    {
        parent::movie(CONTENTS_PROVIDER_NAVER);
    }
}