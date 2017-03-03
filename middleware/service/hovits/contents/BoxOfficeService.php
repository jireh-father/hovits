<?php
namespace middleware\service\hovits\contents;

use framework\library\sql_builder\SqlBuilder;
use framework\library\Time;
use middleware\model\Image;
use middleware\model\Movie;
use middleware\model\RealtimeBoxoffice;
use middleware\service\contents\Contents;

/**
 * 설명
 * @package
 * @author 서일근 <igseo@simplexi.com>
 * @version 1.0
 * @since 2016. 04. 20
 */
class BoxOfficeService
{
    const LIMIT_GRADE_COUNT = 100;
    const LISTING_COUNT = 30;

    private static function _getTotalGradePointExpr()
    {
        $expr = '(';
        $expr_list = array();
        foreach (Contents::$content_provider_list as $provider) {
            if ($provider === CONTENTS_PROVIDER_KOFIC) {
                continue;
            }
            $expr_list[] = "({$provider}_grade_point * {$provider}_grade_count)";
        }
        $expr .= implode(' + ', $expr_list);
        $expr .= ')';

        return $expr;
    }

    private static function _getTotalGradeCountExpr()
    {
        $expr = '(';
        $expr_list = array();
        foreach (Contents::$content_provider_list as $provider) {
            if ($provider === CONTENTS_PROVIDER_KOFIC) {
                continue;
            }
            $expr_list[] = "{$provider}_grade_count";
        }
        $expr .= implode(' + ', $expr_list);
        $expr .= ')';

        return $expr;
    }

    protected function _getAvgTotalPointExpr($total_grade_point_expr, $total_grade_count_expr)
    {
        //    " (
        //                        total_ticket_count /
        //                        (
        //                            select
        //                                max(total_ticket_count)
        //                            from
        //                                realtime_boxoffice
        //                        )
        //                        * 100 * 0.24
        //                    ) +"

        $limit_grade_count = self::LIMIT_GRADE_COUNT;

        return "(
                    (
                        (total_ticket_count / DATEDIFF(current_date, release_date)) /
                        (
                            select
                                max(total_ticket_count) / DATEDIFF(current_date, release_date) max_ticket_count_per_day
                            from
                                realtime_boxoffice
                                    join
                                movie ON realtime_boxoffice.movie_id = movie.movie_id
                            group by total_ticket_count
                            order by max_ticket_count_per_day desc
                            limit 1
                        )
                        * 100 * 0.35
                    ) +
                    (
                        booking_ratio /
                        (
                            select
                                max(booking_ratio)
                            from
                                realtime_boxoffice
                        )
                        * 100 * 0.15
                    ) +
                    (
                        CASE WHEN {$total_grade_count_expr} > {$limit_grade_count} THEN {$total_grade_point_expr} / {$total_grade_count_expr} * 0.5
                        ELSE 0 END
                    )
                )";
    }

    public static function getList($sort, $limit = 30, $is_release_scheduled = false)
    {
        $box_office_model = RealtimeBoxoffice::getInstance();

        $today = Time::Ymd();

        $limit_grade_count = self::LIMIT_GRADE_COUNT;

        if ($is_release_scheduled) {
            $movie_join_stmt = SqlBuilder::join('movie', "realtime_boxoffice.movie_id = movie.movie_id and release_date > '{$today}'");
            $image_join = array('realtime_boxoffice', $movie_join_stmt, SqlBuilder::join('image', "realtime_boxoffice.movie_id = image.content_id"));
        } else {
            $movie_join_stmt = SqlBuilder::join('movie', "realtime_boxoffice.movie_id = movie.movie_id");
            $image_join = array('realtime_boxoffice', SqlBuilder::join('image', "realtime_boxoffice.movie_id = image.content_id"));
        }
        $movie_join = array('realtime_boxoffice', $movie_join_stmt);

        $total_grade_point_expr = self::_getTotalGradePointExpr();
        $total_grade_count_expr = self::_getTotalGradeCountExpr();
        $avg_total_point_expr = self::_getAvgTotalPointExpr($total_grade_point_expr, $total_grade_count_expr);
        $movie_cols = array(
            'movie.*',
            'realtime_boxoffice.*',
            'total_grade_point'        => $total_grade_point_expr,
            'total_grade_count'        => $total_grade_count_expr,
            'avg_grade_point'          => "{$total_grade_point_expr} / {$total_grade_count_expr}",
            'avg_grade_point_filter'   => "CASE WHEN {$total_grade_count_expr} > {$limit_grade_count} THEN {$total_grade_point_expr} / {$total_grade_count_expr} ELSE 0 END",
            //            'avg_total_point'          => $avg_total_point_expr,
            'avg_ticket_count_per_day' => 'total_ticket_count / DATEDIFF(current_date, release_date)'
        );
        $box_office_model->setTable($movie_join);
        $box_office_model->setSelectColumns($movie_cols);
        $movies = $box_office_model->getMap('movie_id', null, $sort, $limit);

        $box_office_model->setSelectColumns(null);
        $box_office_model->setTable($image_join);
        $image_list = $box_office_model->getMap('movie_id', array('content_type' => CONTENT_TYPE_MOVIE, 'image_type' => 'main'));

        $still_cut_list = $box_office_model->getMultiMap('movie_id', array('content_type' => CONTENT_TYPE_MOVIE, 'image_type' => 'still_cut'));

        return array($movies, $image_list, $still_cut_list);
    }

    public static function getScheduledList()
    {
        $movie_model = Movie::getInstance();

        $movie_model->setTable(
            array(
                'movie',
                SqlBuilder::join('realtime_boxoffice', 'movie.movie_id = realtime_boxoffice.movie_id', ' LEFT JOIN')
            )
        );
        $movie_model->setSelectColumns('movie.*, cgv_grade_point, booking_ratio, total_ticket_count');
        $movies = $movie_model->getMap(
            'movie_id',
            array(
                SqlBuilder::plainString(
                    "movie.release_date > CURRENT_DATE
                    and making_year > Year(CURDATE()) - 3 and making_status = '개봉예정'
                    and ( cgv_id is not null or lotte_id is not null or mega_id is not null  )"
                )
            ),
            'movie.release_date, cgv_grade_point desc, booking_ratio desc, total_ticket_count',
            self::LISTING_COUNT
        );
        $movie_ids = array_keys($movies);
        foreach ($movie_ids as $i => $moive_id) {
            $movie_ids[$i] = "{$moive_id}";
        }
        $image_model = Image::getInstance();
        $image_list = $image_model->getMap(
            'content_id',
            array(
                'content_type' => CONTENT_TYPE_MOVIE,
                'image_type'   => 'main',
                SqlBuilder::in($movie_ids, 'content_id')
            )
        );

        $still_cut_list = $image_model->getMultiMap(
            'content_id',
            array(
                'content_type' => CONTENT_TYPE_MOVIE,
                'image_type'   => 'still_cut',
                SqlBuilder::in($movie_ids, 'content_id')
            )
        );

        return array($movies, $image_list, $still_cut_list);
        //        "select * from movie left join realtime_boxoffice on movie.movie_id = realtime_boxoffice.movie_id
        //            where movie.release_date > CURRENT_DATE
        //            and making_year > Year(CURDATE()) - 3 and  making_status = '개봉예정'
        //            and ( cgv_id is not null or lotte_id is not null or mega_id is not null  )
        //            order by movie.release_date, cgv_grade_point desc, realtime_boxoffice.booking_ratio desc, realtime_boxoffice.total_ticket_count desc limit 30;"
    }
}