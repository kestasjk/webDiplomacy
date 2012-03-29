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

require_once('lib/variant.php');
require_once('objects/members.php');

/**
 * Prints data on a game, and loads and manages the collections of members which this game contains.
 * Most used to display the summary, when not loaded as processGame
 *
 * @package Base
 * @subpackage Game
 */
class Game
{
	public static function mapType() {
		if ( isset($_REQUEST['largemap'] ) )
			return 'large';
		elseif ( isset($_REQUEST['mapType']) )
			switch($_REQUEST['mapType'])
			{
				case 'large':
				case 'xml':
				case 'small':
				case 'json':
					return $_REQUEST['mapType'];
			}

		return (isset($_REQUEST['DATC'])?'large':'small');
	}

	public static function mapFilename($gameID, $turn, $mapType=false)
	{
		if( $mapType==false ) $mapType = self::mapType();

		if( defined('DATC') )
			$folder='datc/maps';
		else
			$folder=self::gameFolder($gameID);

		$filename=$turn.'-'.$mapType.'.map';

		return $folder.'/'.$filename;
	}

	public static function wipeCache($gameID, $turn=false)
	{
		$dir = self::gameFolder($gameID);

		if( defined('DATC') )
			libCache::wipeDir($dir, '*json*');
		else
			libCache::wipeDir($dir, ( $turn===false ? '*.*' : '*'.$turn.'-*.*'));
	}

	/**
	 * Create a tree-like folder for a game in a given base folder. e.g. gameFolder('../mapstore',12345)
	 * will return '../mapstore/123/12345', and make sure that that directory exists.
	 * This is used for storing maps, and orderlogs.
	 *
	 * @param $baseDirectory The base directory to write to
	 * @param $gameID The gameID
	 * @return string The game folder
	 */
	public static function gameFolder($gameID)
	{
		if( defined('DATC') )
			return 'datc/maps';
		else
			return libCache::dirID('games',$gameID);
	}

	public static $validPhases = array('Pre-game', 'Diplomacy', 'Retreats', 'Builds', 'Finished');
	/**
	 * The game ID
	 * @var int
	 */
	public $id;

	public $variantID;
	public $Variant;

	/**
	 * The MD5 hash of the game's password
	 * @var string
	 */
	public $password;

	/**
	 * The in-game turn, 0 = Spring 1919, 1 = Autumn 1919
	 * @var int
	 */
	public $turn;

	/**
	 * The game phase: 'Pre-game', 'Diplomacy', 'Retreats', 'Builds', 'Finished'
	 * @var int
	 */
	public $phase;

	public $attempts;

	/**
	 * The deadline when the game must next be processed, a UNIX timestamp
	 * @var int
	 */
	public $processTime;

	/**
	 * True if the game is private
	 * @var bool
	 */
	public $private;

	/**
	 * The game's name
	 * @var string
	 */
	public $name;

	/**
	 * The conditions under which the game ended; 'Won', 'No', 'Drawn'
	 * @var int
	 */
	public $gameOver;

	/**
	 * The number of points in the pot
	 * @var int
	 */
	public $pot;

	/**
	 * The number of minutes per phase, defaults to 1440(24 hours)
	 *
	 * @var int
	 */
	public $phaseMinutes;

	// Arrays of aggregate objects
	/**
	 * An array of Member(/processMember) objects indexed by countryID
	 * @var array
	 */
	public $Members;

	/**
	 * Winner-takes-all/Points-per-supply-center
	 * @var string
	 */
	public $potType;

	/**
	 * Not-processing/Processing/Crashed/Paused
	 * @var string
	 */
	public $processStatus;

	/**
	 * Only used if the game is paused; the amount of seconds remaining until the next turn once the pause is over
	 *
	 * @var int
	 */
	public $pauseTimeRemaining;

	/**
	 * The minimum bet required to join the game; refreshed after each new turn, null if not joinable.
	 *
	 * @var int
	 */
	public $minimumBet;

	/**
	 * Anonymous or not, Yes or No
	 *
	 * @var string
	 */
	public $anon;

	/**
	 * Regular, PublicPressOnly, NoPress
	 *
	 * @var string
	 */
	public $pressType;

	public $lockMode='';

	/**
	 * Normal/Strict
	 * If Strict all players must have completed their orders before the game will proceed.
	 */
	public $missingPlayerPolicy;

	/**
	 * Any value > 0 ends the game after the given turn. Winner is the one with the most SC at this time.
	 * @var int
	 */
	 public $maxTurns;
	 
	/**
	 * Any value > 0 ends the game after a player reaches the given SC count. Winner is the one with the most SC at this time.
	 * @var int
	 */
	 public $targetSCs;
	 
	/**
	 * Some special settings to restrict acces to players baed on their rating
	 * @var int
	 */
	 public $minRating;
	 public $minPhases;
	 public $maxLeft;
	
