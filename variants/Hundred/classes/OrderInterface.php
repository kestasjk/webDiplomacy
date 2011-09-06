<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Hundred variant for webDiplomacy

	The Hundred variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Hundred variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class HundredVariant_OrderInterface extends OrderInterface {

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
		parent::jsLoadBoard();

		if( $this->phase=='Builds' )
		{
			// Expand the allowed SupplyCenters array to include non-home SCs.
			libHTML::$footerIncludes[] = '../variants/BuildAnywhere/resources/supplycenterscorrect.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadBoard();') )
					libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();SupplyCentersCorrect();', $script);
		}
	}

}