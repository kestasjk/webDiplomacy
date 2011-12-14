<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicTouchyVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Austria' => array('Trieste'=>'Army', 'Budapest'=>'Army', 'Vienna'=>'Army'),
		'Germany' => array('Kiel'=>'Fleet', 'Munich'=>'Army', 'Berlin'=>'Army'),
		'Italy' => array('Naples'=>'Fleet', 'Rome'=>'Fleet', 'Venice'=>'Army'),
		'Russia' => array('Warsaw'=>'Army', 'Sevastopol'=>'Fleet', 'Moscow'=>'Army', 'St. Petersburg (South Coast)'=>'Fleet',),
		'Turkey' => array('Constantinople'=>'Fleet', 'Ankara'=>'Army', 'Smyrna'=>'Army'),
		'France' => array('Paris'=>'Army', 'Marseilles'=>'Army', 'Brest'=>'Fleet'),
		'England' => array('London'=>'Army', 'Edinburgh'=>'Fleet', 'Liverpool'=>'Fleet')
	);

}

?>
