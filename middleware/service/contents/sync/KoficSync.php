<?php
namespace middleware\service\contents\sync;

use framework\base\Model;
use framework\library\File;
use framework\library\Log;
use middleware\exception\CrawlerException;
use middleware\exception\ParserException;
use middleware\exception\SynchronizerException;
use middleware\model\Image;
use middleware\model\Movie;
use middleware\model\People;
use middleware\service\contents\crawler\KoficCrawler;
use middleware\service\contents\crawler\KoficMovieCrawler;
use middleware\service\contents\crawler\KoficPeopleCrawler;
use middleware\service\contents\KoficContents;
use middleware\service\contents\parser\KoficParser;
use middleware\service\contents\thumb\ThumbMaker;

abstract class KoficSync extends KoficContents
{
    /**
     * @var KoficParser
     */
    protected $parser = null;

    /**
     * @var KoficCrawler
     */
    protected $crawler = null;

    public function __construct($content_type)
    {
        parent::__construct($content_type);

        if ($this->isMovieContent()) {
            $this->crawler = new KoficMovieCrawler();
        } else {
            $this->crawler = new KoficPeopleCrawler();
        }
        $this->parser = $this->crawler->parser;
    }

    public function syncUpdatedContents($is_force = false, $path = null)
    {
        if (empty($path)) {
            $path = $this->getContentDir();
            $use_backup = false;
        } else {
            $use_backup = true;
        }

        $dir_list = File::getDirList($path, true);
        Log::info('동기화 시작', array('dir cnt' => count($dir_list)));
        $cnt = 0;
        foreach ($dir_list as $dir) {
            $content_id_list = File::getFileList($dir);
            foreach ($content_id_list as $content_id) {
                try {
                    $cnt++;
                    $file_path = $this->getContentPath($content_id, $use_backup);
                    $content_html = File::getFileContents($file_path);
                    if (empty($content_html) === true) {
                        Log::error('Content 내용물 없음', compact('content_id', 'cnt'));
                        continue;
                    }

                    $actor_json = null;
                    $staff_json = null;
                    if ($this->isMovieContent()) {
                        $actor_path = $this->getMovieActorPath($content_id, $use_backup);
                        $staff_path = $this->getMovieStaffPath($content_id, $use_backup);
                        if (is_file($actor_path)) {
                            $actor_json = File::getFileContents($actor_path);
                            if (empty($actor_json)) {
                                $actor_json = null;
                            }
                        }
                        if (is_file($staff_path)) {
                            $staff_json = File::getFileContents($staff_path);
                            if (empty($staff_json)) {
                                $staff_json = null;
                            }
                        }
                    }

                    $ret = $this->syncContent($content_html, $actor_json, $staff_json, $is_force);
                    if ($ret === false || $ret === -1) {
                        continue;
                    }
                } catch (\Exception $e) {
                    Log::error('동기화 실패', array('content_id' => $content_id, 'cnt' => $cnt, $e));
                    continue;
                }

                if (!$use_backup) {
                    $backup_file_path = $this->getBackupContentPath($content_id);
                    File::move($file_path, $backup_file_path);
                    if ($this->isMovieContent()) {
                        $backup_actor_path = $this->getBackupMovieActorPath($content_id);
                        $backup_staff_path = $this->getBackupMovieStaffPath($content_id);
                        if (is_file($actor_path)) {
                            File::move($actor_path, $backup_actor_path);
                        }
                        if (is_file($staff_path)) {
                            File::move($staff_path, $backup_staff_path);
                        }
                    }
                }
                Log::info('동기화 성공', compact('content_id', 'cnt'));
            }
            if (!$use_backup) {
                if (count(File::getFileList($dir)) < 1) {
                    File::removeDir($dir);
                }
            }
        }
        Log::info('동기화 완료', compact('cnt'));
    }

    public function syncContentsDirect(array $content_id_list, $is_force = false)
    {
        if (empty($content_id_list)) {
            return false;
        }

        foreach ($content_id_list as $content_id) {
            $this->syncContentDirect($content_id, $is_force);
        }

        return true;
    }

