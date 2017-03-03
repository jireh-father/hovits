<?php
namespace middleware\service\contents\parser;

use framework\library\Log;
use framework\library\sql_builder\SqlBuilder;
use framework\library\String;
use framework\library\Time;
use middleware\exception\CrawlerException;
use middleware\exception\ParserException;
use middleware\library\QpWrapper;
use middleware\model\CountryName;
use middleware\model\MovieLimitGrade;

class NaverMovieParser extends NaverParser
{
    const MOVIE_CNT_PER_PAGE = 12;

    protected $current_map_list = array();

    public static $grade_limit_map = array(
        '12세관람가' => '12세이상'
    );

    public static $people_name_map = array(
        '밀스'    => '밀즈',
        '알렉산더'  => '알렉산드르',
        '스코트'   => '스콧',
        '루시어'   => '루지어',
        '필립 로스' => '필립 J. 로스',
        '카린'    => '캐린',
        '덕 리만'  => '더그 라이만',
        '패티'    => '팻티'
    );

    public function __construct($crawler = null)
    {
        parent::__construct(CONTENT_TYPE_MOVIE, $crawler);
    }

    protected function _addTmpMatch($content_id, $is_match_value)
    {
        if (empty($content_id)) {
            return false;
        }

        $this->current_map_list[] = array($content_id, $is_match_value);

        return true;
    }

    public function parseMovieJson($search_json)
    {
        if (empty($search_json)) {
            return null;
        }

        if (is_array($search_json)) {
            $search_data = $search_json;
        } else {
            $search_data = json_decode($search_json, true);
        }
        if (empty($search_data)) {
            Log::error('json 데이터 이상', array($this->content_id, $search_json));

            return null;
        }

        $movie_list = array();
        foreach ($search_data as $item) {
            $item['title'] = strip_tags($item['title']);
            if ($item['director']) {
                $item['director'] = $this->parseMovieDirectors($item);
            }
            if ($item['actor']) {
                $item['actor'] = $this->parseMovieActors($item);
            }
            $movie_list[] = $item;
        }

        return $movie_list;
    }

    public function extractContentIdByLink($link)
    {
        $html = $this->crawler->getMoviePageByLink($link, $movie_tree);
        $movie_tree = QpWrapper::getInstance($movie_tree);
        $movie_tree->onAutoDecodeUtf8();

        $meta_url = $movie_tree->find('[property="og:url"]');
        if (!$meta_url->exists()) {
            Log::error('url 메타 데이터 없음', array($link, $html));

            return null;
        }
        $url = $meta_url->attr('content');
        if (empty($url)) {
            Log::error('url 메타 데이터 내용 없음', array($link, $html));

            return null;
        }

        $query = parse_url($url, PHP_URL_QUERY);
        if (empty($query)) {
            Log::error('url 쿼리 파싱 실패', array($link, $url));

            return null;
        }
        parse_str($query, $queries);

        if (empty($queries)) {
            Log::error('url 쿼리 스트링 파싱 실패', array($link, $query));

            return null;
        }

        $movie_tree->offAutoDecodeUtf8();

        if (empty($queries['code'])) {
            Log::error('code 쿼리 없음', array($link, $queries));

            return null;
        }

        return $queries['code'];
    }

    public function parseMovieDirectors($movie)
    {
        if (empty($movie['director'])) {
            return null;
        }

        return String::explodeTrim('|', $movie['director'], true);
    }

    public function parseMovieActors($movie)
    {
        if (empty($movie['actor'])) {
            return null;
        }

        return String::explodeTrim('|', $movie['actor'], true);
    }

