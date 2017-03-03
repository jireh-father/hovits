<?php
namespace middleware\service\contents\sync;

use framework\library\File;
use framework\library\Log;
use framework\library\sql_builder\SqlBuilder;
use framework\library\Time;
use middleware\exception\SynchronizerException;
use middleware\model\Boxoffice;
use middleware\model\Genre;
use middleware\model\MakingCountry;
use middleware\model\MoviePeople;
use middleware\model\RealtimeBoxoffice;

class KoficMovieSync extends KoficSync
{
    public function __construct()
    {
        parent::__construct(CONTENT_TYPE_MOVIE);
    }

    public function syncMoviePeople($people_list, $movie_id, $is_force = false)
    {
        if (empty($movie_id)) {
            throw new SynchronizerException('파라미터 비어있음', $movie_id);
        }

        $model = MoviePeople::getInstance();

        if ($model->exist(compact('movie_id'))) {
            if (!$is_force) {
                return false;
            }
            $ret = $model->remove(compact('movie_id'));
            if ($ret === false) {
                throw new SynchronizerException('영화인 삭제 실패', $movie_id);
            }
        }

        if (!empty($people_list)) {
            foreach ($people_list as $people) {
                $people['movie_id'] = $movie_id;
                if ($model->add($people, false) === false) {
                    throw new SynchronizerException('영화인 추가 실패', $movie_id);
                }
            }
        }

        return true;
    }

    public function syncGenre($genre_list, $movie_id, $is_force = false)
    {
        if (empty($movie_id)) {
            throw new SynchronizerException('파라미터 비어있음', $movie_id);
        }

        $model = Genre::getInstance();

        if ($model->exist(compact('movie_id'))) {
            if (!$is_force) {
                return false;
            }
            $ret = $model->remove(compact('movie_id'));
            if ($ret === false) {
                throw new SynchronizerException('장르 삭제 실패', $movie_id);
            }
        }
        if (!empty($genre_list)) {
            foreach ($genre_list as $genre) {
                $genre_data = compact('movie_id', 'genre');
                if ($model->add($genre_data, false) === false) {
                    throw new SynchronizerException('장르 추가 실패', $movie_id);
                }
            }
        }

        return true;
    }

    public function syncMakingCountry($making_country, $movie_id, $is_force = false)
    {
        if (empty($movie_id)) {
            throw new SynchronizerException('파라미터 비어있음', $movie_id);
        }

        $model = MakingCountry::getInstance();

        if ($model->exist(compact('movie_id'))) {
            if (!$is_force) {
                return false;
            }
            $ret = $model->remove(compact('movie_id'));
            if ($ret === false) {
                throw new SynchronizerException('제작국가 삭제 실패', $movie_id);
            }
        }
        if (!empty($making_country)) {
            foreach ($making_country as $country) {
                $country_data = compact('movie_id', 'country');
                if ($model->add($country_data, false) === false) {
                    throw new SynchronizerException('제작국가 추가 실패', $movie_id);
                }
            }
        }

        return true;
    }

    public function syncRealTimeBoxOffice()
    {
        Log::info('실시간 박스오피스 동기화 시작');
        $search_time = Time::YmdHis();
        $html = $this->crawler->getRealTimeBoxOffice($search_time);
        if (empty($html) === true) {
            throw new SynchronizerException('실시간 박스오피스 검색실패', $search_time);
        }
        $data = $this->parser->parseRealTimeBoxOffice($html, $search_time);
        if (empty($data) === true) {
            throw new SynchronizerException('실시간 박스오피스 파싱실패', $search_time);
        }
        $box_office_model = RealtimeBoxoffice::getInstance();
        $box_office_model->begin();

        $ret = $box_office_model->addList($data, array('search_time' => $search_time));
        if ($ret === false) {
            throw new SynchronizerException('실시간 박스오피스 저장실패', array($data, $search_time));
        }

        $ret = $box_office_model->remove(array(SqlBuilder::expr('search_time', $search_time, '!=')));
        if ($ret === false) {
            $box_office_model->rollBack();
            throw new SynchronizerException('실시간 박스오피스 이전꺼 삭제 실패', array($search_time));
        }

        $box_office_model->commit();

        Log::info('실시간 박스오피스 동기화 완료');

        return true;
    }

    public function syncBoxOfficeList($is_force = false)
    {
        $path = $this->getBoxOfficeExcelDirPath();

        $dir_list = File::getDirList($path, true);
        Log::info('박스오피스 동기화 시작', array('dir cnt' => count($dir_list)));
        $cnt = 0;
        $model = Boxoffice::getInstance();
        $result = array(0, 0, 0);
        foreach ($dir_list as $dir) {
            $date_list = File::getFileList($dir);
            foreach ($date_list as $box_office_date) {
                $box_office_date = substr($box_office_date, 0, 10);
                try {
                    $cnt++;

                    if ($model->exist(array('boxoffice_date' => $box_office_date)) && !$is_force) {
                        Log::info('박스오피스  이미 동기화됨 통과', compact('box_office_date', 'cnt'));
                        $result[2]++;
                        continue;
                    }

                    if (strlen($box_office_date) !== 10) {
                        Log::error('박스오피스 Content 파일 이상', compact('box_office_date', 'cnt'));
                        $result[1]++;
                        continue;
                    }

                    $file_path = $this->getBoxOfficeExcelPath($box_office_date);
                    $box_office_list = $this->parser->parseBoxOfficeExcel($file_path);
                    if (empty($box_office_list)) {
                        Log::error('박스오피스 엑셀파일 파싱 실패', compact('box_office_date', 'cnt'));
                        $result[1]++;
                        continue;
                    }

                    foreach ($box_office_list as $box_office) {
                        $ret = $this->syncBoxOffice($box_office, $box_office_date);
                        if (!$ret) {
                            Log::error('박스오피스 동기화 실패(DB)', array('box_office_date' => $box_office_date, 'cnt' => $cnt, 'data' => $box_office));
                            $result[1]++;
                            break;
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('박스오피스 동기화 실패', array('box_office_date' => $box_office_date, 'cnt' => $cnt, $e));
                    $result[1]++;
                    continue;
                }

                $backup_file_path = $this->getBackupBoxOfficeExcelPath($box_office_date);
                File::move($file_path, $backup_file_path);
                Log::info('박스오피스 동기화 성공', compact('box_office_date', 'cnt'));
                $result[0]++;
            }
            if (count(File::getFileList($dir)) < 1) {
                File::removeDir($dir);
            }
        }

        Log::info('박스오피스 동기화 완료', compact('cnt'));

        return $result;
    }

    public function syncBoxOffice($box_office, $date)
    {
        if (empty($box_office) || empty($date)) {
            return false;
        }

        $model = Boxoffice::getInstance();

        return $model->set($box_office, array('boxoffice_date' => $date));
    }
}