<?php
$movie1_id = $movie1['movie_id'];
$rand1 = rand(0, count($match_still_cut_list[$movie1_id]) - 1);
$movie2_id = $movie2['movie_id'];
$rand2 = rand(0, count($match_still_cut_list[$movie2_id]) - 1);
?>
<div class="right">
    <div style="background-image: url('<?php pr(getImageUri($match_still_cut_list[$movie2_id][$rand2], THUMB_KEY_LOW_QUALITY)) ?>');" class="lazy-image content-bg-img"
         data-original="<?php pr(getImageUri($match_still_cut_list[$movie2_id][$rand2], THUMB_KEY_MID_QUALITY)) ?>"></div>
</div>
<div class="left">
    <div style="background-image: url('<?php pr(getImageUri($match_still_cut_list[$movie1_id][$rand1], THUMB_KEY_LOW_QUALITY)) ?>');" class="lazy-image content-bg-img"
         data-original="<?php pr(getImageUri($match_still_cut_list[$movie1_id][$rand1], THUMB_KEY_MID_QUALITY)) ?>"></div>
</div>
<div class="content-wide-box">
    <div class="right">
        <div class="content-bg-bar"></div>
        <?php
        $movie = $movie1;
        $d_day = \framework\library\Time::diffDays(\framework\library\Time::Ymd(), empty($movie['release_date']) ? $movie['re_release_date'] : $movie['release_date'], true);
        include \framework\core\View::block('content_desc');
        ?>
    </div>
    <div class="left">
        <div class="content-bg-bar"></div>
        <?php
        $movie = $movie2;
        $d_day = \framework\library\Time::diffDays(\framework\library\Time::Ymd(), empty($movie['release_date']) ? $movie['re_release_date'] : $movie['release_date'], true);
        include \framework\core\View::block('content_desc');
        ?>
    </div>

    <div class="horizontal-center" style="width: 490px;">
        <?php
        include \framework\core\View::block('content_match_thumb');
        ?>
    </div>
</div>