    public function extractContentIdInSearch($search_tree, $content)
    {
        if (emptyOr($search_tree, $content)) {
            throw new CrawlerException('파라미터 에러', array($search_array, $content));
        }

        $this->current_map_list = array();

        $top_movie_element = $this->getSearchTopElement($search_tree);
        if (!empty($top_movie_element)) {
            $title = $this->extractTitleInMain($top_movie_element);
            $cgv_content_info = $this->extractContentInfoInMain($top_movie_element);
            $is_match_value = $this->_checkSameMovie($title, $cgv_content_info, $content);

            if ($is_match_value === true) {
                return $this->extractContentIdInMain($top_movie_element);
            } elseif ($is_match_value) {
                $this->_addTmpMatch($this->extractContentIdInMain($top_movie_element), $is_match_value);
            }
        }

        $movie_list_element = $this->getSearchListElement($search_tree);
        if (!empty($movie_list_element)) {
            $content_id_result = $this->searchContentIdInSearchList($movie_list_element, $content);
            if (empty($content_id_result)) {
                $search_cnt = $this->extractSearchListCnt($movie_list_element);
                if ($search_cnt > 4) {
                    $content_id_result = $this->searchContentIdInAllSearchList($content);
                    if (!empty($content_id_result)) {
                        return $content_id_result;
                    }
                }
            } else {
                return $content_id_result;
            }
        }

        return $this->_getBestMatch();
    }

    private function _getBestMatch()
    {
        $current_map_list = $this->current_map_list;
        if (empty($current_map_list)) {
            return null;
        }

        if (count($current_map_list) < 2) {
            return $current_map_list[0][0];
        }

        usort($current_map_list, array($this, 'cmpBestMatch'));

        return $current_map_list[0][0];
    }

    public function cmpBestMatch($a, $b)
    {
        if ($a[1] == $b[1]) {
            return 0;
        }

        return ($a[1] < $b[1]) ? -1 : 1;
    }

    public function searchContentIdInAllSearchList($content)
    {
        if (empty($content)) {
            return null;
        }

        Log::info('영화리스트 전체검색 시작', $content['movie_id']);

        $titles = $this->getTitles($content);

        $movie_search_html = $this->crawler->getMovieSearchPage($titles, $content['movie_id'], null, $movie_search_tree);
        if (empty($movie_search_html)) {
            Log::warning('영화리스트 검색 실패', array('page' => 1, $content['title'], $content['movie_id']));

            return null;
        }

        $movie_search_tree = QpWrapper::getInstance($movie_search_tree);
        $first_page_tree = $this->getSearchListElement($movie_search_tree);

        $content_id_result = $this->searchContentIdInSearchList($first_page_tree, $content, 4);
        if (!empty($content_id_result)) {
            Log::info('영화리스트 페이징에서 검색 완료', array('page' => 1, $content['title'], $content['movie_id']));

            return $content_id_result;
        }

        $cnt = $this->extractSearchListCnt($first_page_tree);
        if (empty($cnt)) {
            Log::warning('검색건수 에러', array($content['movie_id'], $first_page_tree->html()));

            return null;
        }

        Log::info('영화리스트 전체검색 1페이지 검색 실패', array('total' => $cnt));

        if ($cnt <= self::MOVIE_CNT_PER_PAGE) {
            return null;
        }

        $total_page = (int)(($cnt / self::MOVIE_CNT_PER_PAGE) + ($cnt % self::MOVIE_CNT_PER_PAGE > 0 ? 1 : 0));

        for ($i = 2; $i <= $total_page; $i++) {
            Log::info('영화리스트 페이징 검색 시작', array('cur_page' => $i, 'total_page' => $total_page));
            $titles = $this->getTitles($content);
            $movie_search_html = $this->crawler->getMovieSearchPage($titles, $content['movie_id'], $i, $movie_search_tree);
            if (empty($movie_search_html)) {
                Log::warning('영화리스트 검색 실패', array('page' => $i, $content['title'], $content['movie_id']));

                return null;
            }

            $movie_search_tree = QpWrapper::getInstance($movie_search_tree);
            $page_tree = $this->getSearchListElement($movie_search_tree);
            if (empty($page_tree)) {
                Log::warning('영화리스트 파싱 실패', array('page' => $i, $content['title'], $content['movie_id'], $movie_search_tree->html()));

                return null;
            }

            $content_id_result = $this->searchContentIdInSearchList($page_tree, $content);
            if (!empty($content_id_result)) {
                Log::info('영화리스트 페이징에서 검색 완료', array('page' => 1, $content['title'], $content['movie_id']));

                return $content_id_result;
            }
        }

        return null;
    }

