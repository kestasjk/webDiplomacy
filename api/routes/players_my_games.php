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
use API\RequestException;

/**
 * Route: "players/my_games"
 * Args: status, missing_orders, messages_after, press_type, variant_id, anonymous, scoring, player_type
 *
 * Type: GET request
 *
 *      - status: Optional. If set, one of or list of "Playing", "Defeated", "Left", "Won", "Drawn", "Survived", "Resigned"
 *      - missing_orders: Optional. If set, one of "Yes", "No"
 *      - messages_after: Optional. If set a timestamp to filter games where messages were received after that timestamp
 *      - press_type: Optional. If set, one of or list of "Regular", "PublicPressOnly", "NoPress", "RulebookPress"
 *      - variant_id: Optional. If set, one or list of variantIDs (integers)
 *      - anonymous: Optional. If set, one of "Yes", "No"
 *      - scoring: Optional. If set, one of or list of "WTA", "PPSC", "Unranked", "SOS"
 *      - player_type: Optional. If set one of or list of "Members", "Mixed", "MembersVsBots"
  *
 * This route returns a list of tuples (gameID, countryID) for all the games played by the requester
 * that meets the required conditions
 *
 * Example request:
 *      BASE_URL/api.php?route=players/my_games?status=Playing,Left&press_type=Regular&missing_orders=Yes
 *
 * Example response:
 *      [{"gameID": 1, "countryID": 1}, {"gameID": 12345, "countryID": 2}]
 */
class PlayersMyGames extends ApiRoute {

    /**
     * List of (gameID, countryID)
     * @var array
     */
    public $value = array();

    // Static variables
    public static $validStatus = array('Playing', 'Defeated', 'Left', 'Won', 'Drawn', 'Survived', 'Resigned');
    public static $validPress = array('Regular', 'PublicPressOnly', 'NoPress', 'RulebookPress');
    public static $validPotType = array('WTA' => 'Winner-takes-all',
                                        'PPSC' => 'Points-per-supply-center',
                                        'Unranked' => 'Unranked',
                                        'SOS' => 'Sum-of-squares');
    public static $validPlayerType = array('Members', 'Mixed', 'MemberVsBots');

    /**
     * PlayersMyGames constructor.
     *
     * Route: 'players/my_games'
     * Method type: GET
     * Requirements: status, missing_orders, messages_after, press_type, variant_id, anonymous, scoring, player_type
     *
     * @throws \Exception
     */
    public function __construct() {
        $requirements = array('status', 'missing_orders', 'messages_after', 'press_type', 'variant_id', 'anonymous', 'scoring', 'player_type');
        parent::__construct('players/my_games', 'GET', $requirements);
    }

    /**
     * Authorizes Route. This method is **always authorized** - Everyone can get a list of their games.
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     */
    protected function validateRoute($apiKey) { return; }

