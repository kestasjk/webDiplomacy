<?php
/*
	Copyright (C) 2004-2010 Kestas J. Kuliukas / Timothy Jones

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

namespace webdiplomacy_api;

use libVariant;

defined('IN_CODE') or die('This script can not be run by itself.');
/*
 * These classes built the game/status and game/data responses. Those routes are gone (2026-09-20); what is
 * left is here for lib/gamefiles.php, which builds history.json from GameState so that a game replayed from
 * the files is the same game the old API gave.
 */
require_once(l_r('api/responses/message.php'));
require_once(l_r('api/responses/vote_message.php'));
require_once(l_r('api/responses/order.php'));
require_once(l_r('api/responses/unit.php'));

/**
 * Webdiplomacy game step (game phase defined by a webdiplomacy turn number and phase).
 * @package webdiplomacy_api
 */
class GameSteps {
	private $steps;

	public function __construct()
	{
		$this->steps = array();
	}

	/**
	 * Add a data for a turn and a phase.
	 */
	public function set($turn, $phase, $data)
	{
		$this->steps[$turn][$phase] = $data;
	}

	/**
	 * Get data associated to a turn and phase.
	 * If turn and phase is not currently associated to any data,
	 * then associate default data to turn and phase and return default data.
	 */
	public function get($turn, $phase, $defaultData)
	{
		if (isset($this->steps[$turn]) && isset($this->steps[$turn][$phase])) {
			return $this->steps[$turn][$phase];
		}
		$this->steps[$turn][$phase] = $defaultData;
		return $defaultData;
	}

	/**
	 * Return an array of all collected game steps.
	 * A game step will be itself an array with 3 elements: turn number, phase name and associated data.
	 */
	public function toArray()
	{
		$arraySteps = array();
		foreach ($this->steps as $turn => $phases) {
			foreach ($phases as $phase => $data) {
				array_push($arraySteps, array($turn, $phase, $data));
			}
		}
		usort($arraySteps, function($step1, $step2) {
			$phaseRanks = array(
				'Diplomacy' => 0,
				'Retreats' => 1,
				'Builds' => 2,
				'Finished' => 3,
			);
			list($turn1, $phase1, $data1) = $step1;
			list($turn2, $phase2, $data2) = $step2;
			$t = intval($turn1) - intval($turn2);
			if ($t == 0)
				$t = $phaseRanks[$phase1] - $phaseRanks[$phase2];
			return $t;
		});
		return $arraySteps;
	}
}

/**
 * Game Board - Board with units that can be moved to return correct unit location
 * @package webdiplomacy_api
 */
class GameBoard {
    private $board;

    public function __construct()
    {
        $this->board = array();
    }

    /**
     * Adds a unit to the board
     */
    public function add($countryID, $terrID, $unitType) {
        if (isset($this->board[$countryID][$terrID][$unitType]))
            ++$this->board[$countryID][$terrID][$unitType];
        else
            $this->board[$countryID][$terrID][$unitType] = 1;
    }

    /**
     * Removes a unit from the board
     */
    public function remove($countryID, $terrID, $unitType) {
        if (!$unitType) {
            $this->remove($countryID, $terrID, 'Army');
            $this->remove($countryID, $terrID, 'Fleet');
            return;
        }
        if (isset($this->board[$countryID][$terrID][$unitType])) {
            --$this->board[$countryID][$terrID][$unitType];
            if ($this->board[$countryID][$terrID][$unitType] == 0)
                unset($this->board[$countryID][$terrID][$unitType]);
        }
        if (isset($this->board[$countryID][$terrID]) && empty($this->board[$countryID][$terrID]))
            unset($this->board[$countryID][$terrID]);
        if (isset($this->board[$countryID]) && empty($this->board[$countryID]))
            unset($this->board[$countryID]);
    }

    /**
     * Moves a unit on the board
     */
    public function move($countryID, $unitType, $fromTerrID, $toTerrID) {
        $this->remove($countryID, $fromTerrID, $unitType);
        $this->add($countryID, $toTerrID, $unitType);
    }

    /**
     * Returns the current units on the board
     */
    public function getUnits() {
        $units = array();
        foreach ($this->board as $countryID => $countryData) {
            foreach ($countryData as $terrID => $terrData) {
                foreach ($terrData as $unitType => $unitCount) {
                    if ($unitCount != 1)
                        throw new \ServerInternalException('Internal error while retrieving units.');
                    $units[] = new Unit($unitType, $terrID, $countryID, 'No');
                }
            }
        }
        return $units;
    }
}


