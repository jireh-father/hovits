<?php
namespace middleware\service\contents\parser;

use framework\library\FileCache;
use framework\library\Log;
use framework\library\String;
use framework\library\Time;
use middleware\exception\ParserException;
use middleware\library\Excel;
use middleware\library\QpWrapper;
use middleware\service\contents\crawler\KoficCrawler;
use middleware\service\contents\crawler\KoficMovieCrawler;
use QueryPath\ParseException;

class KoficMovieParser extends KoficParser
{
    const MOVIE_INFO_CACHE_TIME = 86400;
    private static $movie_type_list = array('일반영화', '예술영화', '다양성영화', '다양성(예술)영화', '다양성(독립)영화', '다양성(예술,독립)영화', '독립영화', '예술,독립 영화');
    public static $movie_category_list = array();
    public static $genre_list = array();
    public static $limit_grade_list = array();
    public static $making_country_list = array();
    public static $extra_making_country_list = array('소련', '이란', '프랑스', '기타');
    public static $except_genre_list = array('기타');
    public static $filter_genre_list = array('공포(호러)' => '공포', '서부극(웨스턴)' => '서부', '성인물(에로)' => '성인');

    public static $box_office_excel_col_map = array(
        0  => 'rank',
        2  => 'release_date',
        3  => 'sales',
        4  => 'sales_rate',
        5  => 'sales_net_change',
        6  => 'sales_net_change_rate',
        7  => 'total_sales',
        8  => 'ticket',
        9  => 'ticket_net_change',
        10 => 'ticket_net_change_rate',
        11 => 'total_ticket',
        12 => 'screen',
        13 => 'show',
        14 => 'sales',
    );

    private static $real_time_box_office_cols = array(
        'skip',
        'movie_id',
        'skip',
        'booking_ratio',
        'booking_sales',
        'total_sales',
        'booking_ticket_count',
        'total_ticket_count',
    );

    /**
     * @param KoficCrawler $crawler
     */
    public function __construct($crawler = null)
    {
        parent::__construct(CONTENT_TYPE_MOVIE, $crawler);
    }

    protected function _syncEtcInfo()
    {
        if (empty(self::$movie_category_list)) {
            $cache = FileCache::get('crawling_data_movie_category');
            if (empty($cache)) {
                if (empty($this->crawler)) {
                    $crawler = $this->crawler = new KoficMovieCrawler();
                } else {
                    $crawler = $this->crawler;
                }
                self::$movie_category_list = $crawler->getMovieCategory();
                FileCache::set('crawling_data_movie_category', self::$movie_category_list, self::MOVIE_INFO_CACHE_TIME);
            } else {
                self::$movie_category_list = json_decode($cache, true);
            }
        }

        if (empty(self::$genre_list)) {
            $cache = FileCache::get('crawling_data_genre');
            if (empty($cache)) {
                self::$genre_list = $crawler->getGenre();
                FileCache::set('crawling_data_genre', self::$genre_list, self::MOVIE_INFO_CACHE_TIME);
            } else {
                self::$genre_list = json_decode($cache, true);
            }
        }

        if (empty(self::$limit_grade_list)) {
            $cache = FileCache::get('crawling_data_limit_grade');
            if (empty($cache)) {
                self::$limit_grade_list = $crawler->getLimitGrade();
                FileCache::set('crawling_data_limit_grade', self::$limit_grade_list, self::MOVIE_INFO_CACHE_TIME);
            } else {
                self::$limit_grade_list = json_decode($cache, true);
            }
        }

        if (empty(self::$making_country_list)) {
            $cache = FileCache::get('crawling_data_making_country');
            if (empty($cache)) {
                self::$making_country_list = $crawler->getMakingCountry();
                FileCache::set('crawling_data_making_country', self::$making_country_list, self::MOVIE_INFO_CACHE_TIME);
            } else {
                self::$making_country_list = json_decode($cache, true);
            }
            self::$making_country_list = array_merge(self::$extra_making_country_list, self::$making_country_list);
        }

    }

