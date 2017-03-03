<?php
namespace middleware\library\matchtree;

class MatchTreeManager
{
	/**
	 * @var MatchTree[]
	 */
	private $match_tree_list;

	public function __construct()
	{
		$this->match_tree_list = array();
	}

	public function test()
    {
    }


	private function _addMatchTree(MatchTree $match_tree)
	{
		$this->match_tree_list[] = $match_tree;
	}

	public function createMatchList(array $matches)
	{
		$match_list = array();
		foreach ($matches as $match) {
			$match_list[$match['movie_match_id']] = new Match($match['movie_match_id'], $match['selected_movie_id'], $match['unselected_movie_id']);
		}

		return $match_list;
	}

	public function buildMatchTree(array $match_list)
	{
		$original_match_list = $match_list;

		$match_tree = new MatchTree();
		$reverse_match_list = array();
		while (count($match_list) > 0) {
			$match = array_shift($match_list);
			//TODO: return으로 역행되는 원인이 되는 모든 배열들을 함께 리턴하도록
			$ret = $match_tree->appendMatch($match);
			if($ret !== true && !empty($ret)){
				$reverse_match_list[] = $ret;
			}
		}
		$this->_addMatchTree($match_tree);

		if (empty($reverse_match_list)) {
			return;
		}

		foreach ($reverse_match_list as $reverse_match_id_list) {
			$new_match_list = $original_match_list;
			foreach ($reverse_match_id_list as $reverse_match_id) {
				unset($new_match_list[$reverse_match_id]);
			}
			$this->buildMatchTree($new_match_list);
		}
	}

	public function grading()
	{
		$score_set = array();
		foreach ($this->match_tree_list as $match_tree) {
			$match_tree->grading($score_set);
		}
		$sorted_score_set = array();
		foreach($score_set as $node_id => $score){
			$sorted_score_set[$node_id] = $score['score'];
		}
		return $sorted_score_set;
	}

	public function display()
	{
		foreach ($this->match_tree_list as $key => $match_tree) {
			echo "<div>TREE {$key}</div>";
			$match_tree->display();
		}
	}
}
