<?php
namespace middleware\service\contents;

use framework\library\ArrayUtil;
use framework\library\File;
use framework\library\Log;
use framework\library\sql_builder\SqlBuilder;
use framework\library\String;
use framework\library\Time;
use middleware\exception\ContentsException;
use middleware\exception\CrawlerException;
use middleware\exception\ParserException;
use middleware\exception\SynchronizerException;
use middleware\library\QpWrapper;
use middleware\model\Boxoffice;
use middleware\model\ContentGrade;
use middleware\model\Genre;
use middleware\model\Image;
use middleware\model\Movie;
use middleware\model\MovieMatch;
use middleware\model\MovieMatchChoice;
use middleware\model\MovieMatchGrade;
use middleware\model\MoviePeople;
use middleware\model\MovieSimilarity;
use middleware\model\People;
use middleware\model\RealtimeBoxoffice;

abstract class Contents
{
    public static $content_provider_list = array(
        CONTENTS_PROVIDER_KOFIC,
        CONTENTS_PROVIDER_CGV,
        CONTENTS_PROVIDER_DAUM,
        CONTENTS_PROVIDER_IMDB,
        CONTENTS_PROVIDER_LOTTE,
        CONTENTS_PROVIDER_NAVER,
        CONTENTS_PROVIDER_WATCHA,
        CONTENTS_PROVIDER_HOVITS
    );

    private $content_type;
    private $content_vendor;
    const CONTENTS_CRAWLER = 'Crawler';
    const CONTENTS_PARSER = 'Parser';
    const CONTENTS_SYNC = 'Sync';
    const LIMIT_MINIMUM_GRADE_COUNT = 100;

    public function __construct($content_type, $content_vendor)
    {
        $base_class = baseClassName(get_called_class());

        if (strpos($base_class, self::CONTENTS_CRAWLER) !== false) {
            CrawlerException::setContentType($content_type);
            CrawlerException::setContentVendor($content_vendor);
        } elseif (strpos($base_class, self::CONTENTS_PARSER) !== false) {
            ParserException::setContentType($content_type);
            ParserException::setContentVendor($content_vendor);
        } elseif (strpos($base_class, self::CONTENTS_SYNC) !== false) {
            SynchronizerException::setContentType($content_type);
            SynchronizerException::setContentVendor($content_vendor);
        }

        if (empty($content_type) || empty($content_vendor) || ($content_type !== CONTENT_TYPE_MOVIE && $content_type !== CONTENT_TYPE_PEOPLE)) {
            throw new ContentsException('content_type, content_vendor 값이 없거나 이상한 값입니다.', array($content_type, $content_vendor));
        }

        $this->content_type = $content_type;
        $this->content_vendor = $content_vendor;
    }

    public function getCacheKey($key)
    {
        if (empty($key)) {
            return null;
        }

        return joins('_', $this->getContentVendor(), $this->getContentType(), $key);
    }

    public function getContentType()
    {
        return $this->content_type;
    }

    public function getContentVendor()
    {
        return $this->content_vendor;
    }

    public function isMovieContent()
    {
        return $this->content_type === CONTENT_TYPE_MOVIE;
    }

    public function isPeopleContent()
    {
        return $this->content_type === CONTENT_TYPE_PEOPLE;
    }

    public function getContentDir()
    {
        $content_type = $this->getContentType();
        $content_vendor = $this->getContentVendor();

        return PATH_CRAWLING . "/{$content_vendor}/{$content_type}";
    }

    public function getBackupContentDir()
    {
        $content_type = $this->getContentType();
        $content_vendor = $this->getContentVendor();

        return PATH_CRAWLING . "/{$content_vendor}/backup/{$content_type}";
    }

    public function getContentPath($content_id, $use_backup = false)
    {
        $content_type = $this->getContentType();
        $content_dir = substr($content_id, 0, 5);
        $content_vendor = $this->getContentVendor();

        $path = PATH_CRAWLING . "/{$content_vendor}/{$content_type}/{$content_dir}/{$content_id}";
        if ($use_backup === true) {
            if (!is_file($path)) {
                $path = $this->getBackupContentPath($content_id);
                if (!is_file($path)) {
                    $path = null;
                }
            }
        }

        return $path;
    }

