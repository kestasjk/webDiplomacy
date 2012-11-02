<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class CelticBritainVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Brigantes' => array('N.Brigantes'=>'Army', 'Selgovae'=>'Army', 'Votadini'=>'Fleet'),
		'Iceni' => array('Iceni'=>'Army', 'Trinovantes'=>'Army', 'Cantiaci'=>'Fleet'),
		'Caledonii' => array('Venicones'=>'Army', 'Taexali (East Coast)'=>'Fleet', 'Vacomagi'=>'Army', 'Caledonii (South Coast)'=>'Fleet'),
		'Picts' => array('Skitis'=>'Army', 'Limnu'=>'Fleet', 'Carini'=>'Fleet'),
		'Cornovii' => array('Ordovices'=>'Fleet', 'N.Cornovii'=>'Army', 'S.Cornovii'=>'Army', 'Deceangli'=>'Army'),
		'Ivernia' => array('Auteini'=>'Army', 'Menapii'=>'Fleet', 'Velabri'=>'Fleet', 'Iverni'=>'Army'),
		'Voluntii' => array('Erdin'=>'Fleet', 'Darini'=>'Army', 'Voluntii'=>'Fleet', 'Vennicnii'=>'Fleet'),
		'Durotriges' => array('Durotriges'=>'Army', 'Dumnonii'=>'Fleet', 'Belgae'=>'Fleet'),

		);

}

?>