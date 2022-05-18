<?php
defined('IN_CODE') or die('This script can not be run by itself.');

class ColdWarVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'USSR' => array('Moscow'  =>'Army', 'Leningrad (South Coast)' =>'Fleet', 'Albania'=>'Fleet', 'Havana'=>'Fleet', 'Shanghai'=>'Army', 'Vladivostok'=>'Army'),
		'NATO'   => array('New York'=>'Army', 'Los Angeles'   =>'Army', 'Paris'=>'Army', 'London'=>'Fleet', 'Istanbul'=>'Fleet', 'Australia'=>'Fleet')
	);

}