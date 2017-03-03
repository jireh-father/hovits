<?php
namespace middleware\library\recmd;

abstract class SimilaritySet
{
    /**
     * @var Similarity[]
     */
    private $similarity_algorithm_list;

    private $total_list;

    private $weight_multiplier;

    /**
     * @param Similarity[] $similarity_algorithm_list
     * @param array $total_list
     */
    public function __construct(array $similarity_algorithm_list, array $total_list = null)
    {
        $this->similarity_algorithm_list = $similarity_algorithm_list;
        $this->total_list = $total_list;
        $total_weight = 0;
        foreach ($this->similarity_algorithm_list as $algorithm){
            $total_weight += $algorithm->getWeight();
        }
        $this->weight_multiplier = 100 / $total_weight;
    }

    public function calc(array $target_matrix)
    {
        $similarity_matrix = array();
        foreach ($target_matrix as $key1 => $target1) {
            foreach ($target_matrix as $key2 => $target2) {
                if ($key1 == $key2) {
                    continue;
                }
                if (empty($similarity_matrix[$key1])) {
                    $similarity_matrix[$key1] = array();
                }
                $similarity_matrix[$key1][$key2] = $this->calcSimilarity($target1, $target2);
            }
        }
        return $similarity_matrix;
    }

    private function calcSimilarity(array $target1, array $target2)
    {
        $similarity = 0;
        foreach ($this->similarity_algorithm_list as $similarity_algorithm) {
            $weight_multiplier = $this->weight_multiplier * $similarity_algorithm->getWeight();
            $tmp_similarity = $similarity_algorithm->calc($target1, $target2, $this->total_list);
            $similarity += ($weight_multiplier * $tmp_similarity);
        }

        return $similarity;
    }
}