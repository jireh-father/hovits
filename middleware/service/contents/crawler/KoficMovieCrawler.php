<?php
namespace middleware\service\contents\crawler;

use framework\library\File;
use framework\library\Log;
use framework\library\Time;
use middleware\exception\CrawlerException;
use middleware\library\Curl;
use middleware\library\QpWrapper;

class KoficMovieCrawler extends KoficCrawler
{
    const MOVIE_CATEGORY_CRAWLING_URL = 'http://www.kobis.or.kr/kobis/business/comm/comm/findCommCode.do?comCode=2201000000&sCode=&_=1421833365485';
    const GENRE_CRAWLING_URL = 'http://www.kobis.or.kr/kobis/business/comm/comm/findCommCode.do?comCode=2205000000&sCode=&_=1421831283371';
    const LIMIT_GRADE_CRAWLING_URL = 'http://www.kobis.or.kr/kobis/business/comm/comm/findCommCodeTree.do?comCode=2300000000&_=1421833089478';
    const MAKING_COUNTRY_CRAWLING_URL = 'http://www.kobis.or.kr/kobis/business/comm/comm/findCommCodeTree.do?comCode=2203000000&_=1421833228100';
    const REAL_TIME_BOX_OFFICE_CRAWLING_URL = 'http://www.kobis.or.kr/kobis/business/stat/boxs/findRealTicketList.do?loadEnd=0&repNationCd=&areaCd=0105001%3A0105002%3A0105003%3A0105004%3A0105005%3A0105006%3A0105007%3A0105008%3A0105009%3A0105010%3A0105011%3A0105012%3A0105013%3A0105014%3A0105015%3A0105016%3A&repNationSelected=&totIssuAmtRatioOrder=&totIssuAmtOrder=&addTotIssuAmtOrder=&totIssuCntOrder=&totIssuCntRatioOrder=&addTotIssuCntOrder=&dmlMode=search&repNationChk=&repNationKor=on&repNationKor=on&wideareaAll=ALL&wideareaCd=0105001&wideareaCd=0105011&wideareaCd=0105012&wideareaCd=0105015&wideareaCd=0105016&wideareaCd=0105013&wideareaCd=0105014&wideareaCd=0105002&wideareaCd=0105003&wideareaCd=0105005&wideareaCd=0105004&wideareaCd=0105007&wideareaCd=0105006&wideareaCd=0105009&wideareaCd=0105008&wideareaCd=0105010&allMovieYn=Y';
    const BOX_OFFICE_EXCEL_CRAWLING_URL = 'http://www.kobis.or.kr/kobis/business/stat/boxs/findDailyBoxOfficeList.do?loadEnd=0&searchType=excel&';
    const BOX_OFFICE_START_DATE = '2003-11-11';

    public function __construct()
    {
        parent::__construct(CONTENT_TYPE_MOVIE);
    }

    public function getMovieCategory()
    {
        $category_json = Curl::get(self::MOVIE_CATEGORY_CRAWLING_URL, null, 2, array('Content-Type:application/json+sua'), 3);
        $category_crawl = json_decode($category_json, true);
        if (empty($category_crawl)) {
            throw new CrawlerException('영화 유형 데이터 크롤링 실패');
        }
        $category_list = array();
        foreach ($category_crawl['cmData'] as $category) {
            $category_kor = trim($category['korNm']);
            if ($category_kor == '기타') {
                continue;
            }
            $category_list[] = $category_kor;
        }

        return $category_list;
    }

    public function getGenre()
    {
        $genre_json = Curl::get(self::GENRE_CRAWLING_URL, null, 2, array('Content-Type:application/json+sua'), 3);
        $genre_crawl = json_decode($genre_json, true);
        if (empty($genre_crawl)) {
            throw new CrawlerException('영화 장르 데이터 크롤링 실패');
        }
        $genre_list = array();
        foreach ($genre_crawl['cmData'] as $genre) {
            $genre_kor = trim($genre['korNm']);
            if ($genre_kor == '기타') {
                continue;
            }
            $genre_list[] = $genre_kor;
        }

        return $genre_list;
    }

