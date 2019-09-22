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

require_once('api/api_key.php');
require_once('api/api_utils.php');
require_once('api/exceptions.php');
require_once('objects/game.php');
require_once('objects/misc.php');

/**
 * Abstract class that represents an API Route.
 */
abstract class ApiRoute {
    /**
     * API route name.
     * @var string
     */
    public $route;

    /**
     * API route type: either 'GET', 'POST' or 'JSON'.
     * If 'JSON', then entry data should be a JSON-encoded string in raw HTTP body (retrievable from 'php://input').
     * @var string
     */
    public $type;

    /**
     * Array of parameters names expected for this API entry.
     * @var array
     */
    protected $requirements;

    /**
     * Initialize an ApiRoute.
     * @param string $route - API entry name.
     * @param string $type - API entry type ('GET' or 'POST').
     * @param array $requirements - array of API entry parameters names.
     * @throws \Exception - if invalid type or if requirements is not an array.
     */
    public function __construct($route, $type, $requirements) {
        if (!in_array($type, array('GET', 'POST', 'JSON'))) { throw new ServerInternalException('Invalid API entry type'); }
        if (!is_array($requirements)) { throw new ServerInternalException('API entry field names must be an array.'); }
        $this->route = cleanRoute($route);
        $this->type = $type;
        $this->requirements = $requirements;
    }

    /**
     * Return an array of actual API parameters values, retrieved from $_GET or $_POST, depending on API entry type.
     * @return array
     * @throws RequestException
     */
    public function getArgs() {
        $rawArgs = array();

        // Getting GET, POST, JSON data
        if ($this->type == 'GET') {
            $rawArgs = $_GET;
        } else if ($this->type == 'POST') {
            $rawArgs = $_POST;
        } else if ($this->type == 'JSON') {
            $rawArgs = json_decode(file_get_contents("php://input"), true);
            if (!$rawArgs) { throw new RequestException('Invalid JSON request data.'); }
        }

        // Only returning the fields in requirements (null if not present)
        $selectedArgs = array();
        foreach ($this->requirements as $fieldName) {
            $selectedArgs[$fieldName] = isset($rawArgs[$fieldName]) ? $rawArgs[$fieldName] : null;
        }

        return $selectedArgs;
    }

    /**
     * Return Game object for game associated to this API entry call.
     * To get associated game, API entry must expect a parameter named `gameID`.
     * @param int $gameID - The game ID to load
     * @return \Game
     * @throws RequestException - if no gameID field in requirements, or if no valid game ID provided.
     */
    public function getAssociatedGame($gameID) {
        global $DB;
        $Variant = \libVariant::loadFromGameID($gameID);
        \libVariant::setGlobals($Variant);
        $gameRow = $DB->sql_hash('SELECT * from wD_Games WHERE id = '.$gameID);
        if (!$gameRow) { throw new RequestException('Invalid game ID'); }
        return new \Game($gameRow);
    }

    /**
     * Marks a member as active (to avoid putting the member in civil disorder)
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @param \Game $game - The game where the member is playing
     * @param \Member $member - The actual member to mark as active
     */
    protected function markMemberAsActive($apiKey, $game, $member) {
        global $DB;
        $DB->sql_put("UPDATE wD_Members
                      SET userID = ".$apiKey->userID.",
                          status='Playing',
                          missedPhases = 0,
                          timeLoggedIn = ".time()." 
                      WHERE id = ".$member->id);
        unset($game->Members->ByUserID[$member->userID]);
        unset($game->Members->ByStatus['Playing'][$member->id]);
        $member->status='Playing';
        $member->missedPhases=0;
        $member->timeLoggedIn=time();
        $game->Members->ByUserID[$this->member->userID] = $member;
        $game->Members->ByStatus['Playing'][$this->member->id] = $member;
        $DB->sql_put("COMMIT");
    }

    /**
     * Processing game if needed
     * @param \Game $game - The game object to process
     * @param bool $oldReady - Whether the ready flag was set before the orders were submitted
     * @param bool $newReady - Whether the ready flag is now set after orders were submitted
     */
    protected function processGame($game, $oldReady, $newReady) {
        // Not processing if this members orders are not ready
        // OR if they were already ready before we set the orders
        if (!$newReady || $oldReady) { return; }

        global $Misc, $DB;
        $Misc = new \Misc();

        // Trying one last time, then crashing game
        if($game->processStatus != 'Crashed' && $game->attempts > count($game->Members->ByID) * 2) {
            $DB->sql_put("COMMIT");
            $game = \libVariant::$Variant->processGame($game->id);
            $game->crashed();
            $DB->sql_put("COMMIT");

        // Trying to process game regularly
        } elseif($this->game->needsProcess()) {
            $DB->sql_put("UPDATE wD_Games SET attempts=attempts+1 WHERE id=".$game->id);
            $DB->sql_put("COMMIT");
            $game = \libVariant::$Variant->processGame($game->id);
            if($game->needsProcess()) {
                $game->process();
                $DB->sql_put("UPDATE wD_Games SET attempts=0 WHERE id=".$game->id);
                $DB->sql_put("COMMIT");
            }
        }
    }

    /**
     * Process API call.
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @return string - The JSON value to return.
     */
    public function run($apiKey) {
        $this->validateRoute($apiKey);
        return $this->runRoute($apiKey);
    }

    /**
     * Authorize API call. To override in derived classes.
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     */
    abstract protected function validateRoute($apiKey);

    /**
     * Process API call. To override in derived classes.
     * @param ApiKey $apiKey - The API Key of the user requesting the route
     * @return string - The JSON value to return.
     */
    abstract protected function runRoute($apiKey);
}
