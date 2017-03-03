<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title" id="panel-title">정보</h3>
    </div>
    <div class="panel-body">
        Black List 가 White List 보다 우선순위가 높습니다.
        </br><strong>Black List > White List</strong>
    </div>
</div>
<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title" id="panel-title">Action 권한제한 사용법</h3>
    </div>
    <div class="panel-body">
        권한 제한하고싶은 소스코드에 한 줄만 추가해주세요.
        </br><strong>Account::checkActionAuth('action name');</strong>
    </div>
</div>
<div>
    <div style="float: right">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_role_modal" onclick="setRoleData(null);">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Add Role
        </button>
    </div>
</div>
<table class="table table-hover">
    <thead>
    <tr>
        <th>#</th>
        <th>Role Name</th>
        <th>White Menu List</th>
        <th>Black Menu List</th>
        <th>White Action List</th>
        <th>Black Action List</th>
        <th class="col-function">Function</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $i = 0;
    foreach ($aRoles as $sKey => $aRole):
        $aRole[\library\Account::KEY_ROLE] = $sKey;
        $i++;
    ?>
        <tr>
            <th scope="row"><?php echo $i ?></th>
            <td><?php echo $sKey ?></td>
            <td><?php echo implode(',', $aRole[\middleware\library\Menu::KEY_MENU][\library\Account::KEY_WHITE_LIST]) ?></td>
            <td><?php echo implode(',', $aRole[\middleware\library\Menu::KEY_MENU][\library\Account::KEY_BLACK_LIST]) ?></td>
            <td><?php echo implode(',', $aRole[\library\Account::KEY_ACTION][\library\Account::KEY_WHITE_LIST]) ?></td>
            <td><?php echo implode(',', $aRole[\library\Account::KEY_ACTION][\library\Account::KEY_BLACK_LIST]) ?></td>
            <td>
                <button type="button" class="btn btn-default btn-xs" data-toggle="modal" data-target="#add_role_modal"
                    onclick="setRoleData('<?php echo base64_encode(json_encode($aRole)) ?>', 'add_role_modal');">
                    <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>
                </button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<!-- Modal -->
<div class="modal fade" id="add_role_modal" tabindex="-1" role="dialog" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="/setting/role/set" onsubmit="return onSubmitSetRole();">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="addRoleModalLabel">Add Role</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="role_name">Role Name</label> <input type="text" name="role_name" class="form-control" id="role_name">
                    </div>
                    <div class="form-group">
                        <label for="white_action">White Action List</label> <input type="text" name="white_action" class="form-control" id="white_action" placeholder="ALL or action1,action2,action3">
                    </div>
                    <div class="form-group">
                        <label for="black_action">Black Action List</label> <input type="text" name="black_action" class="form-control" id="black_action" placeholder="action1,action2,action3">
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" style="position: relative;">
                            <strong>White Menu List</strong>
                            <div style="right: 14px;top:3px;position: absolute;">
                                <button type="button" class="btn btn-success" onclick="addMenu('white', null);">
                                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div class="panel-body" id="white_menu_box"></div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading" style="position: relative;">
                            <strong>Black Menu List</strong>
                            <div style="right: 14px;top:3px;position: absolute;">
                                <button type="button" class="btn btn-success" onclick="addMenu('black', null);">
                                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                        <div class="panel-body" id="black_menu_box"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <input type="submit" class="btn btn-primary" value="Save"/>
                </div>
            </form>
        </div>
    </div>
</div>

<div style="display: none;">
    <div class="form-group form-inline input-group-menu input-group-menu-white input-group-menu-white-sample">
        <div class="input-group" data-toggle="buttons">
            <div class="input-group-addon">Menu Uri</div>
            <select class="form-control" name="white_menu_uri[]" style="width: 295px;">
                <option value="ALL">전체</option>
                <?php foreach($aMenuUriList as $sMenuUri):?>
                    <option value="<?php echo $sMenuUri?>"><?php echo $sMenuUri?></option>
                <?php endforeach;?>
            </select>
            <label class="btn btn-default">
            <input type="checkbox" autocomplete="off" value="*">하위메뉴 포함 </label>
            <span class="input-group-btn">
                <button type="button" class="btn btn-danger" onclick="removeMenu(this);">
                    <span class="glyphicon glyphicon-minus" aria-hidden="true"></span>
                </button>
            </span>
        </div>
    </div>
</div>

<div style="display: none;">
    <div class="form-group form-inline input-group-menu input-group-menu-black input-group-menu-black-sample">
        <div class="input-group" data-toggle="buttons">
            <div class="input-group-addon">Menu Uri</div>
            <select class="form-control" name="black_menu_uri[]" style="width: 295px;">
                <?php foreach ($aMenuUriList as $sMenuUri): ?>
                    <option value="<?php echo $sMenuUri ?>"><?php echo $sMenuUri ?></option>
                <?php endforeach; ?>
            </select>
            <label class="btn btn-default">
                <input type="checkbox" autocomplete="off" value="*">하위메뉴 포함
            </label>
            <span class="input-group-btn">
                <button type="button" class="btn btn-danger" onclick="removeMenu(this);">
                    <span class="glyphicon glyphicon-minus" aria-hidden="true"></span>
                </button>
            </span>
        </div>
    </div>
</div>