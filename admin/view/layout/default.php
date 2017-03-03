<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <?php echo $meta_tag ?>
    <title><?php echo $title ?></title>
    <?php echo $header_tag ?>
    <?php echo $external_css_link?>
    <?php echo $optimized_css_link ?>
    <!--    <link rel="shortcut icon" href="/res/img/favicon.ico" type="image/x-icon"/>-->
    <?php echo $optimized_css_inline ?>
</head>
<body>
<?php echo $view_contents ?>
<?php echo $external_js_link?>
<?php echo $optimized_js_link ?>
<?php echo $optimized_js_inline ?>
</body>
</html>