    public function getBackupContentPath($content_id)
    {
        $content_type = $this->getContentType();
        $content_dir = substr($content_id, 0, 5);
        $content_vendor = $this->getContentVendor();

        return PATH_CRAWLING . "/{$content_vendor}/backup/{$content_type}/{$content_dir}/{$content_id}";
    }

    protected function _stripAllTagAndWhite($string)
    {
        return String::stripAllWhiteSpaces(strip_tags(str_replace('&#13;', '', $string)));
    }

    /**
     * @param QpWrapper[] $value
     * @throws ParserException
     */
    protected function _exists(array $value)
    {
        $msg = key($value);
        $ele = $value[$msg];

        if (!$ele->exists()) {
            throw new ParserException($msg . ' 태그 없음');
        }
    }

    /**
     * @param array $value
     * @throws ParserException
     */
    protected function _empty(array $value)
    {
        $msg = key($value);
        $text = $value[$msg];

        if (empty($text)) {
            throw new ParserException($msg . ' 빈 값');
        }
    }

    protected function _isEmptyValue($value)
    {
        $empty_value_list = array('해당정보 없음', '기타');

        return in_array($value, $empty_value_list);
    }

    protected function _sleep($num1 = null, $num2 = null)
    {
        if (!empty($num1) && !empty($num2)) {
            $sleep_time = rand($num1, $num2) * 1000;
        } else {
            $sleep_time = rand(25, 50) * 1000;
        }
        usleep($sleep_time);
    }

    public function getSearchPageDir()
    {
        $content_type = $this->getContentType();
        $content_vendor = $this->getContentVendor();

        return PATH_CRAWLING . "/{$content_vendor}/search/{$content_type}";
    }

    public function getSearchPagePath($content_id)
    {
        $content_type = $this->getContentType();
        $content_vendor = $this->getContentVendor();
        $content_dir = substr($content_id, 0, 5);

        return PATH_CRAWLING . "/{$content_vendor}/search/{$content_type}/{$content_dir}/{$content_id}";
    }

    public function getBackupSearchPagePath($content_id)
    {
        $content_type = $this->getContentType();
        $content_vendor = $this->getContentVendor();
        $content_dir = substr($content_id, 0, 5);

        return PATH_CRAWLING . "/{$content_vendor}/backup/search/{$content_type}/{$content_dir}/{$content_id}";
    }

    public function disableMovieMapping($movie)
    {
        $content_id = $movie['movie_id'];
        $is_disabled = false;
        if (!empty($movie['release_date']) && !empty($movie['re_release_date'])) {
            //개봉일 재개봉일 둘다 있음
            if (strlen($movie['release_date']) === 4 && $movie['release_date'] < Time::getDate('Y') &&
                strlen($movie['re_release_date']) === 4 && $movie['re_release_date'] < Time::getDate('Y')
            ) {
                Log::warning('개봉일 재개봉일 모두 개봉년만 있어서 개봉년보다 낮은거 disable', $content_id);
                $is_disabled = true;
            } elseif (strlen($movie['release_date']) >= 7 && $movie['release_date'] < Time::subDays(2) && $movie['re_release_date'] < Time::subDays(2)) {
                Log::warning('개봉일 재개봉일 모두 2일전꺼 disable', $content_id);
                $is_disabled = true;
            }
        } elseif (!empty($movie['release_date']) && empty($movie['re_release_date'])) {
            //개봉일만 있음
            if (strlen($movie['release_date']) >= 7 && $movie['release_date'] < Time::subDays(2)) {
                Log::warning('개봉일 2일전꺼 disable', $content_id);
                $is_disabled = true;
            } elseif (strlen($movie['release_date']) === 4 && $movie['release_date'] < Time::getDate('Y')
            ) {
                Log::warning('개봉일 개봉년만 있어서 개봉년보다 낮은거 disable', $content_id);
                $is_disabled = true;
            }
        } elseif (empty($movie['release_date']) && !empty($movie['re_release_date'])) {
            //재개봉일만 있음
            if (strlen($movie['re_release_date']) >= 7 && $movie['re_release_date'] < Time::subDays(2)) {
                Log::warning('재개봉일 2일전꺼 disable', $content_id);
                $is_disabled = true;
            } elseif (strlen($movie['re_release_date']) === 4 && $movie['re_release_date'] < Time::getDate('Y')
            ) {
                Log::warning('재개봉일 개봉년만 있어서 개봉년보다 낮은거 disable', $content_id);
                $is_disabled = true;
            }
        } else {
            //개봉일 재개봉일 모두 없음
            if (!empty($movie['making_year']) && $movie['making_year'] <= Time::getDate('Y') - 2) {
                Log::warning('개봉일 재개봉일 모두 없어서 제작년보다 2년이하면 disable', $content_id);
                $is_disabled = true;
            }
            if (empty($movie['making_year']) && !String::has($movie['movie_id'], Time::getDate('Y'))) {
                Log::warning('개봉일 재개봉일 제작년도 모두 없는데 영화코드가 올해의 영화가 아니라서 disable', $content_id);
                $is_disabled = true;
            }
        }

        if ($is_disabled) {
            $this->disableMovieVendorMapping($content_id);
        } else {
            Log::info('disabled 안됨', array($content_id, $this->getContentVendor()));

            return false;
        }

        return true;
    }

