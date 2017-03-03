function sendCommand(type) {
    if (!$("#command").val() && type !== 'php_function') {
        $("#command").focus();
        alert('명령어를 입력하세요');
        return false;
    }

    if (!window.commandHistory) {
        window.commandHistory = [];
        window.commandHistoryIdx = 0;
    } else {
        window.commandHistoryIdx = window.commandHistory.length;
    }

    window.commandHistory.push($("#command").val());
    window.commandHistoryIdx++;

    var aParams = {'command': $("#command").val(), 'type': type};

    if (type === 'php_function') {
        aParams['command'] = $("#class_name").val() + $("#function_type").val() + $("#function_name").val();
        aParams['function_type'] = $("#function_type").val();
    }

    ajax('/server/cli/exec', aParams, function (data, error, message) {
        if (error == true) {
            alert('에러: ' + message);
            $("#command").focus();
        } else {
            $("#result").val($("#result").val() + '\n\n[' + new Date().toLocaleString() + ']\n' + data);
            $("#command").val('');
            $("#command").focus();
            var oResult = $('#result');
            if (oResult.length)
                oResult.scrollTop(oResult[0].scrollHeight - oResult.height());
        }
    });
}

function sendSelectedCommand(type) {
    if (!$("#command_list").val()) {
        $("#command_list").focus();
        alert('명령어를 선택하세요');
        return false;
    }

    if (type === 'php_function') {
        var command = $("#command_list").val();
        if (command.search('::') >= 0) {
            $("#function_type").val('::');
            var split = command.split('::');
            $("#class_name").val(split[0]);
            $("#function_name").val(split[1]);
        } else if (command.search('->') >= 0) {
            $("#function_type").val('->');
            var split = command.split('->');
            $("#class_name").val(split[0]);
            $("#function_name").val(split[1]);
        } else {
            $("#function_type").val('');
            $("#class_name").val('');
            $("#function_name").val(command);
        }
    } else {
        $("#command").val($("#command_list").val());
    }
    sendCommand(type);
}

function sendCommandHotKey(event, type) {

    if (event.keyCode === 13) {
        if (type === 'php_code') {
            if (event.ctrlKey) {
                sendCommand(type);
            }
        } else {
            sendCommand(type);
        }
    }
    //up
    if (event.keyCode === 38) {
        if (window.commandHistory[window.commandHistoryIdx - 1]) {
            $("#command").val(window.commandHistory[--window.commandHistoryIdx]);
        }
    }
    //down
    if (event.keyCode === 40) {
        if (window.commandHistory[window.commandHistoryIdx + 1]) {
            $("#command").val(window.commandHistory[++window.commandHistoryIdx]);
        }
    }
}