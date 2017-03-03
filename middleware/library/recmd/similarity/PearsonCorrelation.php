<?php
namespace middleware\library\recmd\similarity;

use middleware\library\recmd\Similarity;

class PearsonCorrelation extends Similarity
{
    public function calc(array $target1, array $target2, array $total_list = null)
    {
        $common_element_keys = array();
        $total_val1 = 0;
        $total_val2 = 0;
        $equal_cnt = 0;
        foreach ($target1 as $key => $element) {
            if (!empty($target2[$key])) {
                $common_element_keys[] = $key;
                $total_val1 += $element;
                $total_val2 += $target2[$key];
                if ($element == $target2[$key]) {
                    $equal_cnt++;
                }
            }
        }
        $avg_val1 = $total_val1 / count($common_element_keys);
        $avg_val2 = $total_val2 / count($common_element_keys);

        $dividend = 0;
        $divisor1 = 0;
        $divisor2 = 0;
        foreach ($common_element_keys as $key) {
            $val1 = $target1[$key];
            $val2 = $target2[$key];
            $dividend += (($val1 - $avg_val1) * ($val2 - $avg_val2));
            $divisor1 += (($val1 - $avg_val1) * ($val1 - $avg_val1));
            $divisor2 += (($val2 - $avg_val2) * ($val2 - $avg_val2));
        }
        $divisor = sqrt($divisor1) * sqrt($divisor2);

        if ($dividend == 0 || $divisor == 0) {
            $similarity = 0;
        } else {
            $similarity = $dividend / $divisor;
        }

        //Pearson 에러로 1점을 얻으면 0점 처리
        if ($similarity == 1 && count($common_element_keys) != $equal_cnt) {
            $similarity = 0;
        }

        return $similarity;
    }
}