    public function syncContentsDirectByPage($page, $is_force = false)
    {
        if (empty($page)) {
            return false;
        }

        try {
            $list_html = $this->crawler->getList($page);
        } catch (CrawlerException $e) {
            return false;
        }

        try {
            $content_ids = $this->parser->extractContentIds($list_html, $page);
        } catch (ParserException $e) {
            return false;
        }

        try {
            return $this->syncContentsDirect($content_ids, $is_force);
        } catch (SynchronizerException $e) {
            return false;
        }
    }

    public function syncContentsDirectByPages($page_list, $is_force = false)
    {
        if (empty($page_list) || !is_array($page_list)) {
            return false;
        }

        foreach ($page_list as $page) {
            $ret = $this->syncContentsDirectByPage($page, $is_force);
            if ($ret === false) {
                Log::error('페이지별 동기화 실패', compact('page'));
            }
        }

        return true;
    }

    private static $image_size_type = array(
        THUMB_KEY_FULL_SIZE,
        THUMB_KEY_BIG_SIZE,
        THUMB_KEY_MID_SIZE,
        THUMB_KEY_SMALL_SIZE,
        THUMB_KEY_HIGH_QUALITY,
        THUMB_KEY_MID_QUALITY,
        THUMB_KEY_LOW_QUALITY
    );

    public function syncImage($image_list, $content_id, $is_force = false)
    {
        if (empty($content_id)) {
            throw new SynchronizerException('파라미터 비어있음', $content_id);
        }

        $model = Image::getInstance();
        $where = array('content_id' => $content_id, 'content_type' => $this->getContentType());
        $old_image_list = $model->getList($where);
        if (!empty($old_image_list) && !empty($image_list) && $is_force === true) {
            if (!$is_force) {
                return false;
            }
            foreach ($old_image_list as $image) {
                foreach (self::$image_size_type as $image_path_var) {
                    if (!empty($image[$image_path_var])) {
                        $image_file_path = ThumbMaker::getImageDir($image['content_type'], $content_id) . '/' . $image[$image_path_var];
                        if (is_file($image_file_path)) {
                            unlink($image_file_path);
                        }
                    }
                }
            }

            $ret = $model->remove($where);
            if ($ret === false) {
                throw new SynchronizerException('이미지 삭제 실패', $where);
            }

            if (!empty($image_list)) {
                foreach ($image_list as $image) {
                    $image['content_id'] = $content_id;

                    if ($model->add($image, false) === false) {
                        throw new SynchronizerException('이미지 추가 실패', $image);
                    }
                }
            }
        } elseif (!empty($old_image_list) && !empty($image_list) && !$is_force) {
            foreach ($image_list as $image) {
                $image['content_id'] = $content_id;

                if (!$model->exist($image)) {
                    if ($model->add($image, false) === false) {
                        throw new SynchronizerException('이미지 추가 실패', $image);
                    }
                }
            }
        } elseif (empty($old_image_list)) {
            if (!empty($image_list)) {
                foreach ($image_list as $image) {
                    $image['content_id'] = $content_id;

                    if ($model->add($image, false) === false) {
                        throw new SynchronizerException('이미지 추가 실패', $image);
                    }
                }
            }
        }

        return true;
    }

    protected function _syncContent($content, $is_force = false)
    {
        if ($this->isMovieContent()) {
            $model = Movie::getInstance();
            $where = array('movie_id' => $content['movie_id']);
        } else {
            $model = People::getInstance();
            $where = array('people_id' => $content['people_id']);
        }

        $old_content = $model->getRow($where);
        if (empty($old_content)) {
            $ret = $model->add($content, false);
        } else {
            if ($old_content['external_update_time'] >= $content['external_update_time'] && $is_force === false) {
                Log::info('이미 업데이트된 컨텐츠', array('old' => $old_content['external_update_time'], 'new' => $content['external_update_time']));

                return false;
            }
            $ret = $model->modify($content, $where, false);
        }

        if ($ret === false) {
            throw new SynchronizerException('컨텐츠 set 실패', $content);
        }

        if (empty($old_content) === false) {
            $new_content = $model->getRow($where);
            $diff1 = array_diff_assoc($old_content, $new_content);
            $diff2 = array_diff_assoc($new_content, $old_content);
            unset($diff1['update_time']);
            unset($diff1['insert_time']);
            unset($diff2['update_time']);
            unset($diff2['insert_time']);
            if (!empty($diff1) || !empty($diff2)) {
                Log::setLogType('contents update diff/' . $this->getContentType());
                Log::info('컨텐츠 set diff', array($where, $diff1, $diff2));
                Log::restoreLogType();
            }
        }

        return true;
    }

