<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class DuoVariant_panelGameBoard extends panelGameBoard {

	public function __construct($gameData)
	{
		parent::__construct($gameData);
	}

	// 2 players are enough in Pregame to start the game:
	public function needsProcess()
	{
		if ($this->phase=='Pre-game' && count($this->Members->ByID)==2 && $this->phaseMinutes>30 )
			return true;
		return parent::needsProcess();
	}
	
}

?>