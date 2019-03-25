<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas and Timothy Jones

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

abstract class ScoringSystem {
	protected $Game;

	public function __construct($Game) {
 	   $this->Game = $Game;
	}

	public function pointsForDraw($Member) {}
	public function pointsForWin($Member) {}
	public function pointsForSurvive($Member) {}
	public function pointsForDefeat($Member) {return 0;}

	public function abbr() {}
	public function longName() {}
}

class ScoringPPSC extends ScoringSystem {
	private $ratios;
	private function PPSCRatios() {
            {
			if( $this->ratios != null ) return $ratios;
 
			foreach($this->Game->Members->ByStatus['Left'] as $Member)
		   		$ratios[$Member->countryID] = 0.0;
		   	foreach($this->Game->Members->ByStatus['Playing'] as $Member)
			   	$ratios[$Member->countryID] = 0.0;
            /*
             * PPSC; calculate based on active-player-owned supply-centers, but
             * things are complicated because players with over $SCTarget SCs are limited
             * to the winnings they would get from $SCTarget, and the remainder is
             * distributed among the survivors according to their winnings.
             */
            $SCsInPlayCount = (float)$this->Game->Members->supplyCenterCount('Playing');

            assert('$SCsInPlayCount > 0');

            $SCTarget = $this->Game->Variant->supplyCenterTarget;
            foreach($this->Game->Members->ByStatus['Playing'] as $Member)
            {	

				if( $Member->supplyCenterNo > $SCTarget )
				{
						/*
						 * Winner is greedy and got more SCs than he needed:
						 * - Get the number of extra SCs he has
						 * - Reduce his total to $SCTarget
						 * - Subtract the extra amount from the total SCs so they scale down
						 */

						/*
						 * Subtracting the over-the-limit extra SCs from the winner and
						 * from the total SC count effectively makes the algorithm behave
						 * as if they didn't exist
						 */
						$SCsInPlayCount -= ( $Member->supplyCenterNo - $SCTarget );
						$ratios[$Member->countryID] = $SCTarget/$SCsInPlayCount;
				}                                            
			}

            foreach($this->Game->Members->ByStatus['Playing'] as $Member)
            {
                if( $Member->supplyCenterNo > $SCTarget) continue;

                $ratios[$Member->countryID] = $Member->supplyCenterNo/$SCsInPlayCount;
            }
        }
		return $ratios;
	}

	public function pointsForDraw($Member) {
	   	return round($this->Game->pot / count($this->Game->Members->ByStatus['Playing']));
	}
	public function pointsForWin($Member) {
		$ratios = $this->PPSCRatios();
		return ceil($ratios[$Member->countryID] * $this->Game->pot);
	}
			
	public function pointsForSurvive($Member) {
		$ratios = $this->PPSCRatios();
		return ceil($ratios[$Member->countryID] * $this->Game->pot);
	}
	public function abbr() { return 'SWS'; }
			public function longName() {return 'Survivors-Win Scoring';} 
}
class ScoringWTA extends ScoringSystem {
	public function pointsForDraw($Member) {
	   	return round($this->Game->pot / count($this->Game->Members->ByStatus['Playing']));
	}
	public function pointsForWin($Member) {return $this->Game->pot;}
	public function pointsForSurvive($Member) {return 0;}
	public function abbr() { return 'DSS'; }
	public function longName() {return 'Draw-Size Scoring';} 
}
// Fibonacci scoring, based on https://webdiplomacy.net/contrib/phpBB3/viewtopic.php?f=16&t=1286&p=60801&hilit=fibonacci#p60801
class ScoringFibonacci extends ScoringSystem {
    private $isSet = false;
    private $shareOfValue = array();

