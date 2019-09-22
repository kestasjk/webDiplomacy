<?php
/*
    Copyright (C) 2004-2019 Kestas J. Kuliukas, Philip Paquette

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

namespace API;
defined('IN_CODE') or die('This script can not be run by itself.');

require_once('api/responses/game_board.php');
require_once('api/responses/game_steps.php');
require_once('api/responses/message.php');
require_once('api/responses/order.php');
require_once('api/responses/territory.php');
require_once('api/responses/unit.php');
require_once('objects/game.php');

/**
 * Represents the state of a game for a power, including previous phases and messages
 * @package API
 */
class GameState {
    /**
     * The game ID
     * @var int
     */
    public $gameID;

    /**
     * The country ID who requested the game state
     * @var int
     */
    public $countryID;

    /**
     * The game's variant ID
     * @var int
     */
    public $variantID;

    /**
     * The game's current turn
     * @var int
     */
    public $turn;

    /**
     * The game's current phase - (Finished, Pre-game, Diplomacy, Retreats, Builds)
     * @var string
     */
    public $phase;

    /**
     * GameOver - No, Won, Drawn
     * @var string
     */
    public $gameOver;

    /**
     * List of game phases (units, centers, orders, and messages per phase)
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
     * @var array - Mapping of terrID: occupiedFromTerrID
     */
    public $occupiedFrom = array();

    /**
     * Initialize a game state object for a given country
     * @param int $gameID - The game ID
     * @param int $countryID - The countryID requesting the game state
     * @param bool $withMessages - Whether to include the private messages in the game state
     * @throws \Exception
     */
    function __construct($gameID, $countryID, $withMessages = false) {
        $this->gameID = intval($gameID);
        $this->countryID = intval($countryID);
        $this->load($withMessages);
    }

