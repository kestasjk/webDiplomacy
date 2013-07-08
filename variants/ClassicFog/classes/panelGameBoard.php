<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Claccic-Fog-of-War variant for webDiplomacy

	The Claccic-Fog-of-War variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General Public
	License as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Claccic-Fog-of-War variant for webDiplomacy is distributed in the hope that 
	it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicFogVariant_panelGameBoard extends panelGameBoard
{

	// Load our custom map.php that revales only parts of the map
	function mapHTML() {

		if ($this->phase == 'Finished') return parent::mapHTML();
	
		global $User, $DB;
		$map=parent::mapHTML();

		if ($this->Members->isJoined()) {
			list($ccode)=$DB->sql_row("SELECT text FROM wD_Notices WHERE toUserID=3 AND timeSent=0 AND fromID=".$this->id);
			$verify=substr($ccode,((int)$this->Members->ByUserID[$User->id]->countryID)*6,6);
		} elseif ($User->type['Moderator']) {
			list($ccode)=$DB->sql_row("SELECT text FROM wD_Notices WHERE toUserID=3 AND timeSent=0 AND fromID=".$this->id);
			$verify=substr($ccode,0,6);
		} else {
			$verify='fog';
		}
		$mapTurn = (($this->phase=='Pre-game'||$this->phase=='Diplomacy') ? $this->turn-1 : $this->turn);
		$newFile = $oldFile = Game::mapFilename($this->id, $mapTurn, 'small');
		$newFile = str_replace(".map","-".$verify.".map",$newFile);
		if (!(file_exists($newFile)))
			$newFile='variants/ClassicFog/resources/fogmap.php?&verify='.$verify.'&gameID='.$this->id.'&turn='.$mapTurn;
		$oldMap="map.php?";
		$newMap="variants/ClassicFog/resources/fogmap.php?verify=".$verify."&";
		$map = str_replace($oldMap ,$newMap ,$map);
		$map = str_replace($oldFile,$newFile,$map);

		$map = str_replace("loadMap(","loadMap('".$verify."',",$map);
		$map = str_replace("loadMapStep(","loadMapStep('".$verify."',",$map);
		
		return $map;
	}
	
	// Load out custom map with the Javascript history-buttons too...
	protected function mapJS($mapTurn) {

		if ($this->phase == 'Finished') return parent::mapJS($mapTurn);

		libHTML::$footerScript[] = 'turnToText='.$this->Variant->turnAsDateJS()."mapArrows($mapTurn,$mapTurn);";
		libHTML::$footerIncludes[] = '../variants/ClassicFog/resources/my_mapUI.js';
	}
	
	// Show a small overview hao many players finalized in the vote-box
	function showVoteForm($vVote, $vCancel)
	{
		$ready= $none= $sav= $wait= 0;
		foreach ($this->Members->ByID as $member) {
			if     ($member->orderStatus->Ready) $ready++;
			elseif ($member->orderStatus->Saved) $sav++;
			else                                 $wait++;
		}
		$buf = parent::showVoteForm($vVote, $vCancel);
		$buf .= '</td> </tr>
			<td class="memberLeftSide"><strong>Status:</strong></td>
			<td class="memberRightSide">
				<strong>'.$sav  .'</strong> player'.($sav  ==1?'':'s').' saved - 
				<strong>'.$ready.'</strong> player'.($ready==1?'':'s').' finalized.
			</td><td><tr>';
		return $buf;
	}
	
	
}

