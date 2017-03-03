<div style="background-image: url('<?php pr(getImageUri($still_cut_list[$movie_id][$rand], THUMB_KEY_LOW_QUALITY)) ?>');" class="lazy-image content-bg-img"
     data-original="<?php pr(getImageUri($still_cut_list[$movie_id][$rand], THUMB_KEY_MID_QUALITY)) ?>"></div>
<div class="content-wide-box">
    <div class="content-bg-bar"></div>

    <?php include \framework\core\View::block('content_desc'); ?>

    <?php
    $movie_title = $movie['title'];
    $movie_id = $movie['movie_id'];
    $thumb_image_data = $image_list[$movie['movie_id']];
    $no_title_tooltip = false;
    include \framework\core\View::block('content_thumb');
    ?>
</div>
