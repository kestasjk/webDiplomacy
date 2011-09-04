<?php

class CustomStartVariant_adjudicatorPreGame extends ClassicVariant_adjudicatorPreGame {

	protected $countryUnits = array(
		'England' => array(),
		'France' => array(),
		'Italy' => array(),
		'Germany' => array(),
		'Austria' => array(),
		'Turkey' => array(),
		'Russia' => array()
		);

	// Disabled; no initial units or occupations
	protected function assignUnits() { }

	protected function assignUnitOccupations() { }

}