    /**
     * @param QpWrapper $info_data
     * @return array|null
     * @throws ParserException
     */
    protected function _extractAka($info_data)
    {
        $title_aka = $info_data->text();
        $this->_empty(compact('title_aka'));
        if ($this->_isEmptyValue($title_aka) === true) {
            return null;
        }
        if ($title_aka[0] === '(' && String::getLastChar($title_aka) === ')') {
            $title_aka = String::cutBothSide($title_aka, 1, 1);
        }

        return compact('title_aka');
    }

    /**
     * @param QpWrapper $info_data
     * @return array
     * @throws ParserException
     */
    protected function _extractInfo($info_data)
    {
        $data = array();
        $this->_syncEtcInfo();

        $is_except_genre = false;

        foreach ($info_data->find('li') as $sub_info_list) {
            $this->_exists(compact('sub_info_list'));
            $sub_info_text = $sub_info_list->text();
            if (empty($sub_info_text)) {
                continue;
            }

            if ($this->_isEmptyValue($sub_info_text)) {
                continue;
            }

            if (in_array($sub_info_text, self::$movie_category_list)) {
                $data['movie_category'] = $sub_info_text;
                continue;
            }
            if (in_array($sub_info_text, self::$movie_type_list)) {
                $data['movie_type'] = $sub_info_text;
                continue;
            }
            if (preg_match('/^([0-9]{1,4})분$/', $sub_info_text, $matches)) {
                if ($matches[1] > 0) {
                    $data['duration'] = $matches[1];
                }
                continue;
            }
            if (in_array($sub_info_text, self::$limit_grade_list)) {
                $data['limit_grade'] = $sub_info_text;
                continue;
            }
            $sub_info_multi = String::explodeTrim(',', $sub_info_text, true);
            if (in_array($sub_info_multi[0], self::$genre_list)) {
                foreach ($sub_info_multi as $i => $genre) {
                    if (in_array($genre, self::$except_genre_list)) {
                        $is_except_genre = true;
                        unset($sub_info_multi[$i]);
                    }
                }
                if (!empty($sub_info_multi)) {
                    $data['genre'] = $sub_info_multi;
                }
                continue;
            }
            if (in_array($sub_info_multi[0], self::$making_country_list)) {
                $idx = array_search('기타', $sub_info_multi);
                if ($idx !== false) {
                    unset($sub_info_multi[$idx]);
                    if (empty($sub_info_multi)) {
                        Log::info('제작국가 기타만있어서 삭제함', $this->content_id);
                        continue;
                    }
                }
                $idx = array_search('총괄(연감)', $sub_info_multi);
                if ($idx !== false) {
                    unset($sub_info_multi[$idx]);
                    if (empty($sub_info_multi)) {
                        Log::info('제작국가에 총괄(연감)만 있어서 삭제함', $this->content_id);
                        continue;
                    }
                }
                $data['making_country'] = $sub_info_multi;
                continue;
            }
            throw new ParserException('요약정보에 알수없는 데이터', array($sub_info_text, $sub_info_list->html(), $info_data->html()));
        }

        if (empty($data['genre']) && $is_except_genre) {
            throw new ParserException('장르 빼서 장르 하나도 없음');
        }

        if (!empty($data['genre'])) {
            $this->_filterGenres($data);
        }

        return $data;
    }

    protected function _filterGenres(&$data)
    {
        foreach ($data['genre'] as $i => $genre) {
            if (!empty(self::$filter_genre_list[$genre])) {
                $data['genre'][$i] = self::$filter_genre_list[$genre];
            }
        }
    }

