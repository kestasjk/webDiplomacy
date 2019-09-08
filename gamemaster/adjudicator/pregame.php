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

defined('IN_CODE') or die('This script can not be run by itself.');

require_once('assignmentSolver.php');

/**
 * @package GameMaster
 * @subpackage Adjudicator
 */
class adjudicatorPreGame 
{
	protected function isEnoughPlayers() 
	{
		global $Game;

		return ( count($Game->Members->ByID) == count($Game->Variant->countries) );
	}

	protected function userCountries() 
	{
		global $Game;
		/*
		 * Find out who gets which countryID;
		 * - Get the number of times each player has played as each countryID (stored in wD_Users)
		 * - Assign players to countries so that they play as a country that they have played
		 *   as as few times as possible.
		 */

		$userIDs = array();
		foreach($Game->Members->ByID as $M) $userIDs[]=$M->userID;
		
		/**
		 * The hungarian solver expects a multidimensional array, all 0-indexed. Therefore
		 * we need to store the userIDs seperately to the weights, so that they can be 
		 * zipped together at the end.
		 */
		$weights = $this->getWeightsAsCountryCounts($userIDs);

		$solver = new assignmentSolver;
		
		// We are minimising the weights, so we pass in "false".
		$assignment = $solver->hungarian($weights, false);
		
		$userCountries = array();
		for($i = 0; $i < count($userIDs); $i++)
		{
			$userID = $userIDs[$i];
			$userCountries[$userID] = $assignment[$i];
		}
			
		/**
		 * Update the play count for the associated countries
		 */
		$this->updateCountryPlayCounts($assignment, $userIDs, $weights);			

		return $userCountries;
	}
	
	protected function updateCountryPlayCounts($assignment, $userIDs, $initialWeights)
	{
		global $Game;
		$vd = new VariantData($Game->variantID);
		$vd->systemToken = 948379409;
		for ($userIndex = 0; $userIndex < count($userIDs); $userIndex++)
		{
			$vd->userID = $userIDs[$userIndex];
			$countryIndex = $assignment[$userIndex];
			$oldPlayCount = $initialWeights[$userIndex][$countryIndex];
			$vd->updateInt($oldPlayCount + 1, $countryIndex);
		}
	}
	
	protected function getWeightsAsCountryCounts($userIDs)
	{
		global $Game;
		$vd = new VariantData($Game->variantID);
		$vd->systemToken = 948379409;
		
		$userCountryCounts = array();
		
		$countryCount = count($Game->Variant->countries);
		for($i = 0; $i < count($userIDs); $i++)
		{
			$userCountryCounts[$i] = array();
			$vd->userID = $userIDs[$i];
			
			for($countryID=1;$countryID<=$countryCount;$countryID++)
			{
				$userCountryCounts[$i][$countryID] = $vd->getInt($countryID, 0);
			}
		}
		
		return $userCountryCounts;
	}

	protected function assignCountries(array $userCountries) 
	{
		global $DB, $Game;

		// Finally the user->countryID array is written to the database and Game->Members objects,
		// and the new countryID chances for each user based on their selection this time are written
		// to the database
		foreach( $userCountries as $userID=>$countryID )
		{
			$DB->sql_put("UPDATE wD_Members SET countryID='".$countryID."' WHERE userID=".$userID." AND gameID = ".$Game->id);
		}

		$Game->Members->ByCountryID=array();
		foreach($Game->Members->ByID as $Member)
		{
			$Member->countryID = $userCountries[$Member->userID];
			$Game->Members->ByCountryID[$Member->countryID] = $Member;
		}

		for($countryID=1; $countryID<=count($Game->Variant->countries); $countryID++)
			assert('$Game->Members->ByCountryID[$countryID]->countryID==$countryID');
	}

