<?php
namespace middleware\service\contents\parser;

use framework\library\ArrayUtil;
use framework\library\Log;
use framework\library\String;
use framework\library\Time;
use middleware\exception\ParserException;
use middleware\library\QpWrapper;
use middleware\service\contents\crawler\KoficCrawler;
use middleware\service\contents\KoficContents;

abstract class KoficParser extends KoficContents
{
    const TITLE_KEY_MOVIE = 'title';
    const TITLE_ENG_KEY_MOVIE = 'title_eng';
    const TITLE_KEY_PEOPLE = 'people_name';
    const TITLE_ENG_KEY_PEOPLE = 'people_name_eng';

    private static $crawling_columns_movie = array(
        'movie_id',
        'title',
        'title_eng',
        'title_aka',
        'external_update_time',
        'movie_category',
        'movie_type',
        'genre',
        'duration',
        'limit_grade',
        'release_date',
        're_release_date',
        'making_year',
        'making_status',
        'making_country',
        'screening_type',
        'pr_genre',
        'crank_in',
        'crank_up',
        'filming_count',
        'synopsis',
        'site_url',
        'film_company',
        'is_screening'
    );

    private static $crawling_columns_people = array(
        'people_id',
        'people_name',
        'people_name_eng',
        'birth_date',
        'sex',
        'external_update_time',
        'people_aka',
        'birth_country',
        'main_job',
        'people_company',
        'biography'
    );

    private static $info_box_handler_movie = array(
        '코드'      => '_extractCode',
        'A.K.A'   => '_extractAka',
        '요약정보'    => '_extractInfo',
        '개봉일'     => '_extractDate',
        '개봉(예정)일' => '_extractDate',
        '크랭크인/업'  => '_extractCrank',
        '상영타입'    => '_extractScreeningType',
        '홍보용장르'   => '_extractPrGenre',
        '재개봉일'    => '_extractNewReleaseDate'
    );

    private static $info_box_handler_people = array(
        '코드'     => '_extractCode',
        'A.K.A'  => '_extractPeopleAka',
        '성별'     => '_extractSex',
        '분야'     => '_extractMainJob',
        '소속'     => '_extractPeopleCompany',
        '관련 URL' => 'ignore'
    );

    private static $etc_info_handler_movie = array(
        "관련사이트"     => '_extractSiteUrl',
        "포스터"       => '_extractPoster',
        "스틸컷"       => '_extractStillCut',
        "시놉시스"      => '_extractSynopsis',
        "영화사"       => '_extractFilmCompany',
        "원작 정보"     => 'ignore',
        "심의/기술정보"   => 'ignore',
        "영화제 출품정보"  => 'ignore',
        "진흥사업 지원정보" => 'ignore',
        "옴니버스 구성영화" => 'ignore',
        "수출정보"      => 'ignore',
        '등급분류/기술정보' => 'ignore'
    );

    private static $etc_info_handler_people = array(
        "바이오그래피" => '_extractBiography',
        "수상내역"   => 'ignore',
        "스틸컷"    => '_extractStillCut'
    );

    private static $content_json_columns = array(
        'screening_type',
        'pr_genre',
        'site_url',
        'film_company'
    );

    private static $content_table_list_movie = array('content', 'genre', 'making_country', 'image');

    private static $content_table_list_people = array('content', 'image');

    private $title_key;
    private $title_eng_key;
    private $info_box_handler;
    private $etc_info_handler;
    private $crawling_columns;
    private $content_table_list;
    protected $content_id;
    protected $crawler = null;

