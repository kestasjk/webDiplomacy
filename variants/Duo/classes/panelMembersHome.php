<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class DuoVariant_panelMembersHome extends panelMembersHome {

	// DElete the "neutral" player from the home-view
	function membersList()
	{
		$a=$this->Game->Variant->countries[2];
		unset ($this->Game->Variant->countries[2]);
	
		$ret=parent::membersList();

		$this->Game->Variant->countries[2]=$a;
		return $ret;
	}
		
}

?>