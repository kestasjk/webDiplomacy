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

namespace API\Route;
defined('IN_CODE') or die('This script can not be run by itself.');

require_once('config.php');
require_once('api/api_key.php');
require_once('api/api_route.php');
require_once('api/exceptions.php');
require_once('board/orders/orderinterface.php');
require_once('objects/game.php');
require_once('objects/misc.php');
require_once('objects/notice.php');
require_once('objects/user.php');
require_once('gamemaster/game.php');
require_once('lib/gamemessage.php');

use API\ApiKey;
use API\ApiRoute;
use API\ClientForbiddenException;
use API\RequestException;
use libGameMessage;

/**
 * Route: "game/orders"
 * Args: gameID, turn, phase, countryID, orders, ready
 * Type: JSON request (POST with JSON as body)
 *
 * This route sets the orders (and, optionally, the ready flag) for a power
 *
 *      gameID: The gameID where the orders are sent
 *      turn: The turn (e.g. 1) that the orders are submitted for
 *      phase: The phase (e.g 'Diplomacy') that the orders are submitted for
 *      countryID: The countryID setting the orders
 *      orders: A list of orders (see below)
 *      ready: Optional. 'Yes' or 'No' to mark orders as ready
 *
 * Orders
 * =================
 *
 * Hold Order:
 * -------------------------------------------------------
 *
 * {"terrID": 123,                  // Territory ID of unit holding
 *  "type": "Hold",                 // "Hold"
 *  "toTerrID": "",                 // Leave blank
 *  "fromTerrID": "",               // Leave blank
 *  "viaConvoy": ""}                // Leave blank
 *
 * Move Order (No convoy):
 * -------------------------------------------------------
 *
 * {"terrID": 123,                  // The territory ID where the unit is
 *  "type": "Move",                 // "Move"
 *  "toTerrID": 456,                // The territory ID where the unit wants to go
 *  "fromTerrID": "",               // Leave blank
 *  "viaConvoy": "No"}              // Needs to be "No"
 *
 * Move Order (Via convoy):
 * -------------------------------------------------------
 *
 * {"terrID": 123,                  // The territory ID where the unit is
 *  "type": "Move",                 // "Move"
 *  "toTerrID": 456,                // The territory ID where the unit wants to go
 *  "fromTerrID": "",               // Leave blank
 *  "viaConvoy": "Yes",             // Needs to be "Yes"
 *  "convoyPath": [123,124,125]}    // 123 is the terrID where we are starting
 *                                  // 124,125 are terrIDs of fleets on the water (any powers)
 *                                  // The path 123-124-125-456 is a valid path (123 is adj. to 124, 124 to 125, 125 to 456)
 *
 * Support hold:
 * -------------------------------------------------------
 *
 * {"terrID": 123,                  // Territory ID of the unit issuing the support
 *  "type": "Support hold",         // "Support hold"
 *  "toTerrID": 456,                // Territory ID of the unit receiving the support
 *  "fromTerrID": "",               // Leave blank
 *  "viaConvoy": ""}                // Leave blank
 *
 * Support move (the supported unit is not being convoyed):
 * -------------------------------------------------------
 *
 * {"terrID": 123,                  // Territory ID of the unit issuing the support
 *  "type": "Support move",         // "Support move"
 *  "toTerrID": 456,                // The territory ID where the supported unit wants to go
 *  "fromTerrID": 234,              // The territory ID where the supported unit currently is
 *  "viaConvoy": ""}                // Leave blank
 *
 * Support move (the supported unit is moving via convoy):
 * -------------------------------------------------------
 *
 * {"terrID": 123,                  // Territory ID of the unit issuing the support
 *  "type": "Support move",         // "Support move"
 *  "toTerrID": 456,                // The territory ID where the supported unit wants to go
 *  "fromTerrID": 234,              // The territory ID where the supported unit currently is
 *  "viaConvoy": "",                // Leave blank
 *  "convoyPath": [234,85,78]}      // 234 is the terrID where the supported unit is
 *                                  // 85,78 are terrIDs of fleets on the water (any powers)
 *                                  // Note: The fleets on the water must EXCLUDE the unit issuing the support move order
 *                                  //       i.e. [234,123,78] would be an invalid convoy path
 *                                  // The path 234-85-78-456 is a valid path (234 is adj. to 85, 85 to 78, 78 to 456)
 *
 * Convoy:
 * -------------------------------------------------------
 *
 * {"terrID": 123,                  // Territory ID of the unit issuing the convoy order
 *  "type": "Convoy",               // "Support move"
 *  "toTerrID": 456,                // The territory ID where the convoyed unit wants to go
 *  "fromTerrID": 234,              // The territory ID where the convoyed unit currently is
 *  "viaConvoy": "",                // Leave blank
 *  "convoyPath": [234,85,123]}     // 234 is the terrID where the convoyed unit is
 *                                  // 85,123 are terrIDs of fleets on the water (any powers)
 *                                  // Note: The fleets on the water must INCLUDE the unit issuing the convoy order
 *                                  //       i.e. [234,85,78] would be an invalid convoy path because 123 is missing
 *                                  // The path 234-85-123-456 is a valid path (234 is adj. to 85, 85 to 123, 123 to 456)
 *
 * Retreat:
 * -------------------------------------------------------
 *
 * {"terrID": 123,                  // The territory ID where the unit is
 *  "type": "Retreat",              // "Retreat"
 *  "toTerrID": 456,                // The territory ID where the unit wants to retreat
 *  "fromTerrID": "",               // Leave blank
 *  "viaConvoy": ""}                // Leave blank
 *
 * Disband (Retreats phase):
 * -------------------------------------------------------
 *
 * {"terrID": 123,                  // The territory ID where the unit to disband is
 *  "type": "Disband",              // "Disband"
 *  "toTerrID": "",                 // Leave blank
 *  "fromTerrID": "",               // Leave blank
 *  "viaConvoy": ""}                // Leave blank
 *
 * Build Army:
 * -------------------------------------------------------
 *
 * {"terrID": 123,                  // The territory ID where to build the army
 *  "type": "Build Army",           // "Build Army"
 *  "toTerrID": 123,                // The territory ID where to build the army (again)
 *  "fromTerrID": "",               // Leave blank
 *  "viaConvoy": ""}                // Leave blank
 *
 * Build Fleet:
 * -------------------------------------------------------
 *
 * {"terrID": 123,                  // The territory ID where to build the fleet
 *  "type": "Build Fleet",          // "Build Fleet"
 *  "toTerrID": 123,                // The territory ID where to build the fleet (again)
 *  "fromTerrID": "",               // Leave blank
 *  "viaConvoy": ""}                // Leave blank
 *
 * Destroy (Builds phase):
 * -------------------------------------------------------
 *
 * {"terrID": 123,                  // The territory ID where to unit to be destroy is
 *  "type": "Destroy",              // "Destroy"
 *  "toTerrID": 123,                // The territory ID where to unit to be destroy is (again)
 *  "fromTerrID": "",               // Leave blank
 *  "viaConvoy": ""}                // Leave blank
 *
 *                                  // NOTE: terrID needs to be without the coast
 *                                  // i.e. to destroy F SPA/NC, you need set the terrID to the value of 'SPA'
 *
 * Wait:
 * -------------------------------------------------------
 *
 * {"terrID": null,                 // Sets to null, all unused builds will be waived
 *  "type": "Wait",                 // "Wait"
 *  "toTerrID": "",                 // Leave blank
 *  "fromTerrID": "",               // Leave blank
 *  "viaConvoy": ""}                // Leave blank
 *
 *
 * Example request:
 *      BASE_URL/api.php?route=game/orders
 *
 * POST Data:
 *     {"gameID":123,
 *      "turn":1,
 *      "phase":"Diplomacy",
 *      "countryID":1,
 *      "orders":[{"terrID": 123, "type": "Hold", "toTerrID": "", "fromTerrID": "", "viaConvoy": ""},
 *                {"terrID": 123, "type": "Move", "toTerrID": 456, "fromTerrID": "", "viaConvoy": "No"}],
 *      "ready": "Yes"}
 *
 * This route returns the list of orders that are now set on the server (same format as above, without convoyPath).
 * They can be used by the client to decide if the orders were set successful or not.
 *
 * Example response:
 *      [{"terrID": 123, "type": "Hold", "toTerrID": "", "fromTerrID": "", "viaConvoy": ""},
 *       {"terrID": 123, "type": "Move", "toTerrID": 456, "fromTerrID": "", "viaConvoy": "No"}]
 */
