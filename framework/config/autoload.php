<?php
return array(
    'file' => array(),
    'dir'  => array(
        array('framework\\base', dirname(PATH_FW)),
        array('framework\\core', dirname(PATH_FW)),
        array('framework\\exception', dirname(PATH_FW)),
        array('framework\\library', dirname(PATH_FW)),
        array('framework\\model', dirname(PATH_FW)),
        array('controller', PATH_APP),
        array('model', PATH_APP),
        array('service', PATH_APP),
        array('library', PATH_APP),
        array('exception', PATH_APP),
        array('console', PATH_APP)
    )
);