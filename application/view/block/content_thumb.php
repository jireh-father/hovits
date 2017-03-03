<div class="content-thumb-container"
    <?php if (isset($no_title_tooltip) === false || $no_title_tooltip !== false): ?>
        data-toggle="tooltip" title="<?php echo $movie_title ?>"
    <?php endif; ?>
>
    <input class="content-id-hidden" type="hidden" value="<?php echo $movie_id ?>"/>

    <div class="content-thumb-box" data-movie-id="<?php echo $movie_id; ?>">
        <div class="glyphicon hidden" aria-hidden="true"></div>

        <div class="content-thumb lazy-image" data-original="<?php echo getImageUri($thumb_image_data, THUMB_KEY_SMALL_SIZE) ?>"></div>
        <div class="content-thumb-menu" style="display: none;">
            <div style="text-align: right;">
                <div style="background-color: black;color: white;text-align: center;padding: 15px;border: 1px solid white;margin-bottom: 10px;font-weight: bold;border-radius:.25em;"><?= $movie_title ?></div>
                <span class="label label-default">평점 73.2</span>
            </div>
            <div style="text-align: right;margin-top: 124px;">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width: 70%;">70점</div>
                </div>
                <button type="button" class="btn btn-primary btn-lg" aria-label="Right Align">
                    <span class="glyphicon glyphicon-thumbs-up" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-danger btn-lg" aria-label="Right Align">
                    <span class="glyphicon glyphicon-thumbs-down" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>