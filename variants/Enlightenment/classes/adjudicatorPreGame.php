<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class EnlightenmentVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Russia'  => array('Moscow'=>'Army', 'Novgorod'=>'Army', 'Volga'=>'Army'),
		'Venice' => array ('Istria'=>'Fleet', 'Peloponnese'=>'Fleet', 'Venice'=>'Army'),
		'Austria' => array('Vienna'=>'Army', 'Prague'=>'Army', 'Budapest'=>'Army'),
		'France' => array('Paris'=>'Army', 'Lyons'=>'Army', 'Brest'=>'Fleet', 'Marseilles'=>'Army'),
		'Prussia' => array('Berlin'=>'Army', 'Stettin'=>'Army', 'Konigsberg'=>'Fleet'),
		'Turkey' => array('Constantinople'=>'Army', 'Ankara'=>'Fleet', 'Cairo'=>'Army', 'Belgrade'=>'Army'),
		'England' => array('Liverpool'=>'Army', 'Edinburgh'=>'Fleet', 'Amsterdam'=>'Army', 'London'=>'Fleet'),
		'Poland' => array('Warsaw'=>'Army', 'Dresden'=>'Army', 'Krakow'=>'Army'),
		'Sweden' => array('Stockholm'=>'Army', 'Riga'=>'Army', 'Abo'=>'Fleet'),
		'Spain' => array('Madrid'=>'Army', 'Sevilla'=>'Fleet', 'Naples'=>'Fleet', 'Flanders'=>'Army'),
	);
}

?>
