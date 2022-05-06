<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class ColdWarVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), // Neutral
		1 => array(160,   1,  31), // USSR
		2 => array(1,    92, 135), // NATO
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/ColdWar/resources/smallmap.png',
				'army'=>'contrib/smallarmy.png',
				'fleet'=>'contrib/smallfleet.png',
				'names'=>'variants/ColdWar/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/ColdWar/resources/map.png',
				'army'=>'contrib/army.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/ColdWar/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
}

?>