class GameOrders extends ApiRoute {

    /**
     * The requested gameID
     * @var int
     */
    public $gameID;

    /**
     * The expected turn
     * @var int
     */
    public $turn;

    /**
     * The expected phase
     * @var string
     */
    public $phase;

    /**
     * The countryID sending the request
     * @var int
     */
    public $countryID;

    /**
     * The requested orders
     * @var array
     */
    public $orders;

    /**
     * The requested ready flag
     * @var string | null
     */
    public $ready;

    /**
     * The requested game
     * @var \Game
     */
    public $game;

    /**
     * The member requesting to set the orders
     * @var \Member
     */
    public $member;

    /**
     * Indicates if the user is replacing someone in CD
     * @var bool
     */
    private $isReplacingUserInCD;

    /**
     * Initial Ready status (the value of the ready flag before setting the orders)
     */
    private $initialReadyStatus;
    private $finalReadyStatus;

    /**
     * GameOrders constructor.
     * Method type: JSON
     * Requirements: gameID, turn, phase, countryID, orders, ready
     *
     * @throws \Exception
     */
    public function __construct() {
        parent::__construct('game/orders', 'JSON', array('gameID', 'turn', 'phase', 'countryID', 'orders', 'ready'));
    }

    /**
     * Authorizes Route. The member must have the permission 'canReplaceUsersInCD' or be a member of the game
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @throws RequestException
     * @throws ClientForbiddenException
     */
    protected function validateRoute($apiKey) {
        global $DB;

        // Parsing args
        $args = $this->getArgs();
        if ($args['gameID'] === null || !is_numeric($args['gameID'])) { throw new RequestException('Invalid game ID: '.$args['gameID']); }
        if ($args['turn'] === null || !is_numeric($args['turn'])) { throw new RequestException('Invalid Turn: '.$args['turn']); }
        if ($args['phase'] === null) { throw new RequestException('Phase is required.'); }
        if ($args['countryID'] === null || !is_numeric($args['countryID'])) { throw new RequestException('Invalid country ID: '.$args['countryID']); }
        if (!is_array($args['orders'])) { throw new RequestException('Body field `orders` is not an array.'); }
        if ($args['ready'] && (!is_string($args['ready']) || !in_array($args['ready'], array('Yes', 'No')))) {
            throw new RequestException('Body field `ready` is not either `Yes` or `No`.');
        }

        // Setting values
        $this->gameID = intval($args['gameID']);
        $this->turn = intval($args['turn']);
        $this->phase = strval($args['phase']);
        $this->countryID = intval($args['countryID']);
        $this->orders = $args['orders'];
        $this->ready = $args['ready'];
        $this->game = $this->getAssociatedGame($this->gameID);
        $this->isReplacingUserInCD = false;

        // Finding member
        if (!isset($this->game->Members->ByCountryID[$this->countryID])) {
            throw new RequestException('Cannot submit orders for an invalid country ID: ' . $this->countryID);
        }
        $this->member = $this->game->Members->ByCountryID[$this->countryID];

        $this->initialReadyStatus = $this->member->orderStatus->Ready;
        $this->finalReadyStatus = $this->initialReadyStatus;

        // Validating game status
        if ($this->game->phase == 'Finished') {
            throw new RequestException('Cannot submit orders for a completed game.');
        }
        if (!in_array($this->game->phase, array('Diplomacy', 'Retreats', 'Builds'))) {
            throw new RequestException('Cannot submit orders in phase `'.$this->game->phase.'`.');
        }
        if ($this->turn != $this->game->turn) {
            throw new RequestException('Invalid turn, expected `'.$this->game->turn.'`, got `'.$this->turn.'`.');
        }
        if ($this->phase != $this->game->phase) {
            throw new RequestException('Invalid phase, expected `'.$this->game->phase.'`, got `'.$this->phase.'`.');
        }

        // 1 - User must be sending orders for himself
        if ($this->member->userID == $apiKey->userID) { return; }

        // 2a - User must have the 'canReplaceUsersInCD' permission and the requested countryID must be in 'Left' status
        if (!$apiKey->permissions['canReplaceUsersInCD']) {
            throw new ClientForbiddenException('You can only submit orders for games where you are a member.');
        }
        if ($this->member->status != 'Left') {
            throw new ClientForbiddenException('You can only submit orders for games where a user is in civil disorder. or you are a member.');
        }

        // 2b - The game must have a 'Normal' missing player policy and not be part of a tournament
        $tournamentRow = $DB->sql_hash("SELECT tournamentID FROM wD_TournamentGames WHERE gameID=".$this->gameID." LIMIT 1;");
        if ($tournamentRow
            || $this->game->allowBotCDOrdering != 'Yes'
            || $this->game->missingPlayerPolicy != 'Normal'
            || !in_array($this->game->variantID, \Config::$apiConfig['variantIDs'])
            || ($this->game->pressType != 'NoPress' && \Config::$apiConfig['noPressOnly'])
            || (!empty(\Config::$apiConfig['restrictToGameIDs']) && !in_array($this->gameID, \Config::$apiConfig['restrictToGameIDs'])))
        {
            throw new ClientForbiddenException('This game is part of a tournament, or has been excluded from the games where users in CD can get their orders replaced.');
        }

        // Marking isReplacingUserInCD as true
        $this->isReplacingUserInCD = true;
        return;
    }