/**
 * Game State JSON response
 * 
 * This API call needs to be optimized; it gets called constantly by the React
 * clients and the bots; so in theory as this is the entire game state put the result in 
 * Redis so it can be fetched as needed, but it's used both to load the entire game state
 * and history and to get the latest updates and game state.
 * e.g. when a vote is sent by a bot, it will use this API call every second to poll 
 * whether the vote went through by fetching, which fetches every order, move, unit, 
 * territory, message, vote, civil disorder, and it fetches the game records twice as 
 * it uses the game object to check the user is a member, it's obscenely wasteful.
 * 
 * @package webdiplomacy_api
 */
class GameState {
	/**
	 * Game ID
	 * @var int
	 */
	public $gameID;

	/**
	 * Country ID
	 * @var int
	 */
	public $countryID;

	/**
	 * Variant ID
	 * @var int
	 */
	public $variantID;
	
	/**
	 * Pot Type - (Winner-takes-all, Points-per-supply-center, Sum-of-squres, Unranked)
	 * @var int
	 */
	public $potType;

	/**
	 * Turn
	 * @var int
	 */
	public $turn;

	/**
	 * Phase - (Finished, Pre-game, Diplomacy, Retreats, Builds)
	 * @var string
	 */
	public $phase;

	/**
	 * GameOver - No, Won, Drawn
	 * @var string
	 */
	public $gameOver;
	
	/**
	 * pressType - Regular, NoPress, PublicPress
	 * @var string
	 */
	public $pressType;

	public $drawType;

	public $processTime;

	public $phaseLengthInMinutes;
	
	public $publicVotes = array();

	public $orderStatuses = array();

	/**
	 * List of game phases (units, centers and orders per phase)
	 * @var array
	 */
	public $phases = array();

	/**
	 * List of standoff statuses per turn.
	 * @var array
	 */
	public $standoffs = array();

	/**
     * List of occupiedFrom territories
     * @var array
     */
    public $occupiedFrom = array();
	/**
	 * List of votes cast comma separated
	 * @var string
	 */
	public $votes = '';
	/**
	 * Statys of countries orders
	 * @var array
	 */
	public $orderStatus = '';
	/**
	 * Status of country
	 * @var array
	 */
	public $status = '';


