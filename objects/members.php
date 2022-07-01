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

require_once(l_r('objects/member.php'));
/**
 * An object which manages the relationship with a game and its members. Often when
 * dealing with a certain game you're actually only dealing with the members of the
 * game.
 *
 * @package Base
 * @subpackage Game
 */
class Members
{
	protected $Game;

	public $ByOrder;
	public $ByID;
	public $ByUserID;
	public $ByCountryID;
	public $ByStatus;

	function SCPercents()
	{
		$SCPercents=array();

		$totalSCs = $this->supplyCenterCount();

		if( $totalSCs == 0 ) // We must be pre-game
		{
			for($countryID=1; $countryID<=count($this->Game->Variant->countries); $countryID++)
				if($countryID == 'Russia')
					$SCPercents[$countryID] = round((4/(3*6+4))*100);
				else
					$SCPercents[$countryID] = round((3/(3*6+4))*100);
		}
		else
		{
			for($countryID=1; $countryID<=count($this->Game->Variant->countries); $countryID++)
				$SCPercents[$countryID] = round(
						($this->ByCountryID[$countryID]->supplyCenterNo / $totalSCs)*100
					);
		}

		$sum=0;
		foreach($SCPercents as $countryID=>$percent)
			$sum += $SCPercents[$countryID];

		// Add the rounding error onto a countryID with a few SCs, where it won't be noticed
		foreach($SCPercents as $countryID=>$percent)
			if($percent>(1/8*100))
			{
				$SCPercents[$countryID] += 100-$sum;
				break;
			}

		return $SCPercents;
	}

	/**
	 * Calculate the points value of a single supply center in this game
	 *
	 * @return float
	 */
	function pointsPerSupplyCenter()
	{
		return ((float)$this->Game->pot / (float)$this->supplyCenterCount('Playing'));
	}

	static $votes = array('Draw','Pause','Cancel','Concede');