	protected function assignTerritories() 
	{
		global $DB, $Game;

		$DB->sql_put(
			"INSERT INTO wD_TerrStatus ( gameID, countryID, terrID )
			SELECT ".$Game->id." as gameID, countryID, id
			FROM wD_Territories
			WHERE countryID > 0 AND mapID=".$Game->Variant->mapID." AND (coast='No' OR coast='Parent')"
		);
	}

	protected function assignUnits() 
	{
		global $DB, $Game;

		$terrIDByName = array();
		$tabl = $DB->sql_tabl("SELECT id, name FROM wD_Territories WHERE mapID=".$Game->Variant->mapID);
		while(list($id, $name) = $DB->tabl_row($tabl))
			$terrIDByName[$name]=$id;

		$UnitINSERTs = array();
		foreach($this->countryUnits as $countryName => $params)
		{
			$countryID = $Game->Variant->countryID($countryName);

			foreach($params as $terrName=>$unitType)
			{
				$terrID = $terrIDByName[$terrName];

				$UnitINSERTs[] = "(".$Game->id.", ".$countryID.", '".$terrID."', '".$unitType."')"; // ( gameID, countryID, terrID, type )
			}
		}

		$DB->sql_put("INSERT INTO wD_Units ( gameID, countryID, terrID, type ) VALUES ".implode(', ', $UnitINSERTs));
	}

	protected function assignUnitOccupations() 
	{
		global $DB, $Game;

		// Now link the TerrStatus and Units records via the occupyingUnitID TerrStatus column
		$DB->sql_put(
			"UPDATE wD_TerrStatus t
			INNER JOIN wD_Units u
				ON (
					t.gameID = u.gameID
					/* TerrStatus does not deal with coasts */
					AND ".$Game->Variant->deCoastCompare('t.terrID','u.terrID')."
				)
			SET t.occupyingUnitID = u.id
			WHERE u.gameID = ".$Game->id
		);
	}

	/**
	 * Initialize the game (more of a phase change than adjudication). Will throw an exception
	 * if the game doesn't have enough players, which will be caught in gamemaster.php and result
	 * in the game's deletion
	 *
	 * Deletes game and throws exception if game cannot start
	 */
	function adjudicate()
	{		
		global $Game, $DB;

		// If the game is a mixed game and there are not enough players in the game, and at least 2 humans then fill the game with bots. 
		if ( ($Game->playerTypes == "Mixed") && (!$this->isEnoughPlayers()) && (count($Game->Members->ByID) > 1) )
		{
			$botNum = (count($Game->Variant->countries) - count($Game->Members->ByID));

			$tabl = $DB->sql_tabl("SELECT id FROM wD_Users WHERE type LIKE '%bot%' LIMIT ".$botNum);

			while (list($botID) = $DB->tabl_row($tabl))
			{
				processMember::create($botID, 5, 0);
			}
		}
		// If the "fill with bots" game fills with all humans then change the game playerTypes to "Members" so GR and game stats are accurate about which games had bots in them.
		else if ( ($Game->playerTypes == "Mixed") && ($this->isEnoughPlayers()) )
		{
			$DB->sql_put("UPDATE wD_Games SET playerTypes = 'Members' WHERE id = ".$gameID);
		}
		else
		{
			// Will give back bets, send messages, delete the game, and throw an exception to get back to gamemaster.php if there are not enough players to start. 
			if( !$this->isEnoughPlayers() ) $Game->setNotEnoughPlayers();
		}

		// Determine which countryID is given to which userID
		if (count($Game->Members->ByCountryID)==0)
		{
			$userCountries = $this->userCountries();// $userCountries[$userID]=$countryID
			assert('count($userCountries) == count($Game->Variant->countries) && count($userCountries) == count($Game->Members->ByID)');
			$this->assignCountries($userCountries);
		}
		
		// Create starting board conditions, typically based on $countryUnits
		$this->assignTerritories(); 
		$this->assignUnits();
		$this->assignUnitOccupations();
	}
}

?>