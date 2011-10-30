<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicOctopusVariant_drawMap extends drawMap {

	/**
	 * An array of colors for different countries, indexed by countryID
	 * @var array
	 */
	protected $countryColors = array(
		0 => array(226, 198, 158), /* Neutral */
		1 => array(239, 196, 228), /* England */
		2 => array(121, 175, 198), /* France  */
		3 => array(164, 196, 153), /* Italy   */
		4 => array(160, 138, 117), /* Germany */
		5 => array(196, 143, 133), /* Austria */
		6 => array(234, 234, 175), /* Turkey  */
		7 => array(168, 126, 159)  /* Russia  */
	);

	/**
	 * Resources, all required except names, which will be drawn on by the computer if not supplied.
	 * @return array[$resourceName]=$resourceLocation
	 */
	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/ClassicOctopus/resources/smallmap.png',
				'army'=>'contrib/smallarmy.png',
				'fleet'=>'contrib/smallfleet.png',
				'names'=>'variants/ClassicOctopus/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/ClassicOctopus/resources/map.png',
				'army'=>'contrib/army.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/ClassicOctopus/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
}

?>