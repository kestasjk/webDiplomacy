<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas / Timothy Jones

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

define('IN_CODE', 1);
require_once('header.php');
require_once('config.php');
require_once('global/definitions.php');
require_once('locales/layer.php');
require_once('objects/database.php');
require_once('objects/memcached.php');
require_once('board/orders/orderinterface.php');
require_once('api/responses/members_in_cd.php');
require_once('api/responses/unordered_countries.php');
require_once('api/responses/active_games.php');
require_once('api/responses/game_state.php');
require_once('objects/game.php');
require_once('objects/user.php');
require_once('lib/cache.php');
require_once('lib/html.php');
require_once('lib/time.php');
require_once('lib/gamemessage.php');
require_once('board/orders/jsonBoardData.php');
require_once('variants/install.php');
$DB = new Database();

/**
 * Exception class - missing credentials (API key).
 */
class ClientUnauthorizedException extends Exception {
	public function __construct($message) {
		parent::__construct($message);
	}
}

/**
 * Exception class - access denied for request sender.
 */
class ClientForbiddenException extends Exception {
	public function __construct($message) {
		parent::__construct($message);
	}
}

/**
 * Exception class - server internal error.
 */
class ServerInternalException extends Exception {
	public function __construct($message) {
		parent::__construct($message);
	}
}

/**
 * Exception class - request is not implemented.
 */
class NotImplementedException extends Exception {
	public function __construct($message) {
		parent::__construct($message);
	}
}

/**
 * Exception class - bad request.
 */
class RequestException extends Exception {
	public function __construct($message) {
		parent::__construct($message);
	}
}

/**
 * Handles an error (user or server) in an API request.
 * @param string $message - Error message.
 * @param int $errorCode - HTTP error code for this error.
 */
function handleAPIError($message, $errorCode) {
	header('Content-Type: text/plain');
	http_response_code($errorCode);
	print $message;
}

/**
 * Get header Authorization
 * Reference: https://stackoverflow.com/a/40582472
 * */
function getAuthorizationHeader() {
	$headers = null;
	if (isset($_SERVER['Authorization'])) {
		$headers = trim($_SERVER["Authorization"]);
	}
	else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
		$headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
	} elseif (function_exists('apache_request_headers')) {
		$rawRequestHeaders = apache_request_headers();
		// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
		$requestHeaders = array();
		foreach ($rawRequestHeaders as $key => $value)
			$requestHeaders[ucwords($key)] = $value;
		if (isset($requestHeaders['Authorization'])) {
			$headers = trim($requestHeaders['Authorization']);
		}
	}
	return $headers;
}

/**
 * get access token from header
 * Reference: https://stackoverflow.com/a/40582472
 * */
function getBearerToken() {
	$headers = getAuthorizationHeader();
	// HEADER: Get the access token from the header
	if (!empty($headers)) {
		if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
			return $matches[1];
		}
	}
	return null;
}

/**
 * Return a proper version of API entry route string.
 * */
function cleanRoute($route) {
	return strtolower(trim($route, " /\t\n\r\0\x0B"));
}

/**
 * Class to manage an API entry.
 */
abstract class ApiEntry {
	/**
	 * API entry name.
	 * @var string
	 */
	private $route;

	/**
	 * API entry type: either 'GET', 'POST' or 'JSON'.
	 * If 'JSON', then entry data should be a JSON-encoded string in raw HTTP body (retrievable from 'php://input').
	 * @var string
	 */
	private $type;

	/**
	 * Permission field name to check in database for this API entry.
	 * @var string
	 */
	private $databasePermissionField;

	/**
	 * Array of parameters names expected for this API entry.
	 * @var array
	 */
	protected $requirements;

	/**
	 * Initialize an ApiEntry.
	 * @param string $route - API entry name.
	 * @param string $type - API entry type ('GET' or 'POST').
	 * @param string $databasePermissionField - name of corresponding permission field in database table `wD_ApiPermissions`.
	 * @param array $requirements - array of API entry parameters names.
	 * @throws Exception - if invalid type or if requirements is not an array.
	 */
	public function __construct($route, $type, $databasePermissionField, $requirements) {
		if (!in_array($type, array('GET', 'POST', 'JSON')))
			throw new ServerInternalException('Invalid API entry type');
		if (!is_array($requirements))
			throw new ServerInternalException('API entry field names must be an array.');
		$this->route = cleanRoute($route);
		$this->type = $type;
		$this->databasePermissionField = $databasePermissionField;
		$this->requirements = $requirements;
	}

	protected function JSONResponse(string $msg, string $referenceCode, bool $success, array $data = [], $JSON_NUMERIC_CHECK = false){
		return json_encode([
			'msg' => $msg,
			'success' => $success,
			'referenceCode' =>$referenceCode,
			'data' => $data,
		], $JSON_NUMERIC_CHECK ? JSON_NUMERIC_CHECK : 0);
	}

	/**
	 * Return API entry name.
	 * @return string
	 */
	public function getRoute() {
		return $this->route;
	}

	/**
	 * Return API entry permission field name.
	 * @return string
	 */
	public function getPermissionField() {
		return $this->databasePermissionField;
	}

	/**
	 * Return an array of actual API parameters values, retrieved from $_GET or $_POST, depending on API entry type.
	 * @return array
	 * @throws RequestException
	 */
	public function getArgs() {
		$rawArgs = array();
		if ($this->type == 'GET')
			$rawArgs = $_GET;
		else if ($this->type == 'POST')
			$rawArgs = $_POST;
		else if ($this->type == 'JSON') {
			$rawArgs = json_decode(file_get_contents("php://input"), true);
			if (!$rawArgs)
				throw new RequestException('Invalid JSON request data.');
		}
		$selectedArgs = array();
		foreach ($this->requirements as $fieldName) {
			$selectedArgs[$fieldName] = isset($rawArgs[$fieldName]) ? $rawArgs[$fieldName] : null;
		}
		return $selectedArgs;
	}

	/**
	 * Return true if this API entry requires a parameter called `gameID`.
	 */
	public function requiresGameID() {
		return in_array('gameID', $this->requirements);
	}

	public function getAssociatedGameId() {
		if (!in_array('gameID', $this->requirements))
			throw new RequestException('No game ID available for this request.');
		$args = $this->getArgs();
		$gameID = $args['gameID'];
		if ($gameID == null)
			throw new RequestException('Game ID not provided.');
		return intval($gameID);
	}
	
