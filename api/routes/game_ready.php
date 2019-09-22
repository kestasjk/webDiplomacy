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

use API\ApiKey;
use API\ApiRoute;
use API\ClientForbiddenException;
use API\RequestException;

/**
 * Route: "game/ready"
 * Args: gameID, turn, phase, countryID, ready
 * Type: JSON request (POST with JSON as body)
 *
 * This route updates the ready flag for a power
 *
 *      gameID: The gameID where the ready flag is set
 *      turn: The turn (e.g. 1) that the ready flag is submitted for
 *      phase: The phase (e.g 'Diplomacy') that the ready flag is submitted for
 *      countryID: The countryID setting the ready flag
 *      ready: 'Yes' or 'No' to mark orders as ready
 *
 * Example request:
 *      BASE_URL/api.php?route=game/ready
 *
 * POST Data:
 *     {"gameID":123,
 *      "turn":1,
 *      "phase":"Diplomacy",
 *      "countryID":1,
 *      "ready": "Yes"}
 *
 * This route returns the current status of the ready flag.
 *
 * Example response:
 *      {"ready": "Yes"}
 */
class GameReady extends ApiRoute {

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
     * The requested ready flag
     * @var string
     */
    public $ready;

    /**
     * The requested game
     * @var \Game
     */
    public $game;

    /**
     * The member requesting to set the ready flag
     * @var \Member
     */
    public $member;

    /**
     * Initial Ready status (the value of the ready flag before setting the orders)
     */
    private $initialReadyStatus;
    private $finalReadyStatus;

    /**
     * GameReady constructor.
     * Method type: JSON
     * Requirements: gameID, turn, phase, countryID, ready
     *
     * @throws \Exception
     */
    public function __construct() {
        parent::__construct('game/ready', 'JSON', array('gameID', 'turn', 'phase', 'countryID', 'ready'));
    }

    /**
     * Authorizes Route. The user must be a member of the game
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @throws RequestException
     * @throws ClientForbiddenException
     */
    protected function validateRoute($apiKey) {
        // Parsing args
        $args = $this->getArgs();
        if ($args['gameID'] === null || !is_numeric($args['gameID'])) { throw new RequestException('Invalid game ID: '.$args['gameID']); }
        if ($args['turn'] === null || !is_numeric($args['turn'])) { throw new RequestException('Invalid Turn: '.$args['turn']); }
        if ($args['phase'] === null) { throw new RequestException('Phase is required.'); }
        if ($args['countryID'] === null || !is_numeric($args['countryID'])) { throw new RequestException('Invalid country ID: '.$args['countryID']); }
        if ($args['ready'] === null) { throw new RequestException('Ready is required.'); }

        // Setting values
        $this->gameID = intval($args['gameID']);
        $this->turn = intval($args['turn']);
        $this->phase = strval($args['phase']);
        $this->countryID = intval($args['countryID']);
        $this->ready = strval($args['ready']);
        $this->game = $this->getAssociatedGame($this->gameID);

        // Finding member
        if (!isset($this->game->Members->ByCountryID[$this->countryID])) {
            throw new RequestException('Cannot update ready flag for an invalid country ID: ' . $this->countryID);
        }
        $this->member = $this->game->Members->ByCountryID[$this->countryID];
        $this->initialReadyStatus = $this->member->orderStatus->Ready;
        $this->finalReadyStatus = $this->initialReadyStatus;

        // Validating game status
        if ($this->game->phase == 'Finished') {
            throw new RequestException('Cannot update ready flag for a completed game.');
        }
        if (!in_array($this->game->phase, array('Diplomacy', 'Retreats', 'Builds'))) {
            throw new RequestException('Cannot update ready flag in phase `'.$this->game->phase.'`.');
        }
        if ($this->turn != $this->game->turn) {
            throw new RequestException('Invalid turn, expected `'.$this->game->turn.'`, got `'.$this->turn.'`.');
        }
        if ($this->phase != $this->game->phase) {
            throw new RequestException('Invalid phase, expected `'.$this->game->phase.'`, got `'.$this->phase.'`.');
        }
        if (!in_array($this->ready, array('Yes', 'No'))) {
            throw new RequestException('Body field `ready` is not either `Yes` or `No`.');
        }

        // User must be updating the ready flag for himself
        if ($this->member->userID != $apiKey->userID) {
            throw new ClientForbiddenException('You can only update the ready flag for your own countries.');
        }
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

        // Setting the member status as Active
        $this->markMemberAsActive($apiKey, $this->game, $this->member);

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

        // Updating flag
        $orderInterface->load();
        $orderInterface->orderStatus->Completed = true;
        $orderInterface->orderStatus->Ready = ($this->ready == 'Yes');
        $orderInterface->writeOrderStatus();
        $this->finalReadyStatus = $orderInterface->orderStatus->Ready;
        $DB->sql_put("COMMIT");

        // Processing game
        $this->processGame($this->game, $this->initialReadyStatus, $this->finalReadyStatus);

        // Returning current status
        return json_encode(array("ready" => $this->finalReadyStatus ? "Yes" : "No"));
    }
}