	/**
	 * @param int/array $gameData The game ID of the game to load, or the array of its database row
	 * @param string[optional] $lockMode The database locking phase to use; no locking by default
	 */
	public function __construct($gameData, $lockMode = NOLOCK)
	{
		$this->lockMode = $lockMode;

		/* If a Game has already been loaded it gets moved out of the way
		 *
		 * We have to use $GLOBALS['Game'] instead of global $Game;, because global $Game; only
		 * creates a reference to the global, and unsetting it doesn't unset the global itself.
		 * Unsetting $GLOBALS['Game'] unsets the global itself, not just the local reference.
		 */
		unset($GLOBALS['Game']);

		$GLOBALS['Game'] = $this;

		if ( $lockMode == NOLOCK && is_array($gameData) )
			$this->loadRow($gameData);
		else
		{
			if( is_array($gameData) )
				$this->id = (int)$gameData['id'];
			else
				$this->id = (int) $gameData;

			$this->load();
		}

		$this->loadMembers();

		// TODO: Make this check work with variants properly
		//if( !( defined("DATC") or $this->phase != "Diplomacy" or count($this->Members->ByID) == count($this->Variant->countries) ) )
		//	trigger_error("Game loaded incorrectly");

		if( $this->processStatus=='Paused' )
		{
			if( (isset($this->processTime)||!is_null($this->processTime))
				|| (!isset($this->pauseTimeRemaining) || is_null($this->pauseTimeRemaining) ))
				trigger_error("Paused game timeout values incorrectly set.");
		}
		elseif( $this->processStatus!='Crashed' && (
			( isset($this->pauseTimeRemaining)||!is_null($this->pauseTimeRemaining) )
			|| ( !isset($this->processTime)||is_null($this->processTime) ) ) )
			trigger_error("Not-paused game process-time values incorrectly set.");
	}

	private $isMemberInfoHidden;

	/**
	 * Should members be hidden for this game and this viewer?
	 *
	 * @return boolean
	 */
	public function isMemberInfoHidden()
	{
		global $User;

		if ( !isset($this->isMemberInfoHidden) )
		{
			/*
			 * Members aren't hidden if either:
			 * - The game isn't anonymous
			 * - The game is finished
			 * - The user is a moderator who isn't in the game
			 */
			if ( $this->anon == 'No' || $this->phase == 'Finished' || ($User->type['Moderator'] && !isset($this->Members->ByUserID[$User->id])) )
				$this->isMemberInfoHidden = false;
			else
				$this->isMemberInfoHidden = true;
		}

		return $this->isMemberInfoHidden;

	}

	function loadRow(array $row)
	{
		foreach( $row as $name=>$value )
		{
			$this->{$name} = $value;
		}

		// If there is a password the game is private
		$this->private = isset($this->password);

		$this->Variant = $GLOBALS['Variants'][$this->variantID];
	}