	private $gameCache = null;
	/**
	 * Return Game object for game associated to this API entry call.
	 * To get associated game, API entry must expect a parameter named `gameID`.
	 * @return Game
	 * @throws RequestException - if no gameID field in requirements, or if no valid game ID provided.
	 */
	public function getAssociatedGame($lockForUpdate = false) {
		global $DB;
		if( !is_null($this->gameCache) ) return $this->gameCache;
		$gameID = $this->getAssociatedGameId();
		$Variant = libVariant::loadFromGameID($gameID);
		libVariant::setGlobals($Variant);
		$this->gameCache = new Game($gameID, $lockForUpdate ? UPDATE : NOLOCK);
		return  $this->gameCache; // Lock game for update, which just ensures the game is always processed sequentially, if the game will be updated
	}

	/**
	 * Process API call. To override in derived classes.
	 * @param int $userID - ID of user who makes API call.
	 * @param bool $permissionIsExplicit - boolean to indicate if permission flag was set for API caller key.
	 */
	abstract public function run($userID, $permissionIsExplicit);
}

/**
 * API entry players/cd
 */
class ListGamesWithPlayersInCD extends ApiEntry {
	public function __construct() {
		parent::__construct('players/cd', 'GET', 'listGamesWithPlayersInCD', array());
	}
	public function run($userID, $permissionIsExplicit) {
		$countriesInCivilDisorder = new \webdiplomacy_api\CountriesInCivilDisorder();
		return $countriesInCivilDisorder->toJson();
	}
}

/**
 * API entry players/missing_orders
 */
class ListGamesWithMissingOrders extends ApiEntry {
	public function __construct() {
		parent::__construct('players/missing_orders', 'GET', '', array());
	}
	public function run($userID, $permissionIsExplicit) {
		$unorderedCountries = new \webdiplomacy_api\UnorderedCountries($userID);

		return $unorderedCountries->toJson();
	}
}

/**
 * API entry players/active_games
 */
class ListActiveGamesForUser extends ApiEntry {
	public function __construct() {
		parent::__construct('players/active_games', 'GET', '', array());
	}
	public function run($userID, $permissionIsExplicit) {
		$activeGames = new \webdiplomacy_api\ActiveGames($userID);
		return $activeGames->toJson();
	}
}

/**
 * API entry game/togglevote
 */
class ToggleVote extends ApiEntry {
	public function __construct() {
		parent::__construct('game/togglevote', 'GET', '', array('gameID','countryID','vote'));
	}
	public function run($userID, $permissionIsExplicit) {
		global $DB;

		$args = $this->getArgs();
		$gameID = intval($args['gameID']);
		$countryID = intval($args['countryID']);
		$vote = $args['vote'];
		if (!in_array($vote, ['Draw', 'Pause', 'Cancel', 'Concede']))
		    throw new RequestException('Invalid vote type; allowed are Draw, Concede, Pause, Cancel');
		if (!empty(Config::$apiConfig['restrictToGameIDs']) && !in_array($gameID, Config::$apiConfig['restrictToGameIDs']))
			throw new ClientForbiddenException('Game ID is not in list of gameIDs where API usage is permitted.');

		$game = 
		$currentVotes = $DB->sql_hash("SELECT votes FROM wD_Members WHERE gameID = ".$gameID." AND countryID = ".$countryID." AND userID = ".$userID);
		$currentVotes = $currentVotes['votes'];

		// Keep a log that a vote was set in the game messages, so the vote time is recorded
		require_once(l_r('lib/gamemessage.php'));
		$voteOn = in_array($vote, explode(',',$currentVotes));
		libGameMessage::send($countryID, $countryID, ($voteOn?'Un-':'').'Voted for '.$vote, $gameID);

		$newVotes = '';
		if( strpos($currentVotes, $vote) !== false )
		{
			// The vote is currently set, so unset it:
			$voteArr = explode(',',$currentVotes);
			$newVoteArr = array();
			for($i=0; $i< count($voteArr); $i++)
				if( $voteArr[$i] != $vote )
					$newVoteArr[] = $voteArr[$i];
			$newVotes = implode(',', $newVoteArr);
		}
		else
		{
			if( strpos($currentVotes,',') !== false )
				$voteArr = explode(',',$currentVotes);
			else
				$voteArr = array($currentVotes);
			$voteArr[] = $vote;
			$newVotes = implode(',', $voteArr);
		}
		$DB->sql_put("UPDATE wD_Members SET votes = '".$newVotes."' WHERE gameID = ".$gameID." AND userID = ".$userID." AND countryID = ".$countryID);
		$DB->sql_put("COMMIT");
		return $newVotes;
	}
}

// FIXME - a bit copypasta with the above API call togglevote.
// togglevote also uses GET rather than POST, but GET is not supposed to be used
// for state-modifying web queries. So probably togglevote should be deprecated.
/**
 * API entry game/setvote
 */
class SetVote extends ApiEntry {
	public function __construct() {
		parent::__construct('game/setvote', 'JSON', '', array('gameID','countryID','vote','voteOn'));
	}
	public function run($userID, $permissionIsExplicit) {
		global $DB;

		$args = $this->getArgs();
		$gameID = intval($args['gameID']);
		$countryID = intval($args['countryID']);
		$vote = $args['vote'];
		$voteOn = filter_var($args['voteOn'], FILTER_VALIDATE_BOOLEAN);
		if (!in_array($vote, ['Draw', 'Pause', 'Cancel', 'Concede']))
		    throw new RequestException('Invalid vote type; allowed are Draw, Concede, Pause, Cancel');

		if (!empty(Config::$apiConfig['restrictToGameIDs']) && !in_array($gameID, Config::$apiConfig['restrictToGameIDs']))
			throw new ClientForbiddenException('Game ID is not in list of gameIDs where API usage is permitted.');

		$game = 
		$currentVotes = $DB->sql_hash("SELECT votes FROM wD_Members WHERE gameID = ".$gameID." AND countryID = ".$countryID." AND userID = ".$userID);
		$currentVotes = $currentVotes['votes'];

		if( $voteOn === in_array($vote, explode(',',$currentVotes)) )
		{
			return $currentVotes;
		}
		// Keep a log that a vote was set in the game messages, so the vote time is recorded
		require_once(l_r('lib/gamemessage.php'));
		libGameMessage::send($countryID, $countryID, ($voteOn?'Un-':'').'Voted for '.$vote, $gameID);

		$newVotes = '';
		if( strpos($currentVotes, $vote) !== false )
		{
			// The vote is currently set, so unset it:
			$voteArr = explode(',',$currentVotes);
			$newVoteArr = array();
			for($i=0; $i< count($voteArr); $i++)
				if( $voteArr[$i] != $vote )
					$newVoteArr[] = $voteArr[$i];
			$newVotes = implode(',', $newVoteArr);
		}
		else
		{
			if( strpos($currentVotes,',') !== false )
				$voteArr = explode(',',$currentVotes);
			else
				$voteArr = array($currentVotes);
			$voteArr[] = $vote;
			$newVotes = implode(',', $voteArr);
		}
		$DB->sql_put("UPDATE wD_Members SET votes = '".$newVotes."' WHERE gameID = ".$gameID." AND userID = ".$userID." AND countryID = ".$countryID);
		$DB->sql_put("COMMIT");
		return $newVotes;
	}
}

