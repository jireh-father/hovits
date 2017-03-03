<?php
namespace middleware\model;

use framework\base\Model;
use framework\library\sql_builder\SqlBuilder;

class MovieMatchChoice extends Model
{
    /**
     * @param null $dsn
     * @param null $custom_cache_key
     * @return MovieMatchChoice
     */
    public static function getInstance($dsn = null, $custom_cache_key = null)
    {
        return parent::getInstance($dsn, $custom_cache_key);
    }

    public function addMatchChoice($selected_movie_id, $unselected_movie_id, $movie_match_id, $user_pk)
    {
        if ($selected_movie_id > $unselected_movie_id) {
            $movie_id1 = $unselected_movie_id;
            $movie_id2 = $selected_movie_id;
        } else {
            $movie_id1 = $selected_movie_id;
            $movie_id2 = $unselected_movie_id;
        }

        $this->begin();
        $ret = $this->set(
            compact('movie_id1', 'movie_id2', 'selected_movie_id', 'unselected_movie_id', 'user_pk', 'movie_match_id'),
            compact('movie_id1', 'movie_id2', 'user_pk', 'movie_match_id')
        );
        if ($ret === false) {
            return false;
        }

        $movie_match_model = MovieMatch::getInstance();
        $ret = $movie_match_model->set(array(SqlBuilder::plainString('match_cnt = match_cnt + 1')), compact('movie_match_id'));
        if ($ret === false) {
            $this->rollBack();

            return false;
        }

        $this->commit();

        return true;
    }
}