	/**
	 * Reload the variables which are stored within this object specificially, ie everything
	 * except aggregates
	 */
	function load()
	{
		global $DB;

		$row = $DB->sql_hash("SELECT
			g.id,
			g.variantID,
			LOWER(HEX(g.password)) as password,
			g.turn,
			g.phase,
			g.processTime,
			g.name,
			g.gameOver,
			g.attempts,
			g.pot,
			g.potType,
			g.phaseMinutes,
			g.processStatus,
			g.pauseTimeRemaining,
			g.minimumBet,
			g.anon,
			g.pressType,
			g.maxTurns,
			g.targetSCs,
			g.minRating,
			g.minPhases,
			g.maxLeft,
			g.missingPlayerPolicy
			FROM wD_Games g
			WHERE g.id=".$this->id.' '.$this->lockMode);

		if ( ! isset($row['id']) or ! $row['id'] )
		{
			libHTML::error("Game not found; ensure a valid game ID has been given. Check that this game hasn't been canceled, you may have received a message about it on your <a href='index.php' class='light'>home page</a>.");
		}

		$this->loadRow($row);
	}

	/**
	 * Reload the Members array
	 */
	function loadMembers()
	{
		$this->Members = $this->Variant->Members($this);
	}

	function isJoinable()
	{
		global $User;

		if( $this->Members->isJoined() ) return false;

		if( !$User->type['User'] ) return false;

		switch($this->phase)
		{
			case 'Finished': return false;
			case 'Pre-game':
				if(count($this->Members->ByID)==count($this->Variant->countries))
					return false;
				elseif($User->points < $this->minimumBet )
					return false;
				else
					return true;
			default:
				if(count($this->Members->ByStatus['Left'])==0)
					return false;
				elseif($User->points < $this->minimumBet )
					return false;
				else
					return true;
		}
	}

	/**
	 * A textual representation of the game over conditions
	 *
	 * @param bool[optional] $map Optional, false by default. If true the text will be without HTML, for the map
	 *
	 * @return string Either HTML or text depending on whether map.php is calling
	 */
	function gameovertxt($map=FALSE)
	{
		assert ('$this->gameOver != "No"');

		switch($this->gameOver)
		{
			case 'Won':
				foreach($this->Members->ByStatus['Won'] as $Winner);
				return 'Game won by '.($map ? $Winner->username : $Winner->profile_link() );

			case 'Drawn':
				return 'Game drawn';
		}
	}

	/**
	 * Return the in-game turn in text format. 0 = Spring 1901 , 1 = Autumn 1901, etc.
	 * It can use the Game object's turn, or a supplied $gdate
	 *
	 * @param int[optional] $turn If this optional parameter is not supplied the Game's turn is used
	 *
	 * @return string The game turn in text format
	 */
	function datetxt($turn = false)
	{
		if( $turn === false )
			$turn = $this->turn;

		return $this->Variant->turnAsDate($turn);
	}

	/**
	 * The Game's phase in textual format
	 *
	 * @return string
	 */
	function modetxt()
	{
		return $this->phase;
	}

	/**
	 * Return the next process time in textual format, in terms of time remaining
	 *
	 * @return string
	 */
	function processTimetxt()
	{
		if ( $this->processTime < time() )
			return "Now";
		else
			return libTime::remainingText($this->processTime);
	}

	static function gamesCanProcess()
	{
		global $Misc;

		static $gamesCanProcess;

		if( !isset($gamesCanProcess) )
		{
			$gamesCanProcess=true;

			if( defined('DATC') )
				$gamesCanProcess = true;
			elseif( $Misc->Panic )
				$gamesCanProcess = false;
			elseif( (time()-$Misc->LastProcessTime) > Config::$downtimeTriggerMinutes*60 )
				$gamesCanProcess = false;
		}

		return $gamesCanProcess;
	}

	/**
	 * Do we need to be processed? Once locked this gives the final test that the game
	 * is ready to go through. Checks if either everyone is ready or the time is up,
	 * and always gives false if the game is finished.
	 *
	 * @return boolean
	 */
	function needsProcess()
	{
		global $Misc, $DB;

		/*
		 * - Games are processing as normal
		 * - The game isn't finished
		 * - The game isn't crashed or paused
		 * - The game isn't in strict mode and missing a players completed moves
		 * - The game is either:
		 * 		- Out of time for the phase
		 * 		- Or either:
		 * 			- It's a normal order-related phase and everyone is ready to proceed
		 * 			- Or it's a pre-game phase and enough people have joined, and it's not a live game
		 */
		if( self::gamesCanProcess() && $this->phase!='Finished' && $this->processStatus=='Not-processing' &&
			!( $this->missingPlayerPolicy=='Strict'&&!$this->Members->isComplete() ) && (
				time() >= $this->processTime
				|| ( ($this->phase!='Pre-game' && $this->Members->isReady() )
					|| ($this->phase=='Pre-game' && count($this->Members->ByID)==count($this->Variant->countries) && $this->phaseMinutes>30 ) )
				)
			)
		{
			/**
			 * Special CD for the first turns
			 * In the first turns extend the gamephase and CD all users who failed to enter an order.
			 */
			$specialCD=(isset(Config::$specialCD) ? (Config::$specialCD) : 0);
			if (($this->turn < $specialCD) && (count($this->Variant->countries) > 2))
			{
				require_once "lib/gamemessage.php";
				$foundNMR=false;
				
				foreach($this->Members->ByID as $Member)
				{
					if ($Member->missedPhases > 0)
					{
						$foundNMR=true;
						if ($Member->status=='Playing') {
							$DB->sql_put(
								"INSERT INTO wD_CivilDisorders ( gameID, userID, countryID, turn, bet, SCCount )
									VALUES ( ".$this->id.", ".$Member->userID.", ".$Member->countryID.", ".$this->turn.", ".$Member->bet.", ".$Member->supplyCenterNo.")"
								);
							$DB->sql_put("UPDATE wD_Members SET status='Left' WHERE id = ".$Member->id);
							unset($this->Members->ByStatus['Playing'][$Member->id]);
							$Member->status = 'Left';
							$this->Members->ByStatus['Left'][$Member->id] = $Member;
							libGameMessage::send(0, 'GameMaster', 'NMR from '.$Member->country.'. Send the country in CD.', $this->id);
						}
					}
				}				
				if ($foundNMR) {
					if (time() >= $this->processTime) {
						$this->processTime = time() + $this->phaseMinutes*60;
						$this->minimumBet = $this->Members->pointsLowestCD();
						$DB->sql_put("UPDATE wD_Games 
										SET processTime = ".$this->processTime.",
										attempts = 0,
										minimumBet = ".$this->minimumBet."
										WHERE id = ".$this->id);
						libGameMessage::send(0, 'GameMaster', 'Missing orders for '.$this->Variant->turnAsDate($this->turn).' ('.$this->phase.'). Extending phase. You need to search for a replacement to make this game progress.', $this->id);
						$this->Members->updateReliability();
					}
					return false;
				}
			}
			return true;
		}	
		else
			return false;			
	}
}


?>
