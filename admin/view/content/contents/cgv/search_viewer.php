<?php if($vendor === CONTENTS_PROVIDER_CGV):?>
<link rel="stylesheet" media="all" type="text/css" href="http://img.cgv.co.kr/R2014/css/reset.css">
<link rel="stylesheet" media="all" type="text/css" href="http://img.cgv.co.kr/R2014/css/layout.css">
<link rel="stylesheet" media="all" type="text/css" href="http://img.cgv.co.kr/R2014/css/module.css">
<link rel="stylesheet" media="all" type="text/css" href="http://img.cgv.co.kr/R2014/css/common.css">
<link rel="stylesheet" media="all" type="text/css" href="http://img.cgv.co.kr/R2014/css/content.css">
<link rel="stylesheet" media="print" type="text/css" href="http://img.cgv.co.kr/R2014/css/print.css">
<link rel="stylesheet" type="text/css" href="http://img.cgv.co.kr/R2014/js/jquery.ui/smoothness/jquery-ui-1.10.4.custom.min.css">
<style>
    #cgv_main_ad {
        display: none;
    }

    #header {
        display: none;
    }

    #ctl00_navigation_line {
        display: none;
    }

    .sect-searcharea{
        padding: 0px !important;
    }
</style>
<?php endif;?>
<?php echo $search_html; ?>