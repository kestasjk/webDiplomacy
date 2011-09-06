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

class Fog_panelMembers extends panelMembers
{
	// If the game is going on make an even occupation bar for all players to hide the strenght of the players
	function occupationBar()
	{
		if ( isset($this->occupationBarCache)) return $this->occupationBarCache;

		if ($this->Game->phase == 'Finished') return parent::occupationBar();
		if( $this->Game->phase == 'Pre-game' ) return parent::occupationBar();
		
		$buf = '';

		$fixed_width=100/count($this->ByStatus['Playing']);
		
		foreach($this->ByStatus['Playing'] as $member)
				$buf .= '<td class="occupationBar'.$member->countryID.' '.libHTML::first().'" style="width:'.$fixed_width.'%"></td>';

		$this->occupationBarCache = '<table class="occupationBarTable"><tr>'.$buf.'</tr></table>';

		return $this->occupationBarCache;
	}

	// Sort the memberlist by alphabet instead of point-value
	function membersList()
	{
		if( $this->Game->phase != 'Pre-game')
		{
			$membersList=array();
			foreach($this->ByStatus['Playing'] as $Member)
				$membersList[$Member->country] = $Member;
			ksort ($membersList);
			$this->ByStatus['Playing'] = $membersList;
		}			
		return parent::membersList();
	}
	

}

class TenSixtySixVariant_panelMembers extends Fog_panelMembers {}

?>