<?php
namespace controller\contents\cgv;

use controller\AdminBase;
use framework\library\sql_builder\SqlBuilder;
use middleware\model\ContentSyncLog;

class NewMapList extends AdminBase
{
    public function index($vendor = CONTENTS_PROVIDER_CGV)
    {
        $model = ContentSyncLog::getInstance();
        $model->setTable(
            array(
                SqlBuilder::subQuery(
                    'content_sync_log',
                    'content_sync_log',
                    '*',
                    array(
                        'content_provider' => $vendor,
                        'content_type'     => CONTENT_TYPE_MOVIE,
                        'sync_type'        => 'CONTENT_ID_MAPPING'
                    ),
                    null,
                    1000
                ),
                SqlBuilder::join('movie', 'content_sync_log.content_id = movie.movie_id')
            )
        );
        $model->setSelectColumns('content_sync_log.*, title, release_date, re_release_date, making_year');
        $log_list = $model->getList();
        $this->addJs('contents/cgv/new_map_list');
        $this->setViewData(compact('log_list', 'vendor'));
    }
}