    /**
     * @param QpWrapper $movie_list_element
     * @return bool|null|string
     * @throws ParserException
     */
    public function extractSearchListCnt($movie_list_element)
    {
        if (empty($movie_list_element) || !$movie_list_element->exists()) {
            return false;
        }
        $cnt = String::extractNumbers($movie_list_element->find('.h-area strong')->text());
        if (empty($cnt)) {
            throw new ParserException('검색건수 파싱 실패', $movie_list_element->html());
        }

        return $cnt;
    }

    /**
     * @param QpWrapper $movie_list_element
     * @return string
     */
    public function searchContentIdInSearchList($movie_list_element, $content, $ignore_index_to = null)
    {
        $ul = $movie_list_element->find('ul');
        $this->_exists(compact('ul'));

        foreach ($ul->find('li') as $i => $li) {
            if (!empty($ignore_index_to) && $i < $ignore_index_to) {
                if ($i < $ignore_index_to) {
                    continue;
                }
            }
            $this->_exists(compact('li'));
            $title = $this->extractTitleInSearchList($li);
            $release_date = $this->extractReleaseDateInSearchList($li);
            $cgv_content_id = $this->extractContentIdInSearchList($li);
            $is_match_value = $this->_checkSameMovie($title, compact('release_date', 'cgv_content_id'), $content, false);

            if ($is_match_value === true) {
                return $cgv_content_id;
            } elseif ($is_match_value) {
                $this->_addTmpMatch($cgv_content_id, $is_match_value);
            }
        }

        return null;
    }

    private function _checkSameMovie($title, $cgv_content, $content, $is_top_movie = true)
    {
        if ($is_top_movie) {
            $log_msg = '';
        } else {
            $log_msg = ' list';
        }

        $release_date = $cgv_content['release_date'];
        $kofic_filter_title = $this->crawler->stripTitle($content['title'], true);
        if (!empty($content['title_aka'])) {
            $kofic_filter_title_aka = $this->crawler->stripTitle($content['title_aka'], true);
        } else {
            $kofic_filter_title_aka = null;
        }
        $cgv_filter_title = $this->crawler->stripTitle($title, true);
        $is_same_title = false;
        $is_similar_title = false;
        $add_log_msg = '';

        if ($title === $content['title'] || $cgv_filter_title === $kofic_filter_title || $cgv_filter_title === $kofic_filter_title_aka) {
            $is_same_title = true;
            if (!empty($release_date)) {
                if (strlen($release_date) === 10) {
                    if ($release_date === $content['release_date']) {
                        Log::info('개봉일 제목 모두같음' . $log_msg, $content['movie_id']);

                        return true;
                    } elseif (!empty($content['re_release_date']) && $release_date === $content['re_release_date']) {
                        Log::warning('재개봉일과 동일함' . $log_msg, $content['movie_id']);

                        return true;
                    } else {
                        $diff_days = Time::diffDays($release_date, $content['release_date']);
                        if ($diff_days !== null && $diff_days <= 7) {
                            Log::warning('개봉일 7일내로 차이나는데 매칭했음' . $log_msg, $content['movie_id']);

                            return 5;
                        } elseif (!empty($content['re_release_date'])) {
                            $diff_days = Time::diffDays($release_date, $content['re_release_date']);
                            if ($diff_days !== null && $diff_days <= 7) {
                                Log::warning('개봉일과 kofic 재개봉일 7일내로 차이나는데 매칭했음' . $log_msg, $content['movie_id']);

                                return 6;
                            }
                        }
                    }
                } elseif (strlen($release_date) === 7 && $release_date === String::getHead($content['release_date'], 7)) {
                    Log::warning('개봉월까지만 같은데 매칭했음' . $log_msg, $content['movie_id']);

                    return 3;
                } elseif (strlen($release_date) === 4 && $release_date === String::getHead($content['release_date'], 4)) {
                    Log::warning('개봉년도만 같은데 매칭했음' . $log_msg, $content['movie_id']);

                    return 4;
                }
            }
        } elseif ((String::charLength($kofic_filter_title) > 1 && String::has($cgv_filter_title, $kofic_filter_title, 30))
            || (String::charLength($kofic_filter_title_aka) > 1 && String::has($cgv_filter_title, $kofic_filter_title_aka, 30)
                || (String::charLength($kofic_filter_title) > 1 && String::getSimilarPercent($cgv_filter_title, $kofic_filter_title) > 85)
                || (String::charLength($kofic_filter_title_aka) > 1 && String::getSimilarPercent($cgv_filter_title, $kofic_filter_title_aka) > 85))
        ) {
            $is_similar_title = true;
            $add_log_msg = ' has';
            if (!empty($release_date)) {
                if (strlen($release_date) === 10) {
                    if ($release_date === $content['release_date']) {
                        Log::info('개봉일 제목 모두같음' . $log_msg . $add_log_msg, $content['movie_id']);

                        return true;
                    } elseif (!empty($content['re_release_date']) && $release_date === $content['re_release_date']) {
                        Log::warning('재개봉일과 동일함' . $log_msg . $add_log_msg, $content['movie_id']);

                        return true;
                    } else {
                        $diff_days = Time::diffDays($release_date, $content['release_date']);
                        if ($diff_days !== null && $diff_days <= 4) {
                            Log::warning('개봉일 4일내로 차이나는데 매칭했음' . $log_msg . $add_log_msg, $content['movie_id']);

                            return 12;
                        } elseif (!empty($content['re_release_date'])) {
                            $diff_days = Time::diffDays($release_date, $content['re_release_date']);
                            if ($diff_days !== null && $diff_days <= 4) {
                                Log::warning('개봉일과 kofic 재개봉일 4일내로 차이나는데 매칭했음' . $log_msg . $add_log_msg, $content['movie_id']);

                                return 13;
                            }
                        }
                    }
                } elseif (strlen($release_date) === 7 && $release_date === String::getHead($content['release_date'], 7)) {
                    Log::warning('개봉월까지만 같은데 매칭했음' . $log_msg . $add_log_msg, $content['movie_id']);

                    return 11;
                }
            }
        }

        if ($is_same_title || $is_similar_title) {
            if ($is_top_movie) {
                //메인 영화목록이면 기타정보 비교해보기
                return $this->_checkSameMovieByEtcInfo($content, $cgv_content);
            } elseif (!$is_top_movie) {
                Log::info('영화 상세정보도 조사하기' . $add_log_msg, $content['movie_id']);
                $cgv_movie_content = $this->crawler->getContent($cgv_content['cgv_content_id'], $content_tree);
                if (!empty($cgv_movie_content)) {
                    $top_movie_element = $this->getSearchTopElement($content_tree);
                    if (!empty($top_movie_element)) {
                        $cgv_main_movie_content = $this->extractContentInfoInMain($top_movie_element);

                        return $this->_checkSameMovieByEtcInfo($content, $cgv_main_movie_content, $is_top_movie, false);
                    }
                }
            }
        }

        return false;
    }

