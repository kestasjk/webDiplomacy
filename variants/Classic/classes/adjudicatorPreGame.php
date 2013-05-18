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

class ClassicVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'England' => array(
					'Edinburgh'=>'Fleet', 'Liverpool'=>'Army', 'London'=>'Fleet'
				),
		'France' => array(
					'Brest'=>'Fleet', 'Paris'=>'Army', 'Marseilles'=>'Army'
				),
		'Italy' => array(
					'Venice'=>'Army', 'Rome'=>'Army', 'Naples'=>'Fleet'
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

		$chanceGrid=array();
		$tabl = $DB->sql_tabl(
				"SELECT m.userID, u.Chance".implode(', u.Chance',$Game->Variant->countries)."
				FROM wD_Users u
				INNER JOIN wD_Members m ON (m.userID = u.id)
				WHERE m.gameID = ".$Game->id
			);
		while($row = $DB->tabl_hash($tabl))
		{
			$chanceRow = array();

			for($countryID=1; $countryID<=count($Game->Variant->countries); $countryID++)
			{
				$countryName=$Game->Variant->countries[$countryID-1];
				$chanceRow[$countryID] = $row['Chance'.$countryName];
			}

			$chanceGrid[$row['userID']] = $chanceRow;
		}
		$userChances=$chanceGrid;

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
			$chancesSQL=array();
			foreach($userChances[$userID] as $countryID=>$chance)
			{
				$countryName=$Game->Variant->countries[$countryID-1];
				$chancesSQL[] = 'Chance'.$countryName.' = '.number_format(round($chance,3),3);
			}

			$DB->sql_put("UPDATE wD_Users SET ".implode(',',$chancesSQL)." WHERE id=".$userID);
		}

		return $userCountries;
	}
}