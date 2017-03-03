function setLogData(sLogData) {
    try {
        var oData = $.parseJSON(atob(sLogData));
        $("#log_data").html(JSONTree.create(oData));
    } catch (e) {
        $("#log_data").html(atob(sLogData));
        //$("#log_data").html(sLogData);
    }
}

function getTraceDetail(iTraceId) {
    var aParams = {trace_id: iTraceId};
    ajax('/log/common/traceDetail', aParams, function (aData) {
        if (aData == false) {
            $("#trace_list_body").empty();
            alert('검색 실패');
        } else {
            window.test = aData;
            $("#trace_list_body").html(buildTraceDetailList(aData));
        }
    });
}

function buildTraceDetailList(aList) {
    var iCnt = aList.length;
    var sTag = '';
    for (var i = 0; i < iCnt; i++) {
        var oLog = aList[i];
        sTag += buildTraceDetail(oLog);
    }
    return sTag;
}

function buildTraceDetail(oLog) {
    var aTraceCols = ['insert_time', 'host', 'log_type', 'log_msg', 'log_data', 'pid', 'log_level'];
    var sTag = '<tr>';
    for (var i in aTraceCols) {
        var sKey = aTraceCols[i];
        var sVal = oLog[sKey];
        sTag += buildTraceDetailCol(sKey, sVal, oLog);
    }
    sTag += '</tr>';
    return sTag;
}

function buildTraceDetailCol(sKey, sVal, oLog) {
    var sTag = '<td>';
    if (sKey === 'log_data') {
        if (sVal) {
            if (!window.traceLogData) {
                window.traceLogData = {};
            }
            window.traceLogData[oLog['log_id']] = sVal;
            sTag += '<button type="button" class="btn btn-default" data-toggle="modal" data-target="#log_data_modal" onclick="setTraceLogData(' + oLog['log_id'] + ');">로그데이터</button>';
        }
    } else {
        sTag += sVal;
    }
    sTag += '</td>';
    return sTag;
}

function setTraceLogData(iLogId) {
    var sJson;
    try {
        sJson = $.parseJSON(window.traceLogData[iLogId]);
    } catch (e) {
        sJson = undefined;
    }
    if (sJson === undefined) {
        $("#log_data").html('<pre>' + window.traceLogData[iLogId] + '</pre>');
    } else {
        $("#log_data").html(JSONTree.create($.parseJSON(window.traceLogData[iLogId]), undefined, 2));
    }
}