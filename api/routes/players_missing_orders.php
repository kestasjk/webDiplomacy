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
require_once('api/responses/game_country.php');

use API\ApiKey;
use API\ApiRoute;
use API\GameCountry;

/**
 * Route: "players/missing_orders"
 * Args: None
 * Type: GET request
 *
 * This route returns a list of tuples (gameID, countryID) for all the games
 * being played by the user owning the API key that requires orders to be submitted
 *
 * Example request:
 *      BASE_URL/api.php?route=players/missing_orders
 *
 * Example response:
 *      [{"gameID": 1, "countryID": 1}, {"gameID": 12345, "countryID": 2}]
 */
class PlayersMissingOrders extends ApiRoute {

    /**
     * List of (gameID, countryID)
     * @var array
     */
    public $value = array();

    /**
     * MissingOrders constructor.
     *
     * Route: 'players/missing_orders'
     * Method type: GET
     * Requirements: None
     *
     * @throws \Exception
     */
    public function __construct() {
        parent::__construct('players/missing_orders', 'GET', array());
    }

    /**
     * Authorizes Route. This method is **always authorized** - Everyone can get a list of their games with missing orders.
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     */
    protected function validateRoute($apiKey) { return; }

    /**
     * Processes Route. Returns a list of tuples (gameID, countryID)
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @return string - The JSON value to return.
     */
    protected function runRoute($apiKey) {
        global $DB;

        // Filter allowed variantIDs
        $apiVariants = implode(', ', \Config::$apiConfig['variantIDs']);

        // Finds powers (gameID, countryID) that
        // 1) Are played by the user linked to the API key making the request (m.userID = $apiKey->userID)
        // 2) On a map (and a gameID) that is supported by the API
        // 3) Where orders have not yet been submitted (orderStatus is NULL or '') and player is playing (not defeated)
        // 4) Only if the game is still active (i.e. not pre-game, finished, paused, etc.)
        // 5) Only if the turn is < 100 (to avoid any games going past W1950A)
        $countryTabl = $DB->sql_tabl("SELECT m.gameID, m.countryID
                                      FROM wD_Members AS m
                                      LEFT JOIN wD_Games AS g ON (g.id = m.gameID)
                                      WHERE (m.orderStatus IS NULL OR m.orderStatus = '')
                                            AND m.status = 'Playing'
                                            AND m.userID = $apiKey->userID
                                            AND g.variantID in ($apiVariants)
                                            AND g.processStatus = 'Not-processing'
                                            AND g.phase IN ('Diplomacy', 'Retreats', 'Builds')
                                            AND g.turn < 100
                                      ORDER BY g.processTime;");

        // Building list of tuples, then serializing to JSON
        while($row = $DB->tabl_hash($countryTabl)) {
            array_push($this->value, new GameCountry($row['gameID'], $row['countryID']));
        }
        return json_encode($this->value);
    }
}
