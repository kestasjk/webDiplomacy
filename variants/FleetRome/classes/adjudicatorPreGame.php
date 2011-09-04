<?php

class FleetRomeVariant_adjudicatorPreGame extends ClassicVariant_adjudicatorPreGame {

	protected $countryUnits = array(
		'England' => array(
					'Edinburgh'=>'Fleet', 'Liverpool'=>'Army', 'London'=>'Fleet'
				),
		'France' => array(
					'Brest'=>'Fleet', 'Paris'=>'Army', 'Marseilles'=>'Army'
				),
		'Italy' => array(
					'Venice'=>'Army', 'Rome'=>'Fleet', /* The change */ 'Naples'=>'Fleet'
				),
		'Germany' => array(
					'Kiel'=>'Fleet', 'Berlin'=>'Army', 'Munich'=>'Army'
				),
		'Austria' => array(
					'Vienna'=>'Army', 'Trieste'=>'Fleet', 'Budapest'=>'Army'
				),
		'Turkey' => array(
					'Smyrna'=>'Army', 'Ankara'=>'Fleet', 'Constantinople'=>'Army'
				),
		'Russia' => array(
					'Moscow'=>'Army', 'St. Petersburg (South Coast)'=>'Fleet', 'Warsaw'=>'Army', 'Sevastopol'=>'Fleet'
				)
		);

}