    /**
     * Load the GameState object.
     * @param bool $withMessages - Whether to include the private messages in the game state
     * @throws \Exception
     */
    function load($withMessages = false) {
        global $DB;

        // Loading game state
        $gameRow = $DB->sql_hash("SELECT id, variantID, turn, phase, gameOver FROM wD_Games WHERE id=".$this->gameID);
        if (!$gameRow) { throw new \Exception("Unknown game ID."); }
        $this->variantID = intval($gameRow['variantID']);
        $this->turn = intval($gameRow['turn']);
        $this->phase = $gameRow['phase'];
        $this->gameOver = $gameRow['gameOver'];

        // Initializing variables
        $units = array();                               // [turn][phase] = units
        $orders = array();                              // list of orders
        $messages = array();                            // [turn][phase] = messages
        $preGameCenters = array();                      // list of Territory
        $inGameCenters = array();                       // [turn] = territories
        $gameSteps = new GameSteps();

        // Setting Variant
        $Variant=\libVariant::loadFromVariantID($this->variantID);
        $mapID = $Variant->mapID;
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

        // Adding the current units to the proper array
        while($row = $DB->tabl_hash($unitTabl)) {
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

        // Loading pre-game centers
        $preGameCentersTabl = $DB->sql_tabl("SELECT t.id, t.countryID
                                             FROM wD_Territories t
                                             WHERE t.supply = 'Yes' AND t.mapID = ".$mapID);
        while ($row = $DB->tabl_hash($preGameCentersTabl)) {
            array_push($preGameCenters, new Territory($row['id'], $row['countryID']));
        }

        // Loading centers from all game turns
        $centersTabl = $DB->sql_tabl("SELECT t.id, ts.countryID, ts.turn
                                      FROM wD_Territories t
                                      JOIN wD_TerrStatusArchive ts ON ( ts.terrID = t.id )
                                      WHERE ts.gameID = ".$this->gameID." AND t.supply = 'Yes' AND t.mapID=".$mapID);
        while ($row = $DB->tabl_hash($centersTabl)) {
            $inGameCenters[intval($row['turn'])][] = new Territory($row['id'], $row['countryID']);
        }

        // Loading previous orders and units
        $orderTabl = $DB->sql_tabl("SELECT turn, countryID, terrID, unitType, type, toTerrID, fromTerrID, viaConvoy, success, dislodged
                                    FROM wD_MovesArchive
                                    WHERE gameID = $this->gameID
                                    ORDER by turn, type;");
        while($row = $DB->tabl_hash($orderTabl)) {
            $order = new Order(
                $row['turn'],                       // $turn
                $phaseTable[$row['type']],          // $phase
                $row['countryID'],                  // $countryID
                $row['terrID'],                     // $terrID
                $row['unitType'],                   // $unitType
                $row['type'],                       // $type
                $row['toTerrID'],                   // $toTerrID
                $row['fromTerrID'],                 // $fromTerrID
                $row['viaConvoy'],                  // $viaConvoy
                $row['success'],                    // $success
                $row['dislodged']);                 // $dislodged
            $orderedUnit = $order->getOrderedUnit();
            array_push($orders, $order);
            if ($orderedUnit) {
                $units[$order->turn][$order->phase][] = $orderedUnit;
            }
        }

        // Loading messages
        // Messages I received, messages I sent, and global messages
        if ($withMessages) {
            // Using notices to record the actual start of Diplomacy, Retreats, Builds phases for a given turn
            $noticeTabl = $DB->sql_tabl("SELECT REPLACE(text, 'Game progressed to ', '') AS text, MAX(timeSent) as startTime
                                         FROM `wD_Notices`
                                         WHERE type='Game' AND text LIKE 'Game progressed to %' AND fromID = $this->gameID
                                         GROUP BY text
                                         ORDER BY startTime;");
            $notices = array();
            $firstYear = -1;
            while($row = $DB->tabl_hash($noticeTabl)) {
                $startTime = intval($row['startTime']);
                list($phase, $season, $year) = explode(",", $row['text']);
                $phase = trim($phase);
                $season = trim($season);
                $year = intval(trim($year));
                if ($firstYear == -1) { $firstYear = $year; }

                // Setting startTime of turn/phase
                $turn = 2 * ($year - $firstYear) + ($season == 'Spring' ? 0 : 1);
                $notices[$turn][$phase] = $startTime;
            }

            // Recording messages
            $msgTabl = $DB->sql_tabl("SELECT turn, timeSent, fromCountryID, toCountryID, message
                                      FROM wD_GameMessages
                                      WHERE gameID = $this->gameID
                                          AND ((toCountryID = $this->countryID AND fromCountryID != $this->countryID) OR
                                               (fromCountryID = $this->countryID AND toCountryID != $this->countryID) OR
                                               (toCountryID = 0 AND fromCountryID != 0))
                                      ORDER BY timeSent;");
            while($row = $DB->tabl_hash($msgTabl)) {
                $turn = intval($row['turn']);
                $timeSent = intval($row['timeSent']);

                // Skipping messages about being muted
                if ($row['message'] == 'Cannot send message; this country has muted you.') { continue; }

                // Computing actual phase
                if (isset($notices[$turn]['Builds']) && $timeSent > $notices[$turn]['Builds']) { $phase = 'Builds'; }
                elseif (isset($notices[$turn]['Retreats']) && $timeSent > $notices[$turn]['Retreats']) { $phase = 'Retreats'; }
                else { $phase = 'Diplomacy'; }

                // Storing message
                $message = new Message(
                    $turn,                              // $turn
                    $phase,                             // $phase
                    $timeSent,                          // $timeSent
                    $row['fromCountryID'],              // $fromCountryID
                    $row['toCountryID'],                // $toCountryID
                    $row['message']);                   // $message
                $messages[$turn][$phase][] = $message;
            }
        }

        // Setting the units, orders, messages, and centers for each phase
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
        foreach ($orders as $order) {                                           /** @var Order $order */
            $phase = $gameSteps->get($order->turn, $order->phase, array());
            $phase['orders'][] = $order;
            $gameSteps->set($order->turn, $order->phase, $phase);
        }
        foreach ($messages as $turn => $msgsPerPhase) {                         /** @var Message $message */
            foreach ($msgsPerPhase as $phaseName => $msgObjects) {
                $phase = $gameSteps->get($turn, $phaseName, array());
                foreach ($msgObjects as $message) {
                    $phase['messages'][] = $message;
                }
                $gameSteps->set($turn, $phaseName, $phase);
            }
        }
        foreach ($gameSteps->toArray() as $step) {
            list($turn, $phaseName, $data) = $step;
            $centerTurn = $turn;

            // Centers for Fall Diplomacy and Fall Retreats are the same as Spring Diplomacy
            // Centers for Spring Diplomacy are the same as Winter builds
            if (($centerTurn % 2 == 1) && ($phaseName != 'Builds')) {$centerTurn -= 1; }
            elseif ($phaseName == 'Diplomacy') {$centerTurn -= 1; }

            // Using pre-game or in-game centers
            if ($centerTurn == -1) { $centers = $preGameCenters; }
            else { $centers = $inGameCenters[$centerTurn]; }

            // Setting data
            $data['centers'] = $centers;
            $data['turn'] = $turn;
            $data['phase'] = $phaseName;
            if (!isset($data['units'])) $data['units'] = array();
            if (!isset($data['orders'])) $data['orders'] = array();
            if (!isset($data['messages'])) $data['messages'] = array();
            $finalPhases[] = $data;
        }

        // Deduce units for Retreats and Builds phases.
        $nbFinalPhases = count($finalPhases);

        // Updating previous units for all phases, except the last
        for ($i = 0; $i < $nbFinalPhases - 1; ++$i) {

            // Resetting game board on movement phase
            if ($finalPhases[$i]['phase'] == 'Diplomacy') {
                $gameBoard = new GameBoard();
                foreach ($finalPhases[$i]['units'] as $previousUnit) {              /** @var Unit $previousUnit */
                    $gameBoard->add($previousUnit->countryID, $previousUnit->terrID, $previousUnit->unitType);
                }
            }

            // Retrieving units on board
            $units = $gameBoard->getUnits();

            // Setting orders for phase
            $retreating = array();
            foreach ($finalPhases[$i]['orders'] as $previousOrder) {                /** @var Order $previousOrder */

                // Unit was disbanded, or failed to retreat, so we can remove it
                if ($previousOrder->type == 'Disband'
                    || ($previousOrder->type == 'Destroy' && $previousOrder->success == 'Yes')
                    || ($previousOrder->type == 'Retreat' && $previousOrder->success == 'No')
                ) {
                    $gameBoard->remove($previousOrder->countryID, $previousOrder->terrID, $previousOrder->unitType);

                // Move or retreat order succeeded, then the unit moved.
                } else if (in_array($previousOrder->type, array('Move', 'Retreat')) && $previousOrder->success == 'Yes') {
                    $gameBoard->move($previousOrder->countryID, $previousOrder->unitType, $previousOrder->terrID, $previousOrder->toTerrID);

                // Unit was built
                } else if (in_array($previousOrder->type, array('Build Army', 'Build Fleet')) && $previousOrder->success == 'Yes') {
                    $gameBoard->add($previousOrder->countryID, $previousOrder->terrID, $previousOrder->type == 'Build Army' ? 'Army' : 'Fleet');
                }

                // Marking the unit that submitted retreat/disband orders as 'retreating'
                if (in_array($previousOrder->type, array('Retreat', 'Disband'))) {
                    $retreating[$previousOrder->countryID][$previousOrder->terrID][$previousOrder->unitType] = true;
                }
            };

            // Updating units for phase
            $nbUnits = count($units);
            for ($j = 0; $j < $nbUnits; ++$j) {
                if (isset($retreating[$units[$j]->countryID][$units[$j]->terrID][$units[$j]->unitType])) {
                    $units[$j]->retreating = 'Yes';
                }
            }
            $finalPhases[$i]['units'] = $units;
        }
        $this->phases = $finalPhases;
    }

    function toJson() { return json_encode($this); }
}
