<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class RinascimentoVariant_panelMember extends panelMember {

	function pointsValue()
	{
		return round($this->Game->Variant->PotShare($this) * $this->Game->pot);
	}
}

?>