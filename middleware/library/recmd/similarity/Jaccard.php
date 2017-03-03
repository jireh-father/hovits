<?php
namespace middleware\library\recmd\similarity;

use middleware\library\recmd\Similarity;

class Jaccard extends Similarity
{
    public function calc(array $target1, array $target2, array $total_list = null)
    {
        $common_element_keys = array();
        foreach ($target1 as $key => $element) {
            if (!empty($target2[$key])) {
                $common_element_keys[] = $key;
            }
        }

        $dividend = 0;
        $divisor = 0;
        foreach ($common_element_keys as $key) {
            $val1 = $target1[$key];
            $val2 = $target2[$key];
            $dividend += (($val1 < $val2) ? $val1 : $val2);
            $divisor += (($val1 < $val2) ? $val2 : $val1);
        }

        $similarity = $dividend / $divisor;

        return $similarity;
    }
}