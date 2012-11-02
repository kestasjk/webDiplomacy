<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class CelticBritainVariant_drawMap extends drawMap {

	public function __construct($smallmap)
	{
		// Map is too big, so up the memory-limit
		parent::__construct($smallmap);
		if ( !$this->smallmap )
			ini_set('memory_limit',"32M");
	}

	protected $countryColors = array(
		0 => array(211, 249, 188),
		1 => array(255, 126, 000),
		2 => array(111, 049, 152),
		3 => array(000, 183, 239),
		4 => array(067, 072, 224),
		5 => array(034, 117, 076),
		6 => array(168, 230, 029),
		7 => array(254, 162, 176),
		8 => array(153, 000, 048),
			);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/CelticBritain/resources/smallmap.png',
				'army'    =>'variants/CelticBritain/resources/SmallArmy.png',
				'fleet'   =>'variants/CelticBritain/resources/SmallFleet.png',
				'names'   =>'variants/CelticBritain/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/CelticBritain/resources/map.png',
				'army'    =>'variants/CelticBritain/resources/Army.png',
				'fleet'   =>'variants/CelticBritain/resources/Fleet.png',
				'names'   =>'variants/CelticBritain/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}

?>