<?php
/*
	Copyright (C) 2012 Mikalis Kamaritis / Oliver Auth

	This file is part of the Europe 1939 variant for webDiplomacy

	The Europe 1939 variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Europe 1939 variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Europe1939Variant_drawMap extends drawMap {

	protected $countryColors = array(
		0  => array(226, 198, 158), /* Neutral   */
		1  => array(239, 196, 228), /* Britain */
		2  => array(121, 175, 198), /* France */
		3  => array(160, 138, 117), /* Germany */
		4  => array(206, 153, 103), /* Spain */
		5  => array(164, 196, 153), /* Italy */
		6  => array(196, 143, 133), /* Poland */
		7  => array(168, 126, 159), /* USSR */
		8  => array(234, 234, 175), /* Turkey */
		9  => array(120, 120, 120), /* Neutral Units*/
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Europe1939/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/Europe1939/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Europe1939/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/Europe1939/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}

?>