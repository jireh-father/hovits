<?php

namespace middleware\library\recmd;

abstract class Similarity
{
    private $weight;

    public function __construct($weight)
    {
        $this->weight = $weight;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    abstract public function calc(array $target1, array $target2, array $total_list = null);
}