/**
 * API entry game/messagesseen
 */
class MessagesSeen extends ApiEntry {
	public function __construct() {
		// lol why is this a GET
		parent::__construct('game/messagesseen', 'GET', '', array('gameID','countryID','seenCountryID'));
	}
	public function run($userID, $permissionIsExplicit) {
		global $Game, $DB;
		$args = $this->getArgs();
		$countryID = intval($args['countryID']);
		$seenCountryID = intval($args['seenCountryID']);
		$Game = $this->getAssociatedGame();
		$member = $Game->Members->ByUserID[$userID];
		$newMessagesFrom = $member->newMessagesFrom;

		foreach($newMessagesFrom as $i => $curCountryID)
		{
			if ( $curCountryID == $seenCountryID )
			{
				unset($newMessagesFrom[$i]);
				break;
			}
		}
		$DB->sql_put("UPDATE wD_Members
						SET newMessagesFrom = '".implode(',',$newMessagesFrom)."'
						WHERE id = ".$member->id);
		$DB->sql_put("COMMIT");
	}
}

/**
 * API entry game/status
 */
class GetGamesStates extends ApiEntry {
	public function __construct() {
		parent::__construct('game/status', 'GET', 'getStateOfAllGames', array('gameID', 'countryID'));
	}
	/**
	 * @throws RequestException
	 */
	public function run($userID, $permissionIsExplicit) {
		$args = $this->getArgs();
		$gameID = $args['gameID'];
		$countryID = $args['countryID'] ?? null;
		if ($gameID === null || !ctype_digit($gameID))
			throw new RequestException('Invalid game ID: '.$gameID);
		if (!empty(Config::$apiConfig['restrictToGameIDs']) && !in_array($gameID, Config::$apiConfig['restrictToGameIDs']))
		    throw new ClientForbiddenException('Game ID is not in list of gameIDs where API usage is permitted.');
		$game = $this->getAssociatedGame();
		if ($countryID != null && (!isset($game->Members->ByUserID[$userID]) || $countryID != $game->Members->ByUserID[$userID]->countryID))
			throw new ClientForbiddenException('A user can only view game state for the country it controls.');
		$gameState = new \webdiplomacy_api\GameState(intval($gameID), $countryID ? intval($countryID) : null);
		return $gameState->toJson();
	}
}

/**
 * API entry game/members
 * Retrieves member data related to a game. 
 */
class GetGameMembers extends ApiEntry {
	private $isAnon;

	private $showDrawVotes;

	public function __construct() {
		parent::__construct('game/members', 'GET', 'getStateOfAllGames', array('gameID'));
	}

	private function getMembers( $members ){
		return array_map( function( $member ){
			return $this->getMemberData($member);
		}, $members->ByOrder );
	}

	private function getMemberData(Member $member, bool $retrievePrivateData = false){
		$votes = $member->votes;
		if(!$this->showDrawVotes && !$retrievePrivateData){
			$drawKey = array_search('Draw', $votes);
			if($drawKey !== false){
				unset($votes[$drawKey]);
				$votes = array_values($votes);
			}
		}
		return [
			'bet' => $member->bet,
			'country' => $member->country,
			'countryID' => $member->countryID,
			'excusedMissedTurns' => $member->excusedMissedTurns,
			'missedPhases' => $member->missedPhases,
			'newMessagesFrom' => $retrievePrivateData ? $member->newMessagesFrom : [],
			'online' => $member->online,
			'orderStatus' => ($this->isAnon && !$retrievePrivateData ? ['Hidden' => 1] : [
				'Ready' => $member->orderStatus->Ready,
				'Saved' => $member->orderStatus->Saved,
				'Completed' => $member->orderStatus->Completed,
				'None' => $member->orderStatus->None,
			]),
			'pointsWon' => $member->pointsWon,
			'status' => $member->status,
			'supplyCenterNo' => $member->supplyCenterNo,
			'timeLoggedIn' => $member->timeLoggedIn,
			'unitNo' => $member->unitNo,
			'userID' => $member->userID,
			'username' => $this->isAnon && !$retrievePrivateData ? '' : $member->username,
			'votes' => $votes,
		];
	}

	public function getData($userID){
		$args = $this->getArgs();
		$gameID = $args['gameID'];
		if ($gameID === null || !ctype_digit($gameID)){
			throw new RequestException(
				$this->JSONResponse(
					'Invalid game ID.', 
					'ggm-err-001', 
					false,
					['gameID' => $gameID]
				)
			);
		}
		$game = $this->getAssociatedGame();
		$this->isAnon = $game->anon === 'Yes' ? true : false;
		$this->showDrawVotes = $game->drawType === 'draw-votes-public' ? true : false;
		$memberData = [
			'members' => $this->getMembers( $game->Members ),
		];
		if (isset($game->Members->ByUserID[$userID])) {
			$memberData['user'] = [
				'member' => $this->getMemberData($game->Members->ByUserID[$userID], true),
			];
		}
		return $memberData;
	}

	/**
	 * @throws RequestException
	 */
	public function run($userID, $permissionIsExplicit) {
		return $this->JSONResponse('Successfully retrieved game members.', 'ggm-s-001', true, $this->getData($userID));
	}
}

/**
 * API entry game/overview
 * 
 * This should be cleaned up. 
 */
class GetGameOverview extends ApiEntry {
	public function __construct() {
		parent::__construct('game/overview', 'GET', 'getStateOfAllGames', array('gameID'));
	}

	private function getMembers( $members ){
		return array_map( function( $member ){
			return $this->getMemberData($member);
		}, $members->ByOrder );
	}

