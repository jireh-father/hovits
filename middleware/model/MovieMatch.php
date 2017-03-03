<?php
namespace middleware\model;

use framework\base\Model;

class MovieMatch extends Model
{
    /**
     * @param null $dsn
     * @param null $custom_cache_key
     * @return MovieMatch
     */
    public static function getInstance($dsn = null, $custom_cache_key = null)
    {
        return parent::getInstance($dsn, $custom_cache_key);
    }

    public function existMatch($movie_id1, $movie_id2)
    {
        if ($movie_id1 > $movie_id2) {
            $movie_id_tmp = $movie_id1;
            $movie_id1 = $movie_id2;
            $movie_id2 = $movie_id_tmp;
        }

        return $this->exist(compact('movie_id1', 'movie_id2'));
    }

    public function setMatch($movie_id1, $movie_id2)
    {
        if ($movie_id1 > $movie_id2) {
            $movie_id_tmp = $movie_id1;
            $movie_id1 = $movie_id2;
            $movie_id2 = $movie_id_tmp;
        }

        $movie_match_id = $this->get('movie_match_id', compact('movie_id1', 'movie_id2'));
        if (empty($movie_match_id)) {
            $ret = $this->add(compact('movie_id1', 'movie_id2'));
            if ($ret === false) {
                return false;
            }
            $movie_match_id = $this->getLastInsertId();
            if (empty($movie_match_id)) {
                return false;
            }
        }

        return $movie_match_id;
    }
}