<table class="table table-hover">
    <thead>
    <tr>
        <th>#</th>
        <th>ID</th>
        <th>ROLE NAME</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $i = 0;
    foreach ($aAccounts as $sId => $aAccount):
        $i++;
        $sRoleName = $aAccount[\library\Account::KEY_ROLE];
    ?>
    <tr>
        <th scope="row"><?php pr($i) ?></th>
        <td><?php pr($sId) ?></td>
        <td>
            <form action="/setting/account/set" method="post" class="form-inline" onsubmit="return confirm('변경하시겠습니까?');">
                <input type="hidden" name="account_name" value="<?=$sId?>"/>
                <?php pr(\library\Account::buildRoleSelectBox($aRoles, $sRoleName)) ?>
                <button type="submit" class="btn btn-default">
                    <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span> 변경
                </button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>