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
/**
 * @package GameMaster
 * @subpackage Adjudicator
 */
class adjudicatorPreGame {

	protected function isEnoughPlayers() {
		global $Game;

		return ( count($Game->Members->ByID) == count($Game->Variant->countries) );
	}

	/**
	 * Takes an array of chances indexed by countryID which add up to less than one and
	 * increases them in proportion so they end up adding up to 1.
	 *
	 * @param $chances array[$countryID]=$chance
	 * @return array[$countryID]=$chance
	 */
	private function balanceChances(array $chances)
	{
		$sum = 0.0;

		foreach($chances as $countryID=>$chance)
		{
			if ( $chance < 0.001 )
				$chance = ($chances[$countryID] = 0.01);

			$sum += $chance;
		}

		foreach($chances as $countryID=>$chance)
			$chances[$countryID] *= 1.0/$sum;

		return $chances;
	}

	protected function userCountries() {
		global $DB, $Game;
		/*
		 * Find out who gets which countryID;
		 * - Get the chances for each player of getting each countryID (stored in wD_Users)
		 *
		 * - Order the players so that the player which has the most difference in their
		 * 	 chances from countryID to countryID gets to draw first. (i.e. order by std.deviation)
		 * 	(So that large inequalities are reduced quickly)
		 *
		 * - For each player:
		 * 		- Scale the chances up to add up to 1
		 * 		- Choose a random number from 0 to 1
		 * 		- For each countryID subtract its probability from the random number. When
		 * 		  the next countryID has a probability higher than the random number that is
		 * 		  the countryID that player gets.
		 * 		- For each player which hasn't been processed yet: Set the chance of being
		 * 		  the countryID the current player was given to 0.
		 *
		 * - Factor the new game's countryID selection into each member's countryID-chances
		 */

		$userIDs = array();
		foreach($Game->Members->ByID as $M) $userIDs[]=$M->userID;
		
		$chanceGrid = $this->getUserCountryChances($userIDs);
		$userChances = $chanceGrid;
		
		/*
		 * - Order the players so that the player which has the most difference in their
		 * 	 chances from countryID to countryID gets to draw first. (So that large differences
		 * 	 are reduced quickly)
		 */
		$selectionOrder = array();
		$standardDevs = array();
		foreach($chanceGrid as $userID=>$chances)
		{
			// Balance chances
			$chanceGrid[$userID] = ($chances = $this->balanceChances($chances));

			$sum=0.0;
			foreach($chances as $chance)
				$sum += pow(abs($chance - 1.0/count($Game->Variant->countries)), 2.0);

			$selectionOrder[] = $userID;
			$standardDevs[] = pow($sum, 0.5);
		}

		/*
		 * We have an array of standard deviations and associated user-IDs, both using the
		 * same numeric index. This makes it easy to sort the user-IDs based on the std-devs.
		 * (An inefficient bubble-sort, but good enough)
		 */
		$memberCount = count($selectionOrder);
		for($i=0; $i<$memberCount-1; $i++)
			for($j=0; $j<$memberCount-1; $j++)
				if ( $standardDevs[$j] < $standardDevs[$j+1])
				{
					$tmp = $standardDevs[$j+1];
					$standardDevs[$j+1] = $standardDevs[$j];
					$standardDevs[$j] = $tmp;

					$tmp = $selectionOrder[$j+1];
					$selectionOrder[$j+1] = $selectionOrder[$j];
					$selectionOrder[$j] = $tmp;
				}

		/*
		 * - For each player:
		 * 		- Scale the chances up to add up to 1
		 * 		- Choose a random number from 0 to 1
		 * 		- For each countryID subtract its probability from the random number. When
		 * 		  the next countryID has a probability higher than the random number that is
		 * 		  the countryID that player gets.
		 * 		- For each player which hasn't been processed yet: Set the chance of being
		 * 		  the countryID the current player was given to 0.
		 */
		$userCountries = array();
		$memberCount = count($selectionOrder);
		foreach($selectionOrder as $playerNo=>$userID)
		{
			// - Scale the chances up to add up to 1
			if ( $playerNo != 0 )
				$chanceGrid[$userID] = $this->balanceChances($chanceGrid[$userID]);

			// - Choose a random number from 0 to 1
			$rand = rand(0,100)/100;

			/*	- For each countryID subtract its probability from the random number. When
			 *	  the next countryID has a probability higher than the random number that is
			 * 	  the countryID that player gets.
			 */
			foreach($chanceGrid[$userID] as $countryID=>$chance)
			{
				if ( $rand <= $chance ) break;
				else $rand -= $chance;
			}

			$userCountries[$userID] = $countryID;

			/* 	- For each player which hasn't been processed yet: Set the chance of being
		 	 * 	  the countryID the current player was given to 0.
		 	 */
			for($i=$playerNo+1; $i<$memberCount; $i++)
				unset($chanceGrid[$selectionOrder[$i]][$countryID]);
		}

		foreach( $userCountries as $userID=>$countryID )
		{
			$userChances[$userID][$countryID] /= 2.0;
			$userChances[$userID]=$this->balanceChances($userChances[$userID]);
		}
		
		$this->setUserCountryChances($userChances);

		return $userCountries;
	}
	
