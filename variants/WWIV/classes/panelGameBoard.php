<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the WWIV variant for webDiplomacy

	The WWIV variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The WWIV variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');
	
require_once('board/chatbox.php');

class ChatMember
{
	function memberCountryName() { return ""; }
	function memberNameCountry() { return ""; }
	public $online = 0;
}

class PreGameChat_panelGameBoard extends panelGameBoard
{
	function mapHTML()
	{
		$html = parent::mapHTML();
		
		if ($this->phase == 'Pre-game')
		{	
			global $DB, $User;
		
			$html .= '<div class="hr"></div>';
			
			for($countryID=1; $countryID<=count($this->Variant->countries); $countryID++)
				$this->Members->ByCountryID[$countryID] = new ChatMember();

			$CB = $this->Variant->Chatbox();

			if( isset($_REQUEST['newmessage']) AND $_REQUEST['newmessage']!="")
			{
				$_REQUEST['newmessage'] = "(".$User->username."): ".$_REQUEST['newmessage'];
				$CB->postMessage(0);
				$DB->sql_put("COMMIT");
			}
			
			$chat = $CB->output(0);
			$chat = preg_replace('-<div id="chatboxtabs".*</div>-',"",$chat);
			$chat = preg_replace('-<div class="chatboxMembersList">.*</div>-',"",$chat);

			$html .= $chat;
			unset($CB);

			libHTML::$footerScript[] = 'makeFormsSafe();';
			
		}	
		return $html;
	}
}

class ZoomMap_panelGameBoard extends PreGameChat_panelGameBoard
{
	function mapHTML() {
		$mapTurn = (($this->phase=='Pre-game'||$this->phase=='Diplomacy') ? $this->turn-1 : $this->turn);
		$mapLink = 'map.php?gameID='.$this->id.'&turn='.$mapTurn.'&mapType=large';

		$html = parent::mapHTML();
		
		$old = '/img id="mapImage" src="(\S*)" alt=" " title="The small map for the current phase. If you are starting a new turn this will show the last turn\'s orders" \/>/';
		$new = 'iframe id="mapImage" src="'.$mapLink.'" alt=" " width="750" height="403"> </iframe>';
		
		$html = preg_replace($old,$new,$html);
		
		return $html;
	}
}

class WWIVVariant_panelGameBoard extends ZoomMap_panelGameBoard {}

