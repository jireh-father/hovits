<?php
namespace middleware\service\hovits\contents;

use framework\library\sql_builder\SqlBuilder;
use framework\library\Time;
use middleware\model\Image;
use middleware\model\Movie;
use middleware\model\MovieMatch;

class MatchService
{
    public static function getMatchRankList($limit = 10)
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
                        $limit,
                        'movie_match_id'
                    ),
                    'movie_match.movie_match_id = movie_match_choice.movie_match_id'
                )
            )
        );

        $match_list = $movie_match_model->getMap(
            'movie_match_id',
            null,
            'choice_count desc'
        );

        if (empty($match_list) === true) {
            return null;
        }

        $movie_id_list = array();
        foreach ($match_list as $match) {
            $movie_id_list[] = $match['movie_id1'];
            $movie_id_list[] = $match['movie_id2'];
        }

        $movie_model = Movie::getInstance();
        $movie_model->setTable(
            array(
                'movie',
                SqlBuilder::join('image', 'movie.movie_id = image.content_id')
            )
        );

        $movies = $movie_model->getMap(
            'movie_id',
            array(
                SqlBuilder::in($movie_id_list, 'movie_id'),
                'content_type' => CONTENT_TYPE_MOVIE,
                'image_type'   => 'main'
            )
        );
        $result_movies = array();

        foreach ($movie_id_list as $movie_id) {
            $result_movies[] = $movies[$movie_id];
        }
        $image_model = Image::getInstance();
        $still_cut_list = $image_model->getMultiMap('content_id', array('content_type' => CONTENT_TYPE_MOVIE, 'image_type' => 'still_cut', SqlBuilder::in($movie_id_list, 'content_id')));

        return array($result_movies, $still_cut_list);
    }
}