    /**
     * @param QpWrapper $info_data
     * @return array
     * @throws ParserException
     */
    protected function _extractDate($info_data, $info_title_text)
    {
        $data = array();
        foreach ($info_data->find('li') as $date_data_list) {
            $this->_exists(compact('date_data_list'));
            $date_title_tag = $date_data_list->find('em');
            if (!$date_title_tag->exists()) {
                $release_date = $date_data_list->text();
                if (empty($release_date) || $this->_isEmptyValue($release_date)) {
                    Log::info('개봉일 정보 없음', array($release_date, $this->content_id));
                    continue;
                }

                if (strlen($release_date) === 10) {
                    $filter_release_date = Time::filterYmdDate($release_date);
                    if (empty($filter_release_date)) {
                        $filter_release_date = Time::filterYmdDate($release_date, '.');
                        if (!empty($filter_release_date)) {
                            $filter_release_date = str_replace('.', '-', $filter_release_date);
                        }
                    }
                    if (empty($filter_release_date)) {
                        $tmp_release_date = String::getHead($release_date, 7);
                        $filter_release_date = Time::filterYmDate($tmp_release_date);
                    }
                } elseif (strlen($release_date) === 8) {
                    $tmp_release_date = String::strToDate($release_date);
                    if ($tmp_release_date !== "1970-01-01") {
                        $filter_release_date = $tmp_release_date;
                    }
                } elseif (strlen($release_date) === 7) {
                    $filter_release_date = Time::filterYmDate($release_date);
                    if (empty($filter_release_date)) {
                        $tmp_release_date = (int)$release_date;
                        if (strlen($tmp_release_date) === 4) {
                            $filter_release_date = $tmp_release_date;
                        }
                    }
                } elseif (strlen($release_date) === 4) {
                    $filter_release_date = Time::filterYearDate($release_date);
                } else {
                    if (strlen($release_date) === 6 && is_numeric($release_date)) {
                        $filter_release_date = implode('-', String::explodeStrLen($release_date, 4));
                    } else {
                        $tmp_release_date = (int)$release_date;
                        if (strlen($tmp_release_date) === 4) {
                            $filter_release_date = $tmp_release_date;
                        } else {
                            Log::warning('개봉일 날짜 데이터 이상', $release_date);
                        }
                    }
                }

                if (empty($filter_release_date)) {
                    Log::warning('개봉일 파싱 실패', array('content_id' => $this->content_id, $date_data_list->html()));
                    continue;
                }

                $info_title_text = trim($info_title_text);

                if ($info_title_text === '개봉일' || $info_title_text === '개봉(예정)일') {
                    $data['release_date'] = $filter_release_date;
                } else {
                    throw new ParseException('개봉일 타이틀 이상', $info_title_text);
                }
            } else {
                $date_title_text = $date_title_tag->text();
                $this->_empty(compact('date_title_text'));
                $date_data_list->remove('em');
                $date_data_text = $date_data_list->text();
                $this->_empty(compact('date_data_text'));
                if ($this->_isEmptyValue($date_data_text)) {
                    continue;
                }
                if ($date_title_text === '제작연도') {
                    $data['making_year'] = substr($date_data_text, 0, 4);
                } elseif ($date_title_text === '제작상태') {
                    $data['making_status'] = $date_data_text;
                } else {
                    throw new ParserException('개봉일정보 태그 이상');
                }
            }
        }

        return $data;
    }

    /**
     * @param QpWrapper $info_data
     * @return null
     * @throws ParserException
     */
    protected function _extractNewReleaseDate($info_data)
    {
        $release_date_text = $info_data->text();

        if (empty($release_date_text) || $this->_isEmptyValue($release_date_text)) {
            Log::warning('재개봉일 정보 없음', array($release_date_text, $this->content_id));

            return null;
        }

        $release_date_list = Time::extractDateStrings($release_date_text);

        if (empty($release_date_list)) {
            if (strlen($release_date_text) === 7) {
                $release_date_list = (array)Time::filterYmDate($release_date_text);
            } elseif (strlen($release_date_text) === 4) {
                $release_date_list = (array)Time::filterYearDate($release_date_text);
            }
            if (empty($release_date_list)) {
                Log::warning('재개봉일 데이터 이상', array($release_date_text, $this->content_id));

                return null;
            }
        }

        if (count($release_date_list) > 1) {
            $highest_release_date = $release_date_list[0];
            foreach ($release_date_list as $release_date) {
                if ($highest_release_date < $release_date) {
                    $highest_release_date = $release_date;
                }
            }

            return array('re_release_date' => $highest_release_date);
        } else {
            return array('re_release_date' => $release_date_list[0]);
        }
    }

