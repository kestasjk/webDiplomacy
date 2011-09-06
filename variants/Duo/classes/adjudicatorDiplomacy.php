<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class DuoVariant_adjudicatorDiplomacy extends adjudicatorDiplomacy {

	function adjudicate()
	{
		global $DB, $Game;
		// Get all the units that want to "transform"
		$trans = $DB->sql_tabl("SELECT unitID, terrID FROM wD_Moves
						WHERE toTerrID=105 AND gameID=".$Game->id);
		// And set them to "Hold"						
		$DB->sql_put("UPDATE wD_Moves 
						SET MoveType='Hold', toTerrID=NULL
						WHERE toTerrID=105 AND gameID = ".$Game->id);
		$DB->sql_put("UPDATE wD_Orders
						SET type='Hold', toTerrID=NULL
						WHERE toTerrID=105 AND gameID = ".$Game->id);

		// Do the adjucation
		$standoffs=parent::adjudicate();

		// Now see what units to change...
		while ( $row = $DB->tabl_hash($trans) )
		{
			// Did we get interrupted by a move request?
			list($ok)=$DB->sql_row("SELECT COUNT(*) FROM wD_Orders 
						WHERE type='Move' AND toTerrID=".$row['terrID']." AND gameID=".$Game->id);
			// No? So lets do the "transform"
			if ($ok == 0) {
				list($type)=$DB->sql_row("SELECT type FROM wD_Units	WHERE id=".$row['unitID']);
				$DB->sql_put("UPDATE wD_Units 
						SET type='".($type == 'Fleet' ? 'Army' : 'Fleet')."'
						WHERE id=".$row['unitID']);
				$DB->sql_put("UPDATE wD_Moves SET success='Yes' WHERE unitID=".$row['unitID']);
			}
			// Generate a Move to it's own territory so the unit is still on it and not at the fake transform territory
			$DB->sql_put("UPDATE wD_Moves  SET moveType='Move',toTerrID=".$row['terrID']." WHERE unitID=".$row['unitID']);
			$DB->sql_put("UPDATE wD_Orders SET     type='Move',toTerrID=".$row['terrID']." WHERE unitID=".$row['unitID']);			
		}
		// Generate a Standoff at the Transform-Territory to avoid retreats here...
		array_push($standoffs,105);
		return $standoffs;
	}

}

?>