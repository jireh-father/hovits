<form id="tutorial-container" action="<?php echo $next_step_uri; ?>" method="post">
    <div id="match-list">
    <?php $cnt = count($movies);?>
    <?php $is_in_tutorial_match = true; ?>
    <?php for($i = 0 ; $i < $cnt; $i+=2 ):?>
    <?php if(empty($movies[$i + 1])) break;?>
    <?php
    if($i / 2 % 2 > 0){
        $match_box_class = 'float-right';
    }else{
        $match_box_class = '';
    }
    $movie1 = $movies[$i];
    $movie2 = $movies[$i + 1];
    ?>
    <?php
    include \framework\core\View::block('content_match_thumb');
    ?>
    <?php endfor;?>
    </div>
    <div id="tutorial-top-menu">
        <p>
            <span id="step-icon" class="label label-gray">STEP <?php echo $step;?></span>
            <?php if($step == 2):?>
            <span id="tutorial_message">더욱 선호하는 영화를 선택해주세요.</span>
            <?php else:?>
            <span id="tutorial_message">마지막으로 더욱 선호하는 영화를 선택해주세요.</span>
            <?php endif;?>
            <span><button id="next_button" class="btn btn-danger hidden float-right" type="submit">다음</button></span>
        </p>

        <div id="progress-bar-box" class="progress">
            <div id="progress_bar" class="progress-bar progress-bar-gray progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="<?php echo $match_cnt; ?>">
                <div id="progress-text"><span id="current_selected">0</span> / <span id="total_select_limit"><?php echo $match_cnt; ?></span></div>
            </div>
        </div>
    </div>
</form>