    private function initFib() {
        if ($this.isSet){
            return;
        }
        $total_pot = $this->Game->pot;

        // TODO: Account for surrenders, year eliminated
        private function rank_compare($a, $b){
            return ($a->supplyCenterNo - $b->supplyCenterNo);
        }

        $this.$rankedMembers = usort($this->$Game->ByID, "rank_compare")
        $count_members = count($rankedMembers);
        // $ranks is a an array of (lowest, highest) tied-score index for each member.
        $ranks = array()
        foreach($rankedMembers as $idx=>$member){
            $lowest = $idx;
            $highest = $idx;
            foreach($rankedMembers as $idxCompare->$memberToCompare){
                if ($member->supplyCenterNo == $memberToCompare->supplyCenterNo){
                    if ($idxCompare < $lowest){
                        $lowest = $idxCompare;
                    }
                    elseif ($idxCompare > $highest){
                        $highest = $idxCompare;
                    }
                }
            }
            $ranks[$idx] = array($lowest, $highest)
        }
        // Produce fibonacci sequence
        $fibonacciSequence = array()
        $fibonacciSum = 0;
        for ($i = 0; $i < $count_members; $i++{
            if ($i == 0){
                $fibonacciSequence[$i] = 0;
            }
            elseif ($i == 1 or $i == 2){
                $fibonacciSequence[$i] = 1;
            }
            else{
                $fibonacciSequence[$i] = $fibonacciSequence[$i-1] + $fibonacciSequence[$i-2];
            }
            $fibonacciSum += $fibonacciSequence[$i];

        }
        // Get each member's share of the pot.
        foreach($rankedMembers as $idx=>$member){
            $memberLowestRank = $ranks[$idx][0]
            $memberHighestRank = $ranks[$idx][1]

            $numberTied = $memberHighestRank - $memberLowestRank + 1;
            $totalSumInRange = 0;
            for ($k = $memberLowestRank; $k <= $memberHighestRank; $k++){
                $totalSumInRange += $fibonacciSequence[$k]
            }
            $averageFibValue = $totalSumInRange / $numberTied;
            $shareOfTotalValue = $averageFibValue / $fibonacciSum;
            $this->$shareOfValue[$member] = $shareOfTotalValue 
        }
        )
        
    }

    public function pointsForDraw($Member) {
        $this.initFib();
        return ceil($this->Game->pot * $this->$shareOfValue[$Member]);
    }
	public function pointsForWin($Member) {return $this->Game->pot;}
	public function pointsForSurvive($Member) {return 0;}
	public function abbr() { return 'FIB'; }
	public function longName() {return 'Fibonacci Scoring';} 
}
class ScoringUnranked extends ScoringSystem {
	public function pointsForDraw($Member) { return $Member->bet;}
	public function pointsForWin($Member) {return $Member->bet;}
	public function pointsForSurvive($Member) {return $Member->bet;}
	public function pointsForDefeat($Member) {return $Member->bet;}
	public function abbr() { return 'Unranked'; }
	public function longName() {return 'Unranked';} 
}

class ScoringSoS extends ScoringSystem {
		private $scoreTotal;

		private function initSos() {
			if($this->scoreTotal != null) return;
            $this->scoreTotal = 0;
			foreach($this->Game->Members->ByStatus['Left'] as $Member)
		   		$this->scoreTotal += $Member->supplyCenterNo * $Member->supplyCenterNo;
		   	foreach($this->Game->Members->ByStatus['Playing'] as $Member)
		   		$this->scoreTotal += $Member->supplyCenterNo * $Member->supplyCenterNo;
		}


	public function pointsForDraw($Member) { $this->initSoS(); return ceil($this->Game->pot *(($Member->supplyCenterNo * $Member->supplyCenterNo)/$this->scoreTotal));}
	public function pointsForWin($Member) {return $this->Game->pot;}
	public function pointsForSurvive($Member) {return 0;}
	public function pointsForDefeat($Member) {return 0;}
	public function abbr() { return 'SoS'; }
	public function longName() {return 'Sum-of-Squares Scoring';} 
}
