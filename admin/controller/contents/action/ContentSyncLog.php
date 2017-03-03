<?php
namespace controller\contents\action;

use framework\base\Controller;
use framework\library\sql_builder\SqlBuilder;

class ContentSyncLog extends Controller
{
    public function remove()
    {
        $params = $this->getParams();

        if (empty($params['sync_id_list'])) {
            if ($params['is_ajax']) {
                $this->ajaxFail('sync_id_list 없음');
            } else {
                $this->back('sync_id_list 없음');
            }
        }

        $model = \middleware\model\ContentSyncLog::getInstance();
        if (count(explode(',', $params['sync_id_list'])) === 1) {
            $ret = $model->remove(array('sync_id' => $params['sync_id_list']));
        } else {
            $ret = $model->remove(array('content_provider' => $params['vendor'], 'content_type' => CONTENT_TYPE_MOVIE, 'sync_type' => 'CONTENT_ID_MAPPING'));
        }

        if ($ret === true) {
            if ($params['is_ajax']) {
                $this->ajaxSuccess('성공');
            } else {
                $this->redirect("/contents/{$params['vendor']}/newMapList", '성공');
            }
        } else {
            if ($params['is_ajax']) {
                $this->ajaxFail('실패');
            } else {
                $this->back('실패');
            }
        }
    }
}