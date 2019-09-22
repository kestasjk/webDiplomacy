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
require_once('api/responses/game_state.php');

use API\ApiKey;
use API\ApiRoute;
use API\GameState;
use API\RequestException;

/**
 * Route: "game/status"
 * Args: gameID, countryID
 * Type: GET request
 *
 * This route returns the state of a game (including the history) as seen by one power.
 *
 *      gameID: The gameID being requested
 *      countryID: The countryID requesting the game (to only see that country's messages)
 *
 * Example request:
 *      BASE_URL/api.php?route=game/status&gameID=123&countryID=4
 *
 * Example response:
 *
 *  {"gameID":123456,
     "countryID":5,
     "variantID":1,
     "turn":2,
     "phase":"Diplomacy",
     "gameOver":"No"
     "phases":[
        {
            "turn":0,
            "phase":"Diplomacy",
            "orders":[
                {"turn":0,"phase":"Diplomacy","countryID":3,"terrID":15,"unitType":"Army","type":"Hold","toTerrID":0,"fromTerrID":0,"viaConvoy":"No","success":"No","dislodged":"No"},
                {"turn":0,"phase":"Diplomacy","countryID":1,"terrID":2,"unitType":"Fleet","type":"Move","toTerrID":52,"fromTerrID":0,"viaConvoy":"No","success":"Yes","dislodged":"No"},
                {"turn":0,"phase":"Diplomacy","countryID":1,"terrID":3,"unitType":"Army","type":"Move","toTerrID":2,"fromTerrID":0,"viaConvoy":"No","success":"Yes","dislodged":"No"},
                ...],
            "messages": [
                {"turn":0,"phase":"Diplomacy","timeSent":1234567,"fromCountryID":5,"toCountryID":1,"message":"Hi There!!"}
                ...],
            "units":[
                {"unitType":"Army","retreating":"No","terrID":15,"countryID":3},
                {"unitType":"Army","retreating":"No","terrID":12,"countryID":3},
                {"unitType":"Fleet","retreating":"No","terrID":11,"countryID":3},
                ...],
            "centers":[
                {"terrID":2,"countryID":1},
                {"terrID":3,"countryID":1},
                {"terrID":6,"countryID":1},
                ...],
        },
        {
            "turn":1,
            "phase":"Diplomacy",
            "orders":[
                {"turn":1,"phase":"Diplomacy","countryID":2,"terrID":8,"unitType":"Army","type":"Hold","toTerrID":0,"fromTerrID":0,"viaConvoy":"No","success":"No","dislodged":"No"},
                {"turn":1,"phase":"Diplomacy","countryID":3,"terrID":15,"unitType":"Army","type":"Hold","toTerrID":0,"fromTerrID":0,"viaConvoy":"No","success":"No","dislodged":"No"},
                {"turn":1,"phase":"Diplomacy","countryID":1,"terrID":2,"unitType":"Army","type":"Move","toTerrID":35,"fromTerrID":0,"viaConvoy":"Yes","success":"Yes","dislodged":"No"},
                ...],
            "messages": [
                {"turn":1,"phase":"Diplomacy","timeSent":1234999,"fromCountryID":1,"toCountryID":5,"message":"Ok. Will do"}
                ...],
            "units":[
                {"unitType":"Army","retreating":"No","terrID":8,"countryID":2},
                {"unitType":"Fleet","retreating":"No","terrID":61,"countryID":2},
                {"unitType":"Army","retreating":"No","terrID":45,"countryID":2},
                ...],
            "centers":[
                {"terrID":2,"countryID":1},
                {"terrID":3,"countryID":1},
                {"terrID":6,"countryID":1},
                ...],
        },
        {
            "turn":1,
            "phase":"Builds",
            "orders":[
                {"turn":1,"phase":"Builds","countryID":7,"terrID":31,"unitType":"","type":"Build Army","toTerrID":0,"fromTerrID":0,"viaConvoy":"No","success":"Yes","dislodged":"No"},
                {"turn":1,"phase":"Builds","countryID":7,"terrID":32,"unitType":"","type":"Build Army","toTerrID":0,"fromTerrID":0,"viaConvoy":"No","success":"Yes","dislodged":"No"},
                {"turn":1,"phase":"Builds","countryID":5,"terrID":74,"unitType":"","type":"Build Army","toTerrID":0,"fromTerrID":0,"viaConvoy":"No","success":"Yes","dislodged":"No"},
                ...],
            "messages": [],
            "units":[
                {"unitType":"Army","retreating":"No","terrID":8,"countryID":2},
                {"unitType":"Army","retreating":"No","terrID":45,"countryID":2},
                {"unitType":"Fleet","retreating":"No","terrID":7,"countryID":2},
                ...],
            "centers":[
                {"terrID":74,"countryID":5},
                {"terrID":73,"countryID":5},
                {"terrID":72,"countryID":5},
                ...]
        },
        {
            "turn":2,
            "phase":"Diplomacy",
            "orders":[],
            "messages": [],
            "units":[
                {"unitType":"Army","retreating":"No","terrID":35,"countryID":1},
                {"unitType":"Fleet","retreating":"No","terrID":53,"countryID":1},
                ...],
            "centers":[
                {"terrID":74,"countryID":5},
                {"terrID":73,"countryID":5},
                ...]
        }],

     "standoffs":[],                // Format: [{"terrID":1,"countryID":0}, ...]      -- countryID is always set to 0
     "occupiedFrom":{}              // Format: {terrID: occupiedFromTerrID}
    }
 */