    /**
     * Processes Route. Returns a list of tuples (gameID, countryID)
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @return string - The JSON value to return.
     * @throws RequestException
     */
    protected function runRoute($apiKey) {
        global $DB;
        $args = $this->getArgs();

        // Status - 'Playing', 'Defeated', 'Left', 'Won', 'Drawn', 'Survived', 'Resigned'
        $filterStatusClause = "";
        $statusToQuery = array();
        if ($args['status'] != null) {
            foreach (explode(",", $args['status']) as $status) {
                if (!in_array($status, PlayersMyGames::$validStatus)) {
                    throw new RequestException('Status `'.$status.'` is not a recognized status.');
                }
                $statusToQuery[] = "'".$status."'";
            }
            $statusList = implode(', ', $statusToQuery);
            $filterStatusClause = "AND m.status IN ($statusList)";
        }

        // Missing Orders - 'Yes', 'No'
        $filterMissingOrdersClause = "";
        if ($args['missing_orders'] != null) {
            if ($args['missing_orders'] == "Yes") {
                $filterMissingOrdersClause = "AND (m.orderStatus IS NULL OR m.orderStatus = '')
                                              AND g.processStatus = 'Not-processing'
                                              AND g.phase IN ('Diplomacy', 'Retreats', 'Builds')
                                              AND g.turn < 100";
            } elseif ($args['missing_orders'] == "No") {
                $filterMissingOrdersClause = "AND (m.orderStatus IS NOT NULL AND m.orderStatus != '')";
            } else {
                throw new RequestException('Value for missing_orders for must one of: "Yes", "No".');
            }
        }

        // Messages after - Timestamp
        $filterMessagesAfterClause = "";
        if ($args['messages_after'] != null) {
            if (!is_numeric($args['messages_after'])) {
                throw new RequestException('The value for messages_after is supposed to be an integer.');
            }
            $messages_after = intval($args['messages_after']);
            $filterMessagesAfterClause = "AND msg.timeSent >= $messages_after";
        }

        // Press type - 'Regular', 'PublicPressOnly', 'NoPress', 'RulebookPress'
        $filterPressTypeClause = "";
        $presstoQuery = array();
        if ($args['press_type'] != null) {
            foreach (explode(",", $args['press_type']) as $pressType) {
                if (!in_array($pressType, PlayersMyGames::$validPress)) {
                    throw new RequestException('Press type `'.$pressType.'` is not a recognized press type.');
                }
                $presstoQuery[] = "'".$pressType."'";
            }
            $pressTypeList = implode(', ', $presstoQuery);
            $filterPressTypeClause = "AND g.pressType IN ($pressTypeList)";
        }

        // Variants IDs
        $filterVariantIdsClause = "";
        $variantsToQuery = array();
        if ($args['variant_id'] != null) {
            foreach (explode(",", $args['variant_id']) as $variant) {
                if (!is_numeric($variant)) {
                    throw new RequestException('Variant IDs are expected to be integers.');
                }
                $variantsToQuery[] = intval($variant);
            }
            $variantsList = implode(', ', $variantsToQuery);
            $filterPressTypeClause = "AND g.variantID IN ($variantsList)";
        }

        // Anonymous - 'Yes', 'No'
        $filterAnonymousClause = "";
        if ($args['anonymous'] != null) {
            if ($args['anonymous'] == "Yes") {
                $filterAnonymousClause = "AND g.anon = 'Yes'";
            } elseif ($args['anonymous'] == "No") {
                $filterAnonymousClause = "AND g.anon = 'No'";
            } else {
                throw new RequestException('Value for anonymous for must one of: "Yes", "No".');
            }
        }

        // Scoring - 'WTA', 'PPSC', 'Unranked', 'SOS'
        $filterScoringClause = "";
        $scoringToQuery = array();
        if ($args['scoring'] != null) {
            foreach (explode(",", $args['scoring']) as $scoring) {
                if (!array_key_exists($scoring, PlayersMyGames::$validPotType)) {
                    throw new RequestException('Scoring `'.$scoring.'` is not a recognized pot type.');
                }
                $scoringToQuery[] = "'".PlayersMyGames::$validPotType[$scoring]."'";
            }
            $scoringList = implode(', ', $presstoQuery);
            $filterScoringClause = "AND g.potType IN ($scoringList)";
        }

        // Player Type - 'Members', 'Mixed', 'MemberVsBots'
        $filterPlayerTypeClause = "";
        $playerTypeToQuery = array();
        if ($args['player_type'] != null) {
            foreach (explode(",", $args['player_type']) as $playerType) {
                if (!in_array($playerType, PlayersMyGames::$validPlayerType)) {
                    throw new RequestException('Player type `'.$playerType.'` is not a recognized player type.');
                }
                $playerTypeToQuery[] = "'".$playerType."'";
            }
            $playerTypeList = implode(', ', $playerTypeToQuery);
            $filterPlayerTypeClause = "AND g.playerTypes IN ($playerTypeList)";
        }

        // Finds powers (gameID, countryID) that are played by the user ID
        // and fit all the other restrictions
        $countryTabl = $DB->sql_tabl("SELECT DISTINCT m.gameID, m.countryID, MAX(msg.timeSent) as lastMsgReceived
                                      FROM wD_Members AS m
                                      LEFT JOIN wD_Games AS g ON (g.id = m.gameID)
                                      LEFT JOIN wD_GameMessages as msg ON (msg.gameID = m.gameID AND msg.toCountryID in (0, m.countryID))
                                      WHERE m.userID = $apiKey->userID
                                            " . $filterStatusClause ."
                                            " . $filterMissingOrdersClause ."
                                            " . $filterMessagesAfterClause ."
                                            " . $filterPressTypeClause ."
                                            " . $filterVariantIdsClause ."
                                            " . $filterAnonymousClause ."
                                            " . $filterScoringClause ."
                                            " . $filterPlayerTypeClause ."
                                      GROUP BY m.gameID, m.countryID;");

        // Building list of tuples, then serializing to JSON
        while( $row = $DB->tabl_hash($countryTabl) ) {
            array_push($this->value, new GameCountry($row['gameID'], $row['countryID']));
        }
        return json_encode($this->value);
    }
}
