<form>
    <div class="form-inline">
        <div class="form-group">
            <?php Value::setValueContainer($search_params, 'search_params') ?>
            <label for="log_id">Log Id</label>
            <input type="text" class="form-control" id="log_id" name="log_id" value="<?php Value::pr('log_id') ?>" style="width: 60px;">
            <label for="trace_id">Trace Id</label>
            <input type="text" class="form-control" id="trace_id" name="trace_id" value="<?php Value::pr('trace_id') ?>" style="width: 60px;">
            <label for="host">Log Caller</label>
            <?php echo HtmlTag::select(toAssoc($log_caller_list), 'name="log_caller" id="log_caller" class="form-control"', Value::get('log_caller'), '선택') ?>
            <label ="log_level">로그레벨</label>
            <?php echo HtmlTag::select(toAssoc($log_level_list), 'name="log_level" id="log_level" class="form-control"', Value::get('log_level'), '선택') ?>
            <label for="log_type">로그타입</label>
            <?php echo HtmlTag::select(toAssoc($log_type_list), 'name="log_type" id="log_type" class="form-control"', Value::get('log_type'), '선택') ?>
        </div>
    </div>
    <br/>

    <div class="form-inline">
        <div class="form-group">
            <label for="from">From</label> <input type="text" class="form-control" id="from" name="insert_time[]" value="<?php Value::pr(array('insert_time', 0)) ?>">
            <label for="to">To</label><input type="text" class="form-control" id="to" name="insert_time[]" value="<?php Value::pr(array('insert_time', 1)) ?>"> <label for="client_ip">Client IP</label>
            <input type="text" class="form-control" id="client_ip" name="client_ip" value="<?php Value::pr('client_ip') ?>"> <label for="server_host">SERVER Host</label>
            <input type="text" class="form-control" id="server_host" name="server_host" value="<?php Value::pr('server_host') ?>">
        </div>
    </div>
    <br/>

    <div class="form-inline">
        <div class="form-group">
            <label for="log_msg">로그메시지</label> <input type="text" class="form-control" id="log_msg" name="log_msg" value="<?php Value::pr('log_msg') ?>">
            <label for="log_data_form">로그데이터</label>
            <input type="text" class="form-control" id="log_data_form" name="log_data" value="<?php Value::pr('log_data') ?>">
            <label for="limit">Limit</label>
            <input type="text" class="form-control" id="limit" name="limit" value="<?php pr($limit) ?>" style="width: 60px;">
            <label for="offset">Offset</label>
            <input type="text" class="form-control" id="offset" name="offset" value="<?php pr($offset) ?>" style="width: 60px;">
        </div>
        <button type="submit" class="btn btn-primary">검색</button>
    </div>
</form>

<p style="margin-top: 20px;">
검색건수: <?php echo $searched_cnt; ?> / <?php echo $total_cnt; ?>
</p>

<div class="table-responsive">
    <table class="table table-hover table-condensed table-striped">
        <thead>
        <tr>
            <th>Log Id</th>
            <th>Trace Id</th>
            <th class="col-sm-2">Log Time</th>
            <th>Client IP</th>
            <th>Log Level</th>
            <th>Log Type</th>
            <th class="col-sm-2">Log Caller</th>
            <th class="col-sm-2">Log Msg</th>
            <th>Log Data</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($log_list as $log):
        Value::setValueContainer($log, 'log');
        if(!empty($log['log_data'])) {
            $log_data = json_decode($log['log_data'], true);
            if(empty($log_data)){
                $log_data = $log['log_data'];
            }
        }
        ?>
            <tr>
                <th scope="row"><?php Value::pr('log_id') ?></th>
                <td><?php Value::pr('trace_id') ?></td>
                <td><?php Value::pr('insert_time') ?></td>
                <td><?php Value::pr('client_ip') ?></td>
                <td><?php Value::pr('log_level') ?></td>
                <td><?php Value::pr('log_type') ?></td>
                <td><?php Value::pr('log_caller') ?></td>
                <td><?php Value::pr('log_msg') ?></td>
                <td>
                    <?php if (empty($log['log_data']) === false): ?>
                    <button type="button" class="btn btn-default" data-toggle="modal" data-target="#log_data_modal" onclick="setLogData('<?php pr(base64_encode(Value::get('log_data'))) ?>');">로그데이터</button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php if (empty($log['log_data']) === false): ?>
            <tr>
                <td colspan="9">
                    <?php echo debug($log_data)?>
                </td>
            </tr>
            <?php endif;?>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="log_data_modal" tabindex="-1" role="dialog" aria-labelledby="logDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="logDataModalLabel">Log Data</h4>
            </div>
            <div class="modal-body">
                <div id="log_data">

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>