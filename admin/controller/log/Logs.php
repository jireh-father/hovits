<?php
namespace controller\log;

use controller\AdminBase;
use framework\library\Log;
use framework\library\sql_builder\SqlBuilder;
use framework\model\CommonLog;

class Logs extends AdminBase
{
    private static $log_search_params = array(
        'log_id'      => SqlBuilder::EXPR_DEFAULT,
        'trace_id'    => SqlBuilder::EXPR_DEFAULT,
        'log_level'   => SqlBuilder::EXPR_DEFAULT,
        'log_type'    => SqlBuilder::EXPR_DEFAULT,
        'log_caller'  => SqlBuilder::EXPR_DEFAULT,
        'log_msg'     => SqlBuilder::EXPR_DEFAULT,
        'log_data'    => SqlBuilder::EXPR_LIKE_BOTH_WILDCARD,
        'client_ip'   => SqlBuilder::EXPR_DEFAULT,
        'server_host' => SqlBuilder::EXPR_DEFAULT,
        'insert_time' => SqlBuilder::EXPR_DATE_RANGE
    );

    private static $aTraceSearchCols = array(
        'log_id'      => SqlBuilder::EXPR_DEFAULT,
        'log_type'    => SqlBuilder::EXPR_DEFAULT,
        'log_level'   => SqlBuilder::EXPR_DEFAULT,
        'trace_id'    => SqlBuilder::EXPR_DEFAULT,
        'insert_time' => SqlBuilder::EXPR_DATE_RANGE
    );

    public function index()
    {
        $search_params = $this->getParams(array_keys(self::$log_search_params));
        $search_data = SqlBuilder::autoBuild($search_params, self::$log_search_params, true);
        $limit = $this->getParam('limit', 100);
        $offset = $this->getParam('offset', 0);
        if ($offset < 0) {
            $offset = 0;
        }
        $log_model = CommonLog::getInstance();
        $log_list = $log_model->getList($search_data, 'log_id DESC', "{$offset}, {$limit}");
        $total_cnt = $log_model->getRowCount();
        $searched_cnt = $log_model->getRowCount($search_data);
        $row_cnt = count($log_list);
        $log_type_list = $log_model->getGroupValues('log_type');
        $log_caller_list = $log_model->getGroupValues('log_caller');
        $this->setTitle('로그 관리');
        $this->addExternalJs('//code.jquery.com/jquery-1.11.0.min.js');
        $this->addViewData('log_list', $log_list);
        $this->addViewData('limit', $limit);
        $this->addViewData('offset', $offset);
        $this->addViewData('search_params', $search_params);
        $this->addViewData('search_string', http_build_query($search_params));
        $this->addViewData('total_cnt', $total_cnt);
        $this->addViewData('searched_cnt', $searched_cnt);
        $this->addViewData('row_cnt', $row_cnt);
        $this->addViewData('log_type_list', $log_type_list);
        $this->addViewData('log_caller_list', $log_caller_list);
        $this->addViewData('log_level_list', array_keys(Log::$LOG_LEVEL));
        $this->addCss('json-tree/jsontree');
        $this->addJs('json-tree/jsontree.min');
        $this->addJs('log/logs');
    }

    public function trace()
    {
        $aSearchData = r()->getParams();
        $aSearchData = $aSearchData['get'];
        $aSearchWhere = $this->_filterSearchData($aSearchData, self::$aTraceSearchCols);

        $oModel = modelCsdCommonlog::getInstance();
        $aLogs = $oModel->searchTraceLogs($aSearchWhere, 100);

        $aHosts = $oModel->getColumnGroup('host');
        $aProjectNames = $oModel->getColumnGroup('project_name');
        $aLogTypes = $oModel->getColumnGroup('log_type');
        $aIndexKeys = $oModel->getColumnGroup('index_key');

        $aViewData = compact(
            'aLogs',
            'aHosts',
            'aProjectNames',
            'aLogTypes',
            'aIndexKeys',
            'aSearchData'
        );
        $aViewData['aLogLevels'] = self::$aLogLevels;

        $this->css('json-tree/jsontree');
        $this->js('json-tree/jsontree.min');
        $this->js('Log/common');

        $this->view('Log/trace', $aViewData);
    }

    public function traceDetail()
    {
        $iTraceId = r()->getParam('trace_id');
        if (empty($iTraceId) === true) {
            echo false;
            exit;
        }

        $oModel = modelCsdCommonlog::getInstance();
        $aTraceLogs = $oModel->getTraceLogs($iTraceId);

        return json_encode($aTraceLogs);
    }

    private function _filterSearchData($aParams, $aSearchCols)
    {
        $aSearchData = array();
        foreach ($aSearchCols as $sCol => $sType) {
            if (empty($aParams[$sCol]) === false) {
                if ($sType === null) {
                    $aSearchData[$sCol] = $aParams[$sCol];
                } else if (is_array($sType) === true) {
                    if (empty($aSearchData[$sCol]) === true) {
                        $aSearchData[$sCol] = array();
                    }
                    $sKey = key($sType);
                    $aSearchData[$sKey][] = array($aParams[$sCol] => $sType[$sKey]);
                } else {
                    $aSearchData[$sCol] = array($aParams[$sCol] => $sType);
                }

            }
        }

        return $aSearchData;
    }
}