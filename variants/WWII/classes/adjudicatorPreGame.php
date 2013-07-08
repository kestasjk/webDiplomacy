<?php
defined('IN_CODE') or die('This script can not be run by itself.');

class WWIIVariant_adjudicatorPreGame extends adjudicatorPreGame
{

	protected $countryUnits = array(
		'France'       => array('Marseille'=>'Fleet', 'Brest'   =>'Fleet', 'Bordeaux'=>'Army',  'Toulouse'=>'Army', 'Paris'=>'Army', 'Orleans'=>'Army', 'Algiers'=>'Army'),
		'Germany'      => array('Kiel'=>'Fleet', 'Munich' =>'Army', 'Berlin'=>'Army', 'Konigsberg'=>'Fleet', 'Hannover'=>'Army', 'Breslau'=>'Army','Frankfurt'=>'Army'),
		'Britain'=> array('Alexandria'=>'Fleet', 'Inverness'=>'Fleet', 'Liverpool'=>'Fleet', 'Newcastle'=>'Fleet', 'Gibraltar'=>'Fleet', 'Palestine'=>'Army', 'London'=>'Army'),
		'Italy'        => array('Sicily'=>'Fleet', 'Rome (South Coast)' =>'Fleet', 'Sardinia'=>'Fleet', 'Naples (North Coast)'=>'Fleet', 'Milan'=>'Army', 'Trieste'=>'Army', 'Tripoli'=>'Army'),
		'Soviet Russia'      => array('Murmansk'=>'Fleet', 'Georgia'   =>'Fleet', 'Leningrad'=>'Army', 'Moscow'=>'Army', 'Minsk'=>'Army', 'Kiev'=>'Army', 'Stalingrad'=>'Army')
	);

}