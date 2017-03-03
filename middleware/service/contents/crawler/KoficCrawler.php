<?php
namespace middleware\service\contents\crawler;

use framework\library\File;
use framework\library\Log;
use middleware\exception\CrawlerException;
use middleware\library\Curl;
use middleware\library\QpWrapper;
use middleware\model\SystemOption;
use middleware\service\contents\KoficContents;
use middleware\service\contents\parser\KoficMovieParser;
use middleware\service\contents\parser\KoficPeopleParser;

abstract class KoficCrawler extends KoficContents
{
    const LIST_API_MOVIE = 'http://www.kobis.or.kr/kobis/business/mast/mvie/searchMovieList.do';
    const CONTENT_API_MOVIE = 'http://www.kobis.or.kr/kobis/business/mast/mvie/searchMovieDtl.do';
    const LIST_API_PEOPLE = 'http://www.kobis.or.kr/kobis/business/mast/peop/searchPeopleList.do';
    const CONTENT_API_PEOPLE = 'http://www.kobis.or.kr/kobis/business/mast/peop/searchPeopleDtl.do';

    const LIST_API_MOVIE_ACTOR = 'http://www.kobis.or.kr/kobis/business/mast/mvie/searchMovActorLists.do';
    const LIST_API_MOVIE_STAFF = 'http://www.kobis.or.kr/kobis/business/mast/mvie/searchMovStaffLists.do';

    public $parser;

    private $list_api;
    private $content_api;

    private $crawled_cnt = 0;

    public function __construct($content_type)
    {
        parent::__construct($content_type);

        if ($this->isMovieContent()) {
            $this->parser = new KoficMovieParser();
            $this->list_api = self::LIST_API_MOVIE;
            $this->content_api = self::CONTENT_API_MOVIE;
        } else {
            $this->parser = new KoficPeopleParser($this);
            $this->list_api = self::LIST_API_PEOPLE;
            $this->content_api = self::CONTENT_API_PEOPLE;
        }
    }

    public function getList($page_no)
    {
        if (empty($page_no)) {
            throw new CrawlerException('page_no 값이 없습니다.');
        }

        $list_html = Curl::post($this->list_api, array('curPage' => $page_no), 3);
        if (empty($list_html) === true) {
            throw new CrawlerException('get list 실패', $page_no);
        }

        $td = QpWrapper::getInstance($list_html, 'table.boardList03 td.last-child[colspan]');
        $td->onAutoDecodeUtf8();
        $result = $td->text();
        $td->offAutoDecodeUtf8();
        if ($td->exists() && $result === '검색된 데이터가 존재하지 않습니다.') {
            Log::error('해당 페이지가 존재하지 않습니다.', $page_no);

            return null;
        }

        return $list_html;
    }

    public function getContent($content_id)
    {
        if (empty($content_id)) {
            throw new CrawlerException('content id가 비었습니다.');
        }

        $params = array('code' => $content_id, 'titleYN' => true, 'isOuterReq' => 'true');

        $content_html = Curl::post($this->content_api, $params, 3);

        if (empty($content_html)) {
            return null;
        }

        $input = QpWrapper::getInstance($content_html, '#errorMessage');
        if ($input->exists()) {
            Log::error('해당 상세정보가 존재하지 않습니다.', $content_id);

            return null;
        }

        $sub_content = substr($content_html, 0, 42);

        if ($sub_content === '<H2>Message from the NSAPI plugin:</H2><P>') {
            Log::error('상세정보 얻어오기 실패(서버 타임아웃).', $content_id);

            return null;
        }

        return $content_html;
    }

    public function getContentLocal($content_id, $use_backup = false)
    {
        $path = $this->getContentPath($content_id, $use_backup);
        if (empty($path)) {
            Log::error('Local에 파일 없음', $content_id);

            return null;
        }

        $content_html = File::getFileContents($path);

        return $content_html;
    }

    public function getMovieActorListLocal($content_id, $use_backup = false)
    {
        $path = $this->getMovieActorPath($content_id, $use_backup);
        if (empty($path)) {
            Log::error('Local에 파일 없음', $content_id);

            return null;
        }

        $content_html = File::getFileContents($path);

        return $content_html;
    }

    public function getMovieStaffListLocal($content_id, $use_backup = false)
    {
        $path = $this->getMovieStaffPath($content_id, $use_backup);
        if (empty($path)) {
            Log::error('Local에 파일 없음', $content_id);

            return null;
        }

        $content_html = File::getFileContents($path);

        return $content_html;
    }

    public function getMovieActorList($movie_id)
    {
        $ret = Curl::post(self::LIST_API_MOVIE_ACTOR, array('movieCd' => $movie_id), 3, array('Accept:application/json, text/javascript, */*; q=0.01'));
        $array = json_decode($ret, true);
        if (empty($array)) {
            return null;
        }

        return $ret;
    }

