<?php
namespace controller;

use exception\HovitsException;
use framework\library\ArrayUtil;
use framework\library\Session;
use framework\library\sql_builder\SqlBuilder;
use middleware\model\Image;
use middleware\model\Movie;
use middleware\model\MovieMatch;
use middleware\model\MovieMatchChoice;
use middleware\service\match\MatchService;
use middleware\service\recmd\RecmdService;
use service\User;

class Tutorial extends Hovits
{
    const TOTAL_SELECT_LIMIT = 6;

    public function index()
    {
        $this->setLayout('default');

        $movie_model = Movie::getInstance();
        //        $movie_model2 = RealtimeBoxoffice::getInstance();

        $movie_list = $movie_model->getList(
            array(
                SqlBuilder::plainExpr('naver_grade_count + cgv_grade_count + daum_grade_count + lotte_grade_count + watcha_grade_count + imdb_grade_count', 15000, '>=')
            ),
            'rand()',
            200
        );

        //        $movie_list = $movie_model2->getList(
        //            null,
        //            'rand()'
        //        );
        $movie_ids = ArrayUtil::getArrayColumn($movie_list, 'movie_id');
        $image_list = Image::getInstance()->getMap('content_id', array('content_type' => CONTENT_TYPE_MOVIE, 'image_type' => 'main', SqlBuilder::in($movie_ids, 'content_id')));
        $this->setViewData(compact('movie_list', 'image_list'));
        $this->addViewData('total_select_limit', self::TOTAL_SELECT_LIMIT);

        $this->_addJsDefault();
    }

    public function step2()
    {
        $movie_id_list = $this->getParam('movie_id');
        if (count($movie_id_list) < self::TOTAL_SELECT_LIMIT) {
            $this->redirect('/tutorial', self::TOTAL_SELECT_LIMIT . '개 이상의 영화를 선택해주세요.');
        }

        $this->setLayout('default');

        $movie_model = Movie::getInstance();
        $movie_model->setTable(
            array(
                'movie',
                SqlBuilder::join('image', 'movie.movie_id = image.content_id')
            )
        );
        $movies = $movie_model->getList(
            array(
                SqlBuilder::in($movie_id_list, 'movie_id'),
                'content_type' => CONTENT_TYPE_MOVIE,
                'image_type'   => 'main'
            )
        );

        shuffle($movies);

        $match_cnt = (int)(count($movies) / 2);

        $step = 2;
        $next_step_uri = '/tutorial/step3';

        $this->setView('tutorial_step2', compact('movies', 'match_cnt', 'step', 'next_step_uri'));

        $this->_addJsDefault();
    }

    public function step3()
    {
        $params = $this->getParams();

        $unselected_movie_ids = $params['unselected_movie_id'];
        $selected_movie_ids = $params['selected_movie_id'];

        Session::set('unselected_movie_ids', $unselected_movie_ids);
        Session::set('selected_movie_ids', $selected_movie_ids);

        $this->setLayout('default');

        $movie_model = Movie::getInstance();
        $movie_model->setTable(
            array(
                'movie',
                SqlBuilder::join('image', 'movie.movie_id = image.content_id')
            )
        );
        $movies1 = $movie_model->getList(
            array(
                SqlBuilder::in($unselected_movie_ids, 'movie_id'),
                'content_type' => CONTENT_TYPE_MOVIE,
                'image_type'   => 'main'
            )
        );

        $movies2 = $movie_model->getList(
            array(
                SqlBuilder::in($selected_movie_ids, 'movie_id'),
                'content_type' => CONTENT_TYPE_MOVIE,
                'image_type'   => 'main'
            )
        );

        shuffle($movies1);
        shuffle($movies2);

        $movies = array_merge($movies1, $movies2);

        $match_cnt = (int)(count($movies) / 2);
        $step = 3;
        $next_step_uri = '/tutorial/complete';

        $this->setView('tutorial_step2', compact('movies', 'match_cnt', 'step', 'next_step_uri'));

        $this->_addJsDefault();
    }

    public function complete()
    {
        //  array(2) {
        //  ["unselected_movie_id"]=>
        //  array(2) {
        //    [0]=>
        //    string(8) "20148132"
        //    [1]=>
        //    string(8) "20140703"
        //  }
        //  ["selected_movie_id"]=>
        //  array(2) {
        //    [0]=>
        //    string(8) "20060218"
        //    [1]=>
        //    string(8) "20122122"
        //  }
        //}'
        $params = $this->getParams();

        $user_pk = User::getUserPk();

        $unselected_movie_ids = $params['unselected_movie_id'];
        $selected_movie_ids = $params['selected_movie_id'];

        $unselected_movie_ids_step2 = Session::get('unselected_movie_ids');
        $selected_movie_ids_step2 = Session::get('selected_movie_ids');
        Session::set('unselected_movie_ids', null);
        Session::set('selected_movie_ids', null);

        $unselected_movie_ids = array_merge($unselected_movie_ids, $unselected_movie_ids_step2);
        $selected_movie_ids = array_merge($selected_movie_ids, $selected_movie_ids_step2);

        $match_model = MovieMatch::getInstance();
        $match_choice_model = MovieMatchChoice::getInstance();
        foreach ($selected_movie_ids as $i => $selected_movie_id) {
            $unselected_movie_id = $unselected_movie_ids[$i];

            $movie_match_id = $match_model->setMatch($selected_movie_id, $unselected_movie_id);
            if (empty($movie_match_id)) {
                throw new HovitsException('movie match id 검색 실패');
            }

            $ret = $match_choice_model->addMatchChoice($selected_movie_id, $unselected_movie_id, $movie_match_id, $user_pk);
            if (!$ret) {
                throw new HovitsException('movie match choice 추가 실패');
            }
        }

        //todo: 취향분석 시작
        MatchService::calcMovieMatchRates($user_pk);

        RecmdService::calcMovieSimilarity();

        $this->redirect('/', '당신의 영화 취향 분석 완료되었습니다.');
    }
}