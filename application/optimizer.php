<?php
require 'bootstrap_resource.php';

$file_name = $_GET['file_name'];
$file_type = $_GET['file_type'];

if ($file_type == 'js') {
    $file_full_path = PATH_CACHE . '/js/' . $file_name;
    header('Content-type: application/javascript');
} elseif ($file_type == 'css') {
    $file_full_path = PATH_CACHE . '/css/' . $file_name;
    header('Content-type: text/css');
} else {
    exit;
}
header('Content-Disposition: attachment; filename="' . $file_name . '"');
readfile($file_full_path);
