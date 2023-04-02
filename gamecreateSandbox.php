<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @package Base
 * @subpackage Forms
 */

require_once('header.php');

if ( $Misc->Panic )
{
	libHTML::notice(l_t('Game creation disabled'), 
	l_t("Game creation has been temporarily disabled while we take care of an unexpected problem. Please try again later, sorry for the inconvenience."));
}

if( !$User->type['User'] )
{
	libHTML::notice(l_t('Not logged on'),l_t("Only a logged on user can create games. Please <a href='logon.php' class='light'>log on</a> to create your own games."));
}

libHTML::starthtml();

if( isset($_REQUEST['newGame']) and is_array($_REQUEST['newGame']) )
{
	try
	{
		libAuth::formToken_Valid();
		
		$form = $_REQUEST['newGame']; // This makes $form look harmless when it is unsanitized; the parameters must all be sanitized

		$input = array();
		$required = array('variantID', 'name', 'armyAssignments', 'fleetAssignments', 'scAssignments');

		$playerTypes = 'MemberVsBots';

		foreach($required as $requiredName)
		{
			if ( isset($form[$requiredName]) ) { $input[$requiredName] = $form[$requiredName]; }
			else{ throw new Exception(l_t('The variable "%s" is needed to create a game, but was not entered.',$requiredName)); }
		}
		unset($required, $form);

		$input['variantID']=(int)$input['variantID'];
		if( !isset(Config::$variants[$input['variantID']]) ) { throw new Exception(l_t("Variant ID given (%s) doesn't represent a real variant.",$input['variantID'])); }

		// If the name isn't unique or is too long the database will stop it
		$input['name'] = $DB->escape($input['name']);
		if ( !$input['name'] ) { throw new Exception(l_t("No name entered.")); }
		
		$Variant = libVariant::loadFromVariantID($input['variantID']);

		// Sanitize assignments: all ints, within country range, within territory range, no duplicate assignments, fleets only in coasts/sea, armies only in coast/land, 
		// no armies in coast children, no fleet in coast parents.
		$countryCount = count($Variant->countries);
		list($territoryCount) = $DB->sql_row("SELECT count(*) FROM wD_Territories WHERE mapID=".$Variant->mapID);
		$takenTerritories = array();
		$takenSCs = array();
		$validArmyIds = array();
		$validFleetIds = array();
		$validSCIds = array();
		$tabl = $DB->sql_tabl("SELECT id FROM wD_Territories WHERE mapID=".$Variant->mapID." AND supply='Yes'");
		while(list($id) = $DB->tabl_row($tabl))
			$validSCIds[] = $id;
		$tabl = $DB->sql_tabl("SELECT id FROM wD_Territories WHERE mapID=".$Variant->mapID." AND ((type='Coast' AND coast IN ('No','Parent')) OR type='Land')");
		while(list($id) = $DB->tabl_row($tabl))
			$validArmyIds[] = $id;
		$tabl = $DB->sql_tabl("SELECT id FROM wD_Territories WHERE mapID=".$Variant->mapID." AND ((type='Coast' AND coast IN ('No','Child')) OR type='Sea')");
		while(list($id) = $DB->tabl_row($tabl))
			$validFleetIds[] = $id;

		foreach(array('armyAssignments', 'fleetAssignments', 'scAssignments') as $assignment)
		{
			$input[$assignment] = explode(':',$input[$assignment]);
			$cleanedInput = array();
			foreach($input[$assignment] as $countryIndex=>$territories)
			{
				$countryIndex = (int)$countryIndex + 1;
				if( $countryIndex < 1 or $countryIndex > $countryCount )
					continue;
				
				$territories = explode(',',$territories);
				
				$cleanedTerritories = array();
				foreach($territories as $territoryID)
				{
					$territoryID = (int)$territoryID;
					if( $territoryID < 1 or $territoryID > $territoryCount )
						continue;
					
					if( $assignment == 'scAssignments' )
					{
						if( in_array($territoryID, $takenSCs) )
							throw new Exception(l_t("Territory ID given (%s) is taken as an SC.",$territoryID));
							
						if( !in_array($territoryID, $validSCIds) )
							throw new Exception(l_t("Territory ID given (%s) is not an SC.",$territoryID));

						$takenSCs[] = $territoryID;
					}
					else
					{
						// For a given territory ID get a list of all associated coastal child/parent territory IDs so we can check for duplicates
						$provinceTerritoryID = $territoryID;
						$provinceTerritoryIDs = array($provinceTerritoryID);
						if( isset($Variant->coastParentIDByChildID[$provinceTerritoryID]) )
						{
							$provinceTerritoryID = $Variant->coastParentIDByChildID[$provinceTerritoryID];
						}
						if( isset($Variant->coastChildIDsByParentID[$provinceTerritoryID]) )
						{
							$provinceTerritoryIDs = array_merge($provinceTerritoryIDs, $Variant->coastChildIDsByParentID[$provinceTerritoryID]);
						}
						foreach( $provinceTerritoryIDs as $terrID )
						{
							if( in_array($terrID, $takenTerritories) )
								throw new Exception(l_t("Territory ID given (%s) is already occupied.",$terrID));
						}
						if( 'fleetAssignments' == $assignment )
						{
							if( !in_array($territoryID, $validFleetIds) )
								throw new Exception(l_t("Territory ID given (%s) cannot contain a fleet.",$territoryID));
						}
						else
						{
							if( !in_array($territoryID, $validArmyIds) )
								throw new Exception(l_t("Territory ID given (%s) cannot contain an army.",$territoryID));
						}
						
						$takenTerritories[] = $territoryID;
					}
					
					$cleanedTerritories[] = $territoryID;
				}
				$cleanedInput[$countryIndex] = $cleanedTerritories;
			}
			$input[$assignment] = $cleanedInput;
		}

		// All sanitized and ready, now create the game:

		// Create Game record & object
		require_once(l_r('gamemaster/sandboxGame.php'));
		
		$Game = processSandboxGame::newGame($input['variantID'], $input['name']);

		// Clear out the defaults and replace with the assignments:
		$DB->sql_put("DELETE FROM wD_TerrStatus WHERE gameID=".$Game->id);
		$DB->sql_put("DELETE FROM wD_Units WHERE gameID=".$Game->id);
		$DB->sql_put("DELETE FROM wD_Orders WHERE gameID=".$Game->id);		
		$unitInserts = array();
		foreach($input['armyAssignments'] as $countryID=>$terrIDs)
			foreach($terrIDs as $terrID)
				$unitInserts[] = "(".$Game->id.", ".$countryID.", '".$terrID."', 'Army')";
		
		foreach($input['fleetAssignments'] as $countryID=>$terrIDs)
			foreach($terrIDs as $terrID)
				$unitInserts[] = "(".$Game->id.", ".$countryID.", '".$terrID."', 'Fleet')";
				
		$scInserts = array();
		foreach($input['scAssignments'] as $countryID=>$terrIDs)
			foreach($terrIDs as $terrID)
				$scInserts[] = "(".$Game->id.", ".$countryID.", '".$terrID."')";

		$DB->sql_put("INSERT INTO wD_TerrStatus ( gameID, countryID, terrID ) VALUES ".implode(', ', $scInserts));
		$DB->sql_put("INSERT INTO wD_Units ( gameID, countryID, terrID, type ) VALUES ".implode(', ', $unitInserts));

		// Reassign terr status unit occupations:
		$adj = $Game->Variant->adjudicatorPreGame();
		$adj->reassignUnitOccupations();

		// Regenerate orders:
		$Game->generateOrders();

		// Archive the territory statuses so the first map will render correctly even with updated territories:
		$Game->archiveTerrStatus();

		$DB->sql_put("COMMIT");

		$Game->Members->joinedRedirect();
	}
	catch(Exception $e)
	{
		print '<div class="content">';
		print '<p class="notice">'.$e->getMessage().'</p>';
		print '</div>';
	}
}

require_once(l_r('locales/English/gamecreateSandbox.php'));

print '</div>';
libHTML::footer();
?>
