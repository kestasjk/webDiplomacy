<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class EnlightenmentVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0  => array(226, 198, 158), // Neutral
		1  => array(168, 126, 159), // Russia
		2  => array(164, 196, 153), // Venice
		3  => array(196, 143, 133), // Austria
		4  => array(121, 175, 198), // France
		5  => array(160, 138, 117), // Prussia
		6  => array(234, 234, 175), // Turkey
		7  => array(239, 196, 228), // England
		8  => array(114, 146, 103), // Poland
		9  => array(120, 120, 120), // Sweden
		10  => array(206, 153, 103),// Spain
	);

	protected function resources()
	{
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Enlightenment/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/Enlightenment/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
				);
		}
		else
		{
			return array(
				'map'=>'variants/Enlightenment/resources/map.png',
				'army'=>'contrib/army.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/Enlightenment/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
}

?>