    public function getLimitGrade()
    {
        $grade_json = Curl::get(self::LIMIT_GRADE_CRAWLING_URL, null, 2, array('Content-Type:application/json+sua'), 3);
        $grade_crawl = json_decode($grade_json, true);
        if (empty($grade_crawl)) {
            throw new CrawlerException('영화 등급 데이터 크롤링 실패');
        }
        $grade_list = array();
        foreach ($grade_crawl['cmData'] as $grade) {
            if ($grade['lvl'] != 3) {
                continue;
            }
            $grade_kor = trim($grade['korNm']);
            if ($grade_kor == '기타') {
                continue;
            }
            $grade_list[] = $grade_kor;
        }

        return array_unique($grade_list);
    }

    public function getMakingCountry()
    {
        $country_json = Curl::get(self::MAKING_COUNTRY_CRAWLING_URL, null, 2, array('Content-Type:application/json+sua'), 3);
        $country_crawl = json_decode($country_json, true);
        if (empty($country_crawl)) {
            throw new CrawlerException('영화 제작국가 데이터 크롤링 실패');
        }
        $country_list = array();
        foreach ($country_crawl['cmData'] as $country) {
            if ($country['lvl'] != 3) {
                continue;
            }
            $country_kor = trim($country['korNm']);
            if ($country_kor == '기타') {
                continue;
            }
            $country_list[] = $country_kor;
        }

        return array_unique($country_list);
    }

    public function getRealTimeBoxOffice($search_time = null)
    {
        $html = Curl::get(self::REAL_TIME_BOX_OFFICE_CRAWLING_URL, null, 3, null, 10, 5);

        if (empty($html)) {
            throw new CrawlerException('실시간 박스오피스 검색 실패');
        }

        $result_cnt_div = QpWrapper::getInstance($html, '.board_btm');

        if (!$result_cnt_div->exists()) {
            Log::error('실시간 박스오피스 검색내용 이상함.', $html);

            return null;
        }

        $result_cnt = $this->parser->extractSearchResultCnt($result_cnt_div);

        if ($result_cnt < 1) {
            throw new CrawlerException('실시간 박스오피스 검색 갯수 0개');
        }

        return $html;
    }

    public function crawlRealTimeBoxOffice()
    {
        $html = $this->getRealTimeBoxOffice();

        $now = Time::getDate('Ym/dH/i');
        $path = $this->getRealTimeBoxOfficePath($now);

        $ret = File::writeToFile($path, $html);
        if (!$ret) {
            throw new CrawlerException('실시간 박스오피스 내용 저장 실패', array($path, $html));
        }

        return true;
    }

    public function buildBoxOfficeExcelUrl($date)
    {
        if (empty($date)) {
            return null;
        }

        $date_param = array('sSearchFrom' => $date, 'sSearchTo' => $date);

        return self::BOX_OFFICE_EXCEL_CRAWLING_URL . http_build_query($date_param);
    }

    public function crawlAllBoxOfficeExcel($is_force = false)
    {
        $start_date = self::BOX_OFFICE_START_DATE;
        $today = Time::Ymd();
        $total_cnt = Time::diffDays($today, $start_date);
        $idx = 0;
        Log::info('박스오피스 엑셀 다운로드 전체 시작', $total_cnt);
        $result = array('success' => 0, 'fail' => 0, 'skip' => 0);
        for ($date = $start_date; $date < $today; $date = Time::addDays(1, $date)) {
            $idx++;
            if ($this->isBoxOfficeExcelFile($date, true) && !$is_force) {
                $result['skip']++;
                continue;
            }

            $ret = $this->crawlBoxOfficeExcel($date);

            if ($ret) {
                Log::info('박스오피스 엑셀 다운로드 성공', compact('total_cnt', 'idx'));
                $result['success']++;
            } else {
                Log::error('박스오피스 엑셀 다운로드 실패', compact('total_cnt', 'idx'));
                $result['fail']++;
            }

            $this->_sleep();
        }
        Log::info('박스오피스 엑셀 다운로드 전체 끝', array($total_cnt, $result));

        return $result;
    }

    public function crawlBoxOfficeExcel($date)
    {
        if (empty($date)) {
            return null;
        }

        $api_url = $this->buildBoxOfficeExcelUrl($date);
        $path = $this->getBoxOfficeExcelPath($date);

        return $this->downloadBoxOfficeExcel($api_url, $path);
    }

    public function downloadBoxOfficeExcel($url, $path)
    {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        $fp = fopen($path, 'w+');

        $header = array('User-Agent:Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36');
        $options = array(CURLOPT_RETURNTRANSFER => false, CURLOPT_BINARYTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_FILE => $fp);

        $ret = Curl::get($url, null, 3, $header, 60, 10, $options);
        fclose($fp);

        return $ret;
    }
}