    /**
     * @param $content_type
     * @param KoficCrawler $crawler
     * @throws \middleware\exception\ContentsException
     */
    public function __construct($content_type, $crawler = null)
    {
        parent::__construct($content_type);

        $this->crawler = $crawler;

        if ($this->isMovieContent()) {
            $this->title_key = self::TITLE_KEY_MOVIE;
            $this->title_eng_key = self::TITLE_ENG_KEY_MOVIE;
            $this->info_box_handler = self::$info_box_handler_movie;
            $this->etc_info_handler = self::$etc_info_handler_movie;
            $this->crawling_columns = self::$crawling_columns_movie;
            $this->content_table_list = self::$content_table_list_movie;
        } else {
            $this->title_key = self::TITLE_KEY_PEOPLE;
            $this->title_eng_key = self::TITLE_ENG_KEY_PEOPLE;
            $this->info_box_handler = self::$info_box_handler_people;
            $this->etc_info_handler = self::$etc_info_handler_people;
            $this->crawling_columns = self::$crawling_columns_people;
            $this->content_table_list = self::$content_table_list_people;
        }
    }

    /**
     * @param $content_html
     * @param $content_id
     * @return array
     * movie: array('content', 'genre', 'making_country', 'image');
     * people: array('content', 'image');
     * @throws ParserException
     */
    public function parseContent($content_html, $content_id = null)
    {
        if (empty($content_html)) {
            throw new ParserException('Contents in the file is empty.');
        }

        $qp = QpWrapper::getInstance($content_html);
        if ($this->isPeopleContent()) {
            $qp->onAutoDecodeUtf8();
        } else {
            $qp->offAutoDecodeUtf8();
        }

        //제목, 배우명 추출
        extract($this->extractTitle($qp));

        //업데이트 날짜 추출
        $external_update_time = $this->extractUpdateDateByQp($qp);

        extract($this->parseBasicInfo($qp));

        $content_etc = $this->parseEtcInfo($qp, $content_html);
        extract($content_etc);

        $main_poster = $this->extractMainPoster($qp);

        $content_info = compact($this->crawling_columns);
        $content_info = $this->_filterContentInfo($content_info, $main_poster, $content_etc);

        //        $time = Time::YmdHisPlain();
        //        $detail_key = $time . '-' . md5($detail_file);
        //        FileCache::set($detail_key, $content_info);

        if ($this->isPeopleContent()) {
            $qp->offAutoDecodeUtf8();
        }

        foreach ($this->crawling_columns as $col) {
            if (!isset($content_info['content'][$col])) {
                $content_info['content'][$col] = null;
            }
        }

        return $content_info;
    }

    /**
     * @param QpWrapper $qp
     * @return array
     * @throws ParserException
     */
    public function extractTitle($qp)
    {
        $title_tag = $qp->find('.w80pB');
        $this->_exists(compact('title_tag'));
        if ($this->isMovieContent()) {
            $is_screening_tag = $title_tag->find('.run');
            if ($is_screening_tag->exists()) {
                $is_screening = true;
            } else {
                $is_screening = false;
            }
        }
        $first_child = $title_tag->firstChild();
        $this->_exists(compact('first_child'));
        if ($first_child->tag() !== 'strong') {
            throw new ParserException('제목 태그 구조 이상', $title_tag->text());
        }
        $title = $first_child->text();
        $this->_empty(compact('title'));
        $title_tag->remove('strong');
        if ($this->isMovieContent()) {
            $title_tag->remove('span');
        }
        $title_eng_text = $title_tag->text();

        $title_eng = null;
        if (!empty($title_eng_text)) {
            $title_eng = $title_eng_text;
            if ($title_eng[0] != '(' || $title_eng[strlen($title_eng) - 1] != ')') {
                throw new ParserException('영어제목 이상', $title_eng_text);
            }
            if ($this->isMovieContent()) {
                $title_eng = String::cutBothSide($title_eng, 1, 1);
            } else {
                $title_eng_list = String::explodeTrim(',', $title_eng);
                if (count($title_eng_list) === 2) {
                    $title_eng = String::cutHead($title_eng_list[0], 1);
                } else {
                    $title_eng = String::cutBothSide($title_eng_list[0], 1, 1);
                }
            }
        }

        if ($this->isMovieContent()) {
            return array($this->title_key => $title, $this->title_eng_key => $title_eng, 'is_screening' => $is_screening);
        } else {
            return array($this->title_key => $title, $this->title_eng_key => $title_eng);
        }
    }

