$(function () {
    if ($('#menu_tree').length === 1) {
        $('#menu_tree').jstree({
            'core': {
                "animation": 200,
                "check_callback": true,
                "themes": {"stripes": true},
                'data': {
                    'url': '/setting/appMenu/html',
                    'data': function (node) {
                        return {'id': node.id};
                    }
                }
            },
            "plugins": [
                "contextmenu", "dnd"
            ]
        });

        $('#menu_tree').on("rename_node.jstree", function (e, data) {
            if (data.old === data.text) {
                return;
            }

            var aMenus = data.text.split('::');
            if (aMenus.length !== 2) {
                $('#menu_tree').jstree(true).set_text(data.node, data.old);
                alert('메뉴 구조가 이상합니다!');
            }
        });
    }
});

function saveMenu() {
    if (confirm('메뉴를 저장하시겠습니까? 확인후 잠시 기다려야합니다.') === false) {
        return false;
    }
    var oMenuTree = $('#menu_tree').jstree(true);
    var oMenuJson = oMenuTree.get_json();

    $.ajax(
        {
            url: '/setting/appMenu/save',
            dataType: 'text',
            data: {'menu_json': JSON.stringify(oMenuJson)},
            timeout: 10000,
            type: 'post',
            success: function (data) {
                if (data === 'success') {
                    alert('성공 : 재로그인해야 반영된 메뉴를 확인할 수 있습니다.');
                } else {
                    alert('실패');
                }
            },
            error: function () {
                alert('실패');
            }
        }
    );
}

function setRoleData(sData) {
    if (sData === null) {
        $("#role_name").val('');
        $("#white_action").val('');
        $("#black_action").val('');
        $("#white_menu_box").empty();
        $("#black_menu_box").empty();
        $("#role_name").removeAttr('readonly');
        return;
    }

    $("#role_name").attr('readonly', 'readonly');
    var oData = $.parseJSON(atob(sData));
    $("#role_name").val(oData['ROLE']);
    $("#white_action").val(oData['ACTION']['WHITE_LIST']);
    $("#black_action").val(oData['ACTION']['BLACK_LIST']);
    $("#white_menu_box").empty();
    $("#black_menu_box").empty();
    setMenuBox('white', oData['MENU']['WHITE_LIST']);
    setMenuBox('black', oData['MENU']['BLACK_LIST']);
}

function onSubmitSetRole() {
    $("#white_menu_box .input-group-menu").each(function () {
        if ($(this).find('input[type=checkbox]').prop('checked')) {
            $(this).find('input[type=checkbox]').attr('name', 'white_children_all_' + $(this).index())
        }
    })

    $("#black_menu_box .input-group-menu").each(function () {
        if ($(this).find('input[type=checkbox]').prop('checked')) {
            $(this).find('input[type=checkbox]').attr('name', 'black_children_all_' + $(this).index())
        }
    })
}

function setMenuBox(sType, oData) {
    for (var i in oData) {
        var sUri = oData[i];
        addMenu(sType, sUri)
    }
}

function addMenu(sType, sUri) {
    if (sType === 'white') {
        var oWhiteMenuItem = $(".input-group-menu-white-sample").clone();
        oWhiteMenuItem.removeClass('input-group-menu-white-sample');
        if (sUri !== null) {
            if (sUri[sUri.length - 1] === '*') {
                sUri = sUri.substr(0, sUri.length - 1);
                oWhiteMenuItem.find('input[type=checkbox]').prop('checked', true);
                oWhiteMenuItem.find('label').addClass('active');
            }
            if (oWhiteMenuItem.find('option[value="' + sUri + '"]').length < 1) {
                oWhiteMenuItem.css('border', '1px solid red');
                oWhiteMenuItem.append('[' + sUri + '] 메뉴가 수정되었거나 삭제되었습니다. 변경해주세요!');
            }
            oWhiteMenuItem.find('select').val(sUri);
        }
        $("#white_menu_box").append(oWhiteMenuItem);
    } else {
        var oBlackMenuItem = $(".input-group-menu-black-sample").clone();
        oBlackMenuItem.removeClass('input-group-menu-black-sample');
        if (sUri !== null) {
            if (sUri[sUri.length - 1] === '*') {
                sUri = sUri.substr(0, sUri.length - 1);
                oBlackMenuItem.find('input[type=checkbox]').prop('checked', true);
                oBlackMenuItem.find('label').addClass('active');
            }
            if (oBlackMenuItem.find('option[value="' + sUri + '"]').length < 1) {
                oBlackMenuItem.css('border', '1px solid red');
                oBlackMenuItem.append('[' + sUri + '] 메뉴가 수정되었거나 삭제되었습니다. 변경해주세요!');
            }
            oBlackMenuItem.find('select').val(sUri);
        }
        $("#black_menu_box").append(oBlackMenuItem);
    }
}

function removeMenu(oThis) {
    var oOption = $(oThis).parents('.input-group-menu');

    if (confirm('선택한 메뉴를 삭제하시겠습니까?') === true) {
        oOption.remove();
    }
}