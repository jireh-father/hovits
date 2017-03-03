<?php
require_once 'bootstrap_cli.php';

$url = 'http://openapi.naver.com/l?AAADVMSw6CMBQ8TbskfY8C7aILf5h4AuOuxVcKBpCKGG4vmJhM5pfMjG+Ki2GnA9MZ03IzCtle8ml5kumGuSH+oMUgIhToBCivhU/V3dtCk0NnPRXWEQ+RvAnT9GTpjmG54jdOejtTTKqh+zerumYLGzn7aqqkDz1Ly2q4E0uPgCCk4pMBKSEXWY6YAfLOtPZcg3iAqtsQr7f64m56vUC9G+NnHX4BL+8MO8sAAAA=';
$ret=\middleware\library\Curl::get($url, null, 3, null, 30, 5, array(CURLOPT_FOLLOWLOCATION=> true));
$haha = \middleware\library\QpWrapper::getInstance($ret);
$haha->onAutoDecodeUtf8();
$content  =$haha->find('[property="og:title"]');
var_dump($content->exists());
exit;

\middleware\library\Image::resizeImageByWidth('c:/1.jpg', 'c:/2.jpg', 1280, 40);
$ret = strip_tags(htmlspecialchars_decode('죽음의 &lt;b>실황중계&lt;/b> 극장판'));
var_dump($ret);