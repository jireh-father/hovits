<div class="content-desc-container">
    <div class="content-desc-box">
        <p class="content-title">
            <?php if ($d_day > 0): ?>
                <span class="label label-primary content-dday">D+<?php echo $d_day; ?></span>
            <?php elseif ($d_day < 0): ?>
                <span class="label label-danger content-dday">D<?php echo $d_day; ?></span>
            <?php else: ?>
                <span class="label label-success content-dday">D-Day</span>
            <?php endif; ?>
            <?php pr($movie, 'title'); ?>
            <?php if ($movie['title_eng']): ?>
                (<?php pr($movie, 'title_eng'); ?>)
            <?php endif; ?>
        </p>

        <p class="content-count">
            <span>예매율: <?php echo empty($movie['booking_ratio']) ? 0 : $movie['booking_ratio'] ?>%</span> | <span>누적관객수: <?php echo number_format($movie['total_ticket_count']) ?>명</span> |
            <span>하루평균 관객수: <?php echo number_format($movie['avg_ticket_count_per_day']) ?>명</span>
        </p>

        <p class="content-count">
            <?php if (!empty($movie['total_grade_count'])): ?>
                <span class="icon-box-hovits" data-toggle="tooltip" title="전체 평균 평점 (<?php echo number_format($movie['cgv_grade_count'] + $movie['naver_grade_count']) ?>명)">
                            <img class="icon-hovits" src="/_resource?file_type=img&file_path=favicon.ico" />
                    <?php echo round($movie['avg_grade_point'] / 10, 1, PHP_ROUND_HALF_UP); ?>
                        </span>
            <?php endif; ?>
            <?php if (!empty($movie['cgv_grade_count'])): ?>
                <span class="icon-box-cgv" data-toggle="tooltip" title="CGV 평점 (<?php echo number_format($movie['cgv_grade_count']) ?>명)">
                            <a href="http://www.cgv.co.kr/movies/detail-view/?midx=<?php echo $movie['cgv_id']; ?>" target="_blank">
                                <img class="icon-cgv" src="http://img.cgv.co.kr/R2014/images/title/h1_cgv.png" /></a>
                    <?php echo $movie['cgv_grade_point'] / 10 ?>
                        </span>
            <?php endif; ?>
            <?php if (!empty($movie['naver_grade_count'])): ?>
                <span class="icon-box-naver" data-toggle="tooltip" title="네이버 평점 (<?php echo number_format($movie['naver_grade_count']) ?>명)">
                            <a href="http://movie.naver.com/movie/bi/mi/basic.nhn?code=<?php echo $movie['naver_id']; ?>" target="_blank">
                                <img class="icon-naver" src="http://www.naver.com/favicon.ico?1" /></a>
                    <?php echo $movie['naver_grade_point'] / 10 ?>
                        </span>
            <?php endif; ?>
            <?php if (!empty($movie['lotte_grade_count'])): ?>
                <span class="icon-box-lotte" data-toggle="tooltip" title="롯데시네마 평점 (<?php echo number_format($movie['lotte_grade_count']) ?>명)">
                            <a href="http://www.lottecinema.co.kr/LHS/LHFS/Contents/MovieInfo/MovieInfoContent.aspx?MovieInfoCode=<?php echo $movie['lotte_id']; ?>" target="_blank">
                                <img class="icon-lotte" src="/_resource?file_type=img&file_path=lotte_logo.png" /></a>
                    <?php echo $movie['lotte_grade_point'] / 10 ?>
                        </span>
            <?php endif; ?>
            <?php if (!empty($movie['mega_grade_count'])): ?>
                <span class="icon-box-mega" data-toggle="tooltip" title="메가박스 평점 (<?php echo number_format($movie['mega_grade_count']) ?>명)">
                            <a href="http://www.megabox.co.kr/?show=detail&rtnShowMovieCode=<?php echo $movie['mega_id']; ?>" target="_blank">
                                <img class="icon-mega" src="/_resource?file_type=img&file_path=mega_logo.png" /></a>
                    <?php echo $movie['mega_grade_point'] / 10 ?>
                        </span>
            <?php endif; ?>
        </p>

        <p class="content-info">
            <?php pr(implode(', ', json_decode($movie['genre'], true))) ?> |
            <?php pr($movie, 'duration') ?>분 |
            <?php pr($movie, 'release_date') ?> |
            <?php pr($movie, 'making_year') ?> |
            <?php pr(implode(', ', json_decode($movie['making_country'], true))) ?>
        </p>

        <p class="content-info">
            <?php if ($movie['directors']): ?>
                감독: <?php pr(implode(', ', json_decode($movie['directors'], true))) ?>
            <?php endif; ?>
            <?php if ($movie['lead_actors']): ?>
                | 배우: <?php pr(implode(', ', json_decode($movie['lead_actors'], true))) ?>
            <?php endif; ?>
        </p>
    </div>
    <!--                        <div style="width: 100%; height: 100%;background-color: black;opacity: 0.2;position: absolute; top: 0;"></div>-->
</div>