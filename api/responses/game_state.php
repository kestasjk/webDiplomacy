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
     * List of units on the board
     * @var array
     */
    public $units = array();

	/**
	 * List of centers currently owned by this country on the board
	 * @var array
	 */
	public $centers = array();

    /**
     * List of orders from previous phases
     * @var array
     */
    public $orders = array();

	/**
	 * Load the UserOptions object. It is assumed that username is already escaped.
	 */
	function load()
	{
		global $DB;

		// Loading game state
		$gameRow = $DB->sql_hash("SELECT id, variantID, turn, phase, gameOver FROM wD_Games WHERE id=".$this->gameID);
		if ( ! $gameRow )
			throw new Exception("Unknown game ID.");
        $this->variantID = intval($gameRow['variantID']);
        $this->turn = $gameRow['turn'];
        $this->phase = $gameRow['phase'];
        $this->gameOver = $gameRow['gameOver'];

        // Loading units
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
                array_push($this->units, new \webdiplomacy_api\Unit($row['regType'], $row['regTerrID'], $row['regCountryID'], 'No'));
            }
            if ($row['disType']) {
                array_push($this->units, new \webdiplomacy_api\Unit($row['disType'], $row['disTerrID'], $row['disCountryID'], 'Yes'));
            }
        }

        // Loading centers
		$Variant=libVariant::loadFromVariantID($this->variantID);
        $mapID = $Variant->mapID;
		$latestTurn = $this->turn;
        if ($this->phase == 'Diplomacy') $latestTurn -= 1;
        if ($latestTurn == -1) {
			$centersSql = "SELECT t.id, t.countryID
							FROM wD_Territories t
							WHERE t.supply = 'Yes' AND t.mapID = ".$mapID;
		} else {
			$centersSql = "SELECT t.id, ts.countryID
							/* Territories are selected first, not TerrStatus, so that unoccupied territories can be drawn neutral */
							FROM wD_Territories t
							JOIN wD_TerrStatusArchive ts
								ON ( ts.gameID = ".$this->gameID." AND ts.turn = ".$latestTurn." AND ts.terrID = t.id )
							/* TerrStatus is non-coastal */
							WHERE t.supply = 'Yes' AND t.mapID=".$mapID;
		}

        $centersTabl = $DB->sql_tabl($centersSql);
        while ($row = $DB->tabl_hash($centersTabl)) {
			array_push($this->centers, new \webdiplomacy_api\Territory($row['id'], $row['countryID']));
		}

        // Loading previous orders
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
            array_push($this->orders, new \webdiplomacy_api\Order($row['turn'],
                                                                  $phaseTable[$row['type']],
                                                                  $row['countryID'],
                                                                  $row['terrID'],
                                                                  $row['unitType'],
                                                                  $row['type'],
                                                                  $row['toTerrID'],
                                                                  $row['fromTerrID'],
                                                                  $row['viaConvoy'],
                                                                  $row['success'],
                                                                  $row['dislodged']));
        }
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