    public function disableMovieMappingBySync($content)
    {
        $is_disabled = false;
        $content_id = $content['movie_id'];

        if (!empty($content['release_date']) && !empty($content['re_release_date'])) {
            //개봉일 재개봉일 둘다 있음
            if (strlen($content['release_date']) === 4 && $content['release_date'] < Time::getDate('Y') &&
                strlen($content['re_release_date']) === 4 && $content['re_release_date'] < Time::getDate('Y')
            ) {
                Log::warning('개봉일 재개봉일 모두 개봉년만 있어서 개봉년보다 낮은거 disable(sync)', $content_id);
                $is_disabled = true;
            } elseif (strlen($content['release_date']) >= 7 && $content['release_date'] < Time::subDays(2) && $content['re_release_date'] < Time::subDays(2)) {
                Log::warning('개봉일 재개봉일 모두 2일전꺼 disable(sync)', $content_id);
                $is_disabled = true;
            }
        } elseif (!empty($content['release_date']) && empty($content['re_release_date'])) {
            //개봉일만 있음
            if (strlen($content['release_date']) >= 7 && $content['release_date'] < Time::subDays(2)) {
                Log::warning('개봉일 2일전꺼 disable(sync)', $content_id);
                $is_disabled = true;
            } elseif (strlen($content['release_date']) === 4 && $content['release_date'] < Time::getDate('Y')
            ) {
                Log::warning('개봉일 개봉년만 있어서 개봉년보다 낮은거 disable(sync)', $content_id);
                $is_disabled = true;
            }
        } elseif (empty($content['release_date']) && !empty($content['re_release_date'])) {
            //재개봉일만 있음
            if (strlen($content['re_release_date']) >= 7 && $content['re_release_date'] < Time::subDays(2)) {
                Log::warning('재개봉일 2일전꺼 disable(sync)', $content_id);
                $is_disabled = true;
            } elseif (strlen($content['re_release_date']) === 4 && $content['re_release_date'] < Time::getDate('Y')
            ) {
                Log::warning('재개봉일 개봉년만 있어서 개봉년보다 낮은거 disable(sync)', $content_id);
                $is_disabled = true;
            }
        } else {
            //개봉일 재개봉일 모두 없음
            if (!empty($content['making_year']) && $content['making_year'] <= Time::getDate('Y') - 2) {
                Log::warning('개봉일 재개봉일 모두 없어서 제작년보다 2년이하면 disable(sync)', $content_id);
                $is_disabled = true;
            }
        }

        if ($is_disabled) {
            $this->disableMovieVendorMapping($content_id);
        } else {
            Log::info('disabled 안됨(sync)', array($content_id, $this->getContentVendor()));
        }

        return $is_disabled;
    }

    public function disableMovieVendorMapping($content_id)
    {
        $movie_model = Movie::getInstance();
        $content_provider = $this->getContentVendor();
        if (empty($content_provider)) {
            Log::error('content vendor 없음');

            return false;
        }
        $ret = $movie_model->modify(array($content_provider . '_disabled' => true), array('movie_id' => $content_id));
        Log::info('검색 실패해서 disabled 세팅', array($content_id, $content_provider));
        if ($ret === false) {
            Log::error('disabled 상태 업데이트 실패', array($content_id, $content_provider));

            return false;
        }

        return true;
    }

