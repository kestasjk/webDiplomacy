<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the ClassicVS variant for webDiplomacy

	The ClassicVS variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The ClassicVS variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

// Use the turn-1 SC's instead of the predefined ones (countryID is different each game)
class CustomCountries_OrderInterface extends OrderInterface
{
	protected function jsLoadBoard()
	{
		parent::jsLoadBoard();

		if( $this->phase=='Builds' )
		{
			global $DB, $Game;
			// Pass a list of possible SC's to the "Builds"...
			$scids="";
			$tabl = $DB->sql_tabl(
				"SELECT ts.terrID 
					FROM wD_TerrStatus ts 
					INNER JOIN (
						SELECT tsa.terrID FROM wD_TerrStatusArchive tsa 
						INNER JOIN wD_Territories t 
							ON (tsa.terrID=t.id) 
						WHERE t.supply='Yes' 
							AND tsa.turn=0
							AND tsa.gameID=".$Game->id." 
							AND tsa.countryID=".$this->countryID." 
							AND t.mapID=".$Game->Variant->mapID.") AS t
					ON (t.terrID=ts.terrID) 
				WHERE ts.gameID=".$Game->id." 
					AND ts.countryID=".$this->countryID);
			while(list($scid) = $DB->tabl_row($tabl))
				$scids .= '"'.$scid.'",';
			$scids=trim($scids,',');
			
			libHTML::$footerIncludes[] = '../variants/'.$Game->Variant->name.'/resources/scLock.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadBoard();') )
					libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();SupplyCentersCorrect(Array('.$scids.'));', $script);
		}		
	}
}

class ClassicVSVariant_OrderInterface extends CustomCountries_OrderInterface {}

