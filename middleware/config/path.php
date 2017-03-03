<?php
define('PATH_CRAWLING', PATH_TEMP . '/crawling');
if (PHP_OS_WINDOWS === true) {
    define('PATH_IMAGE', PATH_TEMP . '/image');
} else {
    define('PATH_IMAGE', '/home/jireh/image');
}

if (PHP_OS_WINDOWS === true) {
    if (iconv("euc-kr", "utf-8", gethostname()) === '서일근PC') {
        define('PATH_IMAGICK', 'C:\Program Files\ImageMagick-6.8.9-Q16');
    } else {
        define('PATH_IMAGICK', 'C:\Program Files\ImageMagick-6.9.2-Q16');
    }
} else {
    define('PATH_IMAGICK', '/usr/bin/convert');
}
