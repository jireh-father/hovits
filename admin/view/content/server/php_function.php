<div class="form-group">
    <label for="result">Result</label> <textarea readonly name="result" class="form-control" id="result" rows="10"></textarea>
</div>
<div class="form-group">
    <label for="function_type">Function Type</label>
    <select name="function_type" class="form-control" id="function_type">
        <?php echo HtmlTag::options($function_types); ?>
    </select>
</div>
<div class="form-group">
    <label for="class_name">Class Name</label>
    <input type="text" name="class_name" class="form-control" id="class_name" onkeyup="sendCommandHotKey(event, '<?php pr(SERVER_CMD_TYPE_FUNC) ?>');"/>
</div>
<div class="form-group">
    <label for="function_name">Function Name</label>
    <input type="text" name="function_name" class="form-control" id="function_name" onkeyup="sendCommandHotKey(event, '<?php pr(SERVER_CMD_TYPE_FUNC) ?>');"/>
</div>
<div class="form-group">
    <button type="button" class="btn btn-default" onclick="sendCommand('<?php pr(SERVER_CMD_TYPE_FUNC) ?>');">Send</button>
</div>

<div class="form-group">
    <label for="command_list">Code List</label> <select name="command_list" class="form-control" id="command_list" onkeydown="sendSelectedCommand('<?php pr(SERVER_CMD_TYPE_FUNC) ?>');">
        <?php echo HtmlTag::options($command_list); ?>
    </select>
</div>
<div class="form-group">
    <button type="button" class="btn btn-default" onclick="sendSelectedCommand('<?php pr(SERVER_CMD_TYPE_FUNC) ?>');">Send</button>
</div>