	private function getMemberData($member){
		return [
			'bet' => $member->bet,
			'country' => $member->country,
			'countryID' => $member->countryID,
			'excusedMissedTurns' => $member->excusedMissedTurns,
			'id' => $member->id,
			'missedPhases' => $member->missedPhases,
			'newMessagesFrom' => $member->newMessagesFrom,
			'online' => $member->online,
			'orderStatus' => $member->orderStatus,
			'pointsWon' => $member->pointsWon,
			'status' => $member->status,
			'supplyCenterNo' => $member->supplyCenterNo,
			'timeLoggedIn' => $member->timeLoggedIn,
			'unitNo' => $member->unitNo,
			'userID' => $member->userID,
			'username' => $member->username,
			'votes' => $member->votes,
		];
	}

	/**
	 * @throws RequestException
	 */
	public function run($userID, $permissionIsExplicit) {
		$args = $this->getArgs();
		$gameID = $args['gameID'];
		if ($gameID === null || !ctype_digit($gameID)){
			throw new RequestException(
				$this->JSONResponse(
					'Invalid game ID.', 
					'GGO-err-001', 
					false,
					['gameID' => $gameID]
				)
			);
		}
		if (!empty(Config::$apiConfig['restrictToGameIDs']) && !in_array($gameID, Config::$apiConfig['restrictToGameIDs'])){
			throw new ClientForbiddenException(
				$this->JSONResponse(
					'Game ID is not in list of gameIDs where API usage is permitted.', 
					'GGO-err-002', 
					false, 
					['gameID' => $gameID]
				)
			);
		}   
		$game = $this->getAssociatedGame();
		$dateTxt = $game->datetxt($game->turn);
		$split = explode(',', $dateTxt);
		$season = $split[0];
		$year = intval($split[1] ?? 1901);

		$payload = array_merge([
			'alternatives' => strip_tags(implode(', ',$game->getAlternatives())),
			'anon' => $game->anon,
			'drawType' => $game->drawType,
			'season' => $season,
			'year' => $year,
			'excusedMissedTurns' => $game->excusedMissedTurns,
			'gameID' => $gameID,
			'gameOver' => $game->gameOver,
			'minimumBet' => $game->minimumBet,
			'name' => $game->name,
			'pauseTimeRemaining' => $game->pauseTimeRemaining,
			'phase' => $game->phase,
			'phaseMinutes' => $game->phaseMinutes,
			'playerTypes' => $game->playerTypes,
			'pot' => $game->pot,
			'potType' => $game->potType,
			'processStatus' => $game->processStatus,
			'processTime' => $game->processTime,
			'pressType' => $game->pressType,
			'startTime' => $game->startTime,
			'season' => $season,
			'turn' => $game->turn,
			'variant' => $game->Variant,
			'variantID' => $game->variantID,
			'year' => $year,
		], (new GetGameMembers)->getData($userID));
		return $this->JSONResponse('Successfully retrieved game overview.', 'GGO-s-001', true, $payload, true);
	}
}

/**
 * API entry game/data
 * Retrieves API data needed for order generation code and game functionality. 
 */
class GetGameData extends ApiEntry {

	private $contextVars;

	public function __construct() {
		parent::__construct('game/data', 'GET', 'getStateOfAllGames', array('gameID', 'countryID'));
	}

	private function setContextVars( $game, $gameID, $userID, $countryID, $member ){
		$this->contextVars = (new OrderInterface(
			$gameID,
			$game->variantID,
			$userID,
			$member->id,
			$game->turn,
			$game->phase,
			$countryID,
			$member->orderStatus,
			null,
			false
		))->load()->getContextVars();
	}

	private function getContextVars(){
		return [
			'context' => json_decode($this->contextVars['context']),
			'contextKey' => $this->contextVars['contextKey'],
		];
	}

	private function getCurrentOrders(){
		return $this->contextVars['ordersData'];
	}

	private function getUnits($gameID){
        return jsonBoardData::getUnitsData($gameID);
    }

    private function getTerrStatus($gameID){
        return jsonBoardData::getTerrStatusData($gameID);
    }

