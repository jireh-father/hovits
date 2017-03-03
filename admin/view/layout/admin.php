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
<nav class="navbar navbar-inverse navbar-fixed-top">
    <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                <span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/">HOVITS</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
            <ul class="nav navbar-nav">
                <?php echo $layout_data['lnb'] ?>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="/setting/account">Setting</a></li>
                <li><a href="/login/logout">Logout</a></li>
                <!--                <li class="dropdown">--><!--                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Dropdown <span class="caret"></span></a>--><!--                    <ul class="dropdown-menu" role="menu">--><!--                        <li><a href="#">Action</a></li>--><!--                        <li><a href="#">Another action</a></li>--><!--                        <li><a href="#">Something else here</a></li>--><!--                        <li class="divider"></li>--><!--                        <li><a href="#">Separated link</a></li>--><!--                    </ul>--><!--                </li>-->
            </ul>
            <form class="navbar-form navbar-right" role="search">
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Search">
                </div>
                <button type="submit" class="btn btn-default">Search</button>
            </form>
        </div>
        <!-- /.navbar-collapse -->
    </div>
    <!-- /.container-fluid -->
</nav>
<div class="container-fluid">
    <div class="row">
        <?php if ($layout_data['is_main_page'] === false): ?>
            <div class="col-sm-3 col-md-2 sidebar">
                <ul class="nav nav-sidebar">
                    <?php echo $layout_data['snb'] ?>
                </ul>
            </div>
            <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
                <h1 class="page-header"><?php echo $layout_data['page_header'] ?></h1>
                <?php echo $view_contents ?>
            </div>
        <?php else: ?>
            <div class="main">
                <?php echo $view_contents ?>
            </div>
        <?php endif; ?>

    </div>
</div>
<?php echo $external_js_link?>
<?php echo $optimized_js_link ?>
<?php echo $optimized_js_inline ?>
</body>
</html>