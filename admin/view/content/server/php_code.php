<div class="form-group">
    <label for="result">Result</label> <textarea readonly name="result" class="form-control" id="result" rows="10"></textarea>
</div>
<div class="form-group">
    <label for="command">Code</label> <textarea name="command" class="form-control" id="command" rows="10" required onkeyup="sendCommandHotKey(event, '<?php pr(SERVER_CMD_TYPE_PHP)?>');"></textarea>
</div>
<div class="form-group">
    <button type="button" class="btn btn-default" onclick="sendCommand('<?php pr(SERVER_CMD_TYPE_PHP)?>');">Send</button>
</div>

<div class="form-group">
    <label for="command_list">Code List</label> <select name="command_list" class="form-control" id="command_list" onkeydown="sendSelectedCommand('<?php pr(SERVER_CMD_TYPE_PHP)?>');">
        <?php echo HtmlTag::options($command_list); ?>
    </select>
</div>
<div class="form-group">
    <button type="button" class="btn btn-default" onclick="sendSelectedCommand('<?php pr(SERVER_CMD_TYPE_PHP)?>');">Send</button>
</div>