    public function _checkSameMovieByEtcInfo($content, $cgv_content, $is_top_movie = true, $is_same_title = true)
    {
        if ($is_top_movie) {
            $log_msg = '';
        } else {
            $log_msg = ' list';
        }

        $is_different_people = false;

        // 감독 비교
        if (!empty($content['directors']) && !empty($cgv_content['directors'])) {
            $local_directors = json_decode($content['directors'], true);
            $cgv_directors = $cgv_content['directors'];
            foreach ($local_directors as $local_director) {
                foreach ($cgv_directors as $cgv_director) {
                    if (String::stripAllWhiteSpaces($local_director, '') === String::stripAllWhiteSpaces($cgv_director, '')) {
                        Log::warning('감독 동일해서 통과시킴' . $log_msg, $content['movie_id']);
                        if ($is_same_title) {
                            return 1;
                        } else {
                            return 9;
                        }
                    } else {
                        $cgv_director = str_replace(array_keys(self::$people_name_map), array_values(self::$people_name_map), $cgv_director);
                        if (String::stripAllWhiteSpaces($local_director, '') === String::stripAllWhiteSpaces($cgv_director, '')) {
                            Log::warning('감독 동일해서 통과시킴(이름조금 다름)' . $log_msg, $content['movie_id']);

                            if ($is_same_title) {
                                return 2;
                            } else {
                                return 10;
                            }
                        }
                    }
                }
            }
            $is_different_people = true;
            Log::warning('감독이름 다름' . $log_msg, $content['movie_id']);
        }

        // 배우
        // 이름 붙쳐서 비교
        if (!empty($content['lead_actors']) && !empty($cgv_content['actors'])) {
            $local_actors = json_decode($content['lead_actors'], true);
            $cgv_actors = $cgv_content['actors'];
            foreach ($local_actors as $local_actor) {
                foreach ($cgv_actors as $cgv_actor) {
                    if (String::stripAllWhiteSpaces($local_actor, '') === String::stripAllWhiteSpaces($cgv_actor, '')) {
                        Log::warning('배우 동일해서 통과시킴' . $log_msg, $content['movie_id']);

                        if ($is_same_title) {
                            return 7;
                        } else {
                            return 8;
                        }
                    } else {
                        $cgv_actor = str_replace(array_keys(self::$people_name_map), array_values(self::$people_name_map), $cgv_actor);
                        if (String::stripAllWhiteSpaces($local_actor, '') === String::stripAllWhiteSpaces($cgv_actor, '')) {
                            Log::warning('배우 동일해서 통과시킴(이름조금 다름)' . $log_msg, $content['movie_id']);

                            if ($is_same_title) {
                                return 14;
                            } else {
                                return 15;
                            }
                        }
                    }
                }
            }
            $is_different_people = true;
            Log::warning('배우이름 다름' . $log_msg, $content['movie_id']);
        }

        $has_etc = array(false, false, false);
        $same_etc = array(false, false, false);
        // 등급제한
        // 붙어있음 이미/포함되는지비교
        if (!empty($content['limit_grade']) && !empty($cgv_content['limit_grade'])) {
            $has_etc[0] = true;
            if (String::has($content['limit_grade'], $cgv_content['limit_grade'])) {
                $same_etc[0] = true;
            } elseif (self::$grade_limit_map[$content['limit_grade']] === $cgv_content['limit_grade']) {
                $same_etc[0] = true;
            }
        }

        //시간비교(비슷한정도까지?
        if (!empty($content['duration']) && !empty($cgv_content['duration'])) {
            $has_etc[1] = true;
            if ($content['duration'] === $cgv_content['duration']) {
                $same_etc[1] = true;
            } elseif ($is_same_title && !$is_different_people) {
                $gap = abs($content['duration'] - $cgv_content['duration']);
                if ($gap <= 10) {
                    Log::warning('10 분차이 나는데 같다고 처리함' . $log_msg, $content['movie_id']);
                    $same_etc[1] = true;
                }
            }
        }

        //국가 비교
        //하나라도 같으면?
        if (!empty($content['making_country']) && !empty($cgv_content['making_country'])) {
            $has_etc[2] = true;
            $local_countries = json_decode($content['making_country'], true);
            $cgv_countries = $cgv_content['making_country'];
            foreach ($local_countries as $local_country) {
                foreach ($cgv_countries as $cgv_country) {
                    if ($local_country === $cgv_country) {
                        $same_etc[2] = true;
                        break;
                    }
                }
                if ($same_etc[2]) {
                    break;
                }
            }
        }

        if (emptyAllArray($has_etc)) {
            return false;
        }

        foreach ($has_etc as $i => $value) {
            if ($value === true && $same_etc[$i] === false) {
                Log::warning('기타 데이터가 다름' . $log_msg, $content['movie_id']);

                return false;
            }
        }

        $cnt = 0;
        foreach ($has_etc as $value) {
            if ($value) {
                $cnt++;
            }
        }

        if ($is_different_people) {
            if ($cnt > 2) {
                Log::warning('감독 배우가 다르고 기타데이터가 3개이상 같아서 매칭시켜버림' . $log_msg, $content['movie_id']);

                if ($is_same_title) {
                    return 18;
                } else {
                    return 19;
                }
            }
        } else {
            if (!$is_same_title && $cnt > 1) {
                Log::warning('기타 데이터가 2개이상 같아서 매칭시켜버림' . $log_msg, $content['movie_id']);

                return 17;
            } elseif ($is_same_title) {
                Log::warning('기타 데이터가 같아서 매칭시켜버림' . $log_msg, $content['movie_id']);

                return 16;
            }
        }

        return false;
    }

