<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the GreekDip variant for webDiplomacy

	The GreekDip variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The GreekDip variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

// Unit-Icons in javascript-code
class NewIcons_OrderInterface extends OrderInterface
{
	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		libHTML::$footerIncludes[] = '../variants/GreekDip/resources/iconscorrect.js';
		foreach(libHTML::$footerScript as $index=>$script)
			libHTML::$footerScript[$index]=str_replace('loadOrdersModel();','loadOrdersModel();IconsCorrect();', $script);
	}
}

// New code for the Bidding-phase
class BiddingStart_OrderInterface extends NewIcons_OrderInterface
{
	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		if( $this->turn=='0' )
		{
			libHTML::$footerIncludes[] = '../variants/GreekDip/resources/phaseBet.js';
			foreach(libHTML::$footerScript as $index=>$script)
				libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase();Bidding();', $script);
		}
		
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
						AND tsa.turn=1
						AND tsa.gameID=".$Game->id." 
						AND tsa.countryID=".$this->countryID." 
						AND t.mapID=".$Game->Variant->mapID.") AS t
				ON (t.terrID=ts.terrID) 
					WHERE ts.gameID=".$Game->id." 
						AND ts.countryID=".$this->countryID);
			while(list($scid) = $DB->tabl_row($tabl))
				$scids .= '"'.$scid.'",';
			$scids=trim($scids,',');
			
			libHTML::$footerIncludes[] = '../variants/GreekDip/resources/supplycenterscorrect_BiddingStart.js';
			foreach(libHTML::$footerScript as $index=>$script)
				libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();SupplyCentersCorrect(Array('.$scids.'));', $script);
		}		
	}
}

class GreekDipVariant_OrderInterface extends BiddingStart_OrderInterface {}