class GameStatus extends ApiRoute {

    /**
     * The requested gameID
     * @var int
     */
    public $gameID;

    /**
     * The countryID sending the request
     * @var int
     */
    public $countryID;

    /**
     * The requested game
     * @var \Game
     */
    public $game;

    /**
     * Indicates that we can also retrieve the private messages
     * @var boolean
     */
    public $withMessages;

    /**
     * GameStatus constructor.
     *
     * Route: 'game_status'
     * Method type: GET
     * Requirements: gameID, countryID
     *
     * @throws \Exception
     */
    public function __construct() {
        parent::__construct('game/status', 'GET', array('gameID', 'countryID'));
    }

    /**
     * Authorizes Route. The member must have the permission 'canReplaceUsersInCD' or be a member of the game
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @throws RequestException
     */
    protected function validateRoute($apiKey) {
        global $DB;
        $this->withMessages = false;

        // Parsing args
        $args = $this->getArgs();
        if ($args['gameID'] === null || !is_numeric($args['gameID'])) { throw new RequestException('Invalid game ID: '.$args['gameID']); }
        if ($args['countryID'] == null || !is_numeric($args['countryID'])) { throw new RequestException('Invalid country ID: '.$args['countryID']); }
        $this->gameID = intval($args['gameID']);
        $this->countryID = intval($args['countryID']);
        $this->game = $this->getAssociatedGame($this->gameID);

        // Retrieving member
        if (!isset($this->game->Members->ByCountryID[$this->countryID])) { return; }
        $member = $this->game->Members->ByCountryID[$this->countryID];

        // 1 - User must be requesting his own status as a member of the game to view messages
        if ($member->userID == $apiKey->userID) {
            $this->withMessages = true;
            return;
        }

        // 2- To view game messages, the player must be able to submit orders for that power
        // Therefore, it needs the 'canReplaceUsersInCD' permission for a country status of 'Left'
        // and a game that is not excluded and not part of a tournament
        $tournamentRow = $DB->sql_hash("SELECT tournamentID FROM wD_TournamentGames WHERE gameID=".$this->gameID." LIMIT 1;");
        if (!$apiKey->permissions['canReplaceUsersInCD']) { return; }
        if ($tournamentRow
            || $member->status != 'Left'
            || $this->game->allowBotCDOrdering != 'Yes'
            || $this->game->missingPlayerPolicy != 'Normal'
            || !in_array($this->game->variantID, \Config::$apiConfig['variantIDs'])
            || ($this->game->pressType != 'NoPress' && \Config::$apiConfig['noPressOnly'])
            || (!empty(\Config::$apiConfig['restrictToGameIDs']) && !in_array($this->gameID, \Config::$apiConfig['restrictToGameIDs'])))
        {
            return;
        }

        $this->withMessages = true;
        return;
    }

    /**
     * Processes Route. Returns the GameState for that game
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @return string - The JSON value to return.
     * @throws \Exception
     */
    protected function runRoute($apiKey) {
        $gameState = new GameState($this->gameID, $this->countryID, $this->withMessages);
        return $gameState->toJson();
    }
}
