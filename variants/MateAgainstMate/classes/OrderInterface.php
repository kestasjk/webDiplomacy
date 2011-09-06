<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the MateAgainstMate variant for webDiplomacy

	The MateAgainstMate variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The MateAgainstMate variant for webDiplomacy is distributed in the hope that it
	will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MateAgainstMateVariant_OrderInterface extends OrderInterface {

	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		// Indonesia can only build on the "North Coast"
		if( ($this->phase=='Builds') && ($this->countryID == 1) ) {
			libHTML::$footerIncludes[] = '../variants/MateAgainstMate/resources/build_indonesia.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadOrdersPhase();'))
					libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase(); CustomBuild_Indonesia();', $script);
		}
		
	}

}

?>