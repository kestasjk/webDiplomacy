<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Germany1648Variant_panelGameBoard extends panelGameBoard {

	// 1 neutral player not needed to process the game
	public function needsProcess()
	{
		$a=array_pop($this->Variant->countries);
		$ret=parent::needsProcess();
		array_push($this->Variant->countries,$a);
		return $ret;
	}
	
	// disallow joining with 1 player less
	public function isJoinable()
	{
		$a=array_pop($this->Variant->countries);
		$ret=parent::isJoinable();
		array_push($this->Variant->countries,$a);
		return $ret;
	}
		
}

?>