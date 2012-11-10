<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class AfricaVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), // Neutral
		1 => array(0, 128, 192), // DRC
		2 => array(192, 160, 0), // Egypt
		3 => array(206, 153, 103), // Ethiopia
		4 => array(168, 126, 159), // Madagascar
		5 => array(224, 224, 0), // Mali
		6 => array(196, 143, 133), // Morocco
		7 => array(0, 128, 64), // Nigeria
		8 => array(64, 108, 128), // South Africa
		9 => array(160, 138, 117)  // Neutral units
	);

	public function __construct($smallmap)
	{
		// Map is too big, so up the memory-limit
		parent::__construct($smallmap);
		if ( !$this->smallmap )
			ini_set('memory_limit',"32M");
	}

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/Africa/resources/smallmap.png',
				'army'=>'contrib/smallarmy.png',
				'fleet'=>'contrib/smallfleet.png',
				'names'=>'variants/Africa/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/Africa/resources/map.png',
				'army'=>'contrib/army.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/Africa/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
}

?>