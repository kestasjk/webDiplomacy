<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class OctopusVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'England' => array('Edinburgh'=>'Fleet', 'Liverpool'=>'Army', 'London'=>'Fleet'),
		'France'  => array('Brest'=>'Fleet', 'Paris'=>'Army', 'Marseilles'=>'Army'),
		'Italy'   => array('Venice'=>'Army', 'Rome'=>'Army', 'Naples'=>'Fleet'),
		'Germany' => array('Kiel'=>'Fleet', 'Berlin'=>'Army', 'Munich'=>'Army'),
		'Austria' => array('Vienna'=>'Army', 'Trieste'=>'Fleet', 'Budapest'=>'Army'),
		'Turkey'  => array('Smyrna'=>'Army', 'Ankara'=>'Fleet', 'Constantinople'=>'Army'),
		'Russia'  => array('Moscow'=>'Army', 'St. Petersburg (South Coast)'=>'Fleet', 'Warsaw'=>'Army', 'Sevastopol'=>'Fleet')
	);

}