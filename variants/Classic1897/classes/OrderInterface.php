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

class Classic1897Variant_OrderInterface extends OrderInterface {

	/**
	 * Call the parent constructor transparently to keep things working
	 */
	public function __construct($gameID, $variantID, $userID, $memberID, $turn, $phase, $countryID,
		setMemberOrderStatus $orderStatus, $tokenExpireTime, $maxOrderID=false)
	{
		parent::__construct($gameID, $variantID, $userID, $memberID, $turn, $phase, $countryID,
			$orderStatus, $tokenExpireTime, $maxOrderID);
	}

	protected function jsLoadBoard() {

		global $DB, $Game;
		$set_sc_after_turn = 4;
		
		parent::jsLoadBoard();

		if( $this->phase=='Builds' )
		{
			// Pass a list of possible SC's to the Build-Army option...
			$scids="";
			if ($this->turn >= $set_sc_after_turn) {
				$tabl = $DB->sql_tabl(
				"SELECT ts.terrID 
					FROM wD_TerrStatus ts 
					INNER JOIN (
						SELECT tsa.terrID FROM wD_TerrStatusArchive tsa 
						INNER JOIN wD_Territories t 
							ON (tsa.terrID=t.id) 
						WHERE t.supply='Yes' 
							AND tsa.turn=".$set_sc_after_turn." 
							AND tsa.gameID=".$Game->id." 
							AND tsa.countryID=".$this->countryID." 
							AND t.mapID=".$Game->Variant->mapID.") AS t
					ON (t.terrID=ts.terrID) 
						WHERE ts.gameID=".$Game->id." 
							AND ts.countryID=".$this->countryID);

				while(list($scid) = $DB->tabl_row($tabl))
					$scids .= '"'.$scid.'",';
				$scids=trim($scids,',');
			}
			// Expand the allowed SupplyCenters array to include non-home SCs.
			libHTML::$footerIncludes[] = '../variants/Classic1897/resources/supplycenterscorrect1897.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadBoard();') )
					libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();SupplyCentersCorrect(Array('.$scids.'));', $script);
		}
	}


}

?>