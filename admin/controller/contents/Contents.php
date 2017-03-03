<?php
namespace controller\contents;

use controller\AdminBase;
use middleware\service\contents\crawler\KoficCrawler;
use middleware\service\contents\crawler\KoficMovieCrawler;
use middleware\service\contents\crawler\KoficPeopleCrawler;
use middleware\service\contents\sync\KoficMovieSync;
use middleware\service\contents\sync\KoficPeopleSync;
use middleware\service\contents\sync\KoficSync;

class Contents extends AdminBase
{
    public function index()
    {
    }

    private static $required_contents_cols = array(
        'content_type',
        array(
            'content_id',
            'page'
        ),
        'action_type' => null,
    );

    public function action()
    {
        setUnlimitTimeout();
        $params = $this->validateParams(self::$required_contents_cols, true);
        $obj = null;
        switch ($params['content_type']) {
            case CONTENT_TYPE_MOVIE:
                if ($params['action_type'] === 'crawler') {
                    $obj = new KoficMovieCrawler();
                } elseif ($params['action_type'] === 'sync') {
                    $obj = new KoficMovieSync();
                }
                break;
            case CONTENT_TYPE_PEOPLE:
                if ($params['action_type'] === 'crawler') {
                    $obj = new KoficPeopleCrawler();
                } elseif ($params['action_type'] === 'sync') {
                    $obj = new KoficPeopleSync();
                }
                break;
        }

        if (!empty($params['page'])) {
            $pages = explode(',', $params['page']);
            if ($params['action_type'] === 'crawler') {
                $this->_crawlPage($obj, $pages);
            } elseif ($params['action_type'] === 'sync') {
                $this->_syncPage($obj, $pages);
            }

        }

        if (!empty($params['content_id'])) {
            $content_ids = explode(',', $params['content_id']);
            if ($params['action_type'] === 'crawler') {
                $this->_crawl($obj, $content_ids);
            } elseif ($params['action_type'] === 'sync') {
                $this->_sync($obj, $content_ids);
            }
        }

        $this->back('요청완료');
    }

    /**
     * @param KoficCrawler $crawler_object
     * @param $content_ids
     * @return bool|int
     */
    private function _crawl($crawler_object, $content_ids)
    {
        return $crawler_object->crawlContentList($content_ids);
    }

    /**
     * @param KoficCrawler $crawler_object
     * @param $pages
     * @return bool
     */
    private function _crawlPage($crawler_object, $pages)
    {
        return $crawler_object->crawlLists($pages);
    }

    /**
     * @param KoficSync $sync_object
     * @param $content_ids
     * @return bool
     */
    private function _sync($sync_object, $content_ids)
    {
        return $sync_object->syncContentsDirect($content_ids, true);
    }

    /**
     * @param KoficSync $sync_object
     * @param $pages
     * @return bool
     */
    private function _syncPage($sync_object, $pages)
    {
        return $sync_object->syncContentsDirectByPages($pages, true);
    }
}