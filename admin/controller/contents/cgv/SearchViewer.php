<?php
namespace controller\contents\cgv;

use framework\base\Controller;
use framework\library\File;
use middleware\service\contents\crawler\CgvMovieCrawler;
use middleware\service\contents\crawler\NaverMovieCrawler;

class SearchViewer extends Controller
{
    public function index()
    {
        list($is_local, $content_type, $content_id, $vendor) = $this->validateParams(array('is_local', 'content_type', 'content_id', 'vendor'));

        if ($content_type === CONTENT_TYPE_MOVIE) {
            switch ($vendor) {
                case CONTENTS_PROVIDER_CGV:
                    $crawler = new CgvMovieCrawler();
                    break;
                case CONTENTS_PROVIDER_NAVER:
                    $crawler = new NaverMovieCrawler();
                    break;
                default:
                    $crawler = new CgvMovieCrawler();
                    break;
            }
        } else {
        }

        if ($is_local == true) {
            $path = $crawler->getSearchPagePath($content_id);
            if (is_file($path)) {
                $search_html = File::getFileContents($path);
            } else {
                $path = $crawler->getBackupSearchPagePath($content_id);
                if (is_file($path)) {
                    $search_html = File::getFileContents($path);
                }
            }
        } else {
            $search_html = '<pre>'.print_r($crawler->getSearchPageById($content_id), true).'</pre>';
        }

        $this->setView('contents/cgv/search_viewer', array('search_html' => $search_html, 'vendor' => $vendor));
    }
}