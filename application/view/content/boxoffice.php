<?php if (empty($movies)): ?>
    데이터 없음
<?php endif; ?>
<div id="wide-content-list" class="content-wide-list">
    <?php $i = 1;
    $is_in_thumb = true;
    foreach ($movies as $movie_id => $movie) : ?>
        <?php $rand = rand(0, count($still_cut_list[$movie_id]) - 1); ?>
        <?php $movie['avg_total_point'] = $movie['avg_total_point'] + ((100 - $movie['avg_total_point']) / 2) ?>
        <?php $movie['avg_ticket_count_per_day'] = $movie['avg_ticket_count_per_day'] < 0 ? 0 : $movie['avg_ticket_count_per_day'] ?>
        <?php $d_day = \framework\library\Time::diffDays(\framework\library\Time::Ymd(), empty($movie['release_date']) ? $movie['re_release_date'] : $movie['release_date'], true) ?>
        <?php ?>
        <div class="content-wide-container">
            <?php include \framework\core\View::block('content_block'); ?>
            <div class="rank-number-bg"><?= $i; ?></div>
        </div>
        <?php $i++; endforeach; ?>
</div>