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
require_once('gamepanel/gameboard.php');
require_once('gamemaster/game.php');
require_once('gamemaster/gamemaster.php');
require_once('board/member.php');

use API\ApiKey;
use API\ApiRoute;
use API\ClientForbiddenException;
use API\RequestException;
use Members;

/**
 * Route: "game/vote"
 * Args: gameID, countryID, vote, value
 * Type: JSON request (POST with JSON as body)
 *
 * This route casts (or cancels) a vote on the server
 *
 *      gameID: The gameID where the vote is happening
 *      countryID: The countryID who is casting a vote
 *      vote: One of "Draw", "Pause", "Cancel", "Concede"
 *      value: One of "Yes", "No"
 *
 * Example request:
 *      BASE_URL/api.php?route=game/vote
 *
 * POST Data:
 *     {"gameID":123,
 *      "countryID":1,
 *      "vote": "Draw",
 *      "value": "Yes"}
 *
 * This route returns whether the status of the draw on the server after the operation.
 *
 * Example response:
 *      {"vote": "Draw", "value": "Yes"}
 */
class GameVote extends ApiRoute {

    /**
     * The requested gameID
     * @var int
     */
    public $gameID;

    /**
     * The countryID casting the vote
     * @var int
     */
    public $countryID;

    /**
     * The vote being cast ("Draw", "Pause", "Cancel", "Concede")
     * @var string
     */
    public $vote;

    /**
     * The vote value ("Yes", "No")
     * @var string
     */
    public $value;

    /**
     * The game where the vote is applied
     * @var \Game
     */
    public $game;

    /**
     * The member voting
     * @var \Member
     */
    public $member;

    /**
     * GameVote constructor.
     * Method type: JSON
     * Requirements: gameID, countryID, vote, value
     *
     * @throws \Exception
     */
    public function __construct() {
        parent::__construct('game/vote', 'JSON', array('gameID', 'countryID', 'vote', 'value'));
    }

    /**
     * Authorizes Route. The user must be a member of the game to vote
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @throws RequestException
     * @throws ClientForbiddenException
     */
    protected function validateRoute($apiKey) {
        // Parsing args
        $args = $this->getArgs();
        if ($args['gameID'] === null || !is_numeric($args['gameID'])) { throw new RequestException('Invalid game ID: '.$args['gameID']); }
        if ($args['countryID'] === null || !is_numeric($args['countryID'])) { throw new RequestException('Invalid country ID: '.$args['countryID']); }
        if ($args['vote'] && (!is_string($args['vote']) || !in_array($args['vote'], Members::$votes))) {
            throw new RequestException('Body field `vote` must be one of: ' . implode(', ', Members::$votes));
        }
        if ($args['value'] && (!is_string($args['value']) || !in_array($args['value'], array('Yes', 'No')))) {
            throw new RequestException('Body field `value` is not either `Yes` or `No`.');
        }

        // Setting values
        $this->gameID = intval($args['gameID']);
        $this->countryID = intval($args['countryID']);
        $this->vote = strval($args['vote']);
        $this->value = strval($args['value']);
        $this->game = $this->getAssociatedGame($this->gameID);

        // Finding member
        if (!isset($this->game->Members->ByCountryID[$this->countryID])) {
            throw new RequestException('The countryID specified is not a member of the game.');
        }
        $this->member = $this->game->Members->ByCountryID[$this->countryID];

        // You can only vote if you are playing the member you requested
        if ($this->member->userID != $apiKey->userID) {
            throw new ClientForbiddenException('You can only vote for the powers you control in a game.');
        }

        // Votes are not allowed during pre-game or when game is completed
        if ($this->game->phase == 'Pre-game' || $this->game->phase == 'Finished') {
            throw new ClientForbiddenException('Votes are not allowed before the game has started, or after it has ended.');
        }

        // Member must be playing to be able to vote
        if ($this->member->status != 'Playing') {
            throw new ClientForbiddenException('Only "Playing" members can vote.');
        }
    }

    /**
     * Processes Route. Updates the vote
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @return string - The JSON value to return.
     * @throws \Exception
     */
    protected function runRoute($apiKey) {
        global $DB;
        $vote_value = in_array($this->vote, $this->member->votes) ? "Yes" : "No";

        // Setting the member status as Active
        $this->markMemberAsActive($apiKey, $this->game, $this->member);

        // Loading panel game
        $Variant = \libVariant::loadFromGameID($this->gameID);
        \libVariant::setGlobals($Variant);
        $panelGame = $Variant->panelGameBoard($this->gameID);

        // Loading user member
        $panelGame->Members->makeUserMember($this->member->userID);
        $userMember = $panelGame->Members->ByUserID[$this->member->userID];

        // Toggling vote if needed
        if ($vote_value != $this->value) {
            $userMember->toggleVote($this->vote);
            $vote_value = in_array($this->vote, $userMember->votes) ? "Yes" : "No";
            $DB->sql_put("COMMIT");
        }

        // Applying votes
        if($panelGame->Members->votesPassed() && $panelGame->phase != 'Finished') {
            $DB->sql_put("UPDATE wD_Games SET attempts=attempts+1 WHERE id=".$this->gameID);
            $DB->sql_put("COMMIT");

            $game = $panelGame->Variant->processGame($this->gameID);
            try {
                $game->applyVotes();
                $DB->sql_put("UPDATE wD_Games SET attempts=0 WHERE id=".$this->gameID);
                $DB->sql_put("COMMIT");
            } catch(\Exception $error) {
                if($error->getMessage() == "Abandoned" || $error->getMessage() == "Cancelled") {
                    assert('$game->phase == "Pre-game" || $error->getMessage() == "Cancelled"');
                    $DB->sql_put("COMMIT");
                }
                else { $DB->sql_put("ROLLBACK"); }
                throw $error;
            }
        }

        // Returning
        return json_encode(array("vote" => $this->vote, "value" => $vote_value));
    }
}
