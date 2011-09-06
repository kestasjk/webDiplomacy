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

class Fog_panelMember extends panelMember
{
	// Hide the SC and units count
	function memberUnitSCCount()
	{
		global $User;
		if ($this->Game->phase == 'Finished') return parent::memberUnitSCCount();
		if ( ($this->userID == $User->id) || ( ($User->type['Moderator']) && (! $this->Game->Members->isJoined()) ) ) return parent::memberUnitSCCount();
		return '<span class="memberSCCount"><em>?</em> supply-centers,<em class="neutral">?</em> units</span>';
	}

	// Hide the progressBar
	function memberProgressBar()
	{
		global $User;
		if ($this->Game->phase == 'Finished') return parent::memberProgressBar();
		if ( ($this->userID == $User->id) || ( ($User->type['Moderator']) && (! $this->Game->Members->isJoined()) ) ) return parent::memberProgressBar();
		libHTML::$first=true;
		return '<table class="memberProgressBarTable"><tr>
				<td class="memberProgressBarRemaining '.libHTML::first().'" style="width:100%"></td>
				</tr></table>';
	}

	// Hide the BetWon
	function memberBetWon()
	{
		if ( $this->Game->phase == 'Finished' ) return parent::memberBetWon();
		if ( $this->Game->phase == 'Pre-game' ) return parent::memberBetWon();
		if ( $this->status == 'Won' ||
			($this->Game->potType == 'Points-per-supply-center' && ( $this->status == 'Survived' || $this->status == 'Drawn' ) )
			) return parent::memberBetWon();
		return 'Bet: <em>?</em>, worth: <em>?</em>';	
	}
	
	function lastLoggedInTxt()
	{
		global $User;
		if ( ($this->userID == $User->id) || ( ($User->type['Moderator']) && (! $this->Game->Members->isJoined()) ) ) return parent::lastLoggedInTxt();
		return '??';
	}	

	// Hide the Points-value from countries in CD
	function pointsValue()
	{
		global $User;
		if ( ($this->userID == $User->id) || ( ($User->type['Moderator']) && (! $this->Game->Members->isJoined()) ) ) return parent::pointsValue();
		return '??';
	}

	function memberFinalized()
	{
		global $User;
		if( $this->status!='Playing' ) return '';
		if ( ($this->userID == $User->id) || ( ($User->type['Moderator']) && (! $this->Game->Members->isJoined()) ) ) return parent::memberFinalized();
		return '<span class="member'.$this->id.'StatusIcon"><img src="variants/TenSixtySix/resources/question.png" alt="?" title="Unknown orderstatus" /></span>';
	}
}

class TenSixtySixVariant_panelMember extends Fog_panelMember {}

