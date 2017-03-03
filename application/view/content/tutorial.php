<form id="tutorial-container" action="/tutorial/step2" method="post">
    <div id="content-list">
        <?php foreach ($movie_list as $movie): ?>
            <?php
            $movie_title = $movie['title'];
            $movie_id = $movie['movie_id'];
            $thumb_image_data = $image_list[$movie['movie_id']];
            $is_in_tutorial_select = true;
            ?>
            <?php if (empty($image_list[$movie_id]['image_url'])): ?>
                <?php echo $movie_title; ?>(<?php echo $movie_id; ?>)
            <?php else: ?>
                <?php include \framework\core\View::block('content_thumb'); ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div id="tutorial-top-menu">
        <p>
            <span id="step-icon" class="label label-gray">STEP 1</span> <span id="tutorial_message">취향분석을 위해 이미 시청한 영화를 선택해주세요.</span>
            <span><button id="next_button" class="btn btn-danger hidden float-right" type="submit">다음</button></span>
        </p>

        <div id="progress-bar-box" class="progress">
            <div id="progress_bar" class="progress-bar progress-bar-gray progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="<?php echo $total_select_limit; ?>">
                <div id="progress-text"><span id="current_selected">0</span> / <span id="total_select_limit"><?php echo $total_select_limit;?></span></div>
            </div>
        </div>
    </div>
</form>