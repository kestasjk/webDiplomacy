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
require_once('lib/gamemessage.php');
require_once('objects/user.php');

use API\ApiKey;
use API\ApiRoute;
use API\ClientForbiddenException;
use API\RequestException;
use libGameMessage;

/**
 * Route: "game/message"
 * Args: gameID, fromCountryID, toCountryID, message
 * Type: JSON request (POST with JSON as body)
 *
 * This route sends a game message to another power
 *
 *      gameID: The gameID where the message is sent
 *      fromCountryID: The sender countryID
 *      toCountryID: The destination countryID
 *      message: The message to send to another country
 *
 * Example request:
 *      BASE_URL/api.php?route=game/message
 *
 * POST Data:
 *     {"gameID":123,
 *      "fromCountryID": 2,
 *      "toCountryID":1,
 *      "message": "This is my message.<br/>Thanks!"}
 *
 * This route returns whether the message was sent successfully or not, with a reason for the failure otherwise.
 *
 * Example response:
 *      {"success": "Yes", "reason": ""}
 */
class GameMessage extends ApiRoute {

    /**
     * The requested gameID
     * @var int
     */
    public $gameID;

    /**
     * The countryID sending the message
     * @var int
     */
    public $fromCountryID;

    /**
     * The countryID receiving the message
     * @var int
     */
    public $toCountryID;

    /**
     * The message to send
     * @var string
     */
    public $message;

    /**
     * The requested game
     * @var \Game
     */
    public $game;

    /**
     * The member sending the message
     * @var \Member
     */
    public $sender;

    /**
     * The member receiving the message
     * @var \Member
     */
    public $recipient;

    /**
     * GameMessage constructor.
     * Method type: JSON
     * Requirements: gameID, fromCountryID, toCountryID, message
     *
     * @throws \Exception
     */
    public function __construct() {
        parent::__construct('game/message', 'JSON', array('gameID', 'fromCountryID', 'toCountryID', 'message'));
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
        if ($args['fromCountryID'] === null || !is_numeric($args['fromCountryID'])) { throw new RequestException('Invalid sender country ID: '.$args['fromCountryID']); }
        if ($args['toCountryID'] === null || !is_numeric($args['toCountryID'])) { throw new RequestException('Invalid destination country ID: '.$args['toCountryID']); }
        if ($args['message'] === null) { throw new RequestException('Message is required.'); }

        // Setting values
        $this->gameID = intval($args['gameID']);
        $this->fromCountryID = intval($args['fromCountryID']);
        $this->toCountryID = intval($args['toCountryID']);
        $this->message = strval($args['message']);
        $this->game = $this->getAssociatedGame($this->gameID);

        // Finding sender
        if (!isset($this->game->Members->ByCountryID[$this->fromCountryID])) {
            throw new RequestException('The sender must be a member of the game.');
        }
        $this->sender = $this->game->Members->ByCountryID[$this->fromCountryID];
        if ($this->sender->userID != $apiKey->userID) {
            throw new ClientForbiddenException("You can only send messages for a country you control.");
        }

        // Finding recipient
        $this->recipient = null;
        if ($this->toCountryID != 0) {
            if (!isset($this->game->Members->ByCountryID[$this->toCountryID])) {
                throw new RequestException('Trying to send to an invalid country ID: ' . $this->toCountryID);
            }
            $this->recipient = $this->game->Members->ByCountryID[$this->toCountryID];
        }
    }

    /**
     * Processes Route. Sends the game message
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @return string - The JSON value to return.
     * @throws \Exception
     */
    protected function runRoute($apiKey) {
        global $DB;

        // Checking if we can send the message
        // We can always send messages during Regular press
        if ($this->game->pressType == 'Regular') {
            $allowMessage = true;
            $reason = "";

        // We can always send messages to ourself
        } elseif ($this->sender->countryID == $this->toCountryID) {
            $allowMessage = true;
            $reason = "";

        // We can only send messages during Diplomacy/Finished phase if RulebookPress is set
        } elseif ($this->game->pressType == 'RulebookPress') {
            if ($this->game->phase == 'Diplomacy' || $this->game->phase == 'Finished') {
                $allowMessage = true;
                $reason = "";
            } else {
                $allowMessage = false;
                $reason = "You can only send messages during Diplomacy and Finished phase for RulebookPress games";
            }

        // We can send global messages during public press
        } elseif ($this->game->pressType == 'PublicPressOnly') {
            if ($this->toCountryID == 0) {
                $allowMessage = true;
                $reason = "";
            } else {
                $allowMessage = false;
                $reason = "You can only send global messages during PublicPressOnly games.";
            }

        // We can send global messages only at the end of NoPress games
        } elseif ($this->game->pressType == 'NoPress') {
            if ($this->toCountryID == 0 && $this->game->phase == 'Finished') {
                $allowMessage = true;
                $reason = "";
            } else {
                $allowMessage = false;
                $reason = "Messages are disabled during NoPress games.";
            }

        // Unknown press type
        } else {
            $allowMessage = false;
            $reason = "The game press type is not recognized.";
        }

        // Detecting if we are sending to a muted player
        if ($allowMessage && $this->recipient != null) {
            $recipient_user = new \User($this->recipient->userID);
            if ($recipient_user->isCountryMuted($this->game, $this->sender->countryID)) {
               $allowMessage = false;
               $reason = "The recipient country has muted you.";
            }
        }

        // Sending the message
        if ($allowMessage) {
            $this->markMemberAsActive($apiKey, $this->game, $this->sender);
            libGameMessage::send($this->toCountryID, $this->sender->countryID, $this->message, $this->gameID);
            $DB->sql_put("COMMIT");
        }

        // Returning
        return json_encode(array("success" => $allowMessage ? "Yes" : "No", "reason" => $reason));
    }
}
