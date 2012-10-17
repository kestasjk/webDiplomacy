<?php
/*
	Copyright (C) 2012 Kaner406 / Oliver Auth

	This file is part of the Mars variant for webDiplomacy

	The Mars variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Mars variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class CustomStart_processGame extends processGame {

	protected function changePhase() {
		if( $this->phase == 'Pre-game' )
		{
			// Builds first after the game starts
			$this->setPhase('Builds');

			// This gives the map some color to start with
			$this->archiveTerrStatus();

			return false;
		}
		elseif( $this->phase == 'Builds' && $this->turn==0 )
		{
			// The first Spring builds just finished, make sure we don't go to the next turn

			$this->phase='Pre-game'; // This prevents a turn being added on in setPhase, keeping it in Spring, 2150
			// (It won't activate twice because the next time it won't go into a Builds phase in Spring)

			$this->setPhase('Diplomacy'); // Diplomacy, Spring 2150, and from then on like nothing was different

			$this->archiveTerrStatus();
			return false;
		}
		else
			return parent::changePhase(); // Except those two phases above behave normally
	}
}

class NeutralUnits_processGame extends CustomStart_processGame
{
	function process()
	{
		global $DB;
		parent::process();
		
		// If only the "neutral player has to do retreats process again.
		if ($this->phase == 'Retreats')
		{	
			list($count) = $DB->sql_row("SELECT COUNT(*)
				FROM wD_Members 
				WHERE orderStatus != 'None' AND gameID = ".$this->id);
			if ($count == 0)
				parent::process();
		}	
	}
}

class MarsVariant_processGame extends NeutralUnits_processGame {}
