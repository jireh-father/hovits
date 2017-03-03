<?php

namespace controller;

use framework\library\ArrayUtil;
use framework\library\sql_builder\SqlBuilder;
use middleware\model\Movie;
use middleware\model\MovieMatch;

class Rank extends Hovits
{
    public function index()
    {
        $this->redirect('/rank/match');
    }

    public function match()
    {
        $model = MovieMatch::getInstance();
        $movie_matchs = $model->getMap('movie_match_id', null, 'match_cnt desc, insert_time desc', 30);

        $movie_ids1 = ArrayUtil::getArrayColumn($movie_matchs, 'movie_id1');
        $movie_ids2 = ArrayUtil::getArrayColumn($movie_matchs, 'movie_id2');
        $movie_ids = ArrayUtil::mergeArray($movie_ids1, $movie_ids2);
        $movie_ids = ArrayUtil::toStringElements(array_unique($movie_ids));

        $movie_model = Movie::getInstance();
        $movies = $movie_model->getMap('movie_id', array(SqlBuilder::in($movie_ids, 'movie_id')));
    }
}