<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class NeutralMember
{
	public $supplyCenterNo;
	public $unitNo;
}

class NeutralUnits_processMembers extends processMembers
{
	// bevore the processing add a minimal-member-object for the "neutral  player"
	function countUnitsSCs()
	{
		$this->ByCountryID[count($this->Game->Variant->countries)+1] = new NeutralMember();
		parent::countUnitsSCs();
		unset($this->ByCountryID[count($this->Game->Variant->countries)+1]);
	}
}

class AfricaVariant_processMembers extends NeutralUnits_processMembers {}

?>