    /**
     * @param QpWrapper $info_data
     * @return array
     * @throws ParserException
     */
    protected function _extractCrank($info_data)
    {
        $data = array();
        foreach ($info_data->find('li') as $crank_list) {
            $this->_exists(compact('crank_list'));
            $crank_title_tag = $crank_list->find('em');
            if (!$crank_title_tag->exists()) {
                $crank_in_text = $crank_list->text();
                $this->_empty(compact('crank_in_text'));
                if ($this->_isEmptyValue($crank_in_text)) {
                    continue;
                }
                $crank_in_up = String::explodeTrim('~', $crank_in_text);
                if (count($crank_in_up) !== 2) {
                    throw new ParserException('크랭크인 업 텍스트 이상', $crank_in_text);
                }
                if (String::isDateString($crank_in_up[0]) === true) {
                    $data['crank_in'] = $crank_in_up[0];
                }
                if (String::isDateString($crank_in_up[1]) === true) {
                    $data['crank_up'] = $crank_in_up[1];
                }
            } else {
                $crank_title_text = $crank_title_tag->text();
                $this->_empty(compact('crank_title_text'));
                $crank_list->remove('em');
                $filming_cnt = $crank_list->text();
                $this->_empty(compact('filming_cnt'));
                if ($this->_isEmptyValue($filming_cnt)) {
                    continue;
                }
                if ($crank_title_text === '촬영회차') {
                    $data['filming_count'] = $filming_cnt;
                } else {
                    throw new ParserException('크랭크인 태그 이상');
                }
            }
        }

        return $data;
    }

    /**
     * @param QpWrapper $info_data
     * @return array
     */
    protected function _extractScreeningType($info_data)
    {
        $html = $info_data->html();
        $tmp_type_list = explode('<br/>', $html);

        $screening_type = null;
        if ($this->_isEmptyValue($this->_stripAllTagAndWhite($tmp_type_list[0]))) {
            return compact('screening_type');
        }

        $screening_type = array();
        foreach ($tmp_type_list as $type) {
            $type = $this->_stripAllTagAndWhite($type);
            $type = preg_replace('/\([0-9A-Z]+\)/', '', $type);
            $type_exploded = String::explodeTrim(':', $type);
            $type_group = $type_exploded[0];
            $contents_exploded = String::explodeTrim(',', $type_exploded[1]);
            foreach ($contents_exploded as $contents) {
                if (empty($screening_type[$type_group])) {
                    $screening_type[$type_group] = array();
                }
                $screening_type[$type_group][] = $contents;
            }
        }

        return compact('screening_type');
    }

    /**
     * @param QpWrapper $info_data
     * @return array
     */
    protected function _extractPrGenre($info_data)
    {
        $text = $info_data->text();

        $tmp_pr_genre_list = String::explodeTrim(',', $text);
        $pr_genre = array();
        foreach ($tmp_pr_genre_list as $genre) {
            $pr_genre[] = $genre;
        }

        return compact('pr_genre');
    }

    /**
     * @param QpWrapper $etc_info
     * @return array|null
     */
    protected function _extractSiteUrl($etc_info)
    {
        $site_url = array();
        foreach ($etc_info->find('a') as $a) {
            $this->_exists(compact('a'));
            $href = $a->attr('href');
            $this->_empty(compact('href'));
            $site_url[] = $href;
        }
        if (empty($site_url)) {
            return null;
        }

        return compact('site_url');
    }

    protected function _extractPoster($etc_info)
    {
        $poster = $this->_extractImageUrl($etc_info);

        if (empty($poster)) {
            return null;
        }

        return compact('poster');
    }

