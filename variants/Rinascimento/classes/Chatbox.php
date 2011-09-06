<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class RinascimentoVariant_Chatbox extends Chatbox {

	// Delete "neutral" players chatbox...
	public function output ($msgCountryID)
	{
		global $Game;
		$a=array_pop($Game->Variant->countries);
		$ret=parent::output($msgCountryID);
		array_push($Game->Variant->countries,$a);
		return $ret;
	}
		
}

?>