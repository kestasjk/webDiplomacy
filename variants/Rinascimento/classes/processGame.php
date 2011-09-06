<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class RinascimentoVariant_processGame extends processGame {

	// 1 neutral player not needed to process the game
	public function needsProcess()
	{
		$a=array_pop($this->Variant->countries);
		$ret=parent::needsProcess();
		array_push($this->Variant->countries,$a);
		return $ret;
	}
	
	public function isJoinable()
	{
		$a=array_pop($this->Variant->countries);
		$ret=parent::isJoinable();
		array_push($this->Variant->countries,$a);
		return $ret;
	}		
	
	function process()
	{
		global $DB;

		// Set neutral player to "Playing" bevore processing
		$DB->sql_put("UPDATE wD_Members SET status='Playing' WHERE gameID=".$this->id." AND userID=3");

		parent::process();
		
		// Set "neutral player" as defeated, so we don't need to wait for his orders and votes
		$DB->sql_put("UPDATE wD_Members SET status='Defeated', missedPhases=0 WHERE gameID=".$this->id." AND userID=3");
		
	}
		
}

?>