    /**
     * @param QpWrapper $qp
     * @return array
     * @throws ParserException
     */
    public function parseBasicInfo($qp)
    {
        $basic_info = array();
        $info_list = $qp->find('.basicInfo dl');
        $this->_exists(compact('info_list'));
        $info_box_handler = $this->info_box_handler;

        foreach ($info_list->find('dt') as $info_title) {
            $this->_exists(compact('info_title'));
            $info_data = $info_title->next();
            $this->_exists(compact('info_data'));
            $info_title_text = $info_title->text();
            $this->_empty(compact('info_title_text'));

            if (empty($info_box_handler[$info_title_text])) {
                Log::warning('컨텐츠 정보박스 핸들러 없음', array($info_title_text, $this->content_id));
                continue;
            }
            $handler = $info_box_handler[$info_title_text];
            if ($handler === 'ignore') {
                continue;
            }
            $basic_info = ArrayUtil::mergeArray($basic_info, $this->$handler($info_data, $info_title_text));
        }

        return $basic_info;
    }

    /**
     * @param QpWrapper $info_data
     * @return array
     * @throws ParserException
     */
    protected function _extractCode($info_data)
    {
        $external_id = $info_data->text();
        $this->_empty(compact('external_id'));
        $data = array($this->getContentType() . '_id' => $external_id);

        ParserException::setContentId($external_id);
        $this->content_id = $external_id;

        return $data;
    }

    /**
     * @param QpWrapper $qp
     * @param $content
     * @throws ParserException
     * @return array|null
     */
    public function parseEtcInfo($qp, $content)
    {
        $detail_ele = $qp->find('section.detailInfo');
        $this->_exists(compact('detail_ele'));
        $etc_info_list = array();
        foreach ($detail_ele->find('h3') as $h3) {
            $this->_exists(compact('h3'));
            $h3->find('span')->remove();
            $title = $h3->text();
            $this->_empty(compact('title'));
            if (empty($this->etc_info_handler[$title])) {
                Log::warning('etc정보 핸들러 없음', array($title, $this->content_id));
                continue;
            }

            $handler = $this->etc_info_handler[$title];
            if ($handler === 'ignore') {
                continue;
            }
            $etc_info = $h3->next();
            $this->_exists(compact('etc_info'));
            $ret = $this->$handler($etc_info, $content);
            if (!empty($ret)) {
                $etc_info_list = ArrayUtil::mergeArray($ret, $etc_info_list);
            }
        }

        return $etc_info_list;
    }

    protected function _extractStillCut($etc_info)
    {
        $still_cut = $this->_extractImageUrl($etc_info, false);

        if (empty($still_cut)) {
            return null;
        }

        return compact('still_cut');
    }

    /**
     * @param QpWrapper $etc_info
     * @param bool $skip_first
     * @return array
     * @throws ParserException
     */
    protected function _extractImageUrl($etc_info, $skip_first = true)
    {
        $image_list = array();
        if ($this->isMovieContent()) {
            $is_first = true;
        }
        foreach ($etc_info->find('li') as $li) {
            if ($this->isMovieContent()) {
                if ($is_first === true && $skip_first === true) {
                    $is_first = false;
                    continue;
                }
            }
            $this->_exists(compact('li'));
            $img = $li->find('a img');
            $this->_exists(compact('img'));
            $src = $img->attr('orgsrc');
            $this->_empty(compact('src'));
            $image_list[] = $src;
        }

        return $image_list;
    }

    /**
     * @param QpWrapper $qp
     * @return string|null
     */
    public function extractMainPoster($qp)
    {
        $basic_info = $qp->find('article.basicInfo');
        $this->_exists(compact('basic_info'));
        $first_child = $basic_info->firstChild();
        $this->_exists(compact('first_child'));
        if ($first_child->tag() !== 'a') {
            return null;
        }

        $poster_uri = $first_child->attr('href');
        $this->_empty(compact('poster_uri'));

        return $poster_uri;
    }

