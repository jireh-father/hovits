<?php
return array(
    'file' => array(),
    'dir'  => array(
        array('middleware\\controller', dirname(PATH_MW)),
        array('middleware\\model', dirname(PATH_MW)),
        array('middleware\\service', dirname(PATH_MW)),
        array('middleware\\library', dirname(PATH_MW)),
        array('middleware\\exception', dirname(PATH_MW)),
        array('middleware\\console', dirname(PATH_MW))
    ),
);