    public function syncContent($content_html, $actor_json = null, $staff_json = null, $is_force = false)
    {
        $data = $this->parser->parseContent($content_html);
        if (empty($data)) {
            throw new SynchronizerException('컨텐츠 파싱 실패', $content_html);
        }
        $content = $data['content'];
        $image = $data['image'];
        if ($this->isMovieContent()) {
            $genre = $data['genre'];
            $making_country = $data['making_country'];
        }
        $content_id_key = $this->getContentType() . '_id';

        Model::getInstance()->begin();

        try {
            if ($this->isMovieContent()) {
                if (!empty($actor_json) || !empty($staff_json)) {
                    $actors = $this->parser->parseMovieActor($actor_json);
                    $staffs = $this->parser->parseMovieStaff($staff_json);

                    if ($actors) {
                        $lead_actors = array();
                        $support_actors = array();
                        foreach ($actors as $actor) {
                            if ($actor['job'] === '주연') {
                                $lead_actors[] = $actor['people_name'];
                            } else {
                                $support_actors[] = $actor['people_name'];
                            }
                        }
                    }
                    if ($staffs) {
                        $directors = array();
                        $etc_staffs = array();
                        foreach ($staffs as $staff) {
                            if ($staff['job'] === '감독') {
                                $directors[] = $staff['people_name'];
                            } else {
                                $etc_staffs[] = $staff['people_name'];
                            }
                        }
                    }
                    $content['lead_actors'] = empty($lead_actors) ? null : json_encode($lead_actors);
                    $content['support_actors'] = empty($support_actors) ? null : json_encode($support_actors);
                    $content['directors'] = empty($directors) ? null : json_encode($directors);
                    $content['staffs'] = empty($etc_staffs) ? null : json_encode($etc_staffs);
                } else {
                    $content['lead_actors'] = null;
                    $content['support_actors'] = null;
                    $content['directors'] = null;
                    $content['staffs'] = null;
                }
            }
            $result = $this->_syncContent($content, $is_force);
            if ($result === false && $is_force === false) {
                Model::getInstance()->rollBack();

                return -1;
            }

            if ($this->isMovieContent()) {
                $this->syncMoviePeople(array_merge((array)$actors, (array)$staffs), $content[$content_id_key], true);
                $this->syncGenre($genre, $content[$content_id_key], true);
                $this->syncMakingCountry($making_country, $content[$content_id_key], true);
            }
            $this->syncImage($image, $content[$content_id_key], $is_force);
            Model::getInstance()->commit();

            return true;
        } catch (\Exception $e) {
            Log::critical('동기화 실패', $e);
            Model::getInstance()->rollBack();

            return false;
        }
    }

    public function syncContentDirect($content_id, $is_force = false)
    {
        $content_html = $this->crawler->getContent($content_id);
        if (empty($content_html)) {
            throw new SynchronizerException('Content 얻어오기 실패', $content_id);
        }
        if ($this->isMovieContent()) {
            $actor_json = $this->crawler->getMovieActorList($content_id);
            $staff_json = $this->crawler->getMovieStaffList($content_id);

            return $this->syncContent($content_html, $actor_json, $staff_json, $is_force);
        } else {
            return $this->syncContent($content_html, null, null, $is_force);
        }
    }

    public function syncContentLocal($content_id, $is_force = false)
    {
        $content_html = $this->crawler->getContentLocal($content_id, true);
        if (empty($content_html)) {
            throw new SynchronizerException('Local에 파일 없음', $content_id);
        }

        if ($this->isMovieContent()) {
            $actor_json = $this->crawler->getMovieActorListLocal($content_id, true);
            $staff_json = $this->crawler->getMovieStaffListLocal($content_id, true);

            return $this->syncContent($content_html, $actor_json, $staff_json, $is_force);
        } else {
            return $this->syncContent($content_html, null, null, $is_force);
        }
    }

    public function syncContentsLocal(array $content_id_list, $is_force = false)
    {
        if (empty($content_id_list)) {
            return false;
        }

        foreach ($content_id_list as $content_id) {
            $this->syncContentLocal($content_id, $is_force);
        }

        return true;
    }
}