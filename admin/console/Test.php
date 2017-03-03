<?php

class Test implements Console
{
    public function run($argv = null)
    {

        $row = \middleware\model\Image::getInstance()->getRow(null, null, array('image', \framework\library\sql_builder\SqlBuilder::join('people', 'image.people_id = people.people_id')));
        var_dump($row);
    }
}