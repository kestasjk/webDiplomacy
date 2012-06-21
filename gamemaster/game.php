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

require_once('gamemaster/gamemaster.php');

require_once('objects/game.php');
require_once('gamemaster/members.php');

/**
 * This class creates games, joins players up to games, manages the passing of games
 * from phase to phase, decides when they're over and allocates points when they are,
 * etc. It is the most general part of the gamemaster, just about all game manipulation
 * occurs within this class.
 *
 * @package GameMaster
 */
class processGame extends Game
{
	function gamelog($message)
	{
		global $User;

		$message = libTime::stamp().' (UTC): '.$message."\n\t".__FILE__.
			"\n\t".print_r($this->processSummary(),true)."\n\n-----------------\n\n";

		if( !($fh = fopen(self::gameFolder($this->id).'/gamelog.txt', 'a')) )
			trigger_error("Couldn't open gamelog.txt");

		fwrite($fh, $message);

		fclose($fh);
	}

	private static $processSummaryFields=array('id','attempts','phase','processStatus','minimumBet','pot','gameOver','pauseTimeRemaining');
	function processSummary()
	{
		$a=array();
		foreach(self::$processSummaryFields as $field)
			$a[$field]=$this->{$field};

		if( $this->processTime )
			$a['processTime']=libTime::stamp($this->processTime);
		else
			$a['processTime']='NULL';

		$a['turn']=$this->datetxt();
		$a['members']=$this->Members->processSummary();

		return $a;
	}

	/**
	 * Look for votes that have passed and process them (but only do one), then remove the
	 * game from the process queue if there are no votes left to process.
	 */
	function applyVotes()
	{
		assert('$this->phase != "Finished"');

		$votes = $this->Members->votesPassed();

		$this->gamelog('Applying votes');

		// Only act on one vote at a time ..
		if ( in_array('Draw', $votes) )
		{
			$this->setDrawn();
		}
		elseif ( in_array('Cancel', $votes) )
		{
			$this->setCancelled();
		}
		elseif( in_array('Pause', $votes) )
		{
			$this->togglePause();
		}
		elseif( in_array('Extend', $votes) && $this->processStatus != 'Paused')
		{
			$this->extendPhase();
		}
		elseif( in_array('Concede', $votes) )
		{
			$this->setConcede();
		}
	}

	/**
	 * The game can't start; not enough players. Send messages, refund points, erase game.
	 */
	function setNotEnoughPlayers()
	{
		$this->Members->setNotEnoughPlayers();

		processGame::eraseGame($this->id);

		// This will be caught by gamemaster.php
		throw new Exception("Abandoned", 12345);
	}

	/**
	 * The game has been abandoned. Send messages, erase game.
	 */
	function setAbandoned()
	{
		assert('$this->phase != "Finished"');

		$this->Members->setAbandoned();

		processGame::eraseGame($this->id);

		// This will be caught by gamemaster.php
		throw new Exception("Abandoned", 12345);
	}

	/**
	 * The game has been cancelled. Send messages, refund points, erase game.
	 */
	function setCancelled()
	{
		assert('$this->phase != "Finished"');

		$this->Members->setCancelled();

		processGame::eraseGame($this->id);

		// This will be caught by gamemaster.php
		throw new Exception("Cancelled", 12345);
	}

	/**
	 * An array of game related tables used by backup functions. The table name is the key,
	 * and the game-id column is the field.
	 * $gameTables[$tableName]=$idColumn
	 *
	 * @var array
	 */
	private static $gameTables=array(
			'Games'=>'id',
			'Members'=>'gameID','Orders'=>'gameID','TerrStatus'=>'gameID','Units'=>'gameID',
			'GameMessages'=>'gameID','TerrStatusArchive'=>'gameID','MovesArchive'=>'gameID'
		);

	/**
	 * Returns an array of game IDs in the backup tables.
	 *
	 * @return array
	 */
	static function backedUpGames()
	{
		global $DB;

		//self::backupTables();

		$tabl = $DB->sql_tabl(
			"SELECT b.id, b.name, g.id FROM wD_Backup_Games b
			LEFT JOIN wD_Games g ON ( b.id = g.id )"
		);

		$games = array();
		while( list($id, $name, $liveID) = $DB->tabl_row($tabl) )
			$games[]=array($id,($id==$liveID ? '<a href="board.php?gameID='.$id.'">'.$name.'</a>' : $name));

		return $games;
	}