    public function syncCrawledContentIds($is_force = true)
    {
        $vendor = $this->getContentVendor();
        $path = $this->getSearchPageDir();
        $vendor_id_key = $vendor . '_id';
        $vendor_disabled_key = $vendor . '_disabled';
        $dir_list = File::getDirList($path, true);
        Log::info($vendor . '동기화 시작', array('dir cnt' => count($dir_list), 'vendor' => $vendor));
        $cnt = 0;
        if ($this->isMovieContent()) {
            $content_model = Movie::getInstance();
        } else {
            $content_model = People::getInstance();
        }
        foreach ($dir_list as $dir) {
            $content_id_list = File::getFileList($dir);
            foreach ($content_id_list as $content_id) {
                try {
                    $cnt++;

                    if ($this->isMovieContent()) {
                        $content_exist_where = array('movie_id' => $content_id);
                    } else {
                        $content_exist_where = array('people_id' => $content_id);
                    }

                    $content_exist_where[] = SqlBuilder::orWhere(array(SqlBuilder::isNotNull($vendor_id_key), $vendor_disabled_key => true));

                    $file_path = $this->getSearchPagePath($content_id);

                    if ($content_model->exist($content_exist_where) && !$is_force) {
                        Log::info('이미 동기화됨 통과', compact('content_id', 'cnt', 'vendor'));
                        $this->moveSearchPageToBackup($file_path, $content_id);
                        continue;
                    }

                    if (strlen($content_id) !== 8) {
                        Log::error('Content 파일 이상', compact('content_id', 'cnt', 'vendor'));
                        $this->moveSearchPageToBackup($file_path, $content_id);
                        continue;
                    }

                    $search_result = File::getFileContents($file_path);
                    if (empty($search_result) === true) {
                        Log::error('Content 내용물 없음', compact('content_id', 'cnt', 'vendor'));
                        $this->moveSearchPageToBackup($file_path, $content_id);
                        continue;
                    }

                    if ($this->isMovieContent()) {
                        $content_where = array('movie_id' => $content_id);
                    } else {
                        $content_where = array('people_id' => $content_id);
                    }
                    $content = $content_model->getRow($content_where);
                    if (empty($content)) {
                        Log::error('Content DB 검색 실패', compact('content_id', 'cnt', 'vendor'));
                        continue;
                    }

                    $ret = $this->syncContentId($search_result, $content, $is_force);

                    if ($ret === false) {
                        $this->disableMovieMappingBySync($content);

                        Log::error('syncContentId 실패', compact('content_id', 'cnt', 'vendor'));
                    }
                } catch (\Exception $e) {
                    Log::error('동기화 실패', array('content_id' => $content_id, 'cnt' => $cnt, $e));
                    continue;
                }

                $this->moveSearchPageToBackup($file_path, $content_id);
                Log::info('동기화 성공', compact('content_id', 'cnt', 'vendor'));
            }
            if (count(File::getFileList($dir)) < 1) {
                File::removeDir($dir);
            }
        }
        Log::info($vendor . ' 동기화 완료', compact('cnt', 'vendor'));
    }

    public function moveSearchPageToBackup($file_path, $content_id)
    {
        $backup_file_path = $this->getBackupSearchPagePath($content_id);
        File::move($file_path, $backup_file_path);
    }

    public function syncBoxofficeRate()
    {
        $movie_join = array(
            SqlBuilder::subQuery('realtime_boxoffice', 'realtime_boxoffice'),
            SqlBuilder::join('movie', 'realtime_boxoffice.movie_id = movie.movie_id')
        );

        $box_office_model = RealtimeBoxoffice::getInstance();

        $movies = $box_office_model->getList(array(SqlBuilder::isNotNull($this->getContentVendor() . '_id')), null, null, $movie_join);

        if (empty($movies)) {
            Log::error('박스오피스 영화 데이터 없음');
        }

        $results = array(0, 0);
        foreach ($movies as $movie) {
            $ret = $this->syncMovieRate($movie['movie_id'], false);
            if ($ret) {
                $results[0]++;
            } else {
                $results[1]++;
            }
        }

        return $results;
    }

    public function syncAllMovieRate($update_history = true)
    {
        $movie_model = Movie::getInstance();
        $vendor = $this->content_vendor;
        $movies = $movie_model->getList(array(SqlBuilder::isNotNull($vendor . '_id')));

        if (empty($movies)) {
            Log::error($vendor . ' id 있는 영화 데이터 없음');
        }

        Log::info($vendor . ' 영화 평점 동기화 시작', count($movies));

        $results = array(0, 0);
        foreach ($movies as $movie) {
            $ret = $this->syncMovieRate($movie['movie_id'], $update_history);
            if ($ret) {
                $results[0]++;
            } else {
                $results[1]++;
            }
        }

        return $results;
    }

