<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Fog_adjudicatorDiplomacy extends adjudicatorDiplomacy
{
	function adjudicate()
	{
		global $DB;

		$fromids=array();
	
		/* Remove invalid support-move orders (Support a move from a territory without a unit*/
		$tabl = $DB->sql_tabl("SELECT terrID FROM wD_Moves 
									WHERE gameID=".$GLOBALS['GAMEID']);
	
		while(list($terrID) = $DB->tabl_row($tabl))
			$fromids[] = $terrID;

		if (!(empty($fromids)))
			$DB->sql_put("UPDATE wD_Moves
							SET moveType = 'Hold'
							WHERE moveType = 'Support move'
								AND gameID=".$GLOBALS['GAMEID']." 
								AND fromTerrID NOT IN (".implode(",", $fromids).")");
					
		return parent::adjudicate();
	}
}

class RatWarsVariant_adjudicatorDiplomacy extends Fog_adjudicatorDiplomacy {}
