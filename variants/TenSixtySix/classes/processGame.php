<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the 1066 variant for webDiplomacy

	The 1066 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The 1066 variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Fog_processGame extends processGame
{
	// After the game is finished delete the map images with hidden territories
	// New map-images with all visible will be created...
	protected function setPhase($phase, $gameOver='')
	{
		parent::setPhase($phase, $gameOver);
		if ( $this->phase == 'Finished' ) $this->wipeCache($this->id);
	}
}

class NeutralUnits_processGame extends Fog_processGame
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

class TenSixtySixVariant_processGame extends NeutralUnits_processGame {}

?>