	/**
	 * Deletes all the backup tables.
	 */
	static function wipeBackups()
	{
		global $DB;

		foreach(self::$gameTables as $tableName=>$idColName)
			$DB->sql_put("DROP TABLE IF EXISTS wD_Backup_".$tableName);
	}

	/**
	 * Restores a game from a backup table, or throws an exception if it isn't in a backup table.
	 * Will erase the live game before restoring it from backup.
	 *
	 * @param $gameID The game ID
	 */
	static function restoreGame($gameID)
	{
		global $DB;

		list($countLive) = $DB->sql_row("SELECT COUNT(id) FROM wD_Games WHERE id = ".$gameID);
		list($countBackup) = $DB->sql_row("SELECT COUNT(id) FROM wD_Backup_Games WHERE id = ".$gameID);

		if ( $countBackup == 0 )
			throw new Exception("Game does not exist in backups, cannot restore.");

		if ( $countLive > 0 )
			self::eraseGame($gameID);

		foreach(self::$gameTables as $tableName=>$idColName)
			$DB->sql_put(
				"INSERT INTO wD_".$tableName."
				SELECT * FROM wD_Backup_".$tableName." WHERE ".$idColName." = ".$gameID
			);

		$DB->sql_put("COMMIT");
	}

	/**
	 * Create the backup tables
	 */
	static private function backupTables()
	{
		global $DB;

		foreach(self::$gameTables as $tableName=>$idColName)
			$DB->sql_put("CREATE TABLE IF NOT EXISTS wD_Backup_".$tableName." LIKE wD_".$tableName);
	}

	/**
	 * Backup a certain game, will create the tables needed if not present, and delete the game from
	 * the backup tables if already backed up.
	 *
	 * @param $gameID
	 */
	static function backupGame($gameID, $commitNow=true)
	{
		global $DB;

		//self::backupTables();

		foreach(self::$gameTables as $tableName=>$idColName)
			$DB->sql_put("DELETE FROM wD_Backup_".$tableName." WHERE ".$idColName." = ".$gameID);

		foreach(self::$gameTables as $tableName=>$idColName)
			$DB->sql_put(
				"INSERT INTO wD_Backup_".$tableName."
				SELECT * FROM wD_".$tableName." WHERE ".$idColName." = ".$gameID
			);

		if ( $commitNow )
			$DB->sql_put("COMMIT");
	}

	/**
	 * Deletes a live game's data. If run within Game::process() an appropriate exception should be
	 * thrown after running this.
	 *
	 * @param $gameID
	 */
	static function eraseGame($gameID)
	{
		global $DB;

		self::wipeCache($gameID);
		self::backupGame($gameID, false);

		foreach(self::$gameTables as $tableName=>$idColName)
			$DB->sql_put("DELETE FROM wD_".$tableName." WHERE ".$idColName." = ".$gameID);

		$DB->sql_put("COMMIT");
	}

	/**
	 * Load a new processMembers() into $this->Members (overrides Game::loadMembers, which loads Members())
	 */
	function loadMembers()
	{
		$this->Members = $this->Variant->processMembers($this);
	}