    public function getMovieStaffList($movie_id)
    {
        $ret = Curl::post(self::LIST_API_MOVIE_STAFF, array('movieCd' => $movie_id), 3, array('Accept:application/json, text/javascript, */*; q=0.01'));
        $array = json_decode($ret, true);
        if (empty($array)) {
            return null;
        }

        return $ret;
    }

    public function crawlUpdatedLists()
    {
        list($last_update_date, $last_id) = $this->_getLastContentInfo();

        //첫페이지 가져오기
        list($last_page, $first_page_content_ids) = $this->_getFirstPageInfo();

        Log::info('첫페이지 수집 정보', $first_page_content_ids);

        $this->crawled_cnt = 0;

        if (empty($last_update_date) || empty($last_id)) {
            //all crawling mode
            $this->_crawlAllLists($first_page_content_ids, $last_page);
        } else {
            // updated crawling mode
            $this->_crawlUpdatedLists($first_page_content_ids, $last_page, $last_update_date, $last_id);
        }

        if ($this->crawled_cnt > 0) {
            Log::info('수집 완료 업데이트 정보 세팅', array('first_content_id' => $first_page_content_ids[0]));
            $this->setLastUpdatedInfo(
                $this->parser->extractUpdateDate(File::getFileContents($this->getContentPath($first_page_content_ids[0])), $first_page_content_ids[0]),
                $first_page_content_ids[0]
            );
        }

        return $this->crawled_cnt;
    }


    public function crawlList($page, $last_update_date = null, $last_id = null)
    {
        if (empty($page) === true) {
            return false;
        }

        Log::info('페이지 크롤링 시작', $page);

        $list_html = $this->getList($page);
        if (empty($list_html) === true) {
            return -1;
        }

        $content_ids = $this->parser->extractContentIds($list_html, $page);
        if (empty($content_ids) === true) {
            if ($this->isMovieContent()) {
                throw new CrawlerException('list에서 아이디 뽑아오기 실패', array('page' => $page, 'html' => $list_html));
            } else {
                return true;
            }
        }

        return $this->crawlContentList($content_ids, $last_update_date, $last_id);
    }

    public function crawlLists(array $page_list)
    {
        if (empty($page_list)) {
            return false;
        }
        Log::log('페이지별 크롤링 시작', $page_list);

        foreach ($page_list as $page) {
            $ret = $this->crawlList($page);
            if ($ret === false) {
                Log::error('페이지별 크롤링 에러발생', $page);
                continue;
            }
            if ($ret === -1) {
                Log::error('페이지별 크롤링 끝', $page);
                break;
            }
        }
    }

    public function crawlContent($content_id, $last_update_date = null, $last_id = null)
    {
        if (empty($content_id)) {
            return false;
        }

        $content_html = $this->getContent($content_id);

        if (empty($content_html)) {
            return false;
        }

        if (!empty($last_update_date)) {
            $cur_update_date = $this->parser->extractUpdateDate($content_html, $content_id);
            if ($last_update_date >= $cur_update_date) {
                return -1;
            }
        }

        $path = $this->getContentPath($content_id);
        $ret = File::writeToFile($path, $content_html);
        if (!$ret) {
            throw new CrawlerException('상세내용 저장 실패', array($path, $content_html));
        }

        if ($this->isMovieContent()) {
            $this->_crawlMoviePeople($content_id);
        }

        $this->crawled_cnt++;

        return true;
    }

    public function crawlContentList(array $content_id_list, $last_update_date = null, $last_id = null)
    {
        if (empty($content_id_list)) {
            return false;
        }

        Log::info('컨텐츠 id별 크롤링 시작', $content_id_list);

        foreach ($content_id_list as $content_id) {
            $ret = $this->crawlContent($content_id, $last_update_date, $last_id);
            if ($ret === false) {
                Log::error('content 크롤링 실패', $content_id);

                if (!empty($last_update_date) && !empty($last_id)) {
                    return false;
                }
            } elseif ($ret === -1) {
                if (!empty($last_update_date) && !empty($last_id)) {
                    return -1;
                }
            }
        }

        Log::info('컨텐츠 id별 크롤링 완료');

        return true;
    }

    protected function _crawlMoviePeople($content_id)
    {
        $actor_list = $this->getMovieActorList($content_id);
        $staff_list = $this->getMovieStaffList($content_id);

        if (empty($actor_list) === false) {
            $path = $this->getMovieActorPath($content_id);
            $ret = File::writeToFile($path, $actor_list);
            if (!$ret) {
                throw new CrawlerException('상세내용 배우 저장 실패', array($content_id, $actor_list));
            }
        }

        if (empty($staff_list) === false) {
            $path = $this->getMovieStaffPath($content_id);
            $ret = File::writeToFile($path, $staff_list);
            if (!$ret) {
                throw new CrawlerException('상세내용 스탭 저장 실패', array($content_id, $staff_list));
            }
        }
    }

