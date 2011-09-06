<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class DuoVariant_panelMembers extends panelMembers {

	// Delete the "neutral" player from the playerlist
	public function membersList()
	{
		if (isset($this->ByUserID[3])) {
			unset($this->ByStatus[$this->ByUserID[3]->status][$this->ByUserID[3]->id]);
			$ret=parent::membersList();
			$this->ByStatus[$this->ByUserID[3]->status][$this->ByUserID[3]->id]=$this->ByUserID[3];
		} else {
			$ret=parent::membersList();
		}
		return $ret;
	}
	
}

?>