	/**
	 * @throws RequestException
	 */
	public function run($userID, $permissionIsExplicit) {
		global $MC;
		$args = $this->getArgs();
		$gameID = $args['gameID'];
		$countryID = $args['countryID'] ?? null;
		if (empty($gameID) || !ctype_digit($gameID)){
			throw new RequestException(
				$this->JSONResponse(
					'Invalid game ID.', 
					'GGD-err-001', 
					false,
					['gameID' => $gameID]
				)
			);
		}
		if (!empty(Config::$apiConfig['restrictToGameIDs']) && !in_array($gameID, Config::$apiConfig['restrictToGameIDs'])){
			throw new ClientForbiddenException(
				$this->JSONResponse(
					'Game ID is not in list of gameIDs where API usage is permitted.', 
					'GGD-err-003', 
					false, 
					['gameID' => $gameID]
				)
			);
		}
		$game = $this->getAssociatedGame();
		$payload = [];

		if (!is_null($countryID)){
			if (empty($countryID) || !ctype_digit($countryID)){
				throw new RequestException(
					$this->JSONResponse(
						'Invalid country ID.', 
						'GGD-err-002', 
						false, 
						['countryID' => $countryID]
					)
				);
			}
			if (!isset($game->Members->ByUserID[$userID]) || $countryID != $game->Members->ByUserID[$userID]->countryID){
				throw new ClientForbiddenException(
					$this->JSONResponse(
						'A user can only view game state for the country it controls.', 
						'GGD-err-004', 
						false, 
						['gameID' => $gameID]
					)
				);
			}
			$member = $game->Members->ByCountryID[$countryID];
			$this->setContextVars($game, $gameID, $userID, $countryID, $member);
			$payload['contextVars'] = $this->getContextVars();
			$payload['currentOrders'] = $this->getCurrentOrders();
		}

		if($game->variantID && is_numeric($game->variantID)){
            $territoriesCacheKey = "territories_$game->variantID";
            $cachedTerritories = $MC->get($territoriesCacheKey);
            if($cachedTerritories){
                $payload['territories'] = $cachedTerritories;
            }else{
                $territories = InstallCache::terrJSONData($game->variantID);
                if(!empty($territories)){
                    $payload['territories'] = $territories;
                    $secondsInDay = 86400;
                    $MC->set($territoriesCacheKey, $territories, $secondsInDay);
                }
            }
        }

		$payload = array_merge(
            $payload,
            [
                'units' => $this->getUnits($gameID),
                'territoryStatuses' => $this->getTerrStatus($gameID),
                'turn' => $game->turn,
                'phase' => $game->phase,
            ],
        );

		return $this->JSONResponse('Successfully retrieved game data.', 'GGD-s-001', true, $payload);
	}
}

/**
 * API entry game/orders
 */
class SetOrders extends ApiEntry {
	public function __construct() {
		parent::__construct(
			'game/orders',
			'JSON',
			'submitOrdersForUserInCD',
			array('gameID', 'turn', 'phase', 'countryID', 'orders', 'ready'));
			// 'ready' is optional.
	}
	/**
	 * @throws Exception
	 * @throws RequestException
	 * @throws ClientForbiddenException
	 */
	public function run($userID, $permissionIsExplicit) {
		global $DB, $MC;
		$args = $this->getArgs();
		$gameID = $args['gameID'];	// checked in getAssociatedGame()
		$turn = $args['turn'];
		$phase = $args['phase'];
		$countryID = $args['countryID'];
		$orders = $args['orders'];
		$readyArg = $args['ready'];

		if ($turn === null)
			throw new RequestException('Turn is required.');
		if ($phase === null)
			throw new RequestException('Phase is required.');
		if ($countryID === null)
			throw new RequestException('Country is required.');
		if (!is_array($orders))
			throw new RequestException('Body field `orders` is not an array.');
		if ($readyArg && (!is_string($readyArg) || !in_array($readyArg, array('Yes', 'No'))))
			throw new RequestException('Body field `ready` is not either `Yes` or `No`.');
        if (!empty(Config::$apiConfig['restrictToGameIDs']) && !in_array($gameID, Config::$apiConfig['restrictToGameIDs']))
            throw new ClientForbiddenException('Game ID is not in list of gameIDs where API usage is permitted.');
		$turn = intval($turn);
		$phase = strval($phase);
		$countryID = intval($countryID);

		$game = $this->getAssociatedGame(true); // Get the game and lock it for update
		if (!in_array($game->phase, array('Diplomacy', 'Retreats', 'Builds')))
			throw new RequestException('Cannot submit orders in phase `'.$game->phase.'`.');
		if ($turn != $game->turn)
			throw new RequestException('Invalid turn, expected `'.$game->turn.'`, got `'.$turn.'`.');
		if ($phase != $game->phase)
			throw new RequestException('Invalid phase, expected `'.$game->phase.'`, got `'.$phase.'`.');
		if (!isset($game->Members->ByCountryID[$countryID]))
			throw new ClientForbiddenException('Unknown country ID `'.$countryID.'`.');
		$member = $game->Members->ByCountryID[$countryID];            /** @var Member $member */

		if (isset($game->Members->ByUserID[$userID]) && $countryID == $game->Members->ByUserID[$userID]->countryID) {
			// API caller is the game member controlling given country ID.
			// Setting the member status as Active
			$DB->sql_put("UPDATE wD_Members SET userID = ".$userID.", status='Playing', missedPhases = 0, timeLoggedIn = ".time()." WHERE id = ".$member->id);
			unset($game->Members->ByUserID[$member->userID]);
			unset($game->Members->ByStatus['Playing'][$member->id]);
			$member->status='Playing';
			$member->missedPhases=0;
			$member->timeLoggedIn=time();
			$game->Members->ByUserID[$member->userID] = $member;
			$game->Members->ByStatus['Playing'][$member->id] = $member;
		} else {
			// API caller is not a game member controlling given country ID,
			// API caller permission must be explicitly set.
			if (!$permissionIsExplicit)
				throw new ClientForbiddenException('User does not have explicit permission to make this API call.');
			// In this case, the ordered country must be in CD.
			if ($member->status != 'Left')
				throw new ClientForbiddenException(
					'A user not controlling a country can submit orders only for a country in CD.');
			// We must have enough time to set orders.
			$currentTime = time();
			if (($currentTime + 60) < $game->processTime) {
				throw new RequestException('Process time is not close enough (current time ' . $currentTime . ', process time ' . $game->processTime . ').');
			}
		}


		$territoryToOrder = array();
		$orderToTerritory = array();
		$updatedOrders = array();
		$sql = 'SELECT wD_Orders.id AS orderID, wD_Units.terrID AS terrID FROM wD_Orders
				LEFT JOIN wD_Units ON (wD_Orders.gameID = wD_Units.gameID AND wD_Orders.countryID = wD_Units.countryID AND wD_Orders.unitID = wD_Units.id) 
				WHERE wD_Orders.gameID = '.$gameID.' AND wD_Orders.countryID = '.$countryID;
		$res = $DB->sql_tabl($sql);
		while ($row = $DB->tabl_hash($res)) {
			$orderID = $row['orderID'];
			$terrID = $row['terrID'];
			$orderToTerritory[$orderID] = $terrID;
			// Order may not be associated to a territory ID in Builds phase.
			if ($terrID !== null)
				$territoryToOrder[$terrID] = $orderID;
		}
		$waitIsSubmitted = false;
		foreach ($orders as $order) {
			$newOrder = array();
			foreach (array('terrID', 'type', 'fromTerrID', 'toTerrID', 'viaConvoy') as $bodyField) {
				if (!array_key_exists($bodyField, $order))
					throw new RequestException('Missing order info: ' . $bodyField);
				$newOrder[$bodyField] = $order[$bodyField];
			}
            if (array_key_exists('convoyPath', $order)) {
                $newOrder['convoyPath'] = $order['convoyPath'];
            }
			if (array_key_exists($order['terrID'], $territoryToOrder)) {
				// There is an order associated to this territory. Get this order ID.
				$newOrder['id'] = $territoryToOrder[$order['terrID']];
			} else {
				// No order yet associated to this territory.
				// Check if there a free (non-associated) orders.
				// If so, use first free order found.
				// Otherwise, raise an exception.
				$freeOrderID = null;
				foreach ($orderToTerritory as $orderID => $territoryID) {
					if ($territoryID === null) {
						$freeOrderID = $orderID;
						break;
					}
				}
				// If no free orders, raise an exception.
				if ($freeOrderID === null)
					throw new RequestException('Unknown territory ID `'.$order['terrID'].'` for country `'.$countryID.'`.');
				// Free order. Use it and update related dictionaries.
				$newOrder['id'] = $freeOrderID;
				$orderToTerritory[$freeOrderID] = $order['terrID'];
				$territoryToOrder[$order['terrID']] = $freeOrderID;
			}
			if (!array_key_exists($order['terrID'], $territoryToOrder))
				throw new RequestException('Unknown territory ID `'.$order['terrID'].'` for country `'.$countryID.'`.');
			$updatedOrders[$newOrder['id']] = $newOrder;
			if ($order['type'] == 'Wait')
				$waitIsSubmitted = true;
		}

		// If a 'Wait' order was submitted on a Builds phase, set all free orders to 'Wait'.
		if ($game->phase == 'Builds' && $waitIsSubmitted) {
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

		$orderInterface = null;
		$previousReadyValue = $member->orderStatus->Ready;
		while (true) {
			// Create order interface in any case.
			$orderInterface = new OrderInterface(
				$gameID,
				$game->variantID,
				$userID,
                $member->id,
				$turn,
				$phase,
				$countryID,
				$member->orderStatus,
				null,
				false
			);
			$orderInterface->orderStatus->Ready = false;
			// If there are no (or no more) updated orders, stop.
			if (empty($updatedOrders))
				break;
			// Load updated orders.
			// FIXME this function (board/orders/orderinterface.php) may report an error
			// via libHTML::notice, which is not friendly to JSON API.
			$orderInterface->load();
			$orderInterface->set(json_encode(array_values($updatedOrders)));
			$results = $orderInterface->validate();
			if ($results['invalid']) {
				// Remove invalid updated orders and re-try.
				foreach ($results['orders'] as $orderID => $orderObject) {
					if ($orderObject['status'] == 'Invalid') {
						unset($updatedOrders[$orderID]);
					}
				}
			} else {
				// No invalid results. No need to retry.
				break;
			}
		}

		if (!empty($updatedOrders))
			$orderInterface->writeOrders();
		$orderInterface->orderStatus->Ready = ($readyArg ? $readyArg == 'Yes' : $previousReadyValue);
		$orderInterface->writeOrderStatus();
        $DB->sql_put("COMMIT");

		// Return current orders.
		$currentOrders = array();
		$currentOrdersTabl = $DB->sql_tabl(
		'SELECT
			wD_Orders.id AS orderID,
			wD_Orders.type AS type,
			wD_Orders.fromTerrID AS fromTerrID,
			wD_Orders.toTerrID AS toTerrID,
			wD_Orders.viaConvoy AS viaConvoy,
            wD_Units.type as unitType,
			wD_Units.terrID AS terrID
			FROM wD_Orders
			LEFT JOIN wD_Units
			ON (wD_Orders.gameID = wD_Units.gameID AND wD_Orders.countryID = wD_Units.countryID AND wD_Orders.unitID = wD_Units.id)
			WHERE wD_Orders.gameID = '.$gameID.' AND wD_Orders.countryID = '.$countryID
		);
		while ($row = $DB->tabl_hash($currentOrdersTabl)) {
			$currentOrders[] = array(
			    'unitType' => $row['unitType'],
				'terrID' => ctype_digit($row['terrID']) ? intval($row['terrID']) : $row['terrID'],
				'type' => $row['type'],
				'fromTerrID' => ctype_digit($row['fromTerrID']) ? intval($row['fromTerrID']) : $row['fromTerrID'],
				'toTerrID' => ctype_digit($row['toTerrID']) ? intval($row['toTerrID']) : $row['toTerrID'],
				'viaConvoy' => $row['viaConvoy']
			);
		}

		// Leave a hint for the game master that this game should be checked:
        if ($orderInterface->orderStatus->Ready && !$previousReadyValue)
		{
			$MC->append('processHint',','.$gameID);
		}
	/*       
	Disabled; all game processing must be done via one path
	elseif (false && $orderInterface->orderStatus->Ready && !$previousReadyValue) {
            require_once(l_r('objects/misc.php'));
            require_once(l_r('objects/notice.php'));
            require_once(l_r('objects/user.php'));
            global $Misc;
            $Misc = new Misc();
            $game = $this->getAssociatedGame();

            if( $game->processStatus!='Crashed' && $game->attempts > count($game->Members->ByID)*2 )
            {
                $DB->sql_put("COMMIT");
                require_once(l_r('gamemaster/game.php'));

                $game = libVariant::$Variant->processGame($game->id);
                $game->crashed();
                $DB->sql_put("COMMIT");
            }
            elseif( $game->needsProcess() )
w            {
                $DB->sql_put("UPDATE wD_Games SET attempts=attempts+1 WHERE id=".$game->id);
                $DB->sql_put("COMMIT");

                require_once(l_r('gamemaster/game.php'));
                $game = libVariant::$Variant->processGame($gameID);
                if( $game->needsProcess() )
                {
                    $game->process();
                    $DB->sql_put("UPDATE wD_Games SET attempts=0 WHERE id=".$game->id);
                    $DB->sql_put("COMMIT");
                }
            }
        }*/

        // Returning current orders
		return json_encode($currentOrders);
	}
}
/**
 * API entry game/sendmessage
 */
class SendMessage extends ApiEntry {
	public function __construct() {
		parent::__construct('game/sendmessage', 'JSON', '', array('gameID','countryID','toCountryID', 'message'));
	}
	public function run($userID, $permissionIsExplicit) {
		global $Game, $DB;
		$args = $this->getArgs();
		$messages = array();

		if ($args['toCountryID'] === null)
			throw new RequestException('toCountryID is required.');

		if ($args['message'] === null)
			throw new RequestException('message is required.');


		$gameID = intval($args['gameID']);
		$countryID = intval($args['countryID']);
		$toCountryID = intval($args['toCountryID']);
		$message = $args['message'];

		$Game = $this->getAssociatedGame();

		$allowed = ($Game->pressType == 'Regular') || 
		           ($countryID == $toCountryID) ||
		           ($Game->pressType == 'RulebookPress' && ($Game->phase == 'Diplomacy' || $Game->phase == 'Finished')) ||
		           ($Game->pressType == 'PublicPressOnly' && $toCountryID == 0);
		if (!$allowed) {
			throw new RequestException("Message is invalid in $Game->pressType");
		}

		if (!(isset($Game->Members->ByUserID[$userID]) && $countryID == $Game->Members->ByUserID[$userID]->countryID)) {
			throw new ClientForbiddenException('User does not have explicit permission to make this API call.');
		}

		if ($toCountryID < 0 || $toCountryID > count($Game->Members->ByID) || $toCountryID == $countryID) {
			throw new RequestException('Invalid toCountryID');
		}

		if ($toCountryID != 0) {
			$toUser = new User($Game->Members->ByCountryID[$toCountryID]->userID);
			if($toUser->isCountryMuted($Game->id, $countryID)) {
				return json_encode(["messages" => []]);
			}
		}

		$timeSent = libGameMessage::send($toCountryID, $countryID, $message);

		// now fetch this message back out of the table.
		// This is the safest way to make sure all the escaping is correct.
		// Should we fetch messages from previous timeSent as well to make sure everything is in sync?
		$tabl = $DB->sql_tabl("SELECT message, turn 
			FROM wD_GameMessages WHERE 
			gameID = $gameID AND 
			timeSent = $timeSent AND 
			fromCountryID = $countryID AND 
			toCountryID = $toCountryID
		");

		while ($msg = $DB->tabl_hash($tabl)) {
			$messages[] = [
				'fromCountryID' => $countryID,
				'message' => $msg['message'],
				'timeSent' => (int) $timeSent,
				'toCountryID' => $toCountryID,
				'turn' => $msg['turn'],
			];
		}
		$ret = [
			"messages" => $messages
		];
		return json_encode($ret);
	}
}

/**
 * API entry game/getmessages
 */
class GetMessages extends ApiEntry {
	public function __construct() {
		parent::__construct('game/getmessages', 'GET', 'getStateOfAllGames', array('gameID','countryID','sinceTime'));
	}
	public function run($userID, $permissionIsExplicit) {
		error_log("message start");
		global $DB, $MC;
		$args = $this->getArgs();
		$countryID = $args['countryID'] ?? 0;
		$gameID = $args['gameID'];
		$messages = array();

		$sinceTime = $args['sinceTime'];
		$lastMsgKey = "lastmsgtime_{$gameID}_{$countryID}";
		// error_log("fetch messages since time= $sinceTime");
		if (isset($sinceTime)) {
			// FIXME: gotta be careful that user has permissions or we could leak
			// the existence of a message by whether we break here!
			$lastMsgTime = $MC->get($lastMsgKey);
			// try to shortcut before doing anything expensive
			// error_log("last message was {$lastMsgTime}, client asked for messages since {$sinceTime}");
			if ($lastMsgTime && $lastMsgTime <= $sinceTime) {
				// error_log("Bailing early because no new messages");
				return $this->JSONResponse(
					'No messages available',
					'',
					true,
					[
						'messages' => $messages,
					]
				);
			}
		}

		$game = $this->getAssociatedGame();
		$gamePhase = $game->phase;
		$pressType = $game->pressType;

		if ($gameID === null || !is_numeric($gameID))
			throw new RequestException(
				$this->JSONResponse('A gameID is required.', '', false, ['gameID' => $gameID])
			);

		if ($countryID === null || !is_numeric($countryID))
			throw new RequestException(
				$this->JSONResponse('A countryID is required.', '', false, ['countryID' => $countryID])
			);

		$where = "(toCountryID = $countryID OR fromCountryID = $countryID)";
		$where = "$where OR (toCountryID = 0 OR fromCountryID = 0)";
		if (isset($args['sinceTime'])) {
			$where = "($where) AND timeSent >= $sinceTime";
		}

		$tabl = $DB->sql_tabl("SELECT message, toCountryID, fromCountryID, turn, timeSent
		FROM wD_GameMessages WHERE gameID = $gameID AND ($where)");
		while ($message = $DB->tabl_hash($tabl)) {
			$messages[] = [
				'fromCountryID' => (int) $message['fromCountryID'],
				'message' => $message['message'],
				'timeSent' => (int) $message['timeSent'],
				'toCountryID' => (int) $message['toCountryID'],
				'turn' => (int) $message['turn'],
			];
		}
		// Return Messages.
		$curTime = time();
		$responseStr = $messages ? 'Successfully retrieved game messages.' : 'No messages available';
		// error_log("$responseStr at time $curTime");
		$newMessagesFrom = $countryID == 0 ? [] : array_map('intval', $game->Members->ByUserID[$userID]->newMessagesFrom);
		return $this->JSONResponse(
			$responseStr,
			'',
			true,
			[
				'messages' => $messages,
				'time' => $curTime,
				'newMessagesFrom' => $newMessagesFrom,
			]
		);
	}
}

/**
 * Class to manage an API authentication and check associated permissions.
 */
abstract class ApiAuth {
	/**
	 * User ID associated to API key.
	 * @var int
	 */
	protected $userID = null;

