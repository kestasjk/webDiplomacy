<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class DuoVariant_Chatbox extends Chatbox {

	// Delete "neutral" players chatbox...
	public function output ($msgCountryID)
	{
		global $Game;
		$a=$Game->Variant->countries[2];
		unset ($Game->Variant->countries[2]);
		
		$ret=parent::output($msgCountryID);
		
		$Game->Variant->countries[2]=$a;
		return $ret;
	}
		
}

?>