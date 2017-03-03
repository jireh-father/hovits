<?php
namespace controller\contents\cgv;

use controller\AdminBase;
use framework\library\Session;
use framework\library\sql_builder\SqlBuilder;
use middleware\model\ContentSyncLog;
use middleware\model\Movie;

class IdMapChecker extends AdminBase
{
    private static $validate_params = array(
        'content_id'   => null,
        'is_local'     => '0',
        'content_type' => CONTENT_TYPE_MOVIE
    );

    public function movie($vendor = null)
    {
        if (empty($vendor)) {
            $vendor = CONTENTS_PROVIDER_CGV;
        }

        $session_vendor_key = "{$vendor}_id_map_checker_idx";
        $vendor_id_key = "{$vendor}_id";
        $vendor_disabled_key = "{$vendor}_disabled";

        $params = $this->validateParams(self::$validate_params, true);
        $params['vendor'] = $vendor;
        if (!empty($params['content_id'])) {
            $content_ids = explode(',', $params['content_id']);
            if (count($content_ids) === 1) {
                $this->setViewData(array_merge($params, array('query' => http_build_query($params))));
                $content_id = $params['content_id'];
            } else {
                $idx = Session::get($session_vendor_key);
                if (empty($idx)) {
                    $idx = 0;
                }

                $is_back = $this->getParam('is_back');
                if (!empty($is_back) && $is_back === 'is_back') {
                    $idx -= 2;
                }

                if (empty($content_ids[$idx])) {
                    Session::set($session_vendor_key, null);
                    $this->addJsCode('alert("전체 테스트 종료");');
                    $idx--;
                }
                $tmp_params = $params;
                $tmp_params['content_id'] = $content_ids[$idx++];
                $content_id = $tmp_params['content_id'];
                Session::set($session_vendor_key, $idx);
                $params['multi_mode'] = true;
                $params['total'] = count($content_ids) - 1;
                $params['index'] = $idx - 1;
                $this->setViewData(array_merge($params, array('query' => http_build_query($tmp_params))));
            }
            if (!empty($content_id)) {
                if ($params['is_local'] == true) {
                    $is_server_type = 'local';
                } else {
                    $is_server_type = 'server';
                }

                $model = ContentSyncLog::getInstance();
                $new_id_map = $model->getRow(
                    array(
                        'content_provider' => $vendor,
                        'content_type'     => CONTENT_TYPE_MOVIE,
                        'sync_type'        => 'CONTENT_ID_MAPPING',
                        'content_id'       => $content_id
                    )
                );

                $this->addViewData(array('cur_content_id' => $content_id, 'is_server_type' => $is_server_type));
                if (!empty($new_id_map)) {
                    $this->addViewData('has_new_map', true);
                    $this->addViewData('sync_id', $new_id_map['sync_id']);
                }
                $model = Movie::getInstance();
                $movie = $model->getRow(array('movie_id' => $content_id));
                if (!empty($movie)) {
                    $this->addViewData(array('vendor_id' => $movie[$vendor_id_key], 'vendor_disabled' => $movie[$vendor_disabled_key]));
                }
            }
        }

        $this->addViewData('vendor', $vendor);

        $this->addJs('contents/cgv/id_map_checker');
        $this->setView('contents/cgv/id_map_checker');
    }

    public function init()
    {
        $vendor = $this->getParam('vendor');
        Session::set($vendor . '_id_map_checker_idx', null);

        $this->redirect("/contents/{$vendor}/idMapChecker/movie");
    }

    public function people()
    {
    }
}