	protected function setUserCountryChances($countryChances)
	{
		global $Game;
		$vd = new VariantData($Game->variantID);
		$vd->systemToken = 948379409;
		
		foreach($countryChances as $userID=>$chances)
		{
			$vd->userID = $userID;
			foreach($chances as $countryID=>$chance)
				$vd->setFloat($chance, $countryID);
		}
	}
	
	protected function getUserCountryChances($userIDs)
	{
		global $Game;
		$vd = new VariantData($Game->variantID);
		$vd->systemToken = 948379409;
		
		$countryChances = array();
		
		$countryCount = count($Game->Variant->countries);
		foreach($userIDs as $userID)
		{
			$countryChances[$userID] = array();
			
			$vd->userID = $userID;
			for($countryID=1;$countryID<=$countryCount;$countryID++)
				$countryChances[$userID][$countryID] = $vd->getFloat($countryID, 1.0/$countryCount);
		}
		
		return $countryChances;
	}

	protected function assignCountries(array $userCountries) {
		global $DB, $Game;

		// Finally the user->countryID array is written to the database and Game->Members objects,
		// and the new countryID chances for each user based on their selection this time are written
		// to the database
		foreach( $userCountries as $userID=>$countryID )
		{
			$DB->sql_put(
				"UPDATE wD_Members
				SET countryID='".$countryID."'
				WHERE userID=".$userID." AND gameID = ".$Game->id
			);
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

	protected function assignTerritories() {
		global $DB, $Game;

		$DB->sql_put(
			"INSERT INTO wD_TerrStatus ( gameID, countryID, terrID )
			SELECT ".$Game->id." as gameID, countryID, id
			FROM wD_Territories
			WHERE countryID > 0 AND mapID=".$Game->Variant->mapID." AND (coast='No' OR coast='Parent')"
		);
	}

	protected function assignUnits() {
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

		$DB->sql_put(
			"INSERT INTO wD_Units ( gameID, countryID, terrID, type )
			VALUES ".implode(', ', $UnitINSERTs)
		);
	}

	protected function assignUnitOccupations() {
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
		global $Game;

		// Will give back bets, send messages, delete the game, and throw an exception to get back to gamemaster.php
		if( !$this->isEnoughPlayers() ) $Game->setNotEnoughPlayers();


		// Determine which countryID is given to which userID
		$userCountries = $this->userCountries();// $userCountries[$userID]=$countryID

		assert('count($userCountries) == count($Game->Variant->countries) && count($userCountries) == count($Game->Members->ByID)');

		$this->assignCountries($userCountries);

		// Create starting board conditions, typically based on $countryUnits
		$this->assignTerritories(); // TerrStatus
		$this->assignUnits(); // Units
		$this->assignUnitOccupations(); // TerrStatus occupyingUnitID
	}
}

?>