<div class="content-match-container <?php echo $match_box_class ?>">
    <?php
    $movie_title = $movie1['title'];
    $movie_id = $movie1['movie_id'];
    $thumb_image_data = $movie1;
    include \framework\core\View::block('content_thumb');
    ?>
    <?php
    $movie_title = $movie2['title'];
    $movie_id = $movie2['movie_id'];
    $thumb_image_data = $movie2;
    include \framework\core\View::block('content_thumb');
    ?>
    <span class="label label-gray versus">VS</span>
</div>