    /**
     * @param QpWrapper $search_tree
     * @return QpWrapper
     */
    public function getSearchTopElement($search_tree)
    {
        $top_movie = $search_tree->find('.sect-base-movie');
        if (!$top_movie->exists()) {
            return null;
        }

        return $top_movie;
    }

    /**
     * @param QpWrapper $search_tree
     * @return QpWrapper
     */
    public function getSearchListElement($search_tree)
    {
        $movie_list = $search_tree->find('.sect-chart');
        if (!$movie_list->exists()) {
            return null;
        }

        return $movie_list;
    }

    /**
     * @param QpWrapper $top_movie_element
     * @return null
     * @throws ParserException
     */
    public function extractTitleInMain($top_movie_element)
    {
        if (empty($top_movie_element) || !$top_movie_element->exists()) {
            throw new ParserException('상단영화박스 없음');
        }

        $title_element = $top_movie_element->find('.box-contents .title strong');
        $this->_exists(compact('title_element'));

        $title = $title_element->text();
        $this->_empty(compact('title'));

        return $title;
    }

    /**
     * @param QpWrapper $top_movie_element
     * @return null
     * @throws ParserException
     */
    public function extractContentIdInMain($top_movie_element)
    {
        if (empty($top_movie_element) || !$top_movie_element->exists()) {
            throw new ParserException('상단영화박스 없음');
        }

        $img_element = $top_movie_element->find('.box-image img');
        $this->_exists(compact('img_element'));

        $src = $img_element->attr('src');
        if (empty($src)) {
            throw new ParserException('이미지 src 없음', $img_element->html());
        }

        $id = basename(dirname($src));
        if (empty($id) || preg_match('/^[0-9]{3,6}$/', $id) !== 1) {
            throw new ParserException('이미지 url에서 아이디 뽑아내기 실패', $src);
        }

        return $id;
    }