    private static function _getTotalGradePointExpr()
    {
        $expr = '(';
        $expr_list = array();
        foreach (Contents::$content_provider_list as $provider) {
            if ($provider === CONTENTS_PROVIDER_KOFIC) {
                continue;
            }
            $expr_list[] = "({$provider}_grade_point * {$provider}_grade_count)";
        }
        $expr .= implode(' + ', $expr_list);
        $expr .= ')';

        return $expr;
    }

    private static function _getTotalGradeCountExpr()
    {
        $expr = '(';
        $expr_list = array();
        foreach (Contents::$content_provider_list as $provider) {
            if ($provider === CONTENTS_PROVIDER_KOFIC) {
                continue;
            }
            $expr_list[] = "{$provider}_grade_count";
        }
        $expr .= implode(' + ', $expr_list);
        $expr .= ')';

        return $expr;
    }

    public static function getMovieData($movie_ids, $data_type_flag = FLAG_CONTENT_DEFAULT_MOVIE)
    {
        if (empty($movie_ids) || !is_array($movie_ids) || empty($data_type_flag) || $data_type_flag < 1) {
            return null;
        }

        $movie_ids = ArrayUtil::toStringElements($movie_ids);
        $movie_data = array();

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_MOVIE_MATCH)) {
            $movie_match1 = MovieMatch::getInstance()->getMultiMap(
                'movie_id',
                array(
                    SqlBuilder::in($movie_ids, 'movie_id1')
                ),
                null,
                null,
                null,
                'movie_match.*, movie_id1 as movie_id, movie_id2 as other_movie_id'
            );

            $movie_match2 = MovieMatch::getInstance()->getMultiMap(
                'movie_id',
                array(
                    SqlBuilder::in($movie_ids, 'movie_id2')
                ),
                null,
                null,
                null,
                'movie_match.*, movie_id1 as other_movie_id, movie_id2 as movie_id'
            );
            $movie_data['movie_matches'] = ArrayUtil::mergeAssocArrayRecur($movie_match1, $movie_match2);


        }

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_MOVIE_MATCH_CHOICE)) {
            $movie_match_choice1 = MovieMatchChoice::getInstance()->getMultiMap(
                'movie_id',
                array(
                    SqlBuilder::in($movie_ids, 'movie_id1')
                ),
                null,
                null,
                null,
                'movie_match_choice.*, movie_id1 as movie_id, movie_id2 as other_movie_id'
            );

            $movie_match_choice2 = MovieMatchChoice::getInstance()->getMultiMap(
                'movie_id',
                array(
                    SqlBuilder::in($movie_ids, 'movie_id2')
                ),
                null,
                null,
                null,
                'movie_match_choice.*, movie_id1 as other_movie_id, movie_id2 as movie_id'
            );
            $movie_data['movie_match_choices'] = ArrayUtil::mergeAssocArrayRecur($movie_match_choice1, $movie_match_choice2);
        }

        if (!empty($movie_data['movie_matches']) || !empty($movie_data['movie_match_choices'])) {
            $other_match_ids1 = self::_extractOtherMovieIds($movie_data['movie_matches']);
            $other_match_ids2 = self::_extractOtherMovieIds($movie_data['movie_match_choices']);
            $movie_ids = ArrayUtil::mergeArray($movie_ids, $other_match_ids1, $other_match_ids2);
        }

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_MOVIE)) {
            //영화
            $movie_model = Movie::getInstance();

            $limit_minimum_grade_count = self::LIMIT_MINIMUM_GRADE_COUNT;
            $total_grade_point_expr = self::_getTotalGradePointExpr();
            $total_grade_count_expr = self::_getTotalGradeCountExpr();
            $movie_cols = array(
                'movie.*',
                'total_grade_point'      => $total_grade_point_expr,
                'total_grade_count'      => $total_grade_count_expr,
                'avg_grade_point'        => "{$total_grade_point_expr} / {$total_grade_count_expr}",
                'avg_grade_point_filter' => "CASE WHEN {$total_grade_count_expr} > {$limit_minimum_grade_count} THEN {$total_grade_point_expr} / {$total_grade_count_expr} ELSE 0 END"
            );
            $movie_model->setSelectColumns($movie_cols);
            $movie_data['movies'] = $movie_model->getMap('movie_id', array(SqlBuilder::in($movie_ids, 'movie_id')));
        }

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_THUMB)) {
            //썸네일
            $movie_data['thumbs'] = Image::getInstance()->getMap(
                'content_id',
                array(
                    'content_type' => CONTENT_TYPE_MOVIE,
                    'image_type'   => 'main',
                    SqlBuilder::in($movie_ids, 'content_id')
                )
            );
        }

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_STILL_CUT)) {
            //스티컬이미지
            $movie_data['still_cuts'] = Image::getInstance()->getMultiMap(
                'content_id',
                array(
                    'content_type' => CONTENT_TYPE_MOVIE,
                    'image_type'   => 'still_cut',
                    SqlBuilder::in($movie_ids, 'content_id')
                )
            );
        }

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_BOX_OFFICE)) {
            $movie_data['box_offices'] = Boxoffice::getInstance()->getMap('movie_id', array(SqlBuilder::in($movie_ids, 'movie_id')));
        }

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_REAL_TIME_BOX_OFFICE)) {
            $real_boxoffice_model = RealtimeBoxoffice::getInstance();
            $movie_join_stmt = SqlBuilder::join('movie', "realtime_boxoffice.movie_id = movie.movie_id");
            $movie_join = array('realtime_boxoffice', $movie_join_stmt);
            $real_boxoffice_model->setTable($movie_join);
            $movie_cols = array(
                'realtime_boxoffice.*',
                'avg_ticket_count_per_day' => 'total_ticket_count / DATEDIFF(current_date, release_date)'
            );
            $real_boxoffice_model->setSelectColumns($movie_cols);
            $movie_data['real_time_box_offices'] = $real_boxoffice_model->getMap('movie_id', array(SqlBuilder::in($movie_ids, 'realtime_boxoffice.movie_id')));
        }

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_GRADE)) {
            $movie_data['content_grades'] = ContentGrade::getInstance()->getMultiMap(
                'content_id',
                array(
                    'content_type' => CONTENT_TYPE_MOVIE,
                    'grade_type'   => 'user',
                    SqlBuilder::in($movie_ids, 'content_id')
                )
            );
        }

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_GENRE)) {
            $movie_data['genres'] = Genre::getInstance()->getMultiMapValues('movie_id', 'genre', array(SqlBuilder::in($movie_ids, 'movie_id')));
        }

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_ALL_IMAGE)) {
            $movie_data['images'] = Image::getInstance()->getMultiMap(
                'content_id',
                array(
                    'content_type' => CONTENT_TYPE_MOVIE,
                    SqlBuilder::in($movie_ids, 'content_id')
                )
            );
        }

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_MOVIE_MATCH_GRADE)) {
            $movie_data['movie_match_grades'] = MovieMatchGrade::getInstance()->getMultiMap(
                'movie_id',
                array(
                    SqlBuilder::in($movie_ids, 'movie_id')
                )
            );
        }

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_MOVIE_PEOPLE)) {
            $movie_data['movie_people'] = MoviePeople::getInstance()->getMultiMap(
                'movie_id',
                array(
                    SqlBuilder::in($movie_ids, 'movie_id')
                )
            );
        }

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_MOVIE_SIMILARITY)) {
            $movie_data['movie_similarities'] = MovieSimilarity::getInstance()->getMapValues(
                'movie_id',
                'similarity_json',
                array(
                    SqlBuilder::in($movie_ids, 'movie_id')
                )
            );
            foreach ($movie_data['movie_similarities'] as &$movie_similarity) {
                $movie_similarity = json_decode($movie_similarity, true);
            }
        }

        if (self::_isContentFlag($data_type_flag, FLAG_CONTENT_PEOPLE)) {

        }

        return $movie_data;
    }

    private static function _extractOtherMovieIds($matches)
    {
        if (empty($matches)) {
            return null;
        }
        $other_ids = array();
        foreach ($matches as $movie_id => $match) {
            $other_ids = ArrayUtil::mergeArray($other_ids, ArrayUtil::getArrayColumn($match, 'other_movie_id'));
        }

        return array_unique($other_ids);
    }

    private static function _isContentFlag($flag_value, $target_flag_value)
    {
        return ($flag_value & $target_flag_value) === $target_flag_value || $flag_value === FLAG_CONTENT_ALL;
    }
}