	/**
	 * Create a new game, insert it into the database, return the Game object.
	 *
	 * @param string $name The name of the game
	 * @param string $password The password required to join the game
	 * @param int $bet The amount which needs to be bet into the game to join initially
	 * @param string $potType 'Winner-takes-all' or 'Points-per-supply-center'
	 *
	 * @return Game The object corresponding to the new game
	 */
	public static function create($variantID, $name, $password, $bet, $potType, $phaseMinutes, $joinPeriod, $anon, $press
		,$maxTurns 
		,$targetSCs 
		,$minRating 
		,$minPhases
		,$specialCDturn 
		,$specialCDcount
		,$rlPolicy
		)
	{
		global $DB;

		if ( $name == 'DATC-Adjudicator-Test' and ! defined('DATC') )
		{
			throw new Exception("The game name 'DATC-Adjudicator-Test'
								is reserved for the automated DATC tester.");
		}

		// Find a unique game name
		$unique = false;
		$i = 1;
		while ( ! $unique )
		{
			list($count) = $DB->sql_row("SELECT COUNT(id) FROM wD_Games WHERE name='".$name.($i > 1 ? '-'.$i : '')."'");

			if ( $count == 0 )
				$unique = true;
			else
				$i++;
		}
		
		/*
		 * The password is not salted, because it's given out to several people anyway and it
		 * isn't worth changing the existing behaviour.
		 */
		$pTime = time() + $joinPeriod*60;
		$pTime = $pTime - fmod($pTime, 300) + 300;	// for short game & phase timer
		
		// Fix the bet to 1 for 2-player games.
		$Variant=libVariant::loadFromVariantID($variantID);
		if (count($Variant->countries)<3)
			$bet=1;
		
		// Check the starting SCs for each player (multiplied by 2)...
		$sql='SELECT count(*)*2 FROM wD_Territories
				WHERE mapID='.$Variant->mapID.' AND supply="Yes" AND countryID>0 
				GROUP BY countryID ASC LIMIT 1';
		list($minSC) = $DB->sql_row($sql);
		
		// TargetSCs greater than any starting SCs
		if ($targetSCs != 0 && $minSC > $targetSCs)
			$targetSCs = $minSC;
			
		// Set the target SCs maximum to the available SCs
		if ($Variant->supplyCenterCount < $targetSCs)
			$targetSCs = $Variant->supplyCenterCount;
										
		$DB->sql_put("INSERT INTO wD_Games
					SET variantID=".$variantID.",
						name = '".$name.($i > 1 ? '-'.$i : '')."',
						potType = '".$potType."',
						pot = 0, /* This will receive the first player's bet soon */
						minimumBet = ".$bet.",
						anon = '".$anon."',
						pressType = '".$press."',
						".( $password ? "password = UNHEX('".md5($password)."')," : "").
						"processTime = ".$pTime.",
						maxTurns = ".$maxTurns.", 
						targetSCs = ".$targetSCs.", 
						minRating = ".$minRating.", 
						minPhases = ".$minPhases.", 
						specialCDturn = ".$specialCDturn.", 
						specialCDcount = ".$specialCDcount.", 
						rlPolicy = '".$rlPolicy."', 
						phaseMinutes = ".$phaseMinutes);

		$gameID = $DB->last_inserted();

		return $Variant->processGame($gameID);
	}

	/**
	 * Set the game as crashed, if it has been found to have been set to Processing for a long period. Also
	 * sends messages to the players notifying them.
	 */
	function crashed()
	{
		global $Misc, $DB;

		assert('$this->processStatus != "Paused"');

		$this->gamelog('Game crashed');

		// The game has crashed
		$this->Members->sendToPlaying('No','The game has not completed a process cycle, due to either a
		software problem (e.g. a bug) or a hardware failure (e.g. overloading). It has been stopped until
		an admin checks on it.');

		$DB->sql_put(
			"UPDATE wD_Games
			SET processStatus = 'Crashed', processTime = NULL, attempts=0
			WHERE id = ".$this->id
		);

		$this->processStatus = 'Crashed';
		unset($this->processTime);

		$Misc->GamesCrashed++;

		$Misc->write();
	}

	/**
	 * Create a new game from a game ID; create the parent for UPDATE so that
	 * no-one else can process this game at the same tiem
	 *
	 * @param int $id Game ID
	 */
	public function __construct($id)
	{
		if( is_array($id) ) $id=(int)$id['id'];

		$GLOBALS['GAMEID'] = (int)$id;

		parent::__construct((int)$id, UPDATE);
	}

	/**
	 * Find the minimum number of points needed to join the game, or NULL if it can't be joined. Will be the cheapest
	 * CD player or the price of joining pre-game, or null. Should be run whenever the status of a member changes.
	 */
	public function resetMinimumBet()
	{
		global $DB;

		$minimumBet=false;
		if ( $this->phase == 'Pre-game' )
		{
			if ( count($this->Members->ByID)<count($this->Variant->countries) )
				$minimumBet = ceil($this->pot / count($this->Members->ByID));
		}
		elseif ( $this->phase != 'Finished' )
		{
			$minimumBet = $this->Members->pointsLowestCD();
		}

		// The new value isn't the same, and it isn't comparing false with null (which are the same in this case)
		if ( $minimumBet != $this->minimumBet && !( $minimumBet==false && is_null($this->minimumBet)) )
		{
			$DB->sql_put("UPDATE wD_Games SET minimumBet = ".($minimumBet?$minimumBet:'NULL')." WHERE id=".$this->id);
			$this->minimumBet = $minimumBet;
		}
	}

	/**
	 * Process; the main gamemaster function for managing games; processes orders, adjudicates them,
	 * applies the results, creates new orders, updates supply center/army numbers, and moves the
	 * game onto the next phase (or updates it as won)
	 */
	function process()
	{
		global $DB;

		$this->gamelog('Beginning process');

		require_once('gamemaster/orders/order.php');
		require_once('gamemaster/orders/diplomacy.php');
		require_once('gamemaster/orders/retreats.php');
		require_once('gamemaster/orders/builds.php');
		require_once('gamemaster/adjudicator/pregame.php');
		require_once('gamemaster/adjudicator/diplomacy.php');
		require_once('gamemaster/adjudicator/retreats.php');
		require_once('gamemaster/adjudicator/builds.php');


		/*
		 * Process the game. In a nutshell:
		 *
		 * - Adjudicate
		 * 		- Save the current state of the game (Units,Orders,TerrStatus) to the archives if we have entered a new turn
		 * 		- Wipe the game's orders
		 * - Re-count the number of units and supply centers each member has
		 * - Move to the next phase or set the game to over
		 * - Wipe old TerrStatus information if entering a new turn
		 * - Create new orders for the current phase
		 * - Set the next date for game processing
		 */

		/*
		 * Except for wiping redundant TerrStatus data after a new turn and generating new orders
		 * this function is the only place which will interact with and manipulate the Orders,
		 * Moves, TerrStatus, Units and *Archive tables
		 *
		 * Also, except for pre-game adjudication, this function doesn't interact with Games or Members
		 */
		$this->adjudicate();

		/*
		 * The phase has been processed; Units and TerrStatus are fully updated, archives
		 * have been taken now only Games and Members need to be updated, and new orders added
		 */
		$this->Members->countUnitsSCs();
		
		/*
		 * Clear all extend-votes for the current phase
		 */
		$this->Members->clearExtendVotes();

		if( $this->turn<1 )
		{
			Game::wipeCache($this->id);
		}
		elseif ( $this->phase == 'Retreats' or $this->phase == 'Builds' )
		{
			/*
			 * If we have just processed either of these two phases then there will already be
			 * a map drawn for our turn, which don't have the new moves drawn onto them.
			 * The current turn's map is deleted
			 */
			Game::wipeCache($this->id,$this->turn);
		}

		/*
		 * Move to a new phase, and also a new turn if necessary. The turn changes when it's a
		 * new Diplomacy phase that hasn't come from a Pre-game phase, or when it's a Finished
		 * phase
		 * This function will also check to see if the game is now finished. Once complete Game
		 * and Members will be up-to-date with the next turn
		 *
		 * This is the function that calls the set* functions, indicating member/game
		 * win/loss/draw etc, and giving points and messages accordingly.
		 */
		$newTurn = $this->changePhase();

		if ( $newTurn )
		{
			/*
			 * We have entered a new turn; clean the TerrStatus records of the previous turn's
			 * retreatingUnitID, occupiedFromTerrID, standoff data, which is no longer valid.
			 */

			$this->cleanTerrStatus();
		}

		if ( $this->phase == 'Finished' )
		{
			/*
			 * The game has finished, all that remains is to remove the TerrStatus
			 * and Units data from the active tables
			 */
			$DB->sql_put("DELETE FROM wD_TerrStatus WHERE gameID = ".$this->id);
			$DB->sql_put("DELETE FROM wD_Units WHERE gameID = ".$this->id);
		}
		else
		{
			// If the game hasn't finished let the active players know the game is in a new phase
			if( $this->phase != 'Finished' )
				$this->Members->notifyGameProgressed();

			/*
			 * The minimum-bet-to-join may have changed, based on supply-centers or people who have
			 * newly Left or been Defeated, recalculate the minimum bet here.
			 */
			$this->resetMinimumBet();

			/*
			 * We are moving on to the next phase; create new orders, and set players who
			 * have new orders to no longer be ready
			 */
			switch($this->phase)
			{
				case 'Diplomacy':
					$PO = $this->Variant->processOrderDiplomacy();
					$PO->create();
					break;
				case 'Retreats':
					$PO = $this->Variant->processOrderRetreats();
					$PO->create();
					break;
				case 'Builds':
					$PO = $this->Variant->processOrderBuilds();
					$PO->create();
					break;
			}

			/*
			 * The missed phase counter goes up for all players that need to log on in this phase,
			 * (all players which have orders to enter) and they need to log on to bring it down.
			 */
			$DB->sql_put("UPDATE wD_Members m
						LEFT JOIN wD_Orders o ON ( o.gameID = m.gameID AND o.countryID = m.countryID )
						SET m.orderStatus=IF(o.id IS NULL, 'None',''),
							missedPhases=IF(m.status='Playing' AND NOT o.id IS NULL, missedPhases + 1, missedPhases)
						WHERE m.gameID = ".$this->id);

			$this->processTime = time() + $this->phaseMinutes*60;

			$DB->sql_put("UPDATE wD_Games SET processTime = ".$this->processTime." WHERE id = ".$this->id);
		}
	}

	/**
	 * Adjudicate; taking entered orders, converting them to moves, processing the moves,
	 * applying the results of the rules.
	 */
	function adjudicate()
	{
		global $DB;

		$DB->sql_put("DELETE FROM wD_Moves WHERE gameID=".$GLOBALS['GAMEID']);

		/*
		 * Adjudicate:
		 * - Make sure all orders are complete, no half-orders
		 * - Enter the orders and units into the Moves table,
		 *   which contains all the info needed for adjudication
		 * - Process the moves table (the actual adjudication)
		 * - Apply the orders depending on whether they were successful or not
		 *   (This usually means moving/creating/destroying Units)
		 * - Wipe the Moves table
		 * - Update which countryID owns which territory (Units occupations->TerrStatus)
		 * - Archive and then wipe the orders
		 *
		 * The adjudicate functions
		 * - Do not interact with the Units or TerrStatus tables
		 * 		(with the exception of the pre-game "adjudicator")
		 * - Do not have to worry about Child coasts
		 *
		 * The processOrders module
		 * - Its interaction with the Moves table is limited to:
		 * 		- Insert moves
		 * 		- Retrieve status of moves (success or fail)
		 * - Must remember to deal with Child coasts
		 * - Update the Orders, Units, and TerrStatus tables
		 *
		 * Orders -> Moves(No coasts) -> Order success/failure
		 * -> Apply orders to Units(Coasts) and TerrStatus(No coasts)
		 * -> Update TerrStatus(No coasts) owners
		 */
		switch( $this->phase )
		{
			case 'Pre-game':
				/*
				 * If there aren't enough players this will throw an exception, which
				 * will be handled by gamemaster.php
				 */
				$adj=$this->Variant->adjudicatorPreGame();
				$adj->adjudicate();

				// At the moment we have a number indexed member list, we need countryID-indexed
				$this->loadMembers();

				return; // We don't need to do archiving/cleaning in pre-game

			case 'Diplomacy':
				$PO=$this->Variant->processOrderDiplomacy();
				$PO->completeAll();
				$PO->toMoves();

				$adj=$this->Variant->adjudicatorDiplomacy();
				$standoffs = $adj->adjudicate();

				/*
				 * Moves are archived before being applied, because Units.terrID is needed to
				 * store where units came from along with the Moves data. After applying the
				 * moves any units will have been moved, so there's no way of knowing where they
				 * came from.
				 */
				$PO->archiveMoves();
				$PO->apply($standoffs);

				unset($standoffs);

				break;

			case 'Retreats':
				$PO=$this->Variant->processOrderRetreats();
				$PO->completeAll();
				$PO->toMoves();

				$adj=$this->Variant->adjudicatorRetreats();
				$adj->adjudicate();

				$PO->archiveMoves();
				$PO->apply();

				break;

			case 'Builds':
				$PO=$this->Variant->processOrderBuilds();
				$PO->completeAll();
				$PO->toMoves();

				$adj=$this->Variant->adjudicatorBuilds();
				$adj->adjudicate();

				$PO->apply();

				/*
				 * With unit placing units aren't needed, but apply() will
				 * change some blank destroy orders, which will need to be
				 * archived
				 */
				$PO->archiveMoves();

				break;

			default:
				$PO=$this->Variant->processOrder();
		}

		// Update who owns which territories, assumes the Units table is fully updated
		$this->updateOwners();

		// Archive the results
		$this->archiveTerrStatus();

		// Wipe the orders for the next phase
		$PO->wipe();
	}

	/**
	 * Use the recently updated Units table to update the TerrStatus table,
	 * regarding who owns which territory and which units occupy which territories
	 */
	protected function updateOwners()
	{
		// (This is protected, and not private, so that the DATC code has access)
		global $DB;

		if ( 0 == ($this->turn % 2 ) )
		{
			/*
			 * It is spring, no supply centers can change owner, and if it isn't
			 * yet owned by anyone it's owned by Neutral
			 */
			$countryID = "IF(t.supply='No',u.countryID,'Neutral')";
			$updateCountryID = '';

			/*
			 * An additional query is needed so that non-supply center territories
			 * which are owned by another countryID will change:
			 */
			$DB->sql_put(
					"UPDATE wD_TerrStatus ts
					INNER JOIN wD_Territories t ON ( t.id = ts.terrID AND t.supply='No' )
					INNER JOIN wD_Units u ON ( u.gameID = ts.gameID AND ".$this->Variant->deCoastCompare('ts.terrID','u.terrID')."  )
					SET ts.countryID = u.countryID
					WHERE ts.gameID = ".$this->id."
						AND t.mapID=".$this->Variant->mapID
				);
		}
		else
		{
			$countryID = "u.countryID";
			$updateCountryID = ', countryID = VALUES(countryID)';
		}

		$DB->sql_put(
			"INSERT INTO wD_TerrStatus (gameID, terrID, countryID, occupyingUnitID )
			SELECT u.gameID,  ".$this->Variant->deCoastSelect('t.id')." as terrID, ".$countryID." as countryID, u.id as occupyingUnitID
			FROM wD_Units u
			INNER JOIN wD_Territories t ON ( ".$this->Variant->deCoastCompare('t.id','u.terrID')."  )
			LEFT JOIN wD_TerrStatus ts ON ( ts.retreatingUnitID = u.id AND ".$this->Variant->deCoastCompare('ts.terrID','u.terrID')." )
			WHERE u.gameID = ".$this->id." AND ts.id IS NULL
				AND t.mapID=".$this->Variant->mapID."
			ON DUPLICATE KEY
				UPDATE occupyingUnitID = VALUES(occupyingUnitID)".$updateCountryID);

		// - Empty all territories now without units
		$DB->sql_put(
			"UPDATE wD_TerrStatus t
			/* If this territory is occupied this join will find a match .. */
			LEFT JOIN wD_Units u ON ( ".$this->Variant->deCoastCompare('t.terrID','u.terrID')." AND u.gameID = t.gameID )
			SET occupyingUnitID = NULL
			WHERE t.gameID = ".$this->id."
				/* .. so all territories which aren't matched are updated */
				AND u.id IS NULL");

		$DB->sql_put("DELETE FROM wD_Moves WHERE gameID=".$GLOBALS['GAMEID']);
	}

	/**
	 * Archive the TerrStatus data into the TerrStatusArchive table
	 */
	protected function archiveTerrStatus()
	{
		global $DB;

		$DB->sql_put("DELETE FROM wD_TerrStatusArchive
				WHERE gameID = ".$this->id." AND turn = ".$this->turn);

		$DB->sql_put("INSERT INTO wD_TerrStatusArchive
						(gameID, turn, terrID, countryID, standoff)
					SELECT gameID, ".$this->turn." as turn, terrID, countryID, standoff
					FROM wD_TerrStatus
					WHERE gameID = ".$this->id);
	}

	/**
	 * Clean the active TerrStatus records to make room for the next turn
	 */
	protected function cleanTerrStatus()
	{
		global $DB;

		$DB->sql_put(
			"UPDATE wD_TerrStatus
			SET occupiedFromTerrID = NULL,
				standoff = 'No',
				retreatingUnitID = NULL
			WHERE gameID = ".$this->id);
	}

	/**
	 * Set the next phase for this game, update the phase and increment the turn as required.
	 * (Finished isn't a new turn)
	 *
	 * @param string $phase The phase to set to next
	 * @param string[optional] $gameOver The game-over status, if the game has finished
	 *
	 * @return bool True if it's a new turn, false otherwise
	 */
	protected function setPhase($phase, $gameOver='')
	{
		global $DB;

		$turn = '';
		if ( $phase == 'Diplomacy' and $this->phase != 'Pre-game' )
		{
			// If we're moving to Diplomacy, and we're not just starting, advance a turn
			$turn = ', turn = turn + 1';
			$this->turn++;
		}

		if ( $gameOver )
		{
			$gameOver = ", gameOver = '".$gameOver."'";
			$this->gameOver = $gameOver;
		}

		$DB->sql_put("UPDATE wD_Games SET phase='".$phase."' ".$turn.$gameOver." WHERE id=".$this->id);

		$this->phase = $phase;

		return ($turn != '');
	}

	/**
	 * Set the game as Won, with the given member as the winner. Will set the game phase,
	 * distribute points, and set member data
	 *
	 * @param Member $Winner
	 */
	protected function setWon(Member $Winner)
	{
		// Game over successfully
		/*
		 * This function splits up the pot according to how the game ended, and will
		 * set the Members' status according to how they ended the game:
		 *
		 * 'Playing'/'Left' -> 'Won'/'Survived'/'Resigned'
		 * ('Defeated' status members are already set by now)
		 */
		$this->Members->setWon($Winner);

		// Then the game is set to finished
		$this->setPhase('Finished', 'Won');
	}

	/**
	 * Change the game's phase, by checking to see which phase this game should change to and calling
	 * setChange to do so. Game-overs are also checked for and responded to.
	 *
	 * @return bool Returns true if changing turn, false otherwise.
	 */
	protected function changePhase()
	{
		global $DB;

		/*
		 * Pre-game -> Diplomacy (same turn)
		 * All but one / All left -> Abandoned (same turn)
		 * All but one defeated -> Won (same turn)
		 * Diplomacy and dislodged units -> Retreats (same turn)
		 * Diplomacy/Retreats and Autumn and Unit/SC imbalance -> Builds (same turn)
		 *
		 * None of the above -> Diplomacy (next turn)
		 */
		// If it's Pre-game make it Diplomacy
		if( $this->phase == 'Pre-game' )
		{
			$this->setPhase('Diplomacy');
			return false; // No TerrStatus to cache -> no need for a new year
		}

		/*
		 * Check for missed turns and adjust the counter in the user-data
		 */
		$this->Members->updateReliabilities();
		
		/*
		 * In the functions below only 'Playing' and 'Left' status members are dealt with:
		 * 'Defeated' players have no bearing, and the other statuses cannot exist at this stage.
		 */

		/*
		 * The findSet* functions affect the Members arrays and Member objects and records,
		 * and will send messages, but they will not affect the rest of the game.
		 */
		$this->Members->findSetLeft(); // This will not give points to the left, since they may come back
		$this->Members->findSetDefeated(); // This will give points to the defeated

		/*
		 * $this->Members->ByStatus can now be easily used to check abandoned/won
		 * situations by checkForWinner()
		 *
		 * checkForWinner() will detect and return the winner member object, in
		 * abandoned/victory scenarios, but will not alter the member object,
		 * this members object, or the game object in any way.
		 */
		if(false!==($Winner = $this->Members->checkForWinner()))
		{
			/*
			 * Now that we know there is a winner, either by victory or abandonment, the
			 * data-structures and records can be updated and points allocated to users
			 * who were still 'Playing' or 'Left', and are now 'Survived','Won','Defeated','Resigned'
			 */
			$this->setWon($Winner);

			return false;
		}

		/*
		 * Any members that needed to have their status changed
		 * have been changed, and the game is carrying on.
		 */
		switch($this->phase)
		{
			case 'Diplomacy':
				list($retreating) = $DB->sql_row("SELECT COUNT(retreatingUnitID)
												FROM wD_TerrStatus WHERE gameID=".$this->id);

				if($retreating)
				{
					$this->setPhase('Retreats');
					return false;
				}

			case 'Retreats':
				/*
				 * If it's autumn and we just came from Diplomacy or Retreats we may
				 * need to make some units.
				 */
				if( 0 != ($this->turn % 2) and $this->Members->checkForUnitSCDifference() )
				{
					$this->setPhase('Builds');
					return false;
				}

			default:
				$this->setPhase('Diplomacy');
				return true; // New turn!
		}
	}

	/**
	 * Toggle the game's paused status.
	 * If paused the game is unpaused, and processTime is set to now plus pauseTimeRemaining, which is then set to null.
	 * If not paused the game is paused, processTime set to null and pauseTimeRemaining set to the currently remaining time.
	 *
	 * Messages are also sent out notifying of the status change.
	 *
	 * All members which had pause votes set are unset.
	 *
	 * @param string $customMessage An alternative message to notify players with, e.g. the reason for the pause.
	 */
	public function togglePause($customMessage=false)
	{
		global $DB;

		if( $this->phase == 'Pre-game' )
			throw new Exception("This game hasn't started");

		if( $this->phase == 'Finished' )
			throw new Exception("This game is finished");

		if( $this->processStatus == 'Paused' )
		{
			$this->processStatus = 'Not-processing';

			$this->processTime = $this->pauseTimeRemaining + time();

			$this->pauseTimeRemaining=false;

			if( !$customMessage )
				$this->Members->notifyUnpaused();
			else
				$this->Members->sendToPlaying('No',$customMessage);
		}
		elseif( $this->processStatus == 'Not-processing' )
		{
			$this->processStatus = 'Paused';

			// Use processTime to find pauseTimeRemaining
			$this->pauseTimeRemaining = $this->processTime - time();

			$this->processTime=false;

			if( !$customMessage )
				$this->Members->notifyPaused();
			else
				$this->Members->sendToPlaying('No',$customMessage);
		}
		else
		{
			throw new Exception("This game has crashed");
		}

		$DB->sql_put(
			"UPDATE wD_Games
			SET processStatus = '".$this->processStatus."',
				pauseTimeRemaining = ".(false===$this->pauseTimeRemaining ? "NULL" : $this->pauseTimeRemaining).",
				processTime = ".(false===$this->processTime ? "NULL" : $this->processTime)."
			WHERE id = ".$this->id);

		// Any votes to toggle the pause are now void
		$DB->sql_put("UPDATE wD_Members SET votes = REPLACE(votes,'Pause','') WHERE gameID = ".$this->id);
	}

	/**
	 * Draw the game; archive the terrstatus and moves, delete active data, set members to drawn
	 * and distribute points among survivors equally. Also delete the current map to display the finished
	 * message on the map
	 */
	public function setDrawn()
	{
		global $DB;

		// Unpause the game so that the processTime data isn't finalized as NULL
		if( $this->processStatus == 'Paused' )
			$this->togglePause();

		$this->archiveTerrStatus();

		if ( $this->phase == 'Diplomacy' and $this->turn > 0 )
		{
			$DB->sql_put("INSERT INTO wD_MovesArchive
				( gameID, turn, terrID, countryID, unitType, success, dislodged, type, toTerrID, fromTerrID, viaConvoy )
				SELECT gameID, turn+1, terrID, countryID, unitType, success, dislodged, type, toTerrID, fromTerrID, viaConvoy
				FROM wD_MovesArchive WHERE gameID = ".$this->id." AND turn = ".($this->turn-1));
		}

		// Sets the Members statuses to Drawn as needed, gives refunds, sends messages
		$this->Members->setDrawn();
		$this->setPhase('Finished', 'Drawn');

		$DB->sql_put("DELETE FROM wD_Orders WHERE gameID = ".$this->id);
		$DB->sql_put("DELETE FROM wD_Units WHERE gameID = ".$this->id);
		$DB->sql_put("DELETE FROM wD_TerrStatus WHERE gameID = ".$this->id);

		Game::wipeCache($this->id,$this->turn);
	}
	
	public function extendPhase()
	{
		global $DB;

		if( $this->phase == 'Pre-game' )
			throw new Exception("This game hasn't started");

		if( $this->phase == 'Finished' )
			throw new Exception("This game is finished");

		if( $this->processStatus == 'Paused' )
			throw new Exception("This game is paused");
			
		$this->Members->notifyExtended();
		
		$DB->sql_put(
			"UPDATE wD_Games
			SET processTime = ".($this->processTime + 345600)."
			WHERE id = ".$this->id);

		// Any extend votes are now void
		$DB->sql_put("UPDATE wD_Members SET votes = REPLACE(votes,'Extend','') WHERE gameID = ".$this->id);
	}
	
	/**
	 * All players but one choosed to concede.
	 * End the game; archive the terrstatus and moves, delete active data, set members to defeated
	 * and set the Winner. Also delete the current map to display the finished
	 * message on the map
	 */
	public function setConcede()
	{
		global $DB;

		// Unpause the game so that the processTime data isn't finalized as NULL
		if( $this->processStatus == 'Paused' )
			$this->togglePause();

		$this->archiveTerrStatus();

		if ( $this->phase == 'Diplomacy' and $this->turn > 0 )
		{
			$DB->sql_put("INSERT INTO wD_MovesArchive
				( gameID, turn, terrID, countryID, unitType, success, dislodged, type, toTerrID, fromTerrID, viaConvoy )
				SELECT gameID, turn+1, terrID, countryID, unitType, success, dislodged, type, toTerrID, fromTerrID, viaConvoy
				FROM wD_MovesArchive WHERE gameID = ".$this->id." AND turn = ".($this->turn-1));
		}

		// Sets the Members statuses to Drawn as needed, gives refunds, sends messages
		$this->Members->setConcede();
		foreach($this->Members->ByStatus['Playing'] as $Member)
			$Winner = $Member;
		$this->setWon($Winner);

		$DB->sql_put("DELETE FROM wD_Orders WHERE gameID = ".$this->id);
		$DB->sql_put("DELETE FROM wD_Units WHERE gameID = ".$this->id);
		$DB->sql_put("DELETE FROM wD_TerrStatus WHERE gameID = ".$this->id);

		Game::wipeCache($this->id,$this->turn);
	}
	
}

?>