	/**
	 * Load the GameState object.
	 * @throws \Exception
	 */
	function load()
	{
		global $DB;

		// Loading game state
		$gameRow = $DB->sql_hash("SELECT id, variantID, potType, turn, phase, gameOver, pressType, drawType, processTime, phaseMinutes, anon FROM wD_Games WHERE id=".$this->gameID);
		if ( ! $gameRow )
			throw new \RequestException("Unknown game ID.");
		$this->variantID = intval($gameRow['variantID']);
		$this->potType = $gameRow['potType'];
		$this->turn = intval($gameRow['turn']);
		$this->phase = $gameRow['phase'];
		$this->gameOver = $gameRow['gameOver'];
		$this->pressType = $gameRow['pressType'];
		$this->drawType=$gameRow['drawType'];
		$this->processTime=$gameRow['processTime'];
		$this->phaseLengthInMinutes = $gameRow['phaseMinutes'];
		if ($this->countryID) {
			$memberData = $DB->sql_hash("SELECT countryID, votes, orderStatus, status FROM wD_Members WHERE gameID = ".$this->gameID." AND countryID = ".$this->countryID);
			$this->votes = $memberData['votes'];
			$this->orderStatus = $memberData['orderStatus'];
			$this->status = $memberData['status'];
		}
		$orderStatusData = $DB->sql_tabl("SELECT countryID, orderStatus FROM wD_Members WHERE gameID = ".$this->gameID);
		$this->orderStatuses = [];
		while ($member = $DB->tabl_hash($orderStatusData)) {
			$countryID = $member["countryID"];
			$orderStatus = $gameRow['anon'] == 'Yes' ? 'Hidden' : $member["orderStatus"];
			$this->orderStatuses[$countryID] = $orderStatus;
		}	

		// current draw votes
		$this->publicVotes = [];
		if ($this->drawType === 'draw-votes-public') {
			$tabl = $DB->sql_tabl("SELECT countryID, votes FROM wD_Members WHERE gameID = ".$this->gameID);
			while ($member = $DB->tabl_hash($tabl)) {
				$countryID = $member["countryID"];
				$votes = $member["votes"];
				$this->publicVotes[$countryID] = $votes;
			}	
		}

		$units = array();
		$orders = array();
		$preGameCenters = array();
		$inGameCenters = array();
		$gameSteps = new GameSteps();

		// Loading current units and standoffs
		$unitTabl = $DB->sql_tabl("SELECT wD_TerrStatus.terrID,
										  wD_TerrStatus.standoff,
                                          wD_TerrStatus.occupiedFromTerrID,
										  regular.type AS regType,
										  regular.terrID AS regTerrID,
										  regular.countryID AS regCountryID,
										  dislodged.type AS disType,
										  dislodged.terrID as disTerrID,
										  dislodged.countryID as disCountryID
								   FROM wD_TerrStatus
								   LEFT JOIN wD_Units as regular ON wD_TerrStatus.occupyingUnitID = regular.id
								   LEFT JOIN wD_Units as dislodged ON wD_TerrStatus.retreatingUnitID = dislodged.id
								   WHERE wD_TerrStatus.gameID = $this->gameID;");

		while( $row = $DB->tabl_hash($unitTabl) )
		{
            if ($row['standoff'] == 'Yes') {
                $this->standoffs[] = array('terrID' => intval($row['terrID']), 'countryID' => 0);
            }
			if ($row['regType']) {
				$units[$this->turn][$this->phase][] = new Unit($row['regType'], $row['regTerrID'], $row['regCountryID'], 'No');
			}
			if ($row['disType']) {
			    if ($this->phase == 'Retreats') {
                    $units[$this->turn][$this->phase][] = new Unit($row['disType'], $row['disTerrID'], $row['disCountryID'], 'Yes');
                    if ($row['occupiedFromTerrID']) {
                        $this->occupiedFrom[intval($row['terrID'])] = $row['occupiedFromTerrID'];
                    }
                }
			}
		}

		$Variant=libVariant::loadFromVariantID($this->variantID);
		$mapID = $Variant->mapID;

		list($preGameCenterRows, $centerRows, $orderRows) = $this->loadArchiveRows($gameRow, $mapID);

		// Loading pre-game centers
		foreach ($preGameCenterRows as list($id, $countryID)) {
			array_push($preGameCenters, new Territory($id, $countryID));
		}

		// Loading centers from all game turns
		foreach ($centerRows as list($id, $countryID, $turn)) {
			$inGameCenters[intval($turn)][] = new Territory($id, $countryID);
		}

		// Loading previous orders and units
		$phaseTable = array("Hold" => "Diplomacy",
							"Move" => "Diplomacy",
							"Support hold" => "Diplomacy",
							"Support move" => "Diplomacy",
							"Convoy" => "Diplomacy",
							"Retreat" => "Retreats",
							"Disband" => "Retreats",
							"Build Army" => "Builds",
							"Build Fleet" => "Builds",
							"Wait" => "Builds",
							"Destroy" => "Builds");

		$maxOrderTurn = -1;
		foreach ($orderRows as list($turn, $countryID, $terrID, $unitType, $type, $toTerrID, $fromTerrID, $viaConvoy, $success, $dislodged))
		{
			$order = new \webdiplomacy_api\Order(
				$turn,
				$phaseTable[$type],
				$countryID,
				$terrID,
				$unitType,
				$type,
				$toTerrID,
				$fromTerrID,
				$viaConvoy,
				$success,
				$dislodged);
			array_push($orders, $order);
			if ($order->turn > $maxOrderTurn)
				$maxOrderTurn = $order->turn;
		}

		// For drawn games and some won games (namely those won by concession), webdip duplicates
		// the final phase's orders in its database. So, we heuristically detect when this is
		// the case and filter them out. If the game is over, and the final phase of the game is a
		// duplicate of the phase just before, then we delete all the final phase orders from the response.

		$gameIsOver = $this->gameOver == "Won" || $this->gameOver == "Drawn";
		if ($gameIsOver && $maxOrderTurn >= 1) {
			$maxTurnOrders = array();
			$preMaxTurnOrders = array();
			foreach ($orders as $order) {
				if ($order->turn == $maxOrderTurn) {
					$maxTurnOrders[$order->terrID] = $order;
				}
				else if ($order->turn == $maxOrderTurn - 1) {
					$preMaxTurnOrders[$order->terrID] = $order;
				}
			}
			$ordersAreDuplicate = true;
			foreach ($maxTurnOrders as $terrID=>$order) {
				if (!array_key_exists($terrID,$preMaxTurnOrders)) {
					$ordersAreDuplicate = false;
					break;
				}
				$order2 = $preMaxTurnOrders[$terrID];
				if($order2->countryID != $order->countryID || 
				   $order2->unitType != $order->unitType || 
				   $order2->type != $order->type || 
				   $order2->toTerrID != $order->toTerrID || 
				   $order2->fromTerrID != $order->fromTerrID || 
				   $order2->viaConvoy != $order->viaConvoy ||
				   $order2->success != $order->success
				) {
					$ordersAreDuplicate = false;
					break;
				}
			}
			if ($ordersAreDuplicate) {
				$orders = array_filter($orders, function($order) use ($maxOrderTurn) {
					return $order->turn != $maxOrderTurn;
				});
			}
		}

		foreach ($orders as $order) {
			$orderedUnit = $order->getOrderedUnit();
			if ($orderedUnit)
				$units[$order->turn][$order->phase][] = $orderedUnit;
		}

		$finalPhases = array();
		foreach ($units as $turn => $unitsPerPhase) {
			foreach ($unitsPerPhase as $phaseName => $unitObjects) {
				$phase = $gameSteps->get($turn, $phaseName, array());
				foreach ($unitObjects as $unit) {
					$phase['units'][] = $unit;
				}
				$gameSteps->set($turn, $phaseName, $phase);
			}
		}
		foreach ($orders as $order) {
			/** @var Order $order */
			$phase = $gameSteps->get($order->turn, $order->phase, array());
			$phase['orders'][] = $order;
			$gameSteps->set($order->turn, $order->phase, $phase);
		}
		// messages
		if ($this->pressType != 'NoPress' && $this->countryID) {
			$msgTabl = $DB->sql_tabl(
				"SELECT turn, fromCountryID, toCountryID, message, timeSent, phaseMarker
				FROM ".(isset(\Config::$allowBotsAccessToUnredactedMessages) && \Config::$allowBotsAccessToUnredactedMessages ? "wD_GameMessages" : "wD_GameMessages_Redacted")." 
				WHERE gameID = ".$this->gameID. 
				" AND (fromCountryID = ".$this->countryID." OR toCountryID = ".$this->countryID.
				" OR toCountryID = 0)
				ORDER BY timeSent"
			);

			while ($row = $DB->tabl_hash($msgTabl)) {
				$message = new \webdiplomacy_api\Message(
					$row['message'],
					$row['fromCountryID'],
					$row['toCountryID'],
					$row['timeSent'],
					$row['phaseMarker']
				);
				$phase = $gameSteps->get($row['turn'], 'Diplomacy', array());
				$phase['messages'][] = $message;
				$gameSteps->set($row['turn'], 'Diplomacy', $phase);
			}
		}

		// draw vote history, this is used by the CICERO bot
		if ($this->drawType === 'draw-votes-public') {
			$messagify_vote = function($vote) {
				return ["Voted for ".$vote, "Un-Voted for ".$vote];
			};
			
			$msgs = array_merge(...array_map($messagify_vote, \Members::$votes));

			$msgTabl = $DB->sql_tabl(
				"SELECT turn, fromCountryID, toCountryID, message, timeSent, phaseMarker 
				FROM ".(isset(\Config::$allowBotsAccessToUnredactedMessages) && \Config::$allowBotsAccessToUnredactedMessages ? "wD_GameMessages" : "wD_GameMessages_Redacted")." 
				WHERE gameID = ".$this->gameID." AND 
				fromCountryID = toCountryID 
				AND message in ('".implode("','",$msgs)."')
				ORDER BY timeSent"
			);

			while ($row = $DB->tabl_hash($msgTabl)) {
				$message = new \webdiplomacy_api\VoteMessage(
					$row['message'],
					$row['fromCountryID'],
					$row['timeSent'],
					$row['phaseMarker']
				);
				$phase = $gameSteps->get($row['turn'], 'Diplomacy', array());
				$phase['publicVotesHistory'][] = $message;
				$gameSteps->set($row['turn'], 'Diplomacy', $phase);
			}
		}

		foreach ($gameSteps->toArray() as $step) {
			list($turn, $phaseName, $data) = $step;
			if( $turn > $this->turn ) continue; // If a sandbox game has been moved back this can fail as there are game steps for future turns.
			$centerTurn = $turn;
			
			if (($centerTurn % 2 == 1) && ($phaseName != 'Builds'))
				$centerTurn -= 1;
			elseif ($phaseName == 'Diplomacy')
                $centerTurn -= 1;

			if ($centerTurn == -1)
				$centers = $preGameCenters;
			else
			{
				// This sometimes gives an undefined key array error, but it is not clear why. Probably due to the
				// game being processed/finishing while being fetched. TODO: Wrap this in a transaction, and cache it
				// in redis for performance.
				// A RequestException so that the client gets the message with a 4xx and the (known, transient) condition
				// isn't written to the error log on every occurrence.
				if (!isset($inGameCenters[$centerTurn]))
                    throw new \RequestException("Game state error: no centers found for turn $turn, phase $phaseName.");
				else
					$centers = $inGameCenters[$centerTurn];
			}

			$data['centers'] = $centers;
			$data['turn'] = $turn;
			$data['phase'] = $phaseName;

			if (!isset($data['units'])) $data['units'] = array();
			if (!isset($data['orders'])) $data['orders'] = array();

			$finalPhases[] = $data;
		}
		// Deduce units for Retreats and Builds phases.
		$nbFinalPhases = count($finalPhases);

		// Updating previous units for all phases.
		// If the game is unfinished, don't compute the last phase, it was already filled in above.
		// If the game is finished, then also compute data the last phase.
		$finalPhaseIdx = $gameIsOver ? $nbFinalPhases - 1 : $nbFinalPhases - 2;
		// The board is reset on each Diplomacy phase below; this covers a history that starts on a Retreats or Builds phase
		$gameBoard = new GameBoard();
		for ($i = 0; $i <= $finalPhaseIdx; ++$i) {

		    // Resetting game board on movement phase
            if ($finalPhases[$i]['phase'] == 'Diplomacy') {
                $gameBoard = new GameBoard();
                foreach ($finalPhases[$i]['units'] as $previousUnit) {
                    /** @var Unit $previousUnit */
                    $gameBoard->add($previousUnit->countryID, $previousUnit->terrID, $previousUnit->unitType);
                }
            }

            // Retrieving units on board
            $units = $gameBoard->getUnits();

            // Setting orders for phase
		    $retreating = array();
            foreach ($finalPhases[$i]['orders'] as $previousOrder) {
                /** @var Order $previousOrder */
                if ($previousOrder->type == 'Disband'
                    || ($previousOrder->type == 'Destroy' && $previousOrder->success == 'Yes')
                    || ($previousOrder->type == 'Retreat' && $previousOrder->success == 'No')
                ) {
                    $gameBoard->remove($previousOrder->countryID, $previousOrder->terrID, $previousOrder->unitType);
                } else if (in_array($previousOrder->type, array('Move', 'Retreat')) && $previousOrder->success == 'Yes') {
                    // Move or retrat order succeeded, then the unit moved.
                    $gameBoard->move($previousOrder->countryID, $previousOrder->unitType, $previousOrder->terrID, $previousOrder->toTerrID);
                } else if (in_array($previousOrder->type, array('Build Army', 'Build Fleet')) && $previousOrder->success == 'Yes') {
                    $gameBoard->add($previousOrder->countryID, $previousOrder->terrID, $previousOrder->type == 'Build Army' ? 'Army' : 'Fleet');
                }
                if (in_array($previousOrder->type, array('Retreat', 'Disband')))
                    $retreating[$previousOrder->countryID][$previousOrder->terrID][$previousOrder->unitType] = true;
            };

            // Updating units for phase
            $nbUnits = count($units);
            for ($j = 0; $j < $nbUnits; ++$j) {
                if (isset($retreating[$units[$j]->countryID][$units[$j]->terrID][$units[$j]->unitType]))
                    $units[$j]->retreating = 'Yes';
            }
            $finalPhases[$i]['units'] = $units;
		}

		// Append a extra phase with the final unit positions and no orders
		if ($gameIsOver && $finalPhaseIdx >= 0) {
			$data = array();
			$data['centers'] = $finalPhases[$finalPhaseIdx]['centers'];
			$data['units'] = $gameBoard->getUnits();
			$data['orders'] = array();
			$data['turn'] = $finalPhases[$finalPhaseIdx]['turn'];
			$data['phase'] = "Finished";
			$finalPhases[] = $data;
		}

		$this->phases = $finalPhases;
	}

	/**
	 * How long the archive rows are cached for, in seconds. Only a backstop; the cached rows are checked against
	 * the game row on every read, and Game::wipeCache() clears them.
	 */
	const ARCHIVE_CACHE_SECONDS = 600;

	/**
	 * The Redis key the archive rows of a game are cached under.
	 * @param int $gameID
	 * @return string
	 */
	public static function archiveCacheKey($gameID)
	{
		return 'gameStateArchive_'.intval($gameID);
	}

	/**
	 * Get the map's territories, and the game's center owners and orders from past phases, as lists of row values:
	 * [ [[id, countryID], ..], [[id, countryID, turn], ..], [[turn, countryID, terrID, unitType, type, toTerrID,
	 * fromTerrID, viaConvoy, success, dislodged], ..] ]
	 *
	 * These queries were most of the cost of game/status, which bots poll constantly. The archive tables only change
	 * when the game is processed, moved back or ended, which all change the game's turn, phase, gameOver or
	 * processTime in the same transaction, so the rows are cached in Redis against those values. The game row must
	 * have been read before calling this, so that rows read just before a change are never saved against the game
	 * row after it.
	 *
	 * @param array $gameRow The game's wD_Games row
	 * @param int $mapID
	 * @return array
	 */
	private function loadArchiveRows($gameRow, $mapID)
	{
		global $DB, $Redis;

		$cacheKey = self::archiveCacheKey($this->gameID);
		$version = implode('/', array($mapID, $gameRow['turn'], $gameRow['phase'], $gameRow['gameOver'], $gameRow['processTime']));

		try {
			$cached = $Redis->get($cacheKey);
			if ($cached !== false) {
				$cached = json_decode(@gzuncompress($cached), true);
				if (is_array($cached) && isset($cached['version']) && $cached['version'] === $version)
					return $cached['rows'];
			}
		} catch (\Exception $e) {
			// Redis is down; fall back to the database
		}

		$rows = array(array(), array(), array());

		$tabl = $DB->sql_tabl(
			"SELECT t.id, t.countryID
				  FROM wD_Territories t
				  WHERE t.mapID = ".$mapID
		);
		while ($row = $DB->tabl_row($tabl))
			$rows[0][] = $row;

		$tabl = $DB->sql_tabl(
			"SELECT t.id, ts.countryID, ts.turn
				  FROM wD_Territories t
				  JOIN wD_TerrStatusArchive ts
				  ON ( ts.terrID = t.id )
				  WHERE ts.gameID = ".$this->gameID." AND t.mapID=".$mapID
		);
		while ($row = $DB->tabl_row($tabl))
			$rows[1][] = $row;

		$tabl = $DB->sql_tabl("SELECT turn, countryID, terrID, unitType, type, toTerrID, fromTerrID, viaConvoy, success, dislodged
									FROM wD_MovesArchive
									WHERE gameID = $this->gameID
									ORDER by turn ASC, type ASC;");
		while ($row = $DB->tabl_row($tabl))
			$rows[2][] = $row;

		try {
			$Redis->set($cacheKey, gzcompress(json_encode(array('version' => $version, 'rows' => $rows))), self::ARCHIVE_CACHE_SECONDS);
		} catch (\Exception $e) {
			// Not cached this time
		}

		return $rows;
	}

	function toJson($gameIDMultiplexer)
	{
		$gameID = $this->gameID;
		$this->gameID = $gameIDMultiplexer->gameIDToMultiplexedGameID($this->gameID);
		$jsonString = json_encode($this);
		$this->gameID = $gameID;
		return $jsonString;
	}

	/**
	 * Initialize a game state object for a given country
	 * @param int $gameID - Game ID
	 *
	 */
	function __construct($gameID, $countryID)
	{
		$this->gameID = intval($gameID);
		$this->countryID = $countryID ? intval($countryID) : null;
		$this->load();
	}

}
