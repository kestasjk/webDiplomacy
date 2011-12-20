<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Fog_adjudicatorPreGame extends adjudicatorPreGame {

	function adjudicate()
	{
		global $DB, $Game;
		parent::adjudicate();

		// Generate the verification code
		$ccode="";
		for ($i=0; $i<50; $i++) {
			$d=rand(1,30)%2;
			$ccode .= $d ? chr(rand(65,90)) : chr(rand(48,57));
		}

		// And save the code in the database:
		$DB->sql_put(
			"INSERT INTO wD_Notices (toUserID,fromID,text,linkName) VALUES 
				(3,".$Game->id.",'".$ccode."','Variant-Data')");
	}		
}

class CustomStartVariant_adjudicatorPreGame extends Fog_adjudicatorPreGame
{
	// Disabled; no initial units or occupations
	protected function assignUnits() { }
	protected function assignUnitOccupations() { }
}

class RatWarsVariant_adjudicatorPreGame extends CustomStartVariant_adjudicatorPreGame {}