    protected function _filterContentInfo($content_info, $main_poster, $content_etc)
    {
        if ($this->isMovieContent()) {
            //genre 처리
            $genre = null;
            if (!empty($content_info['genre'])) {
                $genre = $content_info['genre'];
                $content_info['genre'] = json_encode($content_info['genre']);
            }

            //making_country 처리
            $making_country = null;
            if (!empty($content_info['making_country'])) {
                $making_country = $content_info['making_country'];
                $content_info['making_country'] = json_encode($content_info['making_country']);
            }
        }

        //image 처리
        $image = array();

        if (!empty($main_poster)) {
            $image[] = array(
                'content_type' => strtolower($this->getContentType()),
                'image_type'   => 'main',
                'image_url'    => "http://www.kobis.or.kr{$main_poster}"
            );
        }

        if (!empty($content_etc['still_cut'])) {
            foreach ($content_etc['still_cut'] as $still_cut) {
                $image[] = array(
                    'content_type' => strtolower($this->getContentType()),
                    'image_type'   => 'still_cut',
                    'image_url'    => "http://www.kobis.or.kr{$still_cut}"
                );
            }
        }

        if ($this->isMovieContent()) {
            if (!empty($content_etc['poster'])) {
                foreach ($content_etc['poster'] as $poster) {
                    $image[] = array(
                        'content_type' => strtolower($this->getContentType()),
                        'image_type'   => 'poster',
                        'image_url'    => "http://www.kobis.or.kr{$poster}"
                    );
                }
            }
        }

        //insert_time 추가
        $content_info['insert_time'] = Time::YmdHis();

        if ($this->isMovieContent()) {
            foreach (self::$content_json_columns as $json_col) {
                if (!empty($content_info[$json_col])) {
                    $content_info[$json_col] = json_encode($content_info[$json_col]);
                }
            }
        }

        $content = $content_info;

        return compact($this->content_table_list);
    }

    public function extractContentIds($list_html, $page = null)
    {
        if (empty($list_html)) {
            throw new ParserException('list_html 값 비었음');
        }

        $qp = QpWrapper::getInstance($list_html, ".boardList03 tbody");
        $id_list = array();
        foreach ($qp->find('tr') as $tr) {
            $this->_exists(compact('tr'));
            if ($this->isPeopleContent()) {
                $a = $tr->find('.last-child[title] a');
                if (!$a->exists()) {
                    continue;
                }
            }
            $child = $tr->firstChild()->firstChild();
            $this->_exists(compact('child'));
            $id_list[] = $this->extractContentId($child);
        }

        return $id_list;
    }

    /**
     * @param QpWrapper $link_tag
     * @throws ParserException
     */
    public function extractContentId($link_tag)
    {
        $id_code = $link_tag->attr('onclick');
        $this->_empty(compact('id_code'));
        $ret = preg_match("/'([0-9a-zA-Z]{8})'/", $id_code, $matches);
        if ($ret !== 1) {
            throw new ParserException('아이디 추출 실패', compact('id_code', 'list_html'));
        }

        return $matches[1];
    }

    public function extractTotalCount($list_html, $page = null)
    {
        if (empty($list_html)) {
            throw new ParserException('List contents in the file is empty.');
        }

        $qp = QpWrapper::getInstance($list_html, 'div.board_top02 em');
        $this->_exists(compact('qp'));
        $total_text = $qp->text();
        $this->_empty(compact('total_text'));
        $ret = preg_match('/[0-9]+/', $total_text, $matches);
        if ($ret !== 1) {
            throw new ParserException('total count text is invalid', $total_text);
        }

        if ($matches[0] < 1) {
            throw new ParserException('total count text is invalid 2', $matches[0]);
        }

        return (int)$matches[0];
    }

    public function extractUpdateDate($contents, $content_id = null)
    {
        if (empty($contents)) {
            throw new ParserException('Contents in the file is empty.');
        }
        $qp = QpWrapper::getInstance($contents);

        return $this->extractUpdateDateByQp($qp);
    }

