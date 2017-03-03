<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="user-scalable=no, initial-scale=0.5">
    <!--    <meta name="viewport" content="width=device-width, initial-scale=0.5">-->
    <?php echo $meta_tag ?>
    <title><?php echo $title ?></title>
    <meta property="og:title" content="호비츠 <영화 박스오피스>">
    <meta property="og:url" content="http://www.hovits.com/">
    <meta property="og:image" content="http://img.hovits.com/logo_bg.png">
    <meta property="og:description" content="네이버영화, CGV, 메가박스의 영화평점을 한 곳에서 확인하세요.">
    <?php echo $header_tag ?>
    <?php echo $external_css_link ?>
    <?php echo $optimized_css_link ?>
    <?php if (isMobile()): ?>
        <link rel="apple-touch-icon-precomposed" sizes="114x114" href="http://img.hovits.com/logo_bg.png"/>
    <?php else: ?>
        <link rel="shortcut icon" href="/_resource?file_type=img&file_path=favicon.ico" type="image/x-icon"/>
    <?php endif; ?>
    <?php echo $optimized_css_inline ?>
</head>
<body>
<!-- Google Tag Manager -->
<noscript>
    <iframe src="//www.googletagmanager.com/ns.html?id=GTM-W3D2P3"
        height="0" width="0" class="none-hidden"></iframe>
</noscript>
<script>(function (w, d, s, l, i) {
        w[l] = w[l] || [];
        w[l].push({
            'gtm.start': new Date().getTime(), event: 'gtm.js'
        });
        var f = d.getElementsByTagName(s)[0],
            j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : '';
        j.async = true;
        j.src =
            '//www.googletagmanager.com/gtm.js?id=' + i + dl;
        f.parentNode.insertBefore(j, f);
    })(window, document, 'script', 'dataLayer', 'GTM-W3D2P3');</script>
<!-- End Google Tag Manager -->

<?php if (!empty($sub_menu_list[$layout_data['top_uri']])): ?>
<div style="margin-top: 90px;">
<?php else: ?>
    <div style="margin-top: 90px;">
<?php endif; ?>
<?php echo $view_contents ?>
</div>

<div id="top-menu">
    <div id="top-log-box">
        <a href="/boxOffice/bookingRatio"><img width="110" src="http://img.hovits.com/logo_text.png"/></a>
    </div>
    <ul id="menu-list">
        <?php $sub_menu_list = array(); ?>
        <?php foreach ($layout_data['menu_list'] as $menu): ?>
            <?php
            if (!empty($menu['sub_menu'])) {
                $sub_menu_list[$menu['uri']] = $menu['sub_menu'];
            }
            ?>
            <li>
                <?php if (($layout_data['uri'] === '/' && $layout_data['uri'] === $menu['uri']) ||
                    ($menu['uri'] !== '/' && strpos($layout_data['uri'], $menu['uri']) === 0)
                ): ?>
                    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
                    <span class="menu-text">
                    <?php echo $menu['menu']; ?>
                    </span>
                <?php else: ?>
                    <a href="<?php echo $menu['uri']; ?>"><?php echo $menu['menu']; ?></a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <!--    <ul id="user_menu_list" style="list-style-type: none;padding: 0;display: inline-block;float: right; margin-right: 10px;">-->
    <!--        <li style="display: inline-block;">-->
    <!--            --><?php //if (\service\User::isLogin()): ?>
    <!--                <a href="/user/logout">로그아웃</a>-->
    <!--            --><?php //else:?>
    <!--                <a href="/user/login">로그인</a>-->
    <!--            --><?php //endif;?>
    <!--        </li>-->
    <!--    </ul>-->
</div>
    <?php if (!empty($sub_menu_list[$layout_data['top_uri']])): ?>
        <div id="sub-menu">
        <ul id="sub-menu-list">
            <?php foreach ($sub_menu_list[$layout_data['top_uri']] as $sub_menu): ?>
                <li>
                    <?php if ($layout_data['uri'] === $sub_menu['uri']): ?>
                        <span class="menu-text">
                        <?php echo $sub_menu['menu']; ?>
                        </span>
                    <?php else: ?>
                        <a href="<?php echo $sub_menu['uri']; ?>"><?php echo $sub_menu['menu']; ?></a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php echo $external_js_link ?>

    <?php echo $optimized_js_link ?>

    <?php echo $optimized_js_inline ?>
</body>
</html>