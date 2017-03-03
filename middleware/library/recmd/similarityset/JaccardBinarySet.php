<?php
namespace middleware\library\recmd\similarityset;

use middleware\library\recmd\similarity\JaccardBinary;
use middleware\library\recmd\SimilaritySet;

class JaccardBinarySet extends SimilaritySet
{
    public function __construct($matrix = null)
    {
        $recmd_algorithm_list = array();
        $recmd_algorithm_list[] = new JaccardBinary(100);
        parent::__construct($recmd_algorithm_list);
    }
}