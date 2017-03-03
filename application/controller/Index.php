<?php
namespace controller;

use framework\library\ArrayUtil;
use framework\library\sql_builder\SqlBuilder;
use framework\library\Time;
use middleware\model\Image;
use middleware\model\Movie;
use middleware\model\MovieMatch;
use middleware\model\RealtimeBoxoffice;
use middleware\service\hovits\contents\BoxOfficeService;
use middleware\service\hovits\contents\MatchService;
use middleware\service\recmd\RecmdService;
use service\User;

class Index extends Hovits
{
    public function index()
    {
        $this->redirect('/boxOffice/bookingRatio');
        list($box_offices, $image_list, $still_cut_list) = BoxOfficeService::getList('booking_ratio desc', 3);
        list($match_list, $match_still_cut_list) = MatchService::getMatchRankList(3);
        self::setViewData(compact('box_offices', 'image_list', 'still_cut_list', 'match_list', 'match_still_cut_list'));
        $this->_addJsDefault(false);

        //        //        $this->redirect('/boxOffice');
        //        //박스오피스 데이터 가져오기
        //        $box_offices = $this->_getBoxOffices();
        //
        //        //추천 데이터 가져오기(로그인 되어 있을 경우만)
        //        $recmd_movies = $this->_getRecmdMovies();
        //
        //        $ranked_matches = $this->_getRankedMatches();
        //        exit;

        //모든영화 리스트의 썸네일과 영화 정보 가져옴
        //        $movie_ids = array_keys($box_offices);
        //        if (!empty($recmd_movies)) {
        //            $movie_ids = array_merge($movie_ids, array_keys($recmd_movies));
        //        }
        //        if (!empty($ranked_matches)) {
        //            $movie_ids = array_merge($movie_ids, ArrayUtil::getArrayColumn($ranked_matches, 'movie_id1'));
        //            $movie_ids = array_merge($movie_ids, ArrayUtil::getArrayColumn($ranked_matches, 'movie_id2'));
        //        }
        //        $movie_ids = ArrayUtil::toStringElements($movie_ids);
        //        $movie_list = Movie::getInstance()->getMap('movie_id', array(SqlBuilder::in($movie_ids, 'movie_id')));
        //        $image_list = Image::getInstance()->getMap(
        //            'content_id',
        //            array(
        //                'content_type' => CONTENT_TYPE_MOVIE,
        //                'image_type'   => 'main',
        //                SqlBuilder::in($movie_ids, 'content_id')
        //            )
        //        );
        //
        //        //각 리스트의 1위 영화는(메인 슬라이드용) 영화 상세정보, 스틸컷, 기타정보 계산 필요
        //        //베스트 박스오피스
        //        $best_content_list = array();
        //        $best_box_office_movie_id = key($box_offices);
        //        $best_movie_ids = array($best_box_office_movie_id);
        //        $best_content_list[$best_box_office_movie_id] = '박스오피스';
        //
        //        //베스트 추천영화
        //        if (!empty($recmd_movies)) {
        //            $rand_key = rand(0, 2);
        //            $best_recomd_movie_id = key($recmd_movies);
        //            for ($i = 0; $i < $rand_key; $i++) {
        //                next($recmd_movies);
        //                $best_recomd_movie_id = key($recmd_movies);
        //            }
        //
        //            if (!empty($best_recomd_movie_id)) {
        //                $best_content_list[$best_recomd_movie_id] = '추천 영화';
        //                $best_movie_ids[] = $best_recomd_movie_id;
        //            }
        //        }
        //
        //        //베스트 영화대결
        //        if (!empty($ranked_matches)) {
        //            $best_match_id = key($ranked_matches);
        //            $best_movie_ids[] = $ranked_matches[$best_match_id]['movie_id1'];
        //            $best_movie_ids[] = $ranked_matches[$best_match_id]['movie_id2'];
        //            $best_content_list[$best_match_id] = '영화매치업';
        //        }
        //
        //        $best_movie_ids = ArrayUtil::toStringElements($best_movie_ids);
        //
        //        $still_cut_list = Image::getInstance()->getMultiMap(
        //            'content_id',
        //            array(
        //                'content_type' => CONTENT_TYPE_MOVIE,
        //                'image_type'   => 'still_cut',
        //                SqlBuilder::in($best_movie_ids, 'content_id')
        //            )
        //        );
        //
        //        $this->setViewData(
        //            compact(
        //                'box_offices',
        //                'recmd_movies',
        //                'movie_list',
        //                'image_list',
        //                'best_content_list',
        //                'still_cut_list',
        //                'ranked_matches'
        //            )
        //        );
        //
        //        $this->_addJsDefault(false);
    }

    private function _getBoxOffices()
    {
        $boxoffice_model = RealtimeBoxoffice::getInstance();

        return $boxoffice_model->getMap('movie_id', null, 'booking_ratio desc', 10);
    }

    private function _getRecmdMovies()
    {
        $user_pk = User::getUserPk();
        if (empty($user_pk)) {
            return null;
        }

        return RecmdService::getMovieRecmdList($user_pk, 10);
    }

    private function _getRankedMatches()
    {
        $a_week_ago = Time::subDays(300, null, 'Y-m-d H:i:s');

        $movie_match_model = MovieMatch::getInstance();
        $movie_match_model->setTable(
            array(
                'movie_match',
                SqlBuilder::join(
                    SqlBuilder::subQuery(
                        'movie_match_choice',
                        'movie_match_choice',
                        'movie_match_id, count(movie_match_id) choice_count',
                        array(
                            SqlBuilder::expr('insert_time', $a_week_ago, '>')
                        ),
                        'choice_count desc',
                        10,
                        'movie_match_id'
                    ),
                    'movie_match.movie_match_id = movie_match_choice.movie_match_id'
                )
            )
        );

        return $movie_match_model->getMap(
            'movie_match_id',
            null,
            'choice_count desc'
        );
    }
}