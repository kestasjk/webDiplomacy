<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class NorthSeaWarsVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Briton' => array('South Britanny'=>'Fleet', 'Cymru'=>'Army'),
		'Roman' => array('Menapia'=>'Fleet', 'Germania Superior'=>'Army'),
		'Frysian' => array('Frisia'=>'Fleet', 'Amsivaria'=>'Army'),
		'Norse' => array('Sorland'=>'Fleet', 'Gotaland'=>'Army'),
	);

}

?>
