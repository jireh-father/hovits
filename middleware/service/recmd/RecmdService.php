<?php

namespace middleware\service\recmd;

use framework\base\Model;
use framework\library\ArrayUtil;
use framework\library\sql_builder\SqlBuilder;
use middleware\library\recmd\SimilaritySet;
use middleware\library\recmd\similarityset\JaccardAndPearson;
use middleware\model\MovieMatchGrade;
use middleware\model\MovieSimilarity;

class RecmdService
{
    const SCORE_MIN_LIMIT = 80;
    const HIGH_SCORE_LIMIT = 80;

    public static function calcMovieSimilarity()
    {
        $similarity_lib = new JaccardAndPearson();

        $grade_model = MovieMatchGrade::getInstance();

        $movie_list = $grade_model->getList(null, 'movie_id');

        $target_matrix = ArrayUtil::buildMatrix($movie_list, 'movie_id', 'user_pk', 'grade_point');

        $similarity_model = MovieSimilarity::getInstance();

        self::_calcSimilarity($similarity_lib, $target_matrix, $similarity_model, 'movie_id');
    }

    public static function calcMatchSimilarity()
    {
        $similarity_lib = new JaccardBinarySet();
        $model = new VoteMovieMatch();

        $match_list = $model->get(null, null, array('match_id'));
        $target_matrix = buildMatrix($match_list, 'match_id', 'user_id');
        $similarity_model = new SimilarityMatch();

        self::_calcSimilarity($similarity_lib, $target_matrix, $similarity_model, 'match_id');
    }

    /**
     * @param SimilaritySet $similar_lib
     * @param $matrix
     * @param Model $similar_model
     * @param $col
     */
    private static function _calcSimilarity($similar_lib, $matrix, $similar_model, $col)
    {
        $ret_matrix = $similar_lib->calc($matrix);

        $similar_model->remove();
        foreach ($ret_matrix as $id => $similarity) {
            $similarity_json = json_encode($similarity);
            $data = array($col => $id, 'similarity_json' => $similarity_json);
            $similar_model->add($data);
        }
    }

    public static function getMovieRecmdList($user_pk, $limit = null)
    {
        $grade_model = MovieMatchGrade::getInstance();
        $similar_model = MovieSimilarity::getInstance();

        $high_score_list = $grade_model->getList(
            array(
                SqlBuilder::expr('grade_point', self::HIGH_SCORE_LIMIT, '>'),
                'user_pk' => $user_pk
            ),
            'grade_point desc'
        );

        $user_movie_list = $grade_model->getValues('movie_id', compact('user_pk'));
        $recmd_score_list = array();

        $movie_id_list = array();
        $movie_score_list = array();
        foreach ($high_score_list as $score_arr) {
            $movie_id = $score_arr['movie_id'];
            $movie_score = $score_arr['grade_point'];
            if ($movie_score < self::SCORE_MIN_LIMIT) {
                continue;
            }
            $movie_id_list[] = $movie_id;
            $movie_score_list[] = $movie_score;
        }

        if (empty($movie_id_list)) {
            return null;
        }

        $similar_json_list = $similar_model->getMap('movie_id', array(SqlBuilder::in($movie_id_list, 'movie_id')));

        foreach ($movie_id_list as $key => $movie_id) {
            if (!isset($similar_json_list[$movie_id])) {
                continue;
            }
            $similar_json = $similar_json_list[$movie_id];
            if (empty($similar_json)) {
                continue;
            }
            $movie_score = $movie_score_list[$key];

            $similar_list = json_decode($similar_json['similarity_json'], true);
            arsort($similar_list);

            foreach ($similar_list as $similar_movie_id => $similar_score) {
                if (in_array($similar_movie_id, $user_movie_list) === true) {
                    continue;
                }
                if ($similar_score < self::SCORE_MIN_LIMIT) {
                    break;
                }
                if (!isset($recmd_score_list[$similar_movie_id])) {
                    $recmd_score_list[$similar_movie_id] = 0;
                }

//                $recmd_score_list[$similar_movie_id] += ($movie_score + $similar_score);
                $recmd_score_list[$similar_movie_id] += $similar_score;
            }
        }

        arsort($recmd_score_list);

        if ($limit > 0 && count($recmd_score_list) > $limit) {
            $recmd_score_list = array_slice($recmd_score_list, 0, $limit, true);
        }


        return $recmd_score_list;
    }

    public static function getMatchRecmdList($user_id, $limit = null)
    {
        $vote_model = new VoteMovieMatch();
        $similar_model = new SimilarityMatch();
        $vote_list = $vote_model->getVal('match_id', compact('user_id'));
        $recmd_list = array();

        $similar_json_list = $similar_model->get(array('match_id' => Database::in($vote_list)));

        foreach ($similar_json_list as $similar_json) {
            if (empty($similar_json)) {
                continue;
            }
            $similar_list = json_decode($similar_json['similarity_json'], true);
            arsort($similar_list);
            foreach ($similar_list as $similar_match_id => $similar_score) {
                if (in_array($similar_match_id, $vote_list) === true) {
                    continue;
                }
                if ($similar_score < self::SCORE_MIN_LIMIT) {
                    break;
                }
                if (!isset($recmd_list[$similar_match_id])) {
                    $recmd_list[$similar_match_id] = 0;
                }
                $recmd_list[$similar_match_id] += $similar_score;
            }
        }
        arsort($recmd_list);

        if ($limit > 0 && count($recmd_list) > $limit) {
            $recmd_list = array_slice($recmd_list, 0, $limit);
        }

        return $recmd_list;
    }
}