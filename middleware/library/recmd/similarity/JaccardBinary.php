<?php
namespace middleware\library\recmd\similarity;

use middleware\library\recmd\Similarity;

class JaccardBinary extends Similarity
{
    public function calc(array $target1, array $target2, array $total_list = null)
    {
        $dividend = 0;
        foreach ($target1 as $key => $element) {
            if (!empty($target2[$key])) {
                $dividend++;
            }
        }

        $divisor = count($target1 + $target2);

        $similarity = $dividend / $divisor;

        return $similarity;
    }
}