    /**
     * Processes Route. Sets the orders for the country in the specified game
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @return string - The JSON value to return.
     * @throws \Exception
     */
    protected function runRoute($apiKey) {
        global $DB;

        // API caller is a member of the game
        // Setting the member status as Active
        if (!$this->isReplacingUserInCD) {
            $this->markMemberAsActive($apiKey, $this->game, $this->member);

        // API Caller is replacing orders for a user in CD
        // Sending a notice to the game to let users know
        } else {
            $apiUser = new \User($apiKey->userID);
            $message = sprintf('%s submitted orders for %s in %s (%s phase).',
                               $apiUser->username,
                               $this->member->country,
                               $this->game->datetxt(),
                               $this->game->phase);
            libGameMessage::send('Global', 'GameMaster', $message, $this->gameID);
            $DB->sql_put("COMMIT");
        }

        // Updating orders
        $this->setOrders($apiKey);
        $currentOrders = $this->getCurrentOrders();

        // Processing game
        $this->processGame($this->game, $this->initialReadyStatus, $this->finalReadyStatus);

        // Returning current orders
        return json_encode($currentOrders);
    }

    /**
     * Sets the requested orders for the countryID on the specified gameID
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @throws RequestException
     * @throws \Exception
     */
    private function setOrders($apiKey) {
        global $DB;

        $territoryToOrder = array();            // Mapping $terrID => $orderID
        $orderToTerritory = array();            // Mapping $orderID => $terrID

        $waitIsSubmitted = false;               // Indicates that we have received a 'Wait' order
        $updatedOrders = array();               // List of new orders

        // Getting the orderID for each unit/territory
        $res = $DB->sql_tabl("SELECT wD_Orders.id AS orderID, wD_Units.terrID AS terrID
                              FROM wD_Orders
                              LEFT JOIN wD_Units ON (wD_Orders.gameID = wD_Units.gameID
                                                     AND wD_Orders.countryID = wD_Units.countryID
                                                     AND wD_Orders.unitID = wD_Units.id) 
                              WHERE wD_Orders.gameID = ".$this->gameID." 
                                AND wD_Orders.countryID = ".$this->countryID);
        while ($row = $DB->tabl_hash($res)) {
            $orderID = $row['orderID'];
            $terrID = $row['terrID'];
            $orderToTerritory[$orderID] = $terrID;
            if ($terrID !== null) { $territoryToOrder[$terrID] = $orderID; }        // null in Builds phase
        }

        // Processing each order
        foreach ($this->orders as $order) {
            $newOrder = array();

            // Checking all fields
            foreach (array('terrID', 'type', 'fromTerrID', 'toTerrID', 'viaConvoy') as $bodyField) {
                if (!array_key_exists($bodyField, $order)) {
                    throw new RequestException('Missing order info: ' . $bodyField);
                }
                $newOrder[$bodyField] = $order[$bodyField];
            }
            if (array_key_exists('convoyPath', $order)) { $newOrder['convoyPath'] = $order['convoyPath']; }

            // There is an order associated to this territory. Get this order ID.
            if (array_key_exists($order['terrID'], $territoryToOrder)) {
                $newOrder['id'] = $territoryToOrder[$order['terrID']];

            // No order yet associated to this territory.
            // Check if there a free (non-associated) orders.
            // If so, use first free order found.
            // Otherwise, raise an exception.
            } else {
                $freeOrderID = null;
                foreach ($orderToTerritory as $orderID => $territoryID) {
                    if ($territoryID === null) {
                        $freeOrderID = $orderID;
                        break;
                    }
                }

                // If no free orders, raise an exception.
                if ($freeOrderID === null) {
                    throw new RequestException('Unknown territory ID `'.$order['terrID'].'` for country `'.$countryID.'`.');
                }

                // Free order. Use it and update related dictionaries.
                $newOrder['id'] = $freeOrderID;
                $orderToTerritory[$freeOrderID] = $order['terrID'];
                $territoryToOrder[$order['terrID']] = $freeOrderID;
            }

            // Making sure the territory requires an order
            // If the state is received before a process, and the orders after the process, this would raised
            if (!array_key_exists($order['terrID'], $territoryToOrder)) {
                throw new RequestException('Unknown territory ID `'.$order['terrID'].'` for country `'.$this->countryID.'`. 
                                            Maybe the phase was processed or the game drawn?');
            }

            // Setting updated order
            $updatedOrders[$newOrder['id']] = $newOrder;
            if ($order['type'] == 'Wait') { $waitIsSubmitted = true; }
        }

        // If a 'Wait' order was submitted on a Builds phase, set all free orders to 'Wait'.
        if ($this->game->phase == 'Builds' && $waitIsSubmitted) {
            foreach ($orderToTerritory as $orderID => $territoryID) {
                if (!array_key_exists($orderID, $updatedOrders) && $territoryID === null) {
                    $updatedOrders[$orderID] = array(
                        'terrID' => null,
                        'type' => 'Wait',
                        'fromTerrID' => null,
                        'toTerrID' => null,
                        'viaConvoy' => null
                    );
                }
            }
        }

        // Setting orders on the server
        $orderInterface = null;
        do {
            if (!$updatedOrders) { break; }
            $ordersSetSuccessfully = true;

            // Creating order interface
            $orderInterface = new \OrderInterface(
                $this->gameID,
                $this->game->variantID,
                $apiKey->userID,
                $this->member->id,
                $this->turn,
                $this->phase,
                $this->countryID,
                $this->member->orderStatus,
                null,
                false
            );
            $orderInterface->orderStatus->Ready = false;

            // Load updated orders.
            $orderInterface->load();
            $orderInterface->set(json_encode(array_values($updatedOrders)));
            $results = $orderInterface->validate();

            // Removing invalid order and retry
            if ($results['invalid']) {
                $ordersSetSuccessfully = false;
                foreach ($results['orders'] as $orderID => $orderObject) {
                    if ($orderObject['status'] == 'Invalid') {
                        unset($updatedOrders[$orderID]);
                    }
                }
            }
        } while(!$ordersSetSuccessfully);

        // Writing orders on server
        if (!empty($updatedOrders)) {
            $orderInterface->writeOrders();
            $DB->sql_put("COMMIT");
        }

        // Updating the ready flag
        $orderInterface->orderStatus->Completed = true;
        $orderInterface->orderStatus->Ready = ($this->ready ? $this->ready == 'Yes' : $this->initialReadyStatus);
        $orderInterface->writeOrderStatus();
        $this->finalReadyStatus = $orderInterface->orderStatus->Ready;
        $DB->sql_put("COMMIT");
    }

    /**
     * Computes and returns the current orders set for the countryID in the requested gameID
     * @return array - List of orders
     */
    private function getCurrentOrders() {
        global $DB;
        $currentOrders = array();
        $currentOrdersTabl = $DB->sql_tabl("SELECT wD_Orders.id AS orderID,
                                                   wD_Orders.type AS type,
                                                   wD_Orders.fromTerrID AS fromTerrID,
                                                   wD_Orders.toTerrID AS toTerrID,
                                                   wD_Orders.viaConvoy AS viaConvoy,
                                                   wD_Units.type as unitType,
                                                   wD_Units.terrID AS terrID
                                            FROM wD_Orders
                                            LEFT JOIN wD_Units ON (wD_Orders.gameID = wD_Units.gameID
                                                                   AND wD_Orders.countryID = wD_Units.countryID
                                                                   AND wD_Orders.unitID = wD_Units.id)
                                            WHERE wD_Orders.gameID = ".$this->gameID."
                                            AND wD_Orders.countryID = ".$this->countryID);
        while ($row = $DB->tabl_hash($currentOrdersTabl)) {
            $currentOrders[] = array(
                'unitType' => $row['unitType'],
                'terrID' => is_numeric($row['terrID']) ? intval($row['terrID']) : $row['terrID'],
                'type' => $row['type'],
                'fromTerrID' => is_numeric($row['fromTerrID']) ? intval($row['fromTerrID']) : $row['fromTerrID'],
                'toTerrID' => is_numeric($row['toTerrID']) ? intval($row['toTerrID']) : $row['toTerrID'],
                'viaConvoy' => $row['viaConvoy']
            );
        }
        return $currentOrders;
    }
}