    /**
     * @param QpWrapper $etc_info
     * @param $content
     * @return array
     * @throws ParserException
     */
    protected function _extractSynopsis($etc_info, $content)
    {
        $p = $etc_info->find('p.contentBreak');
        $synopsis = $p->text();

        if (empty($synopsis)) {
            Log::info('시놉시스 데이터 비어있음', $this->content_id);

            return null;
        }
        $synopsis = String::stripAllWhiteSpaces(preg_replace('/\n\n/', '', $synopsis));
        //        preg_match_all('/<p class="contentBreak">\s*((.|\n)+)<\/p>/', $content, $matches);
        //
        //        if (empty($matches[1][0])) {
        //            throw new ParserException('시놉시스 데이터 이상', $etc_info->html());
        //        }
        //        $synopsis = preg_replace('/\n\n/', '', $matches[1][0]);

        return compact('synopsis');
    }

    /**
     * @param QpWrapper $etc_info
     * @return array
     * @throws ParserException
     */
    protected function _extractFilmCompany($etc_info)
    {
        $film_company = array();
        foreach ($etc_info->find('ul') as $ul) {
            $this->_exists(compact('ul'));
            $group = $ul->firstChild();
            $this->_exists(compact('group'));
            $group_text = $group->text();
            $this->_empty(compact('group_text'));
            $companies = $group->next();
            $this->_exists(compact('companies'));
            foreach ($companies->find('a') as $a) {
                $this->_exists(compact('a'));
                $company = $a->text();
                $this->_empty(compact('company'));
                if (empty($film_company[$group_text])) {
                    $film_company[$group_text] = array();
                }
                $film_company[$group_text][] = $company;
            }
        }

        return compact('film_company');
    }

    /**
     * @param QpWrapper $result_cnt_div
     * @return int
     * @throws ParserException
     */
    public function extractSearchResultCnt($result_cnt_div)
    {
        $em = $result_cnt_div->find('em');
        $this->_exists(compact('em'));
        $result_cnt_text = $em->text();
        $this->_empty(compact('result_cnt_text'));
        $result_cnt_text_split = explode(': ', $result_cnt_text);
        if (empty($result_cnt_text_split[1])) {
            throw new ParserException('검색결과 갯수 텍스트 이상', $result_cnt_text);
        }

        return (int)$result_cnt_text_split;
    }

    public function parseRealTimeBoxOffice($html, $search_time = null)
    {
        if (empty($html) === true) {
            throw new ParserException('파라미터 비었음', $html);
        }

        $table = QpWrapper::getInstance($html, '#mainTbody');

        $this->_exists(compact('table'));

        $result = array();
        foreach ($table->find('tr#tr_') as $tr) {
            $this->_exists(compact('tr'));
            $is_end = false;
            $data = array();
            foreach ($tr->find('td') as $i => $td) {
                $this->_exists(compact('td'));
                if ($i === 0) {
                    continue;
                }
                if ($i === 1) {
                    $data[self::$real_time_box_office_cols[$i]] = $this->extractContentId($td->find('a'));
                } else {
                    $value = $td->text();
                    $float = floatval(str_replace(',', '', $value));
                    if ($i === 2) {
                        if (Time::subMonths(3) > $value) {
                            break;
                        }
                        continue;
                    }
                    if ($i === 3 && $float <= 0) {
                        $is_end = true;
                        break;
                    }

                    $data[self::$real_time_box_office_cols[$i]] = $float;
                }
            }
            if ($is_end) {
                break;
            }
            if (count($data) === 6) {
                $result[] = $data;
            }
        }

        return $result;
    }

    public function parseBoxOfficeExcel($excel_path)
    {
        if (empty($excel_path)) {
            return null;
        }

        $data = Excel::readExcelToArray($excel_path, false, 'HTML');

        return $this->filterBoxOfficeExcel($data);
    }

    public function filterBoxOfficeExcel($data)
    {
        if (empty($data)) {
            return null;
        }

        $filter_data = array();
        unset($data[0]);
        unset($data[1]);
        foreach ($data as $col) {
            if (!is_numeric($col[0])) {
                break;
            }
            foreach ($col as &$value) {
                $value = utf8_decode($value);
            }
            $filter_data[] = $col;
        }

        return $filter_data;
    }
}