	/**
	 * Cache key associated with this API request.
	 * @var string
	 */
	private $cacheKey = null;

	/**
	 * Permissions associated to this API key.
	 * Associative array mapping permission name to a boolean.
	 * @var array
	 */
	protected $permissions = array();

	/**
	 * List of current permissions names in database table `wD_ApiPermissions`.
	 * @var array
	 */
	static protected $permissionFields = array(
		'getStateOfAllGames',
		'submitOrdersForUserInCD',
		'listGamesWithPlayersInCD'
	);

	/**
	 * Load API auth.
	 * @throws ClientUnauthorizedException - if associated user cannot be found.
	 */
	abstract protected function load();

	/**
	 * Initialize API auth object.
	 * @param $route - API route.
	 * @throws ClientUnauthorizedException - If associated user cannot be found.
	 */
	abstract public function __construct(string $route);

	/**
	 * Returns the cache key. This is made in the child depending on class needs. 
	 */
	public function getCacheKey() : string {
		return $this->cacheKey;
	}

	private function isUserMemberOfGame()
	{
		global $DB;
		list($isMember) = $DB->sql_row("SELECT COUNT(id) FROM wD_Members WHERE userID = " . $this->userID ." AND gameID = " . $this->getAssociatedGameId());
		return $isMember === 1;
	}
	/**
	 * Check if this API key is allowed to call given API entry.
	 * Throw an exception if any problem occurs, meaning that either API key does not have enough permissions, or we are unable to check it.
	 * @param ApiEntry $apiEntry - instance of API entry object to check.
	 * @return bool - a Boolean to indicate if permission is explicitly granted from API key (true)
	 * or if, either no permission is need, or permission is granted because user is a game member (false).
	 * @throws ServerInternalException
	 * @throws ClientForbiddenException
	 * @throws RequestException
	 */
	public function assertHasPermissionFor(ApiEntry $apiEntry) {
		global $DB;
		$permissionIsExplicit = false;
		$permissionField = $apiEntry->getPermissionField();

		if ($permissionField == '') {
			// No permission field.
			// If game ID is required, then user must be member of this game.
			// Otherwise, any user can call this function.
			if ($apiEntry->requiresGameID() && !$this->isUserMemberOfGame())
				throw new ClientForbiddenException('Access denied. User is not member of associated game.');
			
		} else {
			// Permission field available.
			if (!in_array($permissionField, self::$permissionFields))
				throw new ServerInternalException('Unknown permission name');

			// Permission field must be set for this user.
			// Otherwise, game ID must be required and user must be member of this game.
			if ($this->permissions[$permissionField]) {
				$permissionIsExplicit = true;
			} else {
				if (!$apiEntry->requiresGameID())
					throw new ClientForbiddenException("Permission denied.");

				if (!$this->isUserMemberOfGame())
					throw new ClientForbiddenException('Permission denied, and user is not member of associated game.');
			}
		}

		return $permissionIsExplicit;
	}