    /**
     * @param QpWrapper $dt
     * @param QpWrapper $dd
     * @return null
     * @throws ParserException
     * @internal param QpWrapper $top_movie_element
     */
    public function extractReleaseDateInMain($dt, $dd)
    {
        if (empty($dt) || !$dt->exists() || empty($dd) || !$dd->exists()) {
            throw new ParserException('기본정보 태그 객체 없음');
        }

        $data = $dd->text();
        if (empty($data) === true) {
            return null;
        }

        if (preg_match('/[0-9]{4}([.][0-9]{2}[.][0-9]{2})?/', $data) === 1) {
            if (strlen($data) > 10) {
                Log::info('개봉일 이상함', array($data, $this->content_id));
                $tmp_data = Time::extractDateStrings($data);
                if (!empty($tmp_data[0])) {
                    return array('release_date' => $tmp_data[0]);
                }
            } else {
                return array('release_date' => str_replace('.', '-', $data));
            }
        }

        return null;
    }

    /**
     * @param QpWrapper $dt
     * @param QpWrapper $dd
     * @return null
     * @throws ParserException
     * @internal param QpWrapper $top_movie_element
     */
    public function extractDirectorInMain($dt, $dd)
    {
        if (empty($dt) || !$dt->exists() || empty($dd) || !$dd->exists()) {
            throw new ParserException('기본정보 태그 객체 없음');
        }

        $directors = array();
        foreach ($dd->find('a') as $a) {
            $director = $a->text();
            if (!empty($director)) {
                $directors[] = trim($director);
            }
        }

        if (empty($directors)) {
            throw new ParserException('cgv 감독 파싱 실패', $dd->html());
        }

        return array('directors' => $directors);
    }

    /**
     * @param QpWrapper $dt
     * @param QpWrapper $dd
     * @return null
     * @throws ParserException
     * @internal param QpWrapper $top_movie_element
     */
    public function extractActorInMain($dt, $dd)
    {
        if (empty($dt) || !$dt->exists() || empty($dd) || !$dd->exists()) {
            throw new ParserException('기본정보 태그 객체 없음');
        }

        $actors = array();
        foreach ($dd->find('a') as $a) {
            $actor = $a->text();
            if (!empty($actor)) {
                $actors[] = trim($actor);
            }
        }

        if (empty($actors)) {
            throw new ParserException('cgv 배우 파싱 실패', $dd->html());
        }

        return array('actors' => $actors);
    }

