<?php
/*

	This file is part of the Rinascimento variant for webDiplomacy

	The Rinascimento variant for webDiplomacy" is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either 
	version 3 of the License, or (at your option) any later version.

	The Rinascimento variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	0.2:   initial install
	0.5:   map and borders ready
	0.9:   first version live
	1.0:   Updated largemap / new code for the neutral units / NoMove improved
	1.1:   New Pot distribution
	1.2:   Edited icons for armies and fleets / Edited drawmap file to change size of square behind units (23-07-2010)
	1.3:   Rules updated with the description of the new pot distribution system (27-07-2010)
	1.4:   Positions of unit icons edited in the install file (both small and large map) (27-07-2010)
	1.5:   Edited icons for supply centers in large map / Edited SC_large file, now 19x20 (31-07-2010)
	1.5.1: Fixed error in new pot disribution.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class RinascimentoVariant extends WDVariant {
	public $id         =29;
	public $mapID      =29;
	public $name       ='Rinascimento';
	public $fullName   ='Rinascimento';
	public $description='The Rinascimento variant allows 12 players (+ 1 neutral force) to struggle for the supremacy in Italy, during the Renaissance.';
	public $author     ='Emmanuele Ravaioli (Tadar Es Darden) and Francesco Malossini';
	public $adapter    ='Emmanuele Ravaioli / Oliver Auth';
	public $version    ='1.5.1';

	public $countries=array(
		'Ferrara','Firenze','French','Genova','Milano','Napoli','Pisa',
		'Savoia','Siena','Stato della Chiesa','Turkish','Venezia','Impartial');

	public function __construct() {
		parent::__construct();

		// Set starting Units, save the Unit-ID of the "No-Move"-unit
		$this->variantClasses['adjudicatorPreGame'] = 'Rinascimento';
		
		// Color the territories, Draw the "neutral" SC's
		$this->variantClasses['drawMap']            = 'Rinascimento';

		// Javascript corrections (Build everywhere, and No-Move)
		$this->variantClasses['OrderInterface']     = 'Rinascimento';
		// Build everywhere
		$this->variantClasses['processOrderBuilds'] = 'Rinascimento';
		$this->variantClasses['userOrderBuilds']    = 'Rinascimento';
		
		// Winner needs ROME + Custom Point distribution
		$this->variantClasses['processMembers']     = 'Rinascimento';
		
		// Custom Point distribution based on growth
		$this->variantClasses['panelMember']        = 'Rinascimento';
		$this->variantClasses['userMember']         = 'Rinascimento';
		$this->variantClasses['processMember']      = 'Rinascimento';

		// Neutral units:
		$this->variantClasses['processGame']        = 'Rinascimento';
		$this->variantClasses['panelGameBoard']     = 'Rinascimento';
		$this->variantClasses['panelGame']          = 'Rinascimento';
		$this->variantClasses['Chatbox']            = 'Rinascimento';
		$this->variantClasses['panelMembersHome']   = 'Rinascimento';
		$this->variantClasses['panelMembers']       = 'Rinascimento';		

	}
	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 33;
	}
	
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1454);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1454);
		};';
	}

	// This function returns the points value of the player (Member)
	public $all_lost;
	public $all_gain;
	
	function PotShare($member)
		{
		// Initial occupation percent of each country (sum=1):
		$i_percent = array (1 , 0.037, 0.074, 0.037, 0.074,
					0.111, 0.148, 0.037, 0.074,
					0.037, 0.148, 0.074, 0.148);
								
		// Count the units and SC's of all active (or left) players
		$all_value  = $member->Game->Members->supplyCenterCount('Playing') + $member->Game->Members->unitCount('Playing');
		$all_value += $member->Game->Members->supplyCenterCount('Left')    + $member->Game->Members->unitCount('Left');

		// The number of players
		$playerNo = count($i_percent) - 1;
		
		// The basic pot-share of each player (pot split even between all players)
		$pot_share = 1 / $playerNo;

		// if not done precalculate the sums of all "lost" and "gained" percents.
		// This function gets called quite often, so store the values in a variable to avoid a new claculation.
		if ( ($this->all_lost == 0) && ($this->all_gain == 0) ){
		
			// Check all players if they have a different share of the SC's and units as they had at the start of the game
			for($id=1; $id<=$playerNo; $id++) {
				$diff = (($member->Game->Members->ByCountryID[$id]->unitNo + $member->Game->Members->ByCountryID[$id]->supplyCenterNo ) / $all_value) - $i_percent[$id];
				// We count how much percent (of the pot) are lost (to distribute them to the winning players)
				if ($diff < 0 ) {
					$this->all_lost+= abs($diff / $i_percent[$id] * $pot_share);
				// if a players "value" is more we count all these gain of "value" related to the initial value
				} elseif ($diff > 0 ) {
					$this->all_gain+= $diff;
		} } }

		// The current players occupation-percentage compared to all SC and units on the board
		$my_percent = ($member->unitNo + $member->supplyCenterNo) / $all_value;

		// Easy: If a player looses value remove points the same percentage he lost his sc and units compared to the start.
		// Eg. if he has 2 units and 2 sc at the start and now has 1 sc and 1 unit he losses half the bet.
		if ( round($my_percent,3) < $i_percent[$member->countryID]) {
			$pot_share -= ( $i_percent[$member->countryID] - $my_percent) / $i_percent[$member->countryID] * $pot_share;

		// More complex: If a user gained value we check how much value he gained in comparison to his starting position
		// and than we compare this to all other players that gained value.
		// an player with 1 sc and 1 unit has now 2 sc and 2 units is as good as a player with
		// 3 staring sc and 3 starting units that has now 6 of each
		// Than we take the total points lost and redistribute them to these players
		} elseif (round($my_percent,3) > $i_percent[$member->countryID]) {
			$pot_share += ($my_percent - $i_percent[$member->countryID]) / $this->all_gain * $this->all_lost;
		}

		return $pot_share;
	}

}
?>