	/**
	 * Return associated user ID.
	 */
	public function getUserID() {
		return $this->userID;
	}
}

class ApiKey extends ApiAuth {

	/**
	 * API access key.
	 * @var string
	 */
	private $apiKey;

	protected function load(){
		global $DB;
		$rowUserId = $DB->sql_hash("SELECT userID from wD_ApiKeys WHERE apiKey = '".$DB->escape($this->apiKey)."'");
		if (!$rowUserId)
			throw new ClientUnauthorizedException('No user associated to this API key.');
		$this->userID = intval($rowUserId['userID']);
		$permissionRow = $DB->sql_hash("SELECT * FROM wD_ApiPermissions WHERE userID = ".$this->userID);
		if ($permissionRow) {
			foreach (self::$permissionFields as $permissionField) {
				if ($permissionRow[$permissionField] == 'Yes')
					$this->permissions[$permissionField] = true;
			}
		}
	}

	public function __construct($route){
		$apiKeyString = getBearerToken();
		if ($apiKeyString == null)
			throw new ClientUnauthorizedException('No API key provided.');
		$this->apiKey = $apiKeyString;
		$this->cacheKey = str_replace(' ', '_', 'api' . $this->apiKey . $route );
		foreach (self::$permissionFields as $permissionField)
			$this->permissions[$permissionField] = false;
		$this->load();
	}
}

class ApiSession extends ApiAuth {
	protected function load(){
		global $User;
		if( !empty( $User ) && $User->type['User'] === true && (int)$User->id > 0 ){
			$this->userID = $User->id;
		}
	}