    /**
     * @param QpWrapper $qp
     * @return string
     * @throws ParserException
     */
    public function extractUpdateDateByQp($qp)
    {
        $date_ele = $qp->find('.f11');
        if (!$date_ele->exists()) {
            throw new ParserException('최종수정 날짜 엘리먼트 없음');
        }

        $date_code = trim($date_ele->text());

        if (empty($date_code)) {
            throw new ParserException('html에 최종수정 날짜 텍스트 없음');
        }
        $date_array = explode(' ', $date_code);

        if (empty($date_array[1]) || empty($date_array[2])) {
            throw new ParserException('html에 최종수정 날짜태그값 이상', $date_array);
        }

        $external_update_time = trim("{$date_array[1]} {$date_array[2]}");
        if (String::isDateTimeString($external_update_time) === false) {
            throw new ParserException('html에 최종수정 날짜태그값 빼내기 실패', $external_update_time);
        }

        return $external_update_time;
    }

    public function parseMovieActor($actor_list_json)
    {
        return $this->_parseMoviePeople($actor_list_json);
    }

    public function parseMovieStaff($staff_list_json)
    {
        return $this->_parseMoviePeople($staff_list_json, 'staff');
    }

    protected function _parseMoviePeople($people_list_json, $people_type = 'actor')
    {
        if (empty($people_list_json)) {
            return null;
        }

        $people_list = json_decode($people_list_json, true);
        if ($people_list === false) {
            throw new ParserException('people data couldn\'t be decoded from json to array', array('people_data' => $people_list));
        }

        if (empty($people_list)) {
            Log::info('people 데이터가 없음', $people_list_json);
        }

        $ret_people_list = array();
        foreach ($people_list as $people) {
            $filter_list = array();
            $filter_list['people_id'] = empty($people['peopleCd']) ? null : $people['peopleCd'];
            $filter_list['people_name'] = empty($people['peopleNm']) ? null : $people['peopleNm'];
            $filter_list['people_name_eng'] = empty($people['peopleNmEn']) ? null : $people['peopleNmEn'];
            $filter_list['people_type'] = $people_type;
            if ($people_type == 'actor') {
                if ($people['actorGb'] == 1) {
                    $filter_list['job'] = '주연';
                } elseif ($people['actorGb'] == 2) {
                    $filter_list['job'] = '조연';
                } elseif ($people['actorGb'] == 3) {
                    $filter_list['job'] = '단역';
                } else {
                    throw new ParserException('배우자 주연타입 데이터', array('actorGb' => $people['actorGb']));
                }

                if (empty($filter_list['people_name'])) {
                    $filter_list['people_name'] = empty($people['actorNm']) ? null : $people['actorNm'];
                }

                //배우
                if (empty($people['repRoleNm'])) {
                    $filter_list['job_group'] = null;
                } else {
                    $filter_list['job_group'] = $people['repRoleNm'];
                }
                //캐릭터
                if (empty($people['cast'])) {
                    $filter_list['role'] = null;
                } else {
                    $filter_list['role'] = $people['cast'];
                }

                if (empty($people['castEn'])) {
                    $filter_list['role_eng'] = null;
                } else {
                    $filter_list['role_eng'] = $people['castEn'];
                }

            } else {
                //제작(그룹)
                if (empty($people['roleGroupNm'])) {
                    $filter_list['job_group'] = null;
                } else {
                    $filter_list['job_group'] = $people['roleGroupNm'];
                }

                //프로듀서(상세)
                if (empty($people['roleNm'])) {
                    $filter_list['job'] = null;
                } else {
                    $filter_list['job'] = $people['roleNm'];
                }

                //팀장등?
                if (empty($people['detailRoleNm'])) {
                    $filter_list['role'] = null;
                } else {
                    $filter_list['role'] = $people['detailRoleNm'];
                }

            }
            $ret_people_list[] = $filter_list;
        }

        return $ret_people_list;
    }
}