    protected function _getLastContentInfo()
    {
        if ($this->isMovieContent()) {
            $last_date_key = OPTION_LAST_UPDATE_DATE_MOVIE_KOFIC;
            $last_id_key = OPTION_LAST_UPDATE_ID_MOVIE_KOFIC;
        } else {
            $last_date_key = OPTION_LAST_UPDATE_DATE_PEOPLE_KOFIC;
            $last_id_key = OPTION_LAST_UPDATE_ID_PEOPLE_KOFIC;
        }

        $option_model = SystemOption::getInstance();

        $last_update_date = $option_model->get('option_value', array('option_key' => $last_date_key));
        $last_id = $option_model->get('option_value', array('option_key' => $last_id_key));

        return array($last_update_date, $last_id);
    }

    protected function _getFirstPageInfo($first_page_no = 1)
    {
        $list_html = $this->getList($first_page_no);
        if (empty($list_html) === true) {
            throw new CrawlerException('첫번째 리스트 얻어오기 실패');
        }
        $total_cnt = $this->parser->extractTotalCount($list_html, $first_page_no);
        $last_page = $this->_calcLastPage($total_cnt);

        $first_page_content_ids = $this->parser->extractContentIds($list_html, $first_page_no);
        if (empty($first_page_content_ids) === true) {
            if ($this->isMovieContent()) {
                throw new CrawlerException('list에서 아이디 뽑아오기 실패', $list_html);
            } else {
                $first_page_no++;

                return $this->_getFirstPageInfo($first_page_no);
            }
        }

        return array($last_page, $first_page_content_ids);
    }

    public function setLastUpdatedInfo($update_date, $id)
    {
        if ($this->isMovieContent()) {
            $last_date_key = OPTION_LAST_UPDATE_DATE_MOVIE_KOFIC;
            $last_id_key = OPTION_LAST_UPDATE_ID_MOVIE_KOFIC;
        } else {
            $last_date_key = OPTION_LAST_UPDATE_DATE_PEOPLE_KOFIC;
            $last_id_key = OPTION_LAST_UPDATE_ID_PEOPLE_KOFIC;
        }

        $option_model = SystemOption::getInstance();

        $option_model->begin();

        $ret = $option_model->set(array('option_value' => $update_date), array('option_key' => $last_date_key));
        if ($ret === false) {
            $option_model->rollBack();
            throw new CrawlerException('최신 업데이트 날짜 세팅 실패', $update_date);
        }
        $ret = $option_model->set(array('option_value' => $id), array('option_key' => $last_id_key));
        if ($ret === false) {
            $option_model->rollBack();
            throw new CrawlerException('최신 업데이트 아이디 세팅 실패', $update_date);
        }
        $option_model->commit();
    }

    protected function _crawlAllLists($first_page_content_ids, $last_page)
    {
        Log::log('전체 리스트 수집 시작', compact('last_page'));

        if (!$this->crawlContentList($first_page_content_ids)) {
            throw new CrawlerException('첫번째 페이지 크롤링 실패', $first_page_content_ids);
        }

        $i = 1;
        while (true) {
            $i++;
            $ret = $this->crawlList($i);
            if ($ret === false) {
                throw new CrawlerException('크롤링 실패', array('page' => $i));
            }

            if ($ret === -1) {
                Log::log('리스트 수집 종료(전체 수집)', array('page' => $i, 'last_page' => $last_page));
                break;
            }

            Log::log('리스트 수집 완료(전체 수집)', array('page' => $i, 'last_page' => $last_page));

            $this->_sleep();
        }
    }

    protected function _crawlUpdatedLists($first_page_content_ids, $last_page, $last_update_date, $last_id)
    {
        Log::log('업데이트된 리스트 수집 시작', compact('last_page', 'last_update_date', 'last_id'));

        $ret = $this->crawlContentList($first_page_content_ids, $last_update_date, $last_id);
        if ($ret === false) {
            throw new CrawlerException('첫번째 페이지 크롤링 에러', compact('first_page_content_ids', 'last_update_date', 'last_id'));
        }

        if ($ret === -1) {
            return false;
        }

        for ($i = 2; $i <= $last_page; $i++) {
            $ret = $this->crawlList($i, $last_update_date, $last_id);
            if ($ret === false) {
                throw new CrawlerException('크롤링 실패', array('page' => $i, 'last_update_date' => $last_update_date, 'last_id' => $last_id));
            } elseif ($ret === -1) {
                Log::log('리스트 수집 종료(업데이트 수집)', array('page' => $i, 'last_page' => $last_page));
                break;
            }

            Log::log('리스트 수집 완료(업데이트 수집)', array('page' => $i, 'last_page' => $last_page));

            $this->_sleep();
        }

        return true;
    }

    protected function _calcLastPage($total_cnt)
    {
        $last_page = (int)($total_cnt / 10);
        if ($total_cnt % 10 > 0) {
            $last_page++;
        }

        return $last_page;
    }
}