<?php
/*
    Copyright (C) 20013 Oliver Auth

	This file is part of vDiplomacy.

    vDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    vDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with vDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
*/

class libRating
{
	static public $base_VDip = 1000;

	static public function updateRatings($Game, $updateTimestamp=false)
	{
		global $DB;
		// If Game is a gameID load the 
		if (is_numeric($Game))
		{
			$Variant=libVariant::loadFromGameID($Game);
			$Game = $Variant->Game($Game);
		}
		
		self::updateVDipRating($Game);
		if ($updateTimestamp)
			$DB->sql_put('UPDATE wD_Games SET processTime="'.time().'"
							WHERE id='.$Game->id);
	}

	static public function updateVDipRating($Game)
	{
		global $DB;
	
		$Members = self::loadVDipMembers($Game);
		$Members = self::calcVDipRating($Game, $Members);
		
		foreach ($Members AS $Member)
			$DB->sql_put("INSERT INTO wD_Ratings SET
							ratingType='vDip',
							userID='".$Member['userID']."',
							gameID='".$Game->id."',
							rating=".round($Member['rating'] + $Member['change']));
		return;
	}
	
	static public function loadVDipMembers($Game)
	{
		global $DB;
		$maxSCc = ( $Game->targetSCs > 0 ? $Game->targetSCs : $Game->Variant->supplyCenterTarget );
		
		$tSCc = 0;
		foreach($Game->Members->ByUserID as $userID => $Member)
		{
			if ($Member->supplyCenterNo > $maxSCc)
				$Game->Members->ByUserID[$userID]->supplyCenterNoAdjusted = $maxSCc;
			else
				$Game->Members->ByUserID[$userID]->supplyCenterNoAdjusted = $Member->supplyCenterNo;
			
			$tSCc += $Game->Members->ByUserID[$userID]->supplyCenterNoAdjusted;
		}
		
		foreach (array ('Won', 'Drawn', 'Survived', 'Resigned', 'Defeated') as $status)
			foreach ($Game->Members->ByStatus[$status] AS $Member)
				$Members[$Member->userID] = array (
					'userID'  => $Member->userID,
					'name'    => $Member->username,
					'rating'  => self::getVDipRating($Member->userID, $Game->id),
					'bet'     => $Member->bet,
					'change'  => 0,
					'status'  => $Member->status,
					'SCc'     => $Member->supplyCenterNoAdjusted,
					'SCr'     => $Member->supplyCenterNo,
					'SCq'     => (($tSCc == 0) ? 0 : $Member->supplyCenterNoAdjusted / $tSCc),
					'matches' => array()
				);
		
		$tabl = $DB->sql_tabl(
			"SELECT message FROM wD_GameMessages 
				WHERE message LIKE '%userID=%Reconsider your alliances.%'
					AND fromCountryID = 0
					AND gameID = ".$Game->id);
			
		while (list($CD) = $DB->tabl_row($tabl))
		{
			$userID = preg_replace('/^.*userID=(\d*).*/', '$1', $CD);
			$name   = preg_replace('/^.*ID=\d*">(.*)<\/a>.*/', '$1', $CD);
			$Members[$userID] = array (
				'userID'  => $userID,
				'name'    => $name,
				'rating'  => self::getVDipRating($userID, $Game->id),
				'status'  => 'CD',
				'bet'     => 10,
				'change'  => 0,
				'SCc'     => 0,
				'SCr'     => 0,
				'SCq'     => 0,
				'matches' => array()
			);
		}
		return ($Members);
	}
	
	static public function calcVDipRating($Game, $Members)
	{
		$keys = array_keys($Members);
		for ($i=0; $i<(count($Members) - 1) ; $i++)
			for ($j=$i+1; $j<count($Members); $j++)
				self::calcVDipMatch($Game, $Members[$keys[$i]], $Members[$keys[$j]]);	
		return ($Members);
	}
		
	static public function getVDipRating($userID, $gameID = 0)
	{
		global $DB;
		
		list ($rating) = $DB->sql_row("
					SELECT r.rating	FROM wD_Ratings r
						LEFT JOIN wD_Games g ON (g.id = r.gameID)
					WHERE r.ratingType='vDip'
						&& r.userID=".$userID."
						&& g.phase = 'Finished'
						".($gameID == 0 ? "" : "&& g.processTime < (SELECT processTime FROM wD_Games WHERE id='".$gameID."')")."
					ORDER BY g.processTime DESC LIMIT 1");
		
		if ($rating == 0) $rating = self::$base_VDip;
		
		return $rating;
	}
 
	static public function calcVDipMatch($Game, &$Member1, &$Member2)
	{
		global $DB;
 
		// 2-player variants are always WTA.
		if (count($Game->Variant->countries) == 2) $Game->potType ='Winner-takes-all';

		$SCtotal = $Game->Members->supplyCenterCount();
		$SCc1 = $Member1['SCc']; $SCq1 = $Member1['SCq']; $St1=$Member1['status'];
		$SCc2 = $Member2['SCc']; $SCq2 = $Member2['SCq']; $St2=$Member2['status'];
		
		// Calculate the expected result for Member1
		$Re1= 1 / ( 1 + ( pow( 10 , (($Member2['rating'] - $Member1['rating']) / 400) ) ) );
		$Re2= 1 - $Re1;
		
		// Adjust the restults based on pot-type and game-status
		
		// Resigned is the same as Defeated. SCc=0 and SCq=0
		if ($St1=='Resigned') {$St1='Defeated'; $SCc1=0; $SCq1=0;}
		if ($St2=='Resigned') {$St2='Defeated'; $SCc2=0; $SCq2=0;}

		// CD-players loose 0:100 against everybody else but another CD. CDvsCD is 0 for both
		if ($St1=='CD') { $SCc2=($St2=='CD' ? 0 : 1); $SCq2=($St2=='CD' ? 0 : 1 / count($Game->Members->ByID));	}
		if ($St2=='CD') { $SCc1=($St1=='CD' ? 0 : 1); $SCq1=($St1=='CD' ? 0 : 1 / count($Game->Members->ByID)); }
		
		if ($Game->gameOver == 'Drawn')
		{
			if ($St1=='Drawn') { $SCc1 = 1; $SCq1 = 1 / count($Game->Members->ByStatus['Drawn']); }
			if ($St2=='Drawn') { $SCc2 = 1; $SCq2 = 1 / count($Game->Members->ByStatus['Drawn']); }
		}		
		elseif ($Game->potType == 'Winner-takes-all')
		{
			if ($St1=='Won') { $SCq1 = 1; } else  { $SCc1 = 0; $SCq1 = 0; }
			if ($St2=='Won') { $SCq2 = 1; } else  { $SCc2 = 0; $SCq2 = 0; }
	 	}
		else
		{
			if     ($SCc1 > $SCc2) { $SCc1 = 1; $SCc2 = 0; }
			elseif ($SCc1 < $SCc2) { $SCc2 = 1; $SCc1 = 0; }
			elseif ($SCc1 != 0 )   { $SCc2 = 1; $SCc1 = 1; }
	 	}
		
		// Calculate the real results.
		$Rr1 = ( ($SCc1 + $SCc2) > 0 ) ? ($SCc1 / ($SCc1 + $SCc2)) : 0;
		$Rr2 = ( ($SCc1 + $SCc2) > 0 ) ? ($SCc2 / ($SCc1 + $SCc2)) : 0;
		$mV  = abs($SCq1 - $SCq2); 

		// Value the importance of take-overs. (If a player bet only the half the whole match is worth only half.
		$mV = $mV * (1 - abs($Member1['bet'] - $Member2['bet']) / max($Member1['bet'], $Member2['bet']));		
		
		// Set K-factor to 50
		$K = 50;
		
		// The more people the more important a game...
		$gV = $K * pow(((count($Game->Variant->countries) -1) / count($Game->Variant->countries)),3) * (100- (count($Game->Variant->countries)))/100;
		
		// Do not count Rinascimento games
		if ($Game->Variant->name =='Rinascimento') $gV=0;
		
		// If the winner does not reached the supplyCenterTarget adjust the importance of the game too
		if ($Game->gameOver == 'Won')
		{
			foreach($Game->Members->ByStatus['Won'] as $Winner);
			if ($Winner->supplyCenterNo < $Game->Variant->supplyCenterTarget )
			{
				list($startSC)=$DB->sql_row('SELECT count(*) FROM wD_Territories
					WHERE mapID='.$Game->Variant->mapID.' AND supply="Yes" AND countryID>0 
					GROUP BY countryID ASC LIMIT 1');
				$gV = $gV * ($Winner->supplyCenterNo - $startSC) / ($Game->Variant->supplyCenterTarget - $startSC);
			}
		}
		
		// Calculate Points-change
		$Ch1 = round(($Rr1 - $Re1) * $mV * $gV,2);
		$Ch2 = round(($Rr2 - $Re2) * $mV * $gV,2);
		
		// Save the results in the match-arrays
		$Member1['matches'][$Member2['userID']] = array (
			'Re' => $Re1, 'Rr' => $Rr1,
			'mV' => $mV,  'gV' => $gV,
			'Ch' => $Ch1
		);
		$Member2['matches'][$Member1['userID']] = array (
			'Re' => $Re2, 'Rr' => $Rr2,
			'mV' => $mV,  'gV' => $gV,
			'Ch' => $Ch2
		);
		
		// Save the point-change in the $Member arrays...
		$Member1['change'] += $Ch1;
		$Member2['change'] += $Ch2;
		
		return;
	}

}
?>
