<div class="form-inline">
    <form method="post" target="_blank" action="/contents/<?php echo $vendor;?>/idMapChecker/movie" style="display: inline-block;width: 40%;">
        <input type="hidden" name="is_local" value="1"/>
        <input type="text" name="content_id" class="form-control" value="<?php echo implode(',', array_column($log_list, 'content_id')); ?>" style="width: 76%;"/>
        <button type="submit" class="btn btn-primary">전체 확인하기</button>
    </form>
    <form style="display: inline-block;" action="/contents/action/contentSyncLog/remove" method="post">
        <input type="hidden" name="vendor" value="<?php echo $vendor; ?>" />
        <input type="hidden" name="sync_id_list" value="<?php echo implode(',', array_column($log_list, 'sync_id')); ?>" />
        <button type="submit" class="btn btn-danger">전체 확인처리</button>
    </form>
</div>
</br>
</br>
총 <?php echo count($log_list);?> 개
</br>
<div class="table-responsive">
    <table class="table table-hover table-condensed table-striped">
        <thead>
        <tr>
            <th>Sync Id</th>
            <th>Content ID</th>
            <th class="col-sm-2">정보</th>
            <th>Content Provider</th>
            <th>Content Type</th>
            <th>Provider Content Id</th>
            <th class="col-sm-2">Sync Type</th>
            <th class="col-sm-2">Insert Time</th>
            <th>확인페이지</th>
            <th>확인처리</th>
        </tr>
        </thead>
        <tbody>
        <?php
            foreach($log_list as $log):
            Value::setValueContainer($log, 'log');
        ?>
            <tr>
                <th scope="row"><?php Value::pr('sync_id') ?></th>
                <td><?php Value::pr('content_id') ?></td>
                <td><?php echo "{$log['title']} / {$log['release_date']} / {$log['re_release_date']} / {$log['making_year']}" ?></td>
                <td><?php Value::pr('content_provider') ?></td>
                <td><?php Value::pr('content_type') ?></td>
                <td><?php Value::pr('provider_content_id') ?></td>
                <td><?php Value::pr('sync_type') ?></td>
                <td><?php Value::pr('insert_time') ?></td>
                <td>
                    <a class="btn btn-primary" target="_blank" href="/contents/<?php echo $vendor; ?>/idMapChecker/movie?content_id=<?php Value::pr('content_id') ?>&is_local=1">확인하기</a>
                </td>
                <td>
                    <form action="/contents/action/contentSyncLog/remove">
                        <input type="hidden" name="vendor" value="<?php echo $vendor; ?>" />
                        <input type="hidden" name="sync_id_list" value="<?php Value::pr('sync_id') ?>" />
                        <button type="submit" class="btn btn-danger">확인처리</button>
                    </form>
                </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
</div>