    /**
     * @param QpWrapper $dt
     * @param QpWrapper $dd
     * @return null
     * @throws ParserException
     * @internal param QpWrapper $top_movie_element
     */
    public function extractEtcInfoInMain($dt, $dd)
    {
        if (empty($dt) || !$dt->exists() || empty($dd) || !$dd->exists()) {
            throw new ParserException('기본정보 태그 객체 없음');
        }

        $data = $dd->text();
        if (empty($data) === true) {
            return null;
        }
        $etc_info_list = String::explodeTrim(',', $data, true);
        $etc_info_result = array();
        $limit_model = MovieLimitGrade::getInstance();
        $country_model = CountryName::getInstance();
        foreach ($etc_info_list as $etc_info) {
            $etc_info = str_replace(array(chr(194), chr(160)), '', $etc_info);
            if ($limit_model->exist(array(SqlBuilder::wildcard('limit_grade', String::stripAllWhiteSpaces($etc_info, ''))))) {
                $etc_info_result['limit_grade'] = String::stripAllWhiteSpaces($etc_info, '');
                continue;
            }
            if (preg_match('/([0-9]{1,4})분/', $etc_info, $matches)) {
                if ($matches[1] > 0) {
                    $etc_info_result['duration'] = $matches[1];
                }
                continue;
            }
            $countries = explode('.', $etc_info);
            foreach ($countries as $country) {
                if ($country_model->exist(array('country_name' => String::stripAllWhiteSpaces($country, '')))) {
                    if (empty($etc_info_result['making_country'])) {
                        $etc_info_result['making_country'] = array();
                    }
                    $etc_info_result['making_country'][] = String::stripAllWhiteSpaces($country, '');
                }
            }
        }

        return $etc_info_result;
    }

    private static $content_info_handler = array(
        '감독' => 'extractDirectorInMain',
        '배우' => 'extractActorInMain',
        '기본' => 'extractEtcInfoInMain',
        '개봉' => 'extractReleaseDateInMain'
    );

    private static function _getContentInfoHandler($title)
    {
        foreach (self::$content_info_handler as $key => $handler) {
            if (String::has($title, $key)) {
                return $handler;
            }
        }

        return null;
    }

    /**
     * @param QpWrapper $top_movie_element
     * @return null
     * @throws ParserException
     */
    public function extractContentInfoInMain($top_movie_element)
    {
        if (empty($top_movie_element) || !$top_movie_element->exists()) {
            throw new ParserException('상단영화박스 없음');
        }

        $dl = $top_movie_element->find('.box-contents .spec dl');
        $this->_exists(compact('dl'));
        $content_info = array();
        foreach ($dl->find('dt') as $dt) {
            $title = $dt->text();
            if (empty($title) === true) {
                Log::warning('정보 타이틀 없음', $top_movie_element->html());
                continue;
            }

            $handler = $this->_getContentInfoHandler($title);
            if (empty($handler)) {
                continue;
            }

            $result = $this->$handler($dt, $dt->next());
            if (!empty($result)) {
                $content_info = array_merge($content_info, $result);
            }
        }

        return $content_info;
    }

    /**
     * @param QpWrapper $item
     * @return null
     * @throws ParserException
     */
    public function extractTitleInSearchList($item)
    {
        if (empty($item) || !$item->exists()) {
            throw new ParserException('리스트용 영화박스 없음');
        }

        $title_element = $item->find('.box-contents strong.title');
        $this->_exists(compact('title_element'));

        $title = $title_element->text();
        $this->_empty(compact('title'));

        return $title;
    }

    /**
     * @param QpWrapper $item
     * @return null
     * @throws ParserException
     */
    public function extractContentIdInSearchList($item)
    {
        if (empty($item) || !$item->exists()) {
            throw new ParserException('리스트용 영화박스 없음');
        }

        $a_element = $item->find('.box-contents a');
        $this->_exists(compact('a_element'));

        $href = $a_element->attr('href');
        if (empty($href)) {
            throw new ParserException('a태그 href 없음', $a_element->html());
        }

        $hrefs = explode('=', $href);
        if (empty($hrefs[1]) || preg_match('/^[0-9]{3,6}$/', $hrefs[1]) !== 1) {
            throw new ParserException('a태그 href에서 아이디 뽑아내기 실패', $href);
        }

        return $hrefs[1];
    }

