<?php
namespace middleware\service\contents\parser;

use middleware\exception\ParserException;
use middleware\library\QpWrapper;
use middleware\service\contents\crawler\KoficCrawler;

class KoficPeopleParser extends KoficParser
{
    /**
     * @param KoficCrawler $crawler
     */
    public function __construct($crawler = null)
    {
        parent::__construct(CONTENT_TYPE_PEOPLE, $crawler);
    }


    /**
     * @param QpWrapper $info_data
     * @return array|null
     * @throws ParserException
     */
    protected function _extractPeopleAka($info_data)
    {
        $people_aka = $info_data->text();
        $this->_empty(compact('people_aka'));
        if ($this->_isEmptyValue($people_aka) === true) {
            return null;
        }

        return compact('people_aka');
    }

    /**
     * @param QpWrapper $info_data
     * @return array
     * @throws ParserException
     */
    protected function _extractSex($info_data)
    {
        $data = array();
        foreach ($info_data->find('li') as $li) {
            $this->_exists(compact('li'));
            $em = $li->find('em');
            if (!$em->exists()) {
                //성별
                $content = $li->text();
                $this->_empty(compact('content'));
                if ($this->_isEmptyValue($content)) {
                    continue;
                }
                $data['sex'] = $content === '남자' ? 'M' : 'F';
            } else {
                $li->remove('em');
                $content = $li->text();
                $this->_empty(compact('content'));
                if ($this->_isEmptyValue($content)) {
                    continue;
                }
                if ($em->text() === '출생') {
                    $data['birth_date'] = $content;
                } elseif ($em->text() === '국적') {
                    $data['birth_country'] = $content;
                }
            }
        }

        return $data;
    }

    /**
     * @param QpWrapper $info_data
     * @return array|null
     */
    protected function _extractMainJob($info_data)
    {
        $main_job = $info_data->text();
        if (empty($main_job)) {
            return null;
        }
        if ($this->_isEmptyValue($main_job)) {
            return null;
        }

        return compact('main_job');
    }

    /**
     * @param QpWrapper $info_data
     * @return array|null
     */
    protected function _extractPeopleCompany($info_data)
    {
        $people_company = $info_data->text();
        if (empty($people_company)) {
            return null;
        }
        if ($this->_isEmptyValue($people_company)) {
            return null;
        }

        return compact("people_company");
    }

    /**
     * @param QpWrapper $etc_info
     * @param $content
     * @return array
     * @throws ParserException
     */
    protected function _extractBiography($etc_info, $content)
    {
        preg_match_all('/<h3>바이오그래피<\/h3>\s*<p>\s*((.|\n)+)<\/p>/', $content, $matches);
        if (empty($matches[1][0])) {
            throw new ParserException('바이오그래피 데이터 이상', $etc_info->html());
        }
        $biography = preg_replace('/\n\n/', '', $matches[1][0]);

        if (empty($biography)) {
            $biography = null;
        }

        return compact('biography');
    }
}