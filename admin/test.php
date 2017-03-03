<?php

require_once 'bootstrap_cli.php';
$ret = \middleware\service\contents\Contents::getMovieData(
    array('20157465', 20151383, 20157464),
    FLAG_CONTENT_REAL_TIME_BOX_OFFICE

);

var_dump(json_encode($ret));