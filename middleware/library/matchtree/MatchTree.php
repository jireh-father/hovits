<?php
namespace middleware\library\matchtree;

class MatchTree
{
	const TOP_DEPTH = 1;
	const HIGHEST_GRADE = 100;
	const LOWEST_GRADE = 40;

	/**
	 * @var MatchNode[]
	 */
	private $hash_list;
	private $depth_list;
	private $last_depth;
	private $total_match_cnt;

	public function __construct()
	{
		$this->last_depth = 0;
		$this->total_match_cnt = 0;
		$this->hash_list = array();
		$this->depth_list = array();
	}

	public function _getNode($movie_id)
	{
		return $this->isNode($movie_id) ? $this->hash_list[$movie_id] : null;
	}

	public function getLastDepth()
	{
		return $this->last_depth;
	}

	/**
	 * @param $depth_num
	 * @return MatchNode[]
	 */
	public function getListByDepth($depth_num)
	{
		return isset($this->depth_list[$depth_num]) ? $this->depth_list[$depth_num] : null;
	}

	public function isNode($movie_id)
	{
		return isset($this->hash_list[$movie_id]);
	}

	public function appendMatch(Match $match)
	{
		$parent_id = $match->getChosenId();
		$child_id = $match->getUnchosenId();

		if ($this->isNode($parent_id) === false && $this->isNode($child_id) === false) {
			//트리에 둘다 없을경우
			$parent_node = $this->_createdNode($parent_id);
			$child_node = $this->_createdNode($child_id);

			$this->_setDepth($parent_node);
			$this->_setDepth($child_node, self::TOP_DEPTH + 1);

		} elseif ($this->isNode($parent_id) === false || $this->isNode($child_id) === false) {
			//트리에 하나만 있을경우
			if ($this->isNode($parent_id) === true) {
				//부모가 존재할경우
				$parent_node = $this->_getNode($parent_id);
				$child_node = $this->_createdNode($child_id);

				$this->_setDepth($child_node, $parent_node->getDepth() + 1);
			} else {
				//자식이 존재할경우
				$parent_node = $this->_createdNode($parent_id);
				$child_node = $this->_getNode($child_id);

				$this->_setDepth($parent_node);

				//자식과 그밑에 있는놈들의 depth를 모두 조정
				if ($child_node->getDepth() <= $parent_node->getDepth()) {
					$this->_setDepthRecursive($child_node, $parent_node->getDepth() + 1);
				}
			}
		} else {
			//트리에 둘다 존재할경우
			$parent_node = $this->_getNode($parent_id);
			$child_node = $this->_getNode($child_id);
			$path = array();
			if ($this->_isInParents($child_node, $parent_node, $path) === true) {
				$reverse_match_list = array();
				foreach($path as $path_node_id => $is_path_node){
					if(in_array($path_node_id , $parent_node->getParentIdList())){
						$reverse_match_list[] = $parent_node->getMatchId($path_node_id);
					}
				}
				return $reverse_match_list;
			} else {
				//자식과 그밑에 있는놈들의 depth를 모두 조정
				//부모와 자식만 연결
				if ($child_node->getDepth() <= $parent_node->getDepth()) {
					$this->_setDepthRecursive($child_node, $parent_node->getDepth() + 1);
				}
			}
		}
		$this->_link($child_node, $parent_node, $match->getMatchId());
		$this->total_match_cnt++;

		return true;
	}

	/**
	 * 대결 트리의 각 노드의 점수 계산
	 * A = 트리의 전체 깊이    $total_depth
	 * B = 트리의 전체 전적    $total_match_cnt
	 * C = 최고점수     self::HIGHEST_GRADE
	 * D = 최저점수     self:LOWEST_GRADE
	 * E = 깊이별 기준 점수 : ( C - D ) / A    $score_per_depth
	 * F = 전적별 기준 점수 : ( E / 2 ) / B    $score_per_match
	 * G = 각 깊이의 가중치 : ( A + 1 ) - 각 노드의 깊이     $weight_per_depth
	 * H = 각 깊이의 최저 점수 : D + ( ( G - 1 ) * E )      $lowest_score_per_depth
	 * I = 각 깊이의 평균 점수 : H + ( E / 2)               $avg_score_per_depth
	 * X = 각 노드의 점수 : I + ( 각 노드의 승리 횟수 * F ) - ( 각 노드의 패배 횟수 * F)  $node_score
	 * @param array $score_set [node_id][score, cnt]
	 */
	public function grading(array &$score_set)
	{
		$total_depth = $this->last_depth;
		$total_match_cnt = $this->total_match_cnt;
		$score_per_depth = (self::HIGHEST_GRADE - self::LOWEST_GRADE) / $total_depth;
		$score_per_match = ($score_per_depth / 2) / $total_match_cnt;

		foreach ($this->hash_list as $node) {
			$weight_per_depth = ($total_depth + 1) - $node->getDepth();
			$lowest_score_per_depth = self::LOWEST_GRADE + (($weight_per_depth - 1) * $score_per_depth);
			$avg_score_per_depth = $lowest_score_per_depth + ($score_per_depth / 2);
			$node_score = $avg_score_per_depth + (count($node->getChildIdList()) * $score_per_match) - (count(
						$node->getParentIdList()
					) * $score_per_match);
			$node_id = $node->getId();
			if (isset($score_set[$node_id])) {
				$old_score = $score_set[$node_id]['score'];
				$old_cnt = $score_set[$node_id]['cnt'];
				$new_cnt = $old_cnt + 1;
				$score_set[$node_id]['score'] = (($old_score * $old_cnt) + $node_score) / $new_cnt;
				$score_set[$node_id]['cnt'] = $new_cnt;
			} else {
				$score_set[$node_id] = array();
				$score_set[$node_id]['score'] = $node_score;
				$score_set[$node_id]['cnt'] = 1;
			}
		}
	}