    /**
     * @param QpWrapper $item
     * @return mixed
     * @throws ParserException
     */
    public function extractReleaseDateInSearchList($item)
    {
        if (empty($item) || !$item->exists()) {
            throw new ParserException('리스트용 영화박스 없음');
        }

        $date_element = $item->find('span.txt-info i');
        $this->_exists(compact('date_element'));
        $release_date = $date_element->text();
        if (empty($release_date)) {
            return null;
        }
        if (preg_match('/[0-9]{4}([.][0-9]{2}[.][0-9]{2})?/', $release_date) === 1) {
            return str_replace('.', '-', $release_date);
        }

        throw new ParserException('개봉일 정보 못찾음', $release_date);
    }

    /**
     * @param QpWrapper $score_box_tree
     * @return array
     * @throws ParserException
     */
    public function extractAvgRate($score_box_tree)
    {
        if (empty($score_box_tree) || !$score_box_tree->exists()) {
            Log::error('영화 평점 tree 없음');

            return null;
        }

        $audience_rate_point = $this->extractAudienceRatePoint($score_box_tree);
        $audience_rate_cnt = $this->extractAudienceRateCount($score_box_tree);
        $netizen_rate_point = $this->extractNetizenRatePoint($score_box_tree);
        $netizen_rate_cnt = $this->extractNetizenRateCount($score_box_tree);

        $total_point = ($audience_rate_point * $audience_rate_cnt) + ($netizen_rate_point * $netizen_rate_cnt);

        $total_cnt = $audience_rate_cnt + $netizen_rate_cnt;
        if ($total_cnt < 1) {
            return array(null, $total_cnt);
        }

        return array('point' => $total_point / $total_cnt, 'count' => $total_cnt);
    }

    /**
     * @param QpWrapper $score_box_tree
     * @return int
     */
    public function extractAudienceRatePoint($score_box_tree)
    {
        $em = $score_box_tree->find('#actualPointPersentBasic .star_score em');
        if (!$em->exists()) {
            return 0;
        }
        $point = $em->text();
        if (empty($point)) {
            return 0;
        }

        return (float)$point;
    }

    public function extractAudienceRateCount($score_box_tree)
    {
        $count = $score_box_tree->find('#actualPointCountBasic em')->text();

        return (int)str_replace(',', '', $count);
    }

    /**
     * @param QpWrapper $score_box_tree
     * @return float|int
     */
    public function extractNetizenRatePoint($score_box_tree)
    {
        $em = $score_box_tree->find('#pointNetizenPersentBasic em');
        if (!$em->exists()) {
            return 0;
        }
        $point = $em->text();
        if (empty($point)) {
            return 0;
        }

        return (float)$point;
    }

    public function extractNetizenRateCount($score_box_tree)
    {
        $count = $score_box_tree->find('#pointNetizenCountBasic em')->text();

        return (int)str_replace(',', '', $count);
    }

    /**
     * @param QpWrapper $content_tree
     * @return QpWrapper|null
     */
    public function extractInfoBox($content_tree)
    {
        if (empty($content_tree) || !$content_tree->exists()) {
            return null;
        }

        $info_box = $content_tree->find('.mv_info_area');
        if (empty($info_box) || !$info_box->exists()) {
            return null;
        }

        return $info_box;
    }

    /**
     * @param QpWrapper $info_box
     * @return null
     */
    public function extractReleaseDate($info_box)
    {
        $info_spec = $info_box->find('.info_spec');
        $info_spec->onAutoDecodeUtf8();
        if (empty($info_spec) || !$info_spec->exists()) {
            $info_spec->offAutoDecodeUtf8();

            return null;
        }

        foreach ($info_spec->find('dt') as $dt) {
            $this->_exists(compact('dt'));
            $title = $dt->text();

            if ($title !== '개요') {
                continue;
            }

            $dd = $dt->next();
            $this->_exists(compact('dd'));
            foreach ($dd->find('span') as $span) {
                $date = Time::extractDateString($span->text());
                if (empty($date)) {
                    continue;
                }
                $info_spec->offAutoDecodeUtf8();

                return $date;
            }

        }
        $info_spec->offAutoDecodeUtf8();

        return null;
    }
}