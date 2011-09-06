<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Germany1648Variant_panelMembersHome extends panelMembersHome {

	// Delete the "neutral" player from the home-view
	function membersList()
	{
		$a=array_pop($this->Game->Variant->countries);
		$ret=parent::membersList();
		array_push($this->Game->Variant->countries,$a);
		return $ret;
	}
		
}

?>