	public function display()
	{
		$last_depth = $this->getLastDepth();
		for ($i = 1; $i <= $last_depth; $i++) {
			$depth_list = $this->getListByDepth($i);
			foreach ($depth_list as $node) {
				$node_text = '<span style="margin-right: 15px;">';
				$node_text .= ($node->getId() . ((count($node->getParentIdList()) > 0) ? ('(' . implode(
							',',
							$node->getParentIdList()
						) . ')') : '') . '</span>');
				echo $node_text;
			}
			echo '</br></br>';
		}
	}

	/**
	 * @param $id
	 * @return MatchNode
	 */
	private function _createdNode($id)
	{
		$this->hash_list[$id] = new MatchNode($id);

		return $this->hash_list[$id];
	}

	private function _link(MatchNode $from_node, MatchNode $to_node, $match_id)
	{
		if ($from_node === null || $to_node === null) {
			throw new MatchTreeException('node is null: 노드를 링크할 때는 node가 필요합니다.');
		}

		$to_node->addChild($from_node);
		$from_node->addParent($to_node);

		$to_node->setMatch($from_node->getId(), $match_id);
		$from_node->setMatch($to_node->getId(), $match_id);
	}

	private function _addToDepthList(MatchNode $node, $new_depth)
	{
		if (($old_depth = $node->getDepth()) > 0) {
			unset($this->depth_list[$old_depth][$node->getId()]);
		}
		if (!isset($this->depth_list[$new_depth])) {
			$this->depth_list[$new_depth] = array();
		}
		$this->depth_list[$new_depth][$node->getId()] = $node;
	}

	private function _setDepth(MatchNode $node, $depth = self::TOP_DEPTH)
	{
		if ($depth < 1) {
			return;
		}
		$this->_addToDepthList($node, $depth);
		$node->setDepth($depth);

		if ($this->last_depth < $depth) {
			$this->last_depth = $depth;
		}
	}

	private function _setDepthRecursive(MatchNode $node, $depth)
	{
		if ($depth < 1) {
			return;
		}

		$this->_setDepth($node, $depth);

		if (count($node->getChildIdList()) < 1) {
			return;
		}

		foreach ($node->getChildIdList() as $child_node_id) {
			$this->_setDepthRecursive($this->_getNode($child_node_id), $depth + 1);
		}
	}


	private function _isInParents(MatchNode $target_node, MatchNode $source_node, array &$path)
	{
		$parent_nodes = $source_node->getParentIdList();
		if (count($parent_nodes) < 1) {
			return false;
		}
		if (in_array($target_node->getId(), $parent_nodes) === true) {
			return true;
		} else {
			foreach ($parent_nodes as $parent_node_id) {
				$path[$parent_node_id] = true;
				if ($this->_isInParents($target_node, $this->_getNode($parent_node_id), $path) === true) {
					return true;
				}else{
					unset($path[$parent_node_id]);
				}
			}
		}

		return false;
	}
}

class MatchNode
{
	private $id;
	private $depth;
	private $parent_id_list;
	private $child_id_list;
	private $match_list;

	public function __construct($id)
	{
		$this->id = $id;
		$this->parent_id_list = array();
		$this->child_id_list = array();
		$this->match_list = array();
		$this->depth = 0;
	}

	public function setDepth($depth)
	{
		$this->depth = $depth;
	}

	public function getDepth()
	{
		return $this->depth;
	}

	public function getParentIdList()
	{
		return $this->parent_id_list;
	}

	public function getChildIdList()
	{
		return $this->child_id_list;
	}

	public function getId()
	{
		return $this->id;
	}

	public function addChild(MatchNode $node)
	{
		$this->child_id_list[] = $node->getId();
	}

	public function addParent(MatchNode $node)
	{
		$this->parent_id_list[] = $node->getId();
	}

	public function getMatchId($opponent_node_id){
		return $this->match_list[$opponent_node_id];
	}

	public function setMatch($opponent_node_id, $match_id){
		$this->match_list[$opponent_node_id] = $match_id;
	}
}
