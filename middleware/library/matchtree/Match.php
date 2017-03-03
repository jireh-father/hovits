<?php
namespace middleware\library\matchtree;

class Match
{
	private $match_id;
	private $chosen_id;
	private $unchosen_id;

	public function __construct($match_id, $chosen_id, $unchosen_id)
	{
		$this->match_id = $match_id;
		$this->chosen_id = $chosen_id;
		$this->unchosen_id = $unchosen_id;
	}

	public function getMatchId()
	{
		return $this->match_id;
	}

	public function getChosenId()
	{
		return $this->chosen_id;
	}

	public function getUnchosenId()
	{
		return $this->unchosen_id;
	}
}
