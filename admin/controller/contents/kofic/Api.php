<?php
namespace controller\contents\kofic;

use controller\AdminBase;
use framework\library\File;
use middleware\library\HtmlFormatter;
use middleware\service\contents\crawler\KoficCrawler;
use middleware\service\contents\crawler\KoficMovieCrawler;
use middleware\service\contents\crawler\KoficPeopleCrawler;

class Api extends AdminBase
{
    public function index()
    {
        $this->setView('contents/kofic_api');
    }

    public function files()
    {
        $dir = $this->validateParam('dir');
        if (!is_dir($dir)) {
            $this->ajaxFail('dir value is not a directory', $dir);
        }

        $file_list = File::getFileList($dir);
        $this->ajaxSuccess('success', $file_list);
    }

    private static $required_contents_cols = array(
        'content_type',
        'content_id'
    );

    private function _contentFromServer()
    {
        list($content_type, $content_id) = $this->validateParams(self::$required_contents_cols);

        switch ($content_type) {
            case CONTENT_TYPE_MOVIE:
                $crawler = new KoficMovieCrawler();
                $contents = $crawler->getContent($content_id);

                $this->_printContent($contents);
                break;
            case CONTENT_TYPE_PEOPLE:
                $crawler = new KoficPeopleCrawler();
                $contents = $crawler->getContent($content_id);

                $this->_printContent($contents);
                break;
            case 'movie_actor':
                $crawler = new KoficMovieCrawler();
                $contents = $crawler->getMovieActorList($content_id);
                $contents = json_decode($contents, true);
                $this->_printJson($contents);
                break;
            case 'movie_staff':
                $crawler = new KoficMovieCrawler();
                $contents = $crawler->getMovieStaffList($content_id);
                $contents = json_decode($contents, true);
                $this->_printJson($contents);
                break;
            default:
                echo 'error';
                exit;
        }
    }

    private function _contentFromLocal()
    {
        list($content_type, $content_id) = $this->validateParams(self::$required_contents_cols);

        switch ($content_type) {
            case CONTENT_TYPE_MOVIE:
                $crawler = new KoficMovieCrawler();
                $contents = File::getFileContents($crawler->getContentPath($content_id, true));
                $this->_printContent($contents);
                break;
            case CONTENT_TYPE_PEOPLE:
                $crawler = new KoficPeopleCrawler();
                $contents = File::getFileContents($crawler->getContentPath($content_id, true));
                $this->_printContent($contents);
                break;
            case 'movie_actor':
                $crawler = new KoficMovieCrawler();
                $path = $crawler->getMovieActorPath($content_id, true);
                if (!is_file($path)) {
                    $path = $crawler->getBackupMovieActorPath($content_id, true);
                    if (!is_file($path)) {
                        echo 'NULL';
                        exit;
                    }
                }
                $contents = File::getFileContents($path);
                $contents = json_decode($contents, true);
                $this->_printJson($contents);
                break;
            case 'movie_staff':
                $crawler = new KoficMovieCrawler();
                $path = $crawler->getMovieStaffPath($content_id, true);
                if (!is_file($path)) {
                    $path = $crawler->getBackupMovieStaffPath($content_id, true);
                    if (!is_file($path)) {
                        echo 'NULL';
                        exit;
                    }
                }
                $contents = File::getFileContents($path);
                $contents = json_decode($contents, true);
                $this->_printJson($contents);
                break;
            default:
                echo 'error';
                exit;
        }
    }

    private function _printContent($contents)
    {
        $contents = str_replace('/common/mast', 'http://www.kobis.or.kr/common/mast', $contents);
        $contents = str_replace('/kobis/web/comm/images', 'http://www.kobis.or.kr/kobis/web/comm/images', $contents);
        $contents = str_replace('/upload/up_img', 'http://www.kobis.or.kr/upload/up_img', $contents);
        //        $contents = HtmlFormatter::format($contents);
        //        $this->ajax("<textarea>{$contents}</textarea>");
        $this->setLayout('default');
        $this->setView('contents/view', compact('contents'));
    }

    private function _printJson($contents)
    {
        $content_type = $this->getParam('content_type');
        $only_name = $this->getParam('only_name');
        if ($only_name === 'only_name') {
            if ($content_type === 'movie_actor') {
                foreach ($contents as $content) {
                    echo "{$content['peopleNm']},";
                }
            } elseif ($content_type === 'movie_staff') {
                foreach ($contents as $content) {
                    echo "{$content['roleNm']} : {$content['peopleNm']}</br>";
                }
            }
        } else {
            debug($contents);
        }
        exit;

    }

    public function content()
    {
        $search_type = $this->validateParam('search_type');

        if ($search_type === 'server') {
            $this->_contentFromServer();
        } else {
            $this->_contentFromLocal();
        }
    }
}