<div class="form-group">
    <label for="result">Result</label> <textarea readonly name="result" class="form-control" id="result" rows="20"></textarea>
</div>
<div class="form-group">
    <label for="command">Command</label>
    <input type="text" name="command" class="form-control" id="command" required onkeyup="sendCommandHotKey(event, '<?php pr(SERVER_CMD_TYPE_CLI)?>');">
</div>
<div class="form-group">
    <button type="button" class="btn btn-default" onclick="sendCommand('<?php pr(SERVER_CMD_TYPE_CLI)?>');">Send</button>
</div>

<div class="form-group">
    <label for="command_list">Command List</label>
    <select name="command_list" class="form-control" id="command_list" onkeydown="sendSelectedCommand('<?php pr(SERVER_CMD_TYPE_CLI)?>');">
        <?php echo HtmlTag::options(toAssoc($command_list)); ?>
    </select>
</div>
<div class="form-group">
    <button type="button" class="btn btn-default" onclick="sendSelectedCommand('<?php pr(SERVER_CMD_TYPE_CLI)?>');">Send</button>
</div>