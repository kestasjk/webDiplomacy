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
 * Game State JSON response
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
	 * List of game phases (units, centers and orders per phase)
	 * @var array
	 */
	public $phases = array();

	/**
	 * Load the UserOptions object. It is assumed that username is already escaped.
	 * @throws \Exception
	 */
	function load()
	{
		global $DB;

		// Loading game state
		$gameRow = $DB->sql_hash("SELECT id, variantID, turn, phase, gameOver FROM wD_Games WHERE id=".$this->gameID);
		if ( ! $gameRow )
			throw new \Exception("Unknown game ID.");
		$this->variantID = intval($gameRow['variantID']);
		$this->turn = intval($gameRow['turn']);
		$this->phase = $gameRow['phase'];
		$this->gameOver = $gameRow['gameOver'];

		$units = array();
		$orders = array();
		$preGameCenters = array();
		$inGameCenters = array();
		$gameSteps = new GameSteps();

		// Loading current units
		$unitTabl = $DB->sql_tabl("SELECT wD_TerrStatus.id,
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
			if ($row['regType']) {
				$units[$this->turn][$this->phase][] = new Unit($row['regType'], $row['regTerrID'], $row['regCountryID'], 'No');
			}
			if ($row['disType']) {
				$units[$this->turn][$this->phase][] = new Unit($row['disType'], $row['disTerrID'], $row['disCountryID'], 'Yes');
			}
		}

		$Variant=libVariant::loadFromVariantID($this->variantID);
		$mapID = $Variant->mapID;


		// Loading pre-game centers
		$preGameCentersTabl = $DB->sql_tabl(
			"SELECT t.id, t.countryID
				  FROM wD_Territories t
				  WHERE t.supply = 'Yes' AND t.mapID = ".$mapID
		);
		while ($row = $DB->tabl_hash($preGameCentersTabl)) {
			array_push($preGameCenters, new Territory($row['id'], $row['countryID']));
		}

		// Loading centers from all game turns
		$centersTabl = $DB->sql_tabl(
			"SELECT t.id, ts.countryID, ts.turn
				  FROM wD_Territories t
				  JOIN wD_TerrStatusArchive ts
				  ON ( ts.terrID = t.id )
				  WHERE ts.gameID = ".$this->gameID." AND t.supply = 'Yes' AND t.mapID=".$mapID
		);
		while ($row = $DB->tabl_hash($centersTabl)) {
			$inGameCenters[intval($row['turn'])][] = new Territory($row['id'], $row['countryID']);
		}

		// Loading previous orders and units
		$orderTabl = $DB->sql_tabl("SELECT turn, countryID, terrID, unitType, type, toTerrID, fromTerrID, viaConvoy, success, dislodged
									FROM wD_MovesArchive
									WHERE gameID = $this->gameID
									ORDER by turn ASC, type ASC;");
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

		while( $row = $DB->tabl_hash($orderTabl) )
		{
			$order = new \webdiplomacy_api\Order(
				$row['turn'],
				$phaseTable[$row['type']],
				$row['countryID'],
				$row['terrID'],
				$row['unitType'],
				$row['type'],
				$row['toTerrID'],
				$row['fromTerrID'],
				$row['viaConvoy'],
				$row['success'],
				$row['dislodged']);
			$orderedUnit = $order->getOrderedUnit();
			array_push($orders, $order);
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
		foreach ($gameSteps->toArray() as $step) {
			list($turn, $phaseName, $data) = $step;
			$centerTurn = $turn;
			if ($phaseName == 'Diplomacy')
				$centerTurn -= 1;
			if ($centerTurn == -1)
				$centers = $preGameCenters;
			else
				$centers = $inGameCenters[$centerTurn];
			$data['centers'] = $centers;
			$data['turn'] = $turn;
			$data['phase'] = $phaseName;
			if (!isset($data['units'])) $data['units'] = array();
			if (!isset($data['orders'])) $data['orders'] = array();
			$finalPhases[] = $data;
		}
		$this->phases = $finalPhases;
	}

	function toJson()
	{
		return json_encode($this);
	}

	/**
	 * Initialize a game state object for a given country
	 * @param int $gameID - Game ID
	 *
	 */
	function __construct($gameID, $countryID)
	{
		$this->gameID = intval($gameID);
		$this->countryID = intval($countryID);
		$this->load();
	}

}
