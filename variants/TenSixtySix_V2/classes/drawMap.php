<?php
/*
	Copyright (C) 2012 Oliver Auth

	This file is part of the 1066 (V2.0) variant for webDiplomacy

	The 1066 (V2.0) variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The 1066 (V2.0) variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

include_once ("variants/TenSixtySix/classes/drawMap.php");

class TenSixtySix_V2Variant_drawMap extends TenSixtySixVariant_drawMap
{
	protected $sea_terrs = array(
		'Central North Sea' , 'Firth of Clyde' , 'North Atlantic Ocean' , 'Mid Atlantic Ocean' , 'Irish Sea',
		'Bristol Channel', 'North English Channel', 'Southwest North Sea', 'Strait of Dover',
		'Thames Estuary' , 'South English Channel', 'Northwest North Sea', 'Skagerrak',
		'Norwegian Sea'  , 'Northeast North Sea'  , 'Southeast North Sea', 'Baltic Sea',
		'Channel Islands', 'Shetland and Orkneys' , 'Heligoland Bight');

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map' =>'variants/TenSixtySix_V2/resources/smallmap.png',
				'army' =>'contrib/smallarmy.png',
				'fleet' =>'contrib/smallfleet.png',
				'names' =>'variants/TenSixtySix_V2/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map' =>'variants/TenSixtySix_V2/resources/map.png',
				'army' =>'contrib/army.png',
				'fleet' =>'contrib/fleet.png',
				'names' =>'variants/TenSixtySix_V2/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}

?>
