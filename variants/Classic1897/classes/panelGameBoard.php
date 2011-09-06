<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the 1897 variant for webDiplomacy

	The 1897 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The 1897 variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Classic1897Variant_panelGameBoard extends panelGameBoard
{
	/* The name of the game is 1897, because it starts in Autumn 1897 with a Build phase.
	|  Intern the implementation of the variant make a build-phase in Spring 1898, bevore
	|  the diplomacy phase. Just rename this phase (and the Pre-game) to "Winter 1987"
	*/
	function datetxt($turn = false)
	{
		if ($this->turn == 0) {
			if (($this->phase == 'Builds') || ($this->phase == 'Pre-game'))
				return 'Autumn, 1897';
		}
		return parent::datetxt($turn);
	}	
}

?>
