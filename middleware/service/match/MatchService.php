<?php

namespace middleware\service\match;

use middleware\library\matchtree\MatchTreeManager;
use middleware\model\MovieMatchChoice;
use middleware\model\MovieMatchGrade;

class MatchService
{
    const EQUAL_GAP_LIMIT_PERCENTAGE = 15;

    public static function calcMovieMatchRates($user_pk)
    {
        $match_choice_model = MovieMatchChoice::getInstance();
        $match_list = $match_choice_model->getList(compact('user_pk'));

        $match_tree_manager = new MatchTreeManager();
        $match_tree_manager->buildMatchTree($match_tree_manager->createMatchList($match_list));

        $match_tree_manager->display();

        $score_list = $match_tree_manager->grading();
        debug($score_list);

        $grade_model = MovieMatchGrade::getInstance();
        $grade_model->remove(compact('user_pk'));
        foreach ($score_list as $movie_id => $grade_point) {
            $grade_model->add(compact('user_pk', 'movie_id', 'grade_point'));
        }
    }

    public static function checkTutorial($user_id)
    {
        $vote_model = new VoteMovieMatch();
        $is_tutorial = 'Y';

        return $vote_model->exist(compact('user_id', 'is_tutorial'));
    }

    public static function add(
        $movie1,
        $movie2,
        $match_maker_type,
        $match_maker = null,
        $is_relation,
        $is_tutorial,
        $is_must
    )
    {
        if (empty($movie1) || empty($movie2)) {
            return false;
        }

        if ($movie1 == $movie2) {
            return false;
        }
        if ($movie1 > $movie2) {
            $tmp = $movie1;
            $movie1 = $movie2;
            $movie2 = $tmp;
        }
        $match_model = new MatchMovie();

        return $match_model->add(
            compact('movie1', 'movie2', 'match_maker_type', 'match_maker', 'is_relation', 'is_tutorial', 'is_must')
        );
    }

    public static function searchMatchList($limit, $where = null, $order = null)
    {
        $match_model = new MatchMovie();
        $match_list = $match_model->get($where, null, $order, $limit);
        $total = $match_model->count('id', $where);

        return compact('match_list', 'total');
    }

    public static function getMatchListByIdList(array $match_id_list)
    {
        $match_model = new MatchMovie();

        return $match_model->getByIdList($match_id_list, 'id', 'id');
    }

    public static function searchTutorialMatchList($limit, $where = null, $order = null)
    {
        $match_model = new MatchMovie();
        $match_list = $match_model->getFor('id', $where, null, $order, $limit);
        $total = $match_model->count('id', $where);
        $id_list = $match_list;

        return compact('id_list', 'total');
    }

    public static function setMatch($id, $is_tutorial)
    {
        $match_model = new MatchMovie();

        return $match_model->setAuto(compact('is_tutorial'), compact('id'));
    }

    public static function isMatchTutorial($match_id)
    {
        $match_model = new MatchMovie();
        $is_tutorial = 'Y';

        return $match_model->exist(compact('match_id', 'is_tutorial'));
    }

    public static function getMatch($id)
    {
        $match_model = new MatchMovie();

        return $match_model->getOne(compact('id'));
    }

    public static function deleteMatch($id)
    {
        $match_model = new MatchMovie();

        return $match_model->remove(compact('id'));
    }

    public static function getSimilarityMatch($match_id)
    {
        $similarity_model = new SimilarityMatch();
        $similarity = $similarity_model->getOne(compact('match_id'));

        if (empty($similarity)) {
            return null;
        }
        $json = json_decode($similarity['similarity_json'], true);
        arsort($json);

        return $json;
    }

    public static function getChosenList($movie_id)
    {
        $vote_model = new VoteMovieMatch();

        return $vote_model->getVal('chosen_id', array('unchosen_id' => $movie_id), array('chosen_id'));
    }

    public static function getHotMatchIdList($latest_days = 30, $limit = 10)
    {
        $create_time = changeDate($latest_days);
        $model = new VoteMovieMatch();
        $vote_list = $model->get(
            array('create_time' => Database::h($create_time)),
            array('match_id'),
            array('cnt desc', 'create_time desc'),
            $limit,
            array('count(match_id) cnt', 'match_id')
        );
        $id_list = array_column($vote_list, 'match_id');

        return json_decode(json_encode($id_list), true);
    }

    public static function getMatchVoteCount($match_id)
    {
        $model = new VoteMovieMatch();
        $vote_list = $model->getFor(
            'chosen_id',
            compact('match_id'),
            array('chosen_id'),
            null,
            null,
            array('chosen_id', 'count(chosen_id) cnt')
        );

        return $vote_list;
    }

    public static function getMatchesByRelation($movie_id)
    {
        $chosen_list = self::getChosenList($movie_id);
        $vote_model = new VoteMovieMatch();
        $winner_list = array();
        $winner_record_list = array();
        $equal_list = array();
        $equal_record_list = array();
        foreach ($chosen_list as $chosen_id) {
            $win_cnt = $vote_model->count('id', array('chosen_id' => $chosen_id, 'unchosen_id' => $movie_id));
            $lose_cnt = $vote_model->count('id', array('chosen_id' => $movie_id, 'unchosen_id' => $chosen_id));
            if ($win_cnt > $lose_cnt) {
                $winner_list[$chosen_id] = $win_cnt - $lose_cnt;
                $winner_record_list[$chosen_id] = array($win_cnt, $lose_cnt);
            }
            if (($gap = self::_isEqualMatch($win_cnt, $lose_cnt)) !== false) {
                $equal_list[$chosen_id] = $gap;
                $equal_record_list[$chosen_id] = array($win_cnt, $lose_cnt);
            }
        }
        arsort($winner_list);
        asort($equal_list);

        return compact('winner_list', 'equal_list', 'winner_record_list', 'equal_record_list');
    }

    private static function _isEqualMatch($win_cnt, $lose_cnt)
    {
        if ($win_cnt == 0 || $lose_cnt == 0) {
            return false;
        }
        if ($win_cnt == $lose_cnt) {
            return 0;
        }
        $total_cnt = $win_cnt + $lose_cnt;

        $gap_limit = getAmountByPercent($total_cnt, self::EQUAL_GAP_LIMIT_PERCENTAGE);
        $gap = abs($win_cnt - $lose_cnt);
        if ($gap <= $gap_limit === false) {
            return false;
        } else {
            return $gap;
        }
    }
}