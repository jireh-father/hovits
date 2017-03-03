function ajax(sUri, aParams, sCallBack, sDataType, iTimeout, sRequestMethod) {
    if (!sRequestMethod) {
        sRequestMethod = 'get';
    }

    if (!iTimeout) {
        iTimeout = 3000;
    }

    if (!sDataType) {
        sDataType = 'json';
    }

    $.ajax(
        {
            url: sUri,
            dataType: sDataType,
            data: aParams,
            timeout: iTimeout,
            type: sRequestMethod,
            success: function (data) {
                if (sDataType === 'json') {
                    sCallBack(data['data'], data['error'], data['message']);
                } else {
                    sCallBack(data, false, null);
                }
            },
            error: function () {
                sCallBack(null, true, 'ajax server error');
            }
        }
    );
}

function buildTag(tag_name, attributes, body, has_close_tag, is_new_line) {
    if (!tag_name) {
        return null;
    }

    if (has_close_tag === undefined) {
        has_close_tag = true;
    }

    if (is_new_line === undefined) {
        is_new_line = false;
    }

    var new_line = '';
    if (is_new_line === true) {
        new_line = '\n';
    }

    var tag = '<' + tag_name;
    var attr_string = null;
    if (attributes) {
        if (attributes instanceof Object) {
            var keys = Object.keys(attributes);
            var attr_list = [];
            keys.forEach(function (attr_key) {
                var attr_val = attributes[attr_key];
                attr_list.push(attr_key + '="' + attr_val + '"');
            });
            attr_string = attr_list.join(' ');
        } else if (typeof(attributes ) === 'string') {
            attr_string = attributes;
        }
        tag += (' ' + attr_string);
    }

    if (body) {
        tag += ('>' + new_line + body + new_line + '</' + tag_name + '>');
    } else {
        if (has_close_tag === true) {
            tag += ('>' + '</' + tag_name + '>');
        } else {
            tag += '/>';
        }
    }

    return tag;
}

function buildSelect(data, attributes) {
    return buildTag('select', attributes, buildOptions(data), true, true);
}

function buildOptions(data) {
    if (!data || (!data instanceof Object && !Array.isArray(data))) {
        return null;
    }

    var option_list = [];
    if (Array.isArray(data)) {
        data.forEach(function (element, index) {
            option_list.push(buildOption(data[index], data[index]));
        });
    } else {
        var keys = Object.keys(data);
        keys.forEach(function (option_key) {
            option_list.push(buildOption(option_key, data[option_key]));
        });
    }
    return option_list.join('\n');
}

function buildOption(key, value) {
    return buildTag('option', {'class': 'form-control', 'value': key}, value);
}