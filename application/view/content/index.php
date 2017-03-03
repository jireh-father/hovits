<div style="font-size: 20px;padding: 15px;border-bottom: 1px solid black;border-top: 1px solid black;background-color: white;color: black;text-align: center;">Box Office Top 3</div>
<div class="content-wide-list" id="wide-content-list">
    <?php $i = 1;
    $is_in_thumb = true;
    foreach ($box_offices as $movie_id => $movie) : ?>
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
<div style="font-size: 20px;padding: 15px;border-bottom: 1px solid black;background-color: white;color: black;text-align: center;">Movie Match Top 3</div>
<div class="content-wide-list" id="wide-match-list">
    <?php $cnt = count($match_list); ?>
    <?php $is_in_match = true; ?>
    <?php $match_rank = 1; ?>
    <?php for ($i = 0;$i < $cnt;$i += 2): ?>
        <?php if (empty($match_list[$i + 1])) {
            break;
        } ?>
        <?php
        $movie1 = $match_list[$i];
        $movie2 = $match_list[$i + 1];
        ?>
        <div class="content-wide-container">
            <?php include \framework\core\View::block('content_match_block'); ?>
            <div class="rank-number-bg"><?= $match_rank++; ?></div>
        </div>
    <?php endfor;?>
</div>