	function votesPassed()
	{
		global $DB;
		$votes=self::$votes;

		$concede=0;

		//gets the number of players that are still playing for use of the bot
		$playerCount = count($this->ByStatus['Playing']);

		//initialize an int to keep track of how many bots are playing 
		$botCount = 0; 
		foreach($this->ByStatus['Playing'] as $Member)
		{
			$userPassed = new User($Member->userID);

			//Bot votes are handled differently
			if($userPassed->type['Bot']) 
			{
				$botCount += 1;

				//Each bot will automatically vote for a pause
				$botVotes = array('Pause'); 
				$botSC = $Member->supplyCenterNo;

				//This loop checks to see if the bot is winning or tied for the lead, since it will not vote draw or cancel in these cases
				foreach($this->ByStatus['Playing'] as $CurMember) 
				{
					if ($botSC < $CurMember->supplyCenterNo)
					{
						$botVotes = array('Draw','Pause','Cancel');
					}
				}

				//This variable and the following query get the SC count for 2 years ago, which will be used to prevent bot stalemates
				$oldSC = 0; 
				list($oldSC) = $DB->sql_row("SELECT COUNT(ts.terrID) FROM wD_TerrStatusArchive ts INNER JOIN wD_Territories t ON ( ts.terrID = t.id ) 
				WHERE t.supply='Yes' AND ts.countryID = ".$Member->countryID." AND ts.gameID = ".$this->Game->id." 
				AND t.mapID=".$this->Game->Variant->mapID." AND ts.turn = ".max(0,$this->Game->turn - 4));

				//A bot will draw or cancel if it is stalled out or if it is the first year
				if ($oldSC >= $botSC || $this->Game->turn < 2) 
				{
					$botVotes = array('Draw','Pause','Cancel');
				}
				//A bot will always cancel in the first two years
				elseif ($this->Game->turn < 4) 
				{
					$botVotes = array('Pause','Cancel');
				}
				$votes = array_intersect($votes, $botVotes);
			}
			else
			{
				$votes = array_intersect($votes, $Member->votes);
				if (in_array('Concede',$Member->votes))	$concede++;
			}
		}

		//This condition will force draw the game if the only players left are bots
		if ($playerCount == $botCount) 
		{
			$votes = array('Draw');
		}

		if ($concede >= (count($this->ByStatus['Playing']) - 1) && ($concede > 0))
		{
			$votes[]='Concede';
		}
		
		return $votes;
	}

	function processVotes() {
		global $MC, $DB;
		$Game = $this->Game;
		if( $Game->Members->votesPassed() && $Game->phase!='Finished' )
		{
			$MC->append('processHint',','.$Game->id);

			$DB->get_lock('gamemaster',1);

			$DB->sql_put("UPDATE wD_Games SET attempts=attempts+1 WHERE id=".$Game->id);
			$DB->sql_put("COMMIT");

			require_once(l_r('gamemaster/game.php'));
			$Game = $Game->Variant->processGame($Game->id);
			try
			{
				$Game->applyVotes(); // Will requery votesPassed()
				$DB->sql_put("UPDATE wD_Games SET attempts=0 WHERE id=".$Game->id);
				$DB->sql_put("COMMIT");
			}
			catch(Exception $e)
			{
				if( $e->getMessage() == "Abandoned" || $e->getMessage() == "Cancelled" )
				{
					assert($Game->phase=="Pre-game" || $e->getMessage() == "Cancelled");
					$DB->sql_put("COMMIT");
					return $e->getMessage();
				}
				else
					$DB->sql_put("ROLLBACK");

				throw $e;
			}
		}
		return null;
	}

	function isReady()
	{
		foreach($this->ByStatus['Playing'] as $Member)
			if( !$Member->orderStatus->Ready && !$Member->orderStatus->None )
				return false;

		return true;
	}

	function isCompleted()
	{
		foreach($this->ByStatus['Playing'] as $Member)
			if( !$Member->orderStatus->Completed && !$Member->orderStatus->None )
				return false;

		return true;
	}

	/**
	 * Remove the order status 'Ready' from all members participating
	 */
	function unreadyMembers() 
	{
		global $DB;
		
		foreach($this->ByStatus['Playing'] as $Member) 
		{
			// do not adjust anything if there are no orders this phase
			if( $Member->orderStatus->None ) continue;
			
			$Member->orderStatus->Ready=false;
			
			if( $Member->orderStatus->updated )
				$DB->sql_put("UPDATE wD_Members SET orderStatus='".$Member->orderStatus."' WHERE id = ".$Member->id);
		}
	}

	/**
	 * Checks global $User, sees if he's a member of this game
	 *
	 * @return boolean
	 */
	function isJoined()
	{
		global $User;

		return ( isset($this->ByUserID[$User->id]) );
	}

	/**
	 * Checks global $User, sees if he's a member of this game but is temp banned
	 * from rejoining.
	 *
	 * @return boolean
	 */
	function isTempBanned()
	{
		global $User;
		
		return ( $this->isJoined() && $this->ByUserID[$User->id]->status == "Left" && $User->userIsTempBanned() );
	}

	function makeUserMember($userID)
	{
		$userMember = $this->Game->Variant->userMember($this->ByUserID[$userID]);
		unset($this->ByStatus[$userMember->status][$userMember->id]);

		$this->ByID[$userMember->id] = $userMember;
		$this->ByUserID[$userMember->userID] = $userMember;
		$this->ByStatus[$userMember->status][$userMember->id] = $userMember;
		if(is_array($this->ByCountryID))
			$this->ByCountryID[$userMember->countryID] = $userMember;
	}

	public function __construct(Game $Game)
	{
		$this->Game = $Game;
		$this->load();
	}

	protected function loadMember(array $row)
	{
		return $this->Game->Variant->Member($row);
	}
	function indexMembers()
	{
		$this->ByID=array();
		$this->ByUserID=array();
		$this->ByStatus=array(
			'Playing'=>array(),'Defeated'=>array(),'Left'=>array(),
			'Won'=>array(),'Drawn'=>array(),'Survived'=>array(),'Resigned'=>array()
		);

		if($this->Game->phase == 'Pre-game')
			$this->ByCountryID=null;
		else
			$this->ByCountryID=array();

		foreach($this->ByOrder as $Member)
		{
			$this->ByID[$Member->id] = $Member;
			$this->ByStatus[$Member->status][$Member->id] = $Member;
			$this->ByUserID[$Member->userID] = $Member;

			// If pre-game all countries are 'Unassigned', so members cannot be indexed by countryID.
			if ( $Member->countryID != 0 )
				$this->ByCountryID[$Member->countryID] = $Member;
		}
	}
	public function load()
	{
		global $DB;

		$tabl = $DB->sql_tabl("SELECT m.id AS id,
				m.userID AS userID,
				m.gameID AS gameID,
				m.countryID AS countryID,
				m.status AS status,
				m.orderStatus AS orderStatus,
				m.bet AS bet,
				m.missedPhases as missedPhases,
				m.timeLoggedIn as timeLoggedIn,
				m.newMessagesFrom AS newMessagesFrom,
				m.votes AS votes,
				m.supplyCenterNo as supplyCenterNo,
				m.unitNo as unitNo,
				m.excusedMissedTurns as excusedMissedTurns,
				u.username AS username,
				u.points AS points,
				m.pointsWon as pointsWon,
				IF(s.userID IS NULL,0,1) as online,
				u.type as userType
			FROM wD_Members m
			INNER JOIN wD_Users u ON ( m.userID = u.id )
			LEFT JOIN wD_Sessions s ON ( u.id = s.userID )
			WHERE m.gameID = ".$this->Game->id."
			ORDER BY m.status ASC, m.supplyCenterNo DESC, ".
			($this->Game->anon=='Yes' ? "m.countryID ASC" : "u.points DESC" ).
			$this->Game->lockMode
			);

		$this->ByOrder = array();
		while ( $row = $DB->tabl_hash($tabl) )
		{
			$row['Game'] = $this->Game;

			$Member = $this->loadMember($row);

			$this->ByOrder[] = $Member;
		}

		$this->indexMembers();
	}

	function pointsLowestCD()
	{
		assert('$this->Game->phase != "Pre-game" && $this->Game->phase != "Finished"');

		$pointsLowestCD = false;
		foreach($this->ByStatus['Left'] as $Member)
		{
			$pointsValue = $Member->pointsValueInTakeover();
			if( $pointsLowestCD===false or $pointsLowestCD > $pointsValue )
				$pointsLowestCD = $pointsValue;
		}

		return $pointsLowestCD;
	}

	public function supplyCenterCount($forMemberStatus=false)
	{
		$count=0;

		if($forMemberStatus)
			$Members = $this->ByStatus[$forMemberStatus];
		else
			$Members = $this->ByID;

		foreach($Members as $Member)
			$count += $Member->supplyCenterNo;

		return $count;
	}

	function send($keep, $text)
	{
		foreach($this->ByID as $Member)
			$Member->send($keep, 'No', $text);
		$this->sendToWatchers($keep,$text);
	}

	function sendExcept(Member $notMember, $keep, $text)
	{
		foreach($this->ByID as $id=>$Member)
			if($id != $notMember->id)
				$Member->send($keep, 'No', $text);
		$this->sendToWatchers($keep,$text);
	}

	function sendToPlaying($keep, $text)
	{
		foreach($this->ByStatus['Playing'] as $Member)
			$Member->send($keep, 'No', $text);
		$this->sendToWatchers($keep,$text);
	}

	function sendToWatchers($keep,$text) 
	{
		global $DB;

		$tabl = $DB->sql_tabl('SELECT userID FROM wD_WatchedGames WHERE gameID='.$this->Game->id);
		while($watch=$DB->tabl_hash($tabl))
		{
			notice::send(
				$watch['userID'], $this->Game->id, 'Game',
				$keep, 'No', $text, $this->Game->name, $this->Game->id);

		}
	}

	function cantLeaveReason()
	{
		global $Misc;

		if ( !$this->isJoined() )
			return l_t("not a member");
		elseif($this->Game->phase != 'Pre-game')
			return l_t("game started");
		elseif(count($this->ByID)==count($this->Game->Variant->countries) &&
		       time() + 30*60 > $this->Game->processTime)
			return l_t("game starting soon");
		elseif(time()>$this->Game->processTime)
			return l_t("game starting");
		elseif ( $Misc->Panic )
			return l_t("joining/leaving games disabled while a problem is resolved");
		else
			return false;
	}
}
?>
