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
require_once('api/exceptions.php');

use API\ApiKey;
use API\ApiRoute;
use API\GameCountry;
use API\ClientForbiddenException;

/**
 * Route: "players/cd"
 * Args: None
 * Type: GET request
 *
 * This route returns a list of tuples (gameID, countryID) for all the games
 * where one of the players is in civil disorder and needs orders to be submitted on his behalf
 *
 * Note: The permission "canReplaceUsersInCD" is required to access this API route.
 *
 * Example request:
 *      BASE_URL/api.php?route=players/cd
 *
 * Example response:
 *      [{"gameID": 1, "countryID": 1}, {"gameID": 12345, "countryID": 2}]
 */
class PlayersCD extends ApiRoute {

    /**
     * List of (gameID, countryID)
     * @var array
     */
    public $value = array();

    /**
     * PlayersCD constructor.
     *
     * Route: 'players/cd'
     * Method type: GET
     * Requirements: None
     *
     * @throws \Exception
     */
    public function __construct() {
        parent::__construct('players/cd', 'GET', array());
    }

    /**
     * Authorizes Route. This method requires the permission "canReplaceUsersInCD".
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @throws ClientForbiddenException
     */
    protected function validateRoute($apiKey) {
        if (!$apiKey->permissions['canReplaceUsersInCD']) {
            throw new ClientForbiddenException('Forbidden. The permission "canReplaceUsersInCD" is required to access this route.');
        }
    }

    /**
     * Processes Route. Returns a list of tuples (gameID, countryID)
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @return string - The JSON value to return.
     */
    protected function runRoute($apiKey) {
        global $DB;

        // Filter allowed variantIDs
        $apiVariants = implode(', ', \Config::$apiConfig['variantIDs']);

        // Filter noPress only
        $filterNoPress = '';
        if (\Config::$apiConfig['noPressOnly']) {
            $filterNoPress = "AND g.pressType = 'NoPress'";
        }

        // Filter allowed gameIDs
        $filterGameClause = '';
        if (!empty(\Config::$apiConfig['restrictToGameIDs'])) {
            $filterGameIDs = implode(', ', \Config::$apiConfig['restrictToGameIDs']);
            $filterGameClause = "AND g.id IN ($filterGameIDs)";
        }

        // Finds powers (gameID, countryID) that
        // 1) Are played by a user in "Left" status
        // 2) On a map (and a gameID) that is supported by the API
        // 3) Where orders have not yet been submitted (orderStatus is NULL or '')
        // 4) Only if the game is still active (i.e. not pre-game, finished, paused, etc.)
        // 5) Only if the missingPlayerPolicy is set to Normal (as opposed to Strict or Wait)
        // 6) Only if the game is not part of any tournaments
        $countryTabl = $DB->sql_tabl("SELECT m.gameID, m.countryID
                                      FROM wD_Members AS m
                                      LEFT JOIN wD_Games AS g ON (g.id = m.gameID)
                                      LEFT JOIN wD_TournamentGames as t ON (t.gameID = m.gameID)
                                      WHERE (m.orderStatus IS NULL OR m.orderStatus = '')
                                            AND m.status = 'Left'
                                            AND g.variantID in ($apiVariants)
                                            " . $filterNoPress . "
                                            " . $filterGameClause . "
                                            AND g.processStatus = 'Not-processing'
                                            AND g.phase IN ('Diplomacy', 'Retreats', 'Builds')
                                            AND g.allowBotCDOrdering = 'Yes'
                                            AND g.missingPlayerPolicy = 'Normal'
                                            AND t.tournamentID IS NULL
                                      ORDER BY g.processTime;");

        // Building list of tuples, then serializing to JSON
        while( $row = $DB->tabl_hash($countryTabl) ) {
            array_push($this->value, new GameCountry($row['gameID'], $row['countryID']));
        }
        return json_encode($this->value);
    }
}
