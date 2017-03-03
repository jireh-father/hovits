<?php
require 'bootstrap_resource.php';

$file_path = $_GET['file_path'];
$file_type = $_GET['file_type'];

$file_full_path = PATH_APP_RESOURCE . "/{$file_type}/" . $file_path;
if (Config::$ENABLE_MIDDLEWARE === true && !is_file($file_full_path)) {
    $file_full_path = PATH_MW_RESOURCE . "/{$file_type}/" . $file_path;
    if (!is_file($file_full_path)) {
        exit;
    }
}

header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
readfile($file_full_path);