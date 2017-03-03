<?php
namespace middleware\service\contents\sync;

class KoficPeopleSync extends KoficSync
{
    public function __construct()
    {
        parent::__construct(CONTENT_TYPE_PEOPLE);
    }
}