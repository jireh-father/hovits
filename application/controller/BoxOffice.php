<?php
namespace controller;

use middleware\service\hovits\contents\BoxOfficeService;

class BoxOffice extends Hovits
{
    public function index()
    {
        $this->redirect('/boxOffice/bookingRatio');
    }

    private function _list($sort, $is_release_scheduled = false)
    {
        list($movies, $image_list, $still_cut_list) = BoxOfficeService::getList($sort, $is_release_scheduled);

        self::_addJsDefault(false);

        self::setViewData(compact('movies', 'image_list', 'still_cut_list'));
        self::setView('boxoffice');
    }

    public function totalTicket()
    {
        $this->_list('total_ticket_count desc');
    }

    public function bookingRatio()
    {
        $this->_list('booking_ratio desc');
    }

    public function contentGrade()
    {
        $this->_list('avg_grade_point_filter desc, avg_grade_point desc, total_grade_count desc');
    }

    //    public function totalRank()
    //    {
    //        $this->_list('avg_total_point desc, avg_grade_point desc, total_grade_count desc, avg_ticket_count_per_day desc');
    //    }

    //    public function avgTicket()
    //    {
    //        $this->_list('avg_ticket_count_per_day desc');
    //    }

    public function releaseScheduled()
    {
        list($movies, $image_list, $still_cut_list) = BoxOfficeService::getScheduledList();

        self::_addJsDefault(false);

        self::setViewData(compact('movies', 'image_list', 'still_cut_list'));
        self::setView('boxoffice');
    }

    //    public function releaseScheduleDate()
    //    {
    //        $this->_list('release_date, booking_ratio desc', true);
    //    }
    //
    //    public function releaseScheduleBooking()
    //    {
    //        $this->_list('booking_ratio desc, release_date', true);
    //    }
    //
    //    public function releaseScheduleGrade()
    //    {
    //        $this->_list('avg_grade_point desc, total_grade_count desc, release_date, booking_ratio desc', true);
    //    }
}