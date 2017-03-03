<?php
namespace controller\contents\action;

use framework\base\Controller;

class Movie extends Controller
{
    public function set()
    {
        $params = $this->getParams();
        $vendor = $params['vendor'];
        unset($params['vendor']);

        if (empty($params['content_id']) || $params['movie_id']) {
            $this->ajaxFail('키값이 없음', $params);
        }

        if (!empty($params['content_id'])) {
            $params['movie_id'] = $params['content_id'];
            unset($params['content_id']);
        }

        $where = array('movie_id' => $params['movie_id']);
        unset($params['movie_id']);

        if (!isset($params[$vendor . '_disabled'])) {
            $params[$vendor . '_disabled'] = false;
        }

        $model = \middleware\model\Movie::getInstance();
        $ret = $model->set($params, $where);

        if ($ret === true) {
            $this->ajax('success');
        } else {
            $this->ajaxFail();
        }
    }
}