	public function __construct($route){
		foreach (self::$permissionFields as $permissionField)
			$this->permissions[$permissionField] = false;
		
		// every session user can get state of all games (i.e. spectate)
		$this->permissions["getStateOfAllGames"] = true;

		$this->load();
	}
}

/**
 * API main call to manage calls.
 */
class Api {

	/**
	 * API entries. Array mapping API entry name to ApiEntry instance.
	 * @var array
	 */
	private $entries;

	private $route;

	private $authClass;

	public function __construct() {
		$this->entries = array();
	}

	/**
	 * Load an API entry.
	 * @param string $apiEntryClassName - Name of ApiEntry derived class corresponding to API entry to load.
	 */
	public function load(ApiEntry $apiEntry) {
		$this->entries[$apiEntry->getRoute()] = $apiEntry;
	}

	/**
	 * Returns the API route being used.
	 */
	public function getRoute() : string {
		return $this->route;
	}

	/**
	 * Run API. Parse call and return a response as a JSON-encoded string.
	 * @return string
	 * @throws ClientForbiddenException
	 * @throws ClientUnauthorizedException
	 * @throws NotImplementedException
	 * @throws RequestException
	 * @throws ServerInternalException
	 */
	public function run() {
		global $MC, $User;
		// Get route.
		if (!isset($_GET['route']))
			throw new RequestException('No route provided.');

		$this->route = strtolower(trim($_GET['route']));

		if (!isset($this->entries[$this->route]))
			throw new NotImplementedException('Unknown route.');

		if ( !empty( $User ) && ( $User->type['User'] ?? false ) === true ){
			/**
			 * If the request is an API call using the existing user session, process using the ApiSession class. 
			 */
			$this->authClass = 'ApiSession';
		}else{
			/**
			 * If the request is an API call using an API key, process using the ApiKey class. 
			 */
			$this->authClass = 'ApiKey';
		}

		$apiAuth = new $this->authClass($this->route);
		// Get API entry.
		$apiEntry = $this->entries[$this->route]; /** @var ApiEntry $apiEntry */
		// Check if request is authorized.
		$permissionIsExplicit = $apiAuth->assertHasPermissionFor($apiEntry);
		// Execute request.
		$userID = $apiAuth->getUserID();
		$result = $apiEntry->run($userID, $permissionIsExplicit); 
		
		// if( false && $route == 'players/missing_orders' )
		// {
		// 	$result = $MC->get($key);
		// 	if ( $result )
		// 	{
		// 		return $result;
		// 	}
		// }

		// Cache result
		// FIXME: This breaks API Keys
		// if( $this->route == 'players/missing_orders' && $cacheKey = $apiAuth->getCacheKey() ){
		// 	$MC->set($cacheKey, $result, 60); // Continually No rush to expire , should be cleaned on all game processes anyway
		// }

		return $result;
	}
}

try {
    if (!property_exists('Config', 'apiConfig') || !Config::$apiConfig['enabled']) {
        http_response_code(404);
        die('API is not enabled.');
    }
	// Load API object, load API entries, parse API call and print response as a JSON object.
	$api = new Api();
	$api->load(new ListGamesWithPlayersInCD());
	$api->load(new ListGamesWithMissingOrders());
	$api->load(new ListActiveGamesForUser());
	$api->load(new GetGamesStates());
	$api->load(new GetGameOverview());
	$api->load(new GetGameData());
	$api->load(new GetGameMembers());
	$api->load(new SetOrders());
	$api->load(new ToggleVote());
	$api->load(new SetVote());
	$api->load(new SendMessage());
	$api->load(new GetMessages());
	$api->load(new MessagesSeen());

	$jsonEncodedResponse = $api->run();
	// Set JSON header.
	header('Content-Type: application/json');
	// Print response.
	print $jsonEncodedResponse;
}

// 4xx - User errors - No need to log
catch (RequestException $exc) {
	handleAPIError($exc->getMessage(), 400);
}
catch (ClientUnauthorizedException $exc) {
	handleAPIError($exc->getMessage(), 401);
}
catch (ClientForbiddenException $exc) {
	handleAPIError($exc->getMessage(), 403);
}

// 5xx - Server errors
catch (ServerInternalException $exc) {
	handleAPIError($exc->getMessage(), 500);
	trigger_error($exc->getMessage());
}
catch (NotImplementedException $exc) {
	handleAPIError($exc->getMessage(), 501);
    trigger_error($exc->getMessage());
}
catch (Exception $exc) {
	handleAPIError("Internal error: ".$exc->getMessage(), 501);
    trigger_error($exc->getMessage());
}

?>
