<div style="margin-bottom: 20px;">
    <div class="form-inline">
        <form method="post" style="display: inline-block">
            <label for="content_id">content_id</label> <input id="content_id" type="text" name="content_id" class="form-control" value="<?php echo $content_id ?>"/>
            <label for="is_local">is_local</label>
            <?php if($is_local === '1'):?>
            <input id="is_local" type="checkbox" name="is_local" class="form-control" value="1" checked/>
            <?php else:?>
            <input id="is_local" type="checkbox" name="is_local" class="form-control" value="1"/>
            <?php endif;?>

            <input type="hidden" name="content_type" value="movie"/>
            <button type="submit" class="btn btn-primary">검색</button>
<!--            <a href="" target="_blank" class="btn btn-info" onclick="return searchInCgv();">CGV 검색</a>-->
        </form>

        <?php if ($multi_mode === true): ?>
        <form method="post" style="display: inline-block">
            <input type="hidden" name="content_id" class="form-control" value="<?php echo $content_id ?>" />
            <?php if ($is_local === '1'): ?>
            <input type="hidden" name="is_local" class="form-control" value="1" />
            <?php endif; ?>
            <input type="hidden" name="content_type" value="movie" />
            <input type="hidden" name="is_back" value="is_back"/>
            <button type="submit" class="btn btn-primary">이전</button>
            <a href="/contents/<?php echo $vendor;?>/idMapChecker/init?vendor=<?php echo $vendor; ?>" target="_blank" class="btn btn-info">초기화</a>
            <label>index</label> <span><?php echo $index . '/' . $total?></span>
        </form>
        <?php endif; ?>

        <form method="post" style="display: inline-block" id="id_form">
            <label><?php echo $vendor?>_id</label>
            <input type="hidden" name="vendor" value="<?php echo $vendor;?>"/>
            <input type="hidden" name="content_id" value="<?php echo $cur_content_id ?>" />
            <input id="vendor_id" type="text" class="form-control" value="<?php echo $vendor_id ?>" name="<?php echo $vendor ?>_id" />

            <label><?php echo $vendor ?>_disabled</label>
            <?php if ($vendor_disabled == true): ?>
            <input type="checkbox" class="form-control" checked name="<?php echo $vendor ?>_disabled" value="1" />
            <?php else: ?>
            <input type="checkbox" class="form-control" name="<?php echo $vendor ?>_disabled" value="1" />
            <?php endif; ?>
            <button type="button" class="btn btn-default" onclick="setVendorIdData();">저장</button>
            <?php if($vendor === CONTENTS_PROVIDER_CGV):?>
                <a href="http://www.cgv.co.kr/movies/detail-view/?midx=" class="btn btn-danger" target="_blank" onclick="return openVendorMovieDetail(this, '<?php echo $vendor ?>');"><?php echo strtoupper($vendor) ?>링크</a>
            <?php elseif($vendor === CONTENTS_PROVIDER_NAVER):?>
                <a href="http://movie.naver.com/movie/bi/mi/basic.nhn?code=" class="btn btn-danger" target="_blank" onclick="return openVendorMovieDetail(this, '<?php echo $vendor ?>');"><?php echo strtoupper($vendor) ?>링크</a>
            <?php endif;?>
        </form>
        <?php if($has_new_map):?>
            <button type="button" class="btn btn-success" onclick="return removeNewMap(<?php echo $sync_id ?>, '<?php echo $vendor;?>');">확인처리</button>
        <?php endif;?>
    </div>
</div>
<?php if(!empty($query)):?>
<div style="width: 49%;height:850px;display: inline-block;border: solid 1px gray;vertical-align: top;">
    <iframe width="100%" height="70%" src="/contents/kofic/contentViewer?<?php echo $query ?>"></iframe>
    <iframe style="display: inline-block;" width="49%" height="30%" src="/contents/kofic/api/content?search_type=<?php echo $is_server_type ?>&content_type=movie_staff&content_id=<?php echo $cur_content_id?>&only_name=only_name"></iframe>
    <iframe style="display: inline-block;" width="49%" height="30%" src="/contents/kofic/api/content?search_type=<?php echo $is_server_type ?>&content_type=movie_actor&content_id=<?php echo $cur_content_id ?>&only_name=only_name"></iframe>
</div>
<div style="width: 49%;height:850px;display: inline-block;border: solid 1px gray;vertical-align: top;">
    <iframe width="100%" height="50%" src="/contents/<?php echo $vendor ?>/searchViewer?<?php echo $query ?>"></iframe>
    <?php if ($vendor === CONTENTS_PROVIDER_CGV && $vendor_id): ?>
        <iframe id="movie_detail_iframe" width="100%" height="50%" src="http://www.cgv.co.kr/movies/detail-view/?midx=<?php echo $vendor_id ?>"></iframe>
    <?php elseif($vendor === CONTENTS_PROVIDER_NAVER && $vendor_id): ?>
        <iframe id="movie_detail_iframe" width="100%" height="50%" src="http://movie.naver.com/movie/bi/mi/basic.nhn?code=<?php echo $vendor_id ?>"></iframe>
    <?php endif; ?>
</div>
<?php endif;?>