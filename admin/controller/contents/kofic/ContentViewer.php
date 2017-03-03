<?php
namespace controller\contents\kofic;

use framework\base\Controller;
use framework\library\File;
use middleware\service\contents\crawler\KoficMovieCrawler;
use middleware\service\contents\crawler\KoficPeopleCrawler;

class ContentViewer extends Controller
{
    public function index()
    {
        list($is_local, $content_type, $content_id) = $this->validateParams(array('is_local', 'content_type', 'content_id'));

        if ($content_type === CONTENT_TYPE_MOVIE) {
            $crawler = new KoficMovieCrawler();
        } else {
            $crawler = new KoficPeopleCrawler();
        }

        if ($is_local == true) {
            $path = $crawler->getContentPath($content_id);
            if (is_file($path)) {
                $content_html = File::getFileContents($path);
            } else {
                $path = $crawler->getBackupContentPath($content_id);
                if (is_file($path)) {
                    $content_html = File::getFileContents($path);
                }
            }
        } else {
            $content_html = $crawler->getContent($content_id);
        }

        $this->setView('contents/kofic/content_viewer', array('content_html' => $content_html));
    }
}