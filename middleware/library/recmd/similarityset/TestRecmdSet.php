<?php
namespace middleware\library\recmd\similarityset;

use middleware\library\recmd\similarity\Jaccard;
use middleware\library\recmd\similarity\PearsonCorrelation;
use middleware\library\recmd\SimilaritySet;

class TestRecmdSet extends SimilaritySet{
    public function __construct(){
        $recmd_algorithm_list = array();
        $recmd_algorithm_list[] = new PearsonCorrelation(15);
        $recmd_algorithm_list[] = new Jaccard(85);
        parent::__construct($recmd_algorithm_list);
    }

}