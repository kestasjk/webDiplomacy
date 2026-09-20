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

use function PHPSTORM_META\map;

define('IN_CODE', 1);
define('IN_API', 1); // This will cause errors that would return HTML to instead output JSON
require_once('config.php');
if( Config::isOnPlayNowDomain() ) define('PLAYNOW',true);
require_once('header.php');
require_once('global/definitions.php');
require_once('locales/layer.php');
require_once('objects/database.php');
require_once('objects/database_metrics.php');
require_once('objects/redis.php');
require_once('board/orders/orderinterface.php');
require_once('api/responses/player_context.php');
require_once('objects/game.php');
require_once('objects/user.php');
require_once('lib/cache.php');
require_once('lib/metrics.php');
require_once('lib/html.php');
require_once('lib/time.php');
require_once('lib/gamemessage.php');
require_once('lib/variant.php');
require_once('board/orders/jsonBoardData.php');
require_once('variants/install.php');
require_once('gamemaster/gamemaster.php');
global $DB;

// $DB was created by header.php as a MetricsDatabase; its counters are reset with resetMetrics() just before the
// API call runs. (Creating a second MetricsDatabase here opened, and immediately closed, an extra MySQL
// connection on every API request, which contributed to "Too many connections" errors under load.)

// The API exception classes (RequestException, ClientForbiddenException, ...) are defined in
// global/exceptions.php (loaded by header.php) so that library code shared with the classic web pages
// can throw them too and have api.php map them to 4xx responses instead of logging them as server errors.
require_once('global/exceptions.php');

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
	 * The FAIR bots use a lot of GPU memory, so there is a need to to have a single bot instance switch between
	 * different accounts, even if those accounts are in the same game. As the bots are stateful this means a single
	 * bot needs to be able to submit orders with an API key against a certain game, and for the API to recognize
	 * that the game ID the bot has given encodes both the actual game ID and the bot's account ID.
	 * 
	 * Also when returning a request that contains game ID(s) the ID needs to be adjusted so that it encodes the 
	 * account of the bot that is making the request.
	 * 
	 * This is done by multiplying the game ID by 10 then adding the wD_ApiKey.multiplexOffset to any games returned
	 * to a multiplexed bot, and adding 100000000 to ensure no conflicts with real game IDs and dividing the game ID 
	 * by 10 to get the actual game ID when a multiplexed game ID is
	 * given and taking the remainder to look up the multiplexOffset, giving the bot's specific account ID for that 
	 * request.
	 */

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
	 * Whether to lock the game record for update; increases chance of deadlocks, but prevents game corruption
	 * @var bool
	 */
	protected $gameLocking = false;

	/**
	 * Initialize an ApiEntry.
	 * @param string $route - API entry name.
	 * @param string $type - API entry type ('GET' or 'POST').
	 * @param string $databasePermissionField - name of corresponding permission field in database table `wD_ApiPermissions`.
	 * @param array $requirements - array of API entry parameters names.
	 * @throws Exception - if invalid type or if requirements is not an array.
	 */
	public function __construct($route, $type, $databasePermissionField, $requirements, $gameLocking = false) {
		if (!in_array($type, array('GET', 'POST', 'JSON')))
			throw new ServerInternalException('Invalid API entry type');
		if (!is_array($requirements))
			throw new ServerInternalException('API entry field names must be an array.');
		$this->route = cleanRoute($route);
		$this->type = $type;
		$this->databasePermissionField = $databasePermissionField;
		$this->requirements = $requirements;
		$this->gameLocking = $gameLocking;
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

	private $argsCache = null;
	/**
	 * Prepare / parse the args for this API entry, including detecting and extracing a multiplex ID
	 */
	public function cacheAndProcessArgs() {
		if( $this->argsCache !== null )
			return $this->argsCache;
		
		$rawArgs = array();
		if ($this->type == 'GET')
			$rawArgs = $_GET;
		else if ($this->type == 'POST')
			$rawArgs = $_POST;
		else if ($this->type == 'JSON') {
			// TODO: json_decode() makes me nervous
			$rawArgs = json_decode(file_get_contents("php://input"), true);
			if (!$rawArgs)
				throw new RequestException('Invalid JSON request data.');
		}
		$selectedArgs = array();
		if( isset($rawArgs['multiplexOffset']))
			$selectedArgs['multiplexOffset'] = (int)$rawArgs['multiplexOffset']; // If there is an explicit multiplexOffset save it now
		foreach ($this->requirements as $fieldName) {
			$arg = isset($rawArgs[$fieldName]) ? $rawArgs[$fieldName] : null;
			if( $fieldName === 'gameID' && !is_null($arg) )
			{
				// There is a gameID being given as an input. If it is a multiplexed gameID then we need to
				// adjust it to the actual gameID and store the multiplexOffset and so that we can adjust the
				// gameID back to a non-multiplexed gameID when returning it.
				$arg = (int)$arg;

				// Game IDs between 1 and 100000000 are not multiplexed.
				if( $arg > 100000000 )
				{
					// It's a multiplexed gameID
					$arg = $arg - 100000000;
					$selectedArgs['multiplexOffset'] = $arg % 10;
					$arg = (int)($arg / 10);
				}
				$arg = (string)$arg; // Convert the int back to a string as expected by the API calls
			}
			$selectedArgs[$fieldName] = $arg;
		}
		$this->argsCache = $selectedArgs;
		return $selectedArgs;
	}

	private $multiplexOffsetCache = -1;
	/**
	 * If the args sent has a gameID that's multiplexed this will return the multiplex offset
	 */
	public function getMultiplexOffsetOrNull()
	{
		if( $this->multiplexOffsetCache === -1 )
		{
			$args = $this->cacheAndProcessArgs();
			if( isset($args['multiplexOffset']) )
				$this->multiplexOffsetCache = $args['multiplexOffset'];
			else
				$this->multiplexOffsetCache = null;
		}
		return $this->multiplexOffsetCache;
	}
	
	/**
	 * If a multiplex offset came from the gameID in the args then this class provides the multiplex offset, however if there is
	 * no multiplex offset provided but a multiplexed api key is being used the multiplex offset needs to be set so that 
	 * game IDs returned will be encoded with the multiplex offset.
	 */
	public function setMultiplexOffset($multiplexOffset)
	{
		$this->multiplexOffsetCache = $multiplexOffset;
	}

	/**
	 * Convert a game ID into a multiplexed game ID, which when provided in future API calls can identify the bot userID that requested that game.
	 * 
	 * All game IDs returned via the API must be run throuhg this function
	 */
	public function gameIDToMultiplexedGameID($gameID)
	{
		$multiplexOffset = $this->getMultiplexOffsetOrNull();
		if( $multiplexOffset == null ) return $gameID;

		if( $multiplexOffset >= 10 || $multiplexOffset == null || $multiplexOffset <= 0 )
		{
			throw new Exception("Multiplex offset cannot be >= 10, null, or <= 0");
		}
		if( $gameID > 100000000 )
		{
			throw new Exception("Being asked to multiplex a multiplexed ID");
		}
		$gameID *= 10;
		$gameID += $multiplexOffset;
		$gameID += 100000000;
		return (int)$gameID;
	}
	/**
	 * Return an array of actual API parameters values, retrieved from $_GET or $_POST, depending on API entry type.
	 * @return array
	 * @throws RequestException
	 */
	public function getArgs() {
		return self::cacheAndProcessArgs($this->type, $this->requirements);
	}

	/**
	 * Return true if this API entry requires a parameter called `gameID`.
	 */
	public function requiresGameID() {
		return in_array('gameID', $this->requirements);
	}

	/**
	 * Whether a route which takes a gameID can only be called by a member of that game, when the caller hasn't been
	 * given the route's permission explicitly. A route which returns false has to decide for itself what a
	 * non-member may be given.
	 */
	public function requiresMembership() {
		return $this->requiresGameID();
	}

	/**
	 * Whether a logged-out browser may call this route, with no API key and no session. Only the routes a
	 * page reports its own health on say yes: everything else needs to know who is asking.
	 */
	public function allowsGuest() {
		return false;
	}

	/**
	 * Whether the caller is a logged-in user's browser rather than an API key. Set by Api::run() before run().
	 * @var bool
	 */
	public $isSessionAuth = false;

	public function isUserMemberOfGame($userID)
	{
		global $DB;
		list($isMember) = $DB->sql_row("SELECT COUNT(id) FROM wD_Members WHERE userID = " . $userID ." AND gameID = " . $this->getAssociatedGameID());
		return ($isMember >= 1); // Can be greater if the game is a sandbox game
	}

	public function getAssociatedGameID() {
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
	 * @param useCache if true, use the cache, otherwise always re-fetch from DB.
	 * @return Game
	 * @throws RequestException - if no gameID field in requirements, or if no valid game ID provided.
	 */
	public function getAssociatedGame($useCache = true) {
		global $DB;
			
		if( $useCache && !is_null($this->gameCache) ) return $this->gameCache;
		$gameID = $this->getAssociatedGameID();
	
		// This seems to happen when the client loses the game it was on
		if( $gameID == 0 )
			throw new RequestException("Game ID = 0, invalid request");
	
		$lockMode = $this->gameLocking ? UPDATE : NOLOCK;

		$gameRow = Game::fetchRow($gameID, $lockMode);
		if( $gameRow === false )
			throw new RequestException("Could not fetch row for give gameID, game may have been cancelled");

		$Variant = libVariant::loadFromVariantID($gameRow['variantID']);
		libVariant::setGlobals($Variant);
		
		$this->gameCache = $Variant->Game($gameRow, $lockMode);
		
		return  $this->gameCache; // Lock game for update, which just ensures the game is always processed sequentially, if the game will be updated
	}

	/**
	 * Process API call. To override in derived classes.
	 * @param int $userID - ID of user who makes API call.
	 * @param bool $permissionIsExplicit - boolean to indicate if permission flag was set for API caller key.
	 */
	abstract public function run($userID, $permissionIsExplicit);
}
class SandboxCreate extends ApiEntry {
	public function __construct() {
		parent::__construct('sandbox/create', 'GET', '', array('variantID', 'territoryUnits'), true);
	}
	public function run($userID, $permissionIsExplicit) {
		require_once(l_r('gamemaster/sandboxGame.php'));
		$args = $this->getArgs();
		$sandboxGame = processSandboxGame::newGame(isset($args['variantID']) ? (int)$args['variantID'] : 1);
		return json_encode(['gameID' => $sandboxGame->id]);
	}
}

class SandboxCopy extends ApiEntry {
	public function __construct() {
		parent::__construct('sandbox/copy', 'GET', '', array('copyGameID'), true);
	}
	public function run($userID, $permissionIsExplicit) {
		require_once(l_r('gamemaster/sandboxGame.php'));
		$args = $this->getArgs();
		$sandboxGame = processSandboxGame::copy((int)$args['copyGameID']);
		return json_encode(['gameID' => $sandboxGame->id]);
	}
}

class SandboxMoveTurnBack extends ApiEntry {
	public function __construct() {
		parent::__construct('sandbox/moveTurnBack', 'GET', '', array('gameID'), true);
	}
	public function run($userID, $permissionIsExplicit) {
		require_once(l_r('gamemaster/sandboxGame.php'));
		$args = $this->getArgs();
		$gameID = (int)$args['gameID'];
		$Variant = libVariant::loadFromGameID($gameID);
		$sandboxGame = $Variant->processGame($gameID);
		$sandboxGame->moveTurnBack();
	}
}

class SandboxDelete extends ApiEntry {
	public function __construct() {
		parent::__construct('sandbox/delete', 'GET', '', array('gameID'), true);
	}
	public function run($userID, $permissionIsExplicit) {
		require_once(l_r('gamemaster/sandboxGame.php'));
		$args = $this->getArgs();
		$gameID = (int)$args['gameID'];
		processSandboxGame::eraseGame($gameID);
	}
}

/**
 * API entry game/togglevote
 * *Multiplexed
 * TODO: Merge with SetVote
 */
class ToggleVote extends ApiEntry {
	public function __construct() {
		parent::__construct('game/togglevote', 'GET', '', array('gameID','countryID','vote'), true);
	}
	public function run($userID, $permissionIsExplicit) {
		global $DB, $Redis;

		$args = $this->getArgs();
		$gameID = intval($args['gameID']);
		$countryID = intval($args['countryID']);
		$vote = $args['vote'];
		if (!in_array($vote, ['Draw', 'Pause', 'Cancel', 'Concede']))
		    throw new RequestException('Invalid vote type; allowed are Draw, Concede, Pause, Cancel');

		$currentVotes = $DB->sql_hash("SELECT votes FROM wD_Members WHERE gameID = ".$gameID." AND countryID = ".$countryID." AND userID = ".$userID);
		// Without this a member could log a vote as any country in the game, though not change its votes
		if( !$currentVotes )
			throw new ClientForbiddenException('A user can only vote for the country it controls.');
		$currentVotes = $currentVotes['votes'] ?? ''; // If no votes are set, default to empty string

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
		$DB->sql_put("UPDATE wD_Members SET votes = '".$newVotes."', votesChanged=UNIX_TIMESTAMP() WHERE gameID = ".$gameID." AND userID = ".$userID." AND countryID = ".$countryID);
		$DB->sql_put("COMMIT");

		// The vote, and for games with public draw votes the log of it
		libGameFiles::refresh($gameID, array('status', 'messages'));

		$Redis->trigger("private-game" . $gameID, 'overview', 'set-vote');
		
		return $newVotes;
	}
}

// FIXME - a bit copypasta with the above API call togglevote.
// togglevote also uses GET rather than POST, but GET is not supposed to be used
// for state-modifying web queries. So probably togglevote should be deprecated.
/**
 * API entry game/setvote
 * *Multiplexed
 */
class SetVote extends ApiEntry {
	public function __construct() {
		parent::__construct('game/setvote', 'JSON', '', array('gameID','countryID','vote','voteOn'), true);
	}
	public function run($userID, $permissionIsExplicit) {
		global $DB, $Redis;

		$args = $this->getArgs();
		$gameID = intval($args['gameID']);
		$countryID = intval($args['countryID']);
		$vote = $args['vote'];
		$voteOn = filter_var($args['voteOn'], FILTER_VALIDATE_BOOLEAN);
		if (!in_array($vote, ['Draw', 'Pause', 'Cancel', 'Concede']))
		    throw new RequestException('Invalid vote type; allowed are Draw, Concede, Pause, Cancel');

		$currentVotes = $DB->sql_hash("SELECT votes FROM wD_Members WHERE gameID = ".$gameID." AND countryID = ".$countryID." AND userID = ".$userID);
		// Without this a member could log a vote as any country in the game, though not change its votes
		if( !$currentVotes )
			throw new ClientForbiddenException('A user can only vote for the country it controls.');
		$currentVotes = $currentVotes['votes'] ?? ''; // If no votes are set, default to empty string

		if( $voteOn === in_array($vote, explode(',',$currentVotes)) )
		{
			return $currentVotes;
		}
		// Keep a log that a vote was set in the game messages, so the vote time is recorded
		// ($voteOn is the state asked for here, where in game/togglevote it is the state before the toggle)
		require_once(l_r('lib/gamemessage.php'));
		libGameMessage::send($countryID, $countryID, ($voteOn?'':'Un-').'Voted for '.$vote, $gameID);

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
		$DB->sql_put("UPDATE wD_Members SET votes = '".$newVotes."', votesChanged=UNIX_TIMESTAMP() WHERE gameID = ".$gameID." AND userID = ".$userID." AND countryID = ".$countryID);
		$DB->sql_put("COMMIT");

		// The vote, and for games with public draw votes the log of it
		libGameFiles::refresh($gameID, array('status', 'messages'));

		$Redis->trigger("private-game" . $gameID, 'overview', 'set-vote');
		
		return $newVotes;
	}
}

/**
 * API entry sse/authentication
 * Game auth isn't important, but we need some auth to ensure no-one is seeing who else is receiving messages.
 * Just take the user's ID and the game ID and return a 
 */
class SSEAuthentication extends ApiEntry {
	public function __construct() {
		parent::__construct('sse/authentication', 'JSON', 'getStateOfAllGames', array('gameID', 'channel_name'));
	}
	public function run($userID, $permissionIsExplicit) {
		$args 				= $this->getArgs();
		$channelName	= $args['channel_name'];

		$channelNameParams = explode("-", $channelName);
		$gameID = intval(str_replace("game", "", $channelNameParams[1]));
		$countryID = 0;
		
		if (count($channelNameParams) > 2) {
			$countryID = intval(str_replace("country", "", $channelNameParams[2]));
		}
		
		$Game = $this->getAssociatedGame();

		// There are 2 authorization validations because a player can
		// subscribe to the game overview channel or to the messages channel
		// game overview channel doesn't include the countryID, and anyone can subscribe
		if ($countryID != 0) {
			if (!(isset($Game->Members->ByCountryID[$countryID]) && $userID == $Game->Members->ByCountryID[$countryID]->userID)) {
			// if (!(isset($Game->Members->ByUserID[$userID]) && $countryID == $Game->Members->ByUserID[$userID]->countryID)) {
				throw new ClientForbiddenException('User does not have explicit permission to make this API call.');
			}
		}

		// The token will expire every day to prevent reusing on games that the user has since left.
		// This token is passed to any validated by the SSE server, but the info it gives is not very useful, 
		// just who is receiving messages when.
		// board.php and game/playercontext give members this token with the page, so this is only needed once
		// that one has expired.
		$token = libAuth::sseChannelToken($channelName);
		
		return $this->JSONResponse(
			"User was successfully authenticated for this channel",
			'',
			true,
			[
				'auth' => $token
			]
		);
	}
}

/**
 * API entry game/messagesseen
 * *Multiplexed
 */
class MessagesSeen extends ApiEntry {
	public function __construct() {
		parent::__construct('game/messagesseen', 'JSON', '', array('gameID','countryID','seenCountryID'), false); // Shouldnt need to lock whole game
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
		$DB->sql_put("UPDATE wD_Members SET newMessagesFrom = '".implode(',',$newMessagesFrom)."', timeLoggedIn = ".time()." WHERE id = ".$member->id);
		$DB->sql_put("COMMIT");
	}
}

/**
 * API entry push/subscribe
 * Stores this browser's push subscription (endpoint + encryption keys) against the logged-in user,
 * when the user asks for notifications, or with resync set when the browser's subscription has
 * changed since it was registered. Returns whether the browser is now subscribed, and how many
 * devices the user has subscribed.
 */
class PushSubscribe extends ApiEntry {
	public function __construct() {
		parent::__construct('push/subscribe', 'JSON', '', array('endpoint', 'p256dh', 'auth', 'resync'));
	}
	public function run($userID, $permissionIsExplicit) {
		global $DB;
		if (!libPush::isEnabledForUser($userID))
			throw new ClientForbiddenException('Push notifications are not enabled for this user.');
		$args = $this->getArgs();
		if ($args['endpoint'] === null || $args['p256dh'] === null || $args['auth'] === null)
			throw new RequestException('endpoint, p256dh and auth are required.');
		try {
			$subscribed = libPush::subscribeBrowser($userID, $args['endpoint'], $args['p256dh'], $args['auth'],
				isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '', !empty($args['resync']));
		}
		catch (Exception $e) {
			throw new RequestException($e->getMessage());
		}
		$DB->sql_put("COMMIT");
		return $this->JSONResponse(
			$subscribed ? 'Push subscription registered.' : 'This browser is no longer subscribed.',
			'',
			true,
			array('subscribed' => $subscribed, 'devices' => libPush::countSubscriptions($userID))
		);
	}
}

/**
 * API entry push/unsubscribe
 * Removes one of the logged-in user's push subscriptions: the one with the given endpoint, or this
 * browser's if endpoint is null. Deliberately not gated on the push feature flag: a user removed
 * from the trial must still be able to clean up their subscription.
 */
class PushUnsubscribe extends ApiEntry {
	public function __construct() {
		parent::__construct('push/unsubscribe', 'JSON', '', array('endpoint'));
	}
	public function run($userID, $permissionIsExplicit) {
		global $DB;
		$args = $this->getArgs();
		libPush::unsubscribeBrowser($userID, $args['endpoint'] === null ? null : (string)$args['endpoint']);
		$DB->sql_put("COMMIT");
		return $this->JSONResponse('Push subscription removed.', '', true);
	}
}

/**
 * API entry game/markbackfromleft
 * *Multiplexed
 */
class MarkBackFromLeft extends ApiEntry {
	public function __construct() {
		parent::__construct('game/markbackfromleft', 'JSON', '', array('gameID','countryID'), true);
	}
	public function run($userID, $permissionIsExplicit) {
		global $Game, $DB;
		$args = $this->getArgs();
		$countryID = intval($args['countryID']);
		$Game = $this->getAssociatedGame();
		$member = $Game->Members->ByUserID[$userID];
		$member->markBackFromLeft();
		$DB->sql_put("COMMIT");

		// The member's status is back to Playing
		libGameFiles::refresh($Game->id, array('game', 'status'));
	}
}

/**
 * API entry game/playercontext
 * Everything about a game which depends on who is asking: their orders, messages, votes, order status and SSE token,
 * and where the game's public JSON files are. Without a gameID, a row for each of the caller's active games.
 * See doc/gamedata/02-spec.md and api/responses/player_context.php
 * *Multiplexed
 */
class GetPlayerContext extends ApiEntry {
	public function __construct() {
		parent::__construct('game/playercontext', 'GET', '',
			array('gameID', 'countryID', 'orders', 'messages', 'messagesSince', 'messageTurns', 'sbToken'), false);
	}
	/**
	 * Anyone may ask about any game; someone who isn't playing in it is only told what is public.
	 */
	public function requiresMembership() {
		return false;
	}
	public function run($userID, $permissionIsExplicit) {
		if( empty($userID) )
			throw new ClientUnauthorizedException('Not logged in.');

		$args = $this->getArgs();

		if( is_null($args['gameID']) )
			$context = \webdiplomacy_api\PlayerContext::forUser($this, $userID);
		else
			$context = \webdiplomacy_api\PlayerContext::forGame($this, $userID, $this->isSessionAuth, $args);

		return json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
	}
}

/**
 * API entry game/join
 */
class JoinGame extends ApiEntry {
	public function __construct() {
		parent::__construct(
			'game/join',
			'POST',
			'',
			array('gameID'),
			true);
	}
	/**
	 * @throws Exception
	 * @throws RequestException
	 * @throws ClientForbiddenException
	 */
	public function run($userID, $permissionIsExplicit) {
		$User = new User($userID);

		if ( !$User->type['User'] ) throw new ClientForbiddenException("Only users can join games");

		$args = $this->getArgs();
		$gameID = (int)$args['gameID'];

		require_once(l_r('gamemaster/game.php'));
		
		$Variant=libVariant::loadFromGameID($gameID);

		// A bot only knows the maps its code was written for, so joining any other variant would
		// leave it in civil disorder until a moderator removed it. A logged-in user's browser is a
		// person, who can join whatever the game itself allows.
		if( !$this->isSessionAuth && !in_array($Variant->id, libVariant::botVariantIDs()) )
			throw new ClientForbiddenException('Bots cannot play this variant.');

		libVariant::setGlobals($Variant);
		$Game = $Variant->processGame($gameID);
		
		// They will be stopped here if they're not allowed.
		$Game->Members->join(
			( isset($args['gamepass']) && $args['gamepass'] ?? null ),
			( isset($args['countryID']) && $args['countryID'] ?? null )
		 );
		
		return $this->JSONResponse('Successfully joined.', 'GGD-JOIN', true);
	}
}

/**
 * API entry game/leave
 */
class LeaveGame extends ApiEntry {
	public function __construct() {
		parent::__construct(
			'game/leave',
			'POST',
			'',
			array('gameID'),
			true);
	}
	/**
	 * @throws Exception
	 * @throws RequestException
	 * @throws ClientForbiddenException
	 */
	public function run($userID, $permissionIsExplicit) {
		$User = new User($userID);

		if ( !$User->type['User'] ) throw new ClientForbiddenException("Only users can join games");

		$args = $this->getArgs();
		$gameID = (int)$args['gameID'];

		require_once(l_r('gamemaster/game.php'));
		
		$Variant=libVariant::loadFromGameID($gameID);
		libVariant::setGlobals($Variant);
		$Game = $Variant->processGame($gameID);
		
		$reason=$Game->Members->cantLeaveReason();

		if($reason)
			throw new RequestException(l_t("Can't leave game; %s.",$reason));
		else
			$Game->Members->ByUserID[$User->id]->leave();

		return $this->JSONResponse('Successfully left game.', 'GGD-LEAVE', true);
	}
}

/**
 * API entry game/orders
 * *Multiplexed
 */
class SetOrders extends ApiEntry {
	public function __construct() {
		parent::__construct(
			'game/orders',
			'JSON',
			'submitOrdersForUserInCD',
			array('gameID', 'turn', 'phase', 'countryID', 'orders', 'ready'),
			gameLocking: false); // This should only require the member record for the country being updated get locked for update, this is how the ajax.php
			// order interface locking works. Locking on this is creating 95+% of deadlocks, which is causing 80+% of errors as of 2022-10-12
			// 'ready' is optional.
			// 20250919 Even with game locking disabled this still caused deadlocks, probably better to lock the game record for update
			// Concerned locking game for update on every order submission will cause more deadlocks, though it seems to be OK so far during low traffic with
			// just 1. Disabling for now 20250920
	}		
	/**
	 * @throws Exception
	 * @throws RequestException
	 * @throws ClientForbiddenException
	 */
	public function run($userID, $permissionIsExplicit) {
		global $DB, $Redis;
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
		$turn = intval($turn);
		$phase = strval($phase);
		$countryID = intval($countryID);

		// Getting frequent deadlocks when getting the game and locking wmembers for update, perhaps because the permission check has to query members.
		// So commit and begin to release anything locked and start over
		$DB->sql_put("COMMIT");
		// Lock the member record for update, as this will be updated but the game will not be.
		// Taking this lock is where most deadlocks / lock wait timeouts surface (contention with the gamemaster and
		// other order submissions for the same game). Nothing has been written yet at this point, so it is safe to
		// roll back and retry the lock a few times before giving up.
		$DB->withRetry(function() use ($DB, $gameID, $countryID) {
			$DB->sql_put("BEGIN");
			$DB->sql_row("SELECT id FROM wD_Members WHERE gameID = ".$gameID." AND countryID = ".$countryID." FOR UPDATE");
		});
		$game = $this->getAssociatedGame();
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
				WHERE wD_Orders.gameID = '.$gameID.
				(is_null($game->sandboxCreatedByUserID) ? ' AND wD_Orders.countryID = '.$countryID : '');
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
			foreach (array('terrID', 'type', 'fromTerrID', 'toTerrID', 'viaConvoy', 'countryID') as $bodyField) {
				if (!array_key_exists($bodyField, $order))
				{
					if( $bodyField == 'countryID' )
						$order[$bodyField] = $countryID; // Bots that were done before sandbox mode won't provide this, so fill in the member country ID
					else
						throw new RequestException('Missing order info: ' . $bodyField);
				}
					
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
						'viaConvoy' => null,
						'id' => $orderID,
						'countryID' => $countryID
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
				false,
				!is_null($game->sandboxCreatedByUserID)
			);
			$orderInterface->orderStatus->Ready = false;
			// If there are no (or no more) updated orders, stop.
			if (empty($updatedOrders))
				break;
			// Load updated orders.
			// FIXME this function (board/orders/orderinterface.php) may report an error
			// via libHTML::notice, which is not friendly to JSON API.
			$orderInterface->load(true);
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
		$orderInterface->orderStatus->Saved = true; // Always ensure the order status is saved, which it may not be if a bot is submitting all hold orders
		// which can cause the bot to loop, thinking it hasnt submitted orders yet
		$orderInterface->writeOrderStatus();
        $DB->sql_put("COMMIT");

		// Let the other players' clients see the new order status (where the game shows it). The member's status may
		// also have changed above, from Left back to Playing.
		libGameFiles::refresh($gameID, array('game', 'status'));

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
			wD_Units.terrID AS terrID,
			wD_Orders.countryID AS countryID
			FROM wD_Orders
			LEFT JOIN wD_Units
			ON (wD_Orders.gameID = wD_Units.gameID AND wD_Orders.countryID = wD_Units.countryID AND wD_Orders.unitID = wD_Units.id)
			WHERE wD_Orders.gameID = '.$gameID.
			(is_null($game->sandboxCreatedByUserID) ? ' AND wD_Orders.countryID = '.$countryID : '')
		);
		while ($row = $DB->tabl_hash($currentOrdersTabl)) {
			$currentOrders[] = array(
			    'unitType' => $row['unitType'],
				'terrID' => $row['terrID'] !== null && ctype_digit($row['terrID']) ? intval($row['terrID']) : $row['terrID'],
				'type' => $row['type'],
				'fromTerrID' => $row['fromTerrID'] !== null && ctype_digit($row['fromTerrID']) ? intval($row['fromTerrID']) : $row['fromTerrID'],
				'toTerrID' => $row['toTerrID'] !== null && ctype_digit($row['toTerrID']) ? intval($row['toTerrID']) : $row['toTerrID'],
				'viaConvoy' => $row['viaConvoy'],
				'countryID' => $row['countryID']
			);
		}

		// Leave a hint for the game master that this game should be checked:
        if ($orderInterface->orderStatus->Ready && !$previousReadyValue)
		{
			$Redis->append('processHint',','.$gameID);
		}
        // Returning current orders
		return json_encode($currentOrders);
	}
}
/**
 * API entry game/sendmessage
 * *Multiplexed
 */
class SendMessage extends ApiEntry {
	public function __construct() {
		parent::__construct('game/sendmessage', 'JSON', '', array('gameID','countryID','toCountryID', 'message'), false);
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

		$allowed = ($Game->phase == 'Finished') ||
		           ($Game->pressType == 'Regular') || 
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
	protected $cacheKey = null;

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
	abstract public function load($multiplexOffset = 0);

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
			if ($apiEntry->requiresMembership() && !$apiEntry->isUserMemberOfGame($this->userID))
				throw new ClientForbiddenException('Access denied. User '.$this->userID.' is not member of associated game.');
			
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

				if (!$apiEntry->isUserMemberOfGame($this->userID))
					throw new ClientForbiddenException('Permission denied, and user '.$this->userID.' is not member of associated game.');
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

	/**
	 * Multiplex offset associated to this API key. If an API key has multiple associated accounts then when load()
	 * is called here without a multiplex offset the last requested account will be used, but if an offset if provided
	 * , typically because a gameID arg was given containing a multiplex offset, that particular account will be loaded.
	 * 
	 * If no multiplex offset this is null.
	 */
	private $multiplexOffset = null;
	public function getMultiplexOffsetOrNull() { return $this->multiplexOffset; }

	/**
	 * Initialize API auth object, getting a userID from the API key. If an API key is associated with multiple 
	 * userIDs either a multiplexOffset can be given to select a specific userID, or else if no multiplexOffset is
	 * given this function will return the userID which hasn't been selected for the longest
	 * 
	 * @param $multiplexOffset - If the args provided contain a game with an embedded multiplex offset it is set here
	 */
	public function load($multiplexOffset = 0)
	{
		global $DB;
		
		if( $multiplexOffset == null ) $multiplexOffset = 0;
		$multiplexOffset = intval($multiplexOffset);

		// By selecting the multiplex offset account if an offset is provided, but if one isn't provided choosing
		// the last requested multiplexed account, we can ensure a multiplexed api key will rotate between accounts.
		$rowUserID = $DB->sql_hash(
			"SELECT userID, multiplexOffset FROM wD_ApiKeys WHERE apiKey = '".$DB->escape($this->apiKey)."' "
			.($multiplexOffset > 0 ? " AND multiplexOffset = " . $multiplexOffset . " " : "" ) 
			." ORDER BY lastHit LIMIT 1");
		if (!$rowUserID)
			die();//throw new ClientUnauthorizedException('No user associated to this API key.');
		$this->userID = intval($rowUserID['userID']);
		$this->multiplexOffset = $rowUserID['multiplexOffset'];
		$permissionRow = $DB->sql_hash("SELECT * FROM wD_ApiPermissions WHERE userID = ".$this->userID);
		if ($permissionRow) {
			foreach (self::$permissionFields as $permissionField) {
				if ($permissionRow[$permissionField] == 'Yes')
					$this->permissions[$permissionField] = true;
			}
		}
		$DB->sql_put("UPDATE wD_ApiKeys SET hits = hits + 1, lastHit = UNIX_TIMESTAMP() WHERE apiKey = '".$DB->escape($this->apiKey)."'"
			.($this->multiplexOffset > 0 ? " AND multiplexOffset = " . $this->multiplexOffset . " " : "" ) );
		$DB->sql_put("COMMIT");
	}

	public function __construct($route){
		$apiKeyString = getBearerToken();
		/*
		If you suddenly get this error for no reason all of a sudden try this in .htaccess:
		RewriteEngine On
		RewriteCond %{HTTP:Authorization} ^(.*)
		RewriteRule .* - [E=HTTP_AUTHORIZATION:%1]
		TODO: Make more stable solution for getting the API key from the request.
		*/
		if ($apiKeyString == null)
			throw new ClientUnauthorizedException('No API key provided.');
		$this->apiKey = $apiKeyString;
		$this->cacheKey = str_replace(' ', '_', 'api' . $this->apiKey . $route );
		foreach (self::$permissionFields as $permissionField)
			$this->permissions[$permissionField] = false;
	}
}

class ApiSession extends ApiAuth {
	public function load($multiplexOffset = 0){
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
	}
	
	public function getMultiplexOffsetOrNull() { return null; }
}

/**
 * What the two client/* routes have in common: they are called by a page reporting on itself, so the caller
 * may be a guest, everything in the body is untrusted, and a browser that is broken enough to report in a
 * loop must not be able to fill Redis or the error log with it.
 */
abstract class ClientReportEntry extends ApiEntry {
	/**
	 * A page can report without being logged in: a script error on the home page is worth as much as one on
	 * the board, and a guest is who most often sees a page fail to load.
	 */
	public function allowsGuest() {
		return true;
	}

	/**
	 * The address reports are counted against, as elsewhere in the site: the forwarded address when there is
	 * one, since production and staging both sit behind something.
	 */
	protected function clientAddress() {
		if( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) )
			return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);

		return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
	}

	/**
	 * Whether this address may report again in the current five minute window. Without Redis there is nothing
	 * to count with, and the limits are low enough that dropping the check is safer than dropping the reports.
	 *
	 * @param string $bucket The kind of report, counted separately
	 * @param int $limit Reports allowed per address per five minutes
	 * @return bool
	 */
	protected function withinRateLimit($bucket, $limit) {
		global $Redis;

		if( empty($Redis) )
			return true;

		try
		{
			$key = 'CLIENTREPORT_'.$bucket.'_'.md5($this->clientAddress()).'_'.intval(time() / 300);
			if( intval($Redis->get($key)) >= $limit )
				return false;

			$Redis->setIfMissing($key, 0, 600);
			$Redis->incrementMany(array($key => 1));
		}
		catch(Exception $e)
		{
			// A report is never worth an error of our own
		}

		return true;
	}
}

/**
 * API entry client/metrics
 *
 * What a page saw of its own loading, added to the METRICS_CLIENT_* counters status.php lists beside the
 * server's own. The names are an allow-list (libMetrics::clientParts()); anything else is ignored, since the
 * body decides Redis keys.
 */
class ClientMetrics extends ClientReportEntry {
	public function __construct() {
		parent::__construct('client/metrics', 'JSON', '', array('metrics'));
	}

	public function run($userID, $permissionIsExplicit) {
		$args = $this->getArgs();

		if( !is_array($args['metrics']) )
			throw new RequestException('Body field `metrics` is not an array.');

		// A page sends one of these per load; a page sending more than a report a second for five minutes is
		// looping, and the counters are worth more without it
		if( !$this->withinRateLimit('metrics', 300) )
			return $this->JSONResponse('Reporting too often.', '', true, array('recorded' => 0));

		$recorded = 0;
		foreach( array_slice($args['metrics'], 0, 20) as $metric )
		{
			if( !is_array($metric) || !isset($metric['name']) )
				continue;

			if( libMetrics::recordClient($metric['name'], $metric['count'] ?? 1,
					isset($metric['ms']) && is_numeric($metric['ms']) ? $metric['ms'] : null) )
				$recorded++;
		}

		return $this->JSONResponse('Recorded.', '', true, array('recorded' => $recorded));
	}
}

/**
 * API entry client/error
 *
 * An error a browser hit: window.onerror, an unhandled promise rejection, or a React error boundary. It is
 * written to the error log directory in the same form as a server error, with the same de-duplication, so
 * the admin error list holds both.
 */
class ClientError extends ClientReportEntry {
	public function __construct() {
		parent::__construct('client/error', 'JSON', '',
			array('kind', 'message', 'source', 'line', 'column', 'stack', 'componentStack', 'url'));
	}

	public function run($userID, $permissionIsExplicit) {
		$args = $this->getArgs();

		$kinds = array('script' => 'ERROR_SCRIPT', 'promise' => 'ERROR_PROMISE', 'react' => 'ERROR_REACT');
		$kind = is_string($args['kind']) ? strtolower($args['kind']) : '';
		if( !isset($kinds[$kind]) )
			throw new RequestException('Body field `kind` is not one of script, promise, react.');

		if( !is_string($args['message']) || trim($args['message']) === '' )
			throw new RequestException('Body field `message` is missing.');

		// Counted before the rate limit and the de-duplication, so the count still says how often it is
		// happening when only the first trace was kept
		libMetrics::recordClient($kinds[$kind]);

		if( !$this->withinRateLimit('error', 20) )
			return $this->JSONResponse('Reporting too often.', '', true, array('logged' => false));

		$logged = libError::logClientError($kind, array(
			'message' => $args['message'],
			'source' => $args['source'],
			'line' => $args['line'],
			'column' => $args['column'],
			'stack' => $args['stack'],
			'componentStack' => $args['componentStack'],
			'url' => $args['url'],
			'userAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
		), intval($userID));

		return $this->JSONResponse('Logged.', '', true, array('logged' => $logged));
	}
}

/**
 * A caller with neither a session nor an API key, for the routes which allow one (ApiEntry::allowsGuest).
 * It is nobody: userID 0, no permissions, and no database lookup of its own.
 */
class ApiGuest extends ApiAuth {
	public function load($multiplexOffset = 0){
		$this->userID = 0;
	}

	public function __construct($route){
		foreach (self::$permissionFields as $permissionField)
			$this->permissions[$permissionField] = false;

		$this->cacheKey = 'apiguest'.$route;
	}

	public function getMultiplexOffsetOrNull() { return null; }
}

/**
 * API main call to manage calls.
 */
class Api {

	/**
	 * API entries. Array mapping API entry name to ApiEntry instance.
	 * @var array
	 */
	public $entries;

	public $route;

	public $authClass;

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
		global $Redis, $User;
		// Get route.
		if (!isset($_GET['route']))
			throw new RequestException('No route provided.');

		$this->route = strtolower(trim($_GET['route']));

		if (!isset($this->entries[$this->route]))
			throw new NotImplementedException('Unknown route '.$this->route.'.');

		// Get API entry.
		$apiEntry = $this->entries[$this->route]; /** @var ApiEntry $apiEntry */

		if ( !empty( $User ) && ( $User->type['User'] ?? false ) === true ){
			/**
			 * If the request is an API call using the existing user session, process using the ApiSession class. 
			 */
			$this->authClass = 'ApiSession';
		}elseif ( $apiEntry->allowsGuest() && getBearerToken() == null ){
			/**
			 * A route a page may call while logged out, e.g. to report the errors it hit. Nothing is read or
			 * written on anyone's behalf, so there is nobody to authenticate.
			 */
			$this->authClass = 'ApiGuest';
		}else{
			/**
			 * If the request is an API call using an API key, process using the ApiKey class. 
			 */
			$this->authClass = 'ApiKey';
		}

		$apiAuth = new $this->authClass($this->route);

		// If the args contains a game ID which encodes a multiplex offset, which lets one bot enter orders for several bot user accounts,
		// extract it here and use it to load the correct bot account. (Note has no effect if it's an ApiSession request)
		$multiplexOffset = $apiEntry->getMultiplexOffsetOrNull();
		$apiAuth->load($multiplexOffset);
		// If there was no game ID to get a multiplexed offset from, but this is a multiplexed account, then instead of
		// the $apiEntry setting the $apiAuth multiplexOffset the $apiAuth needs to set the $apiEntry multiplexOffset.
		$apiEntry->setMultiplexOffset($apiAuth->getMultiplexOffsetOrNull()); 
		// The user ID and API entry are now set up with the multiplex offset, so any game IDs returned can be multiplexed.
		
		// Check if request is authorized.
		$permissionIsExplicit = $apiAuth->assertHasPermissionFor($apiEntry);
		// Execute request.
		
		$userID = $apiAuth->getUserID();
		$apiEntry->isSessionAuth = ( $this->authClass === 'ApiSession' );
		$result = $apiEntry->run($userID, $permissionIsExplicit); 
		
		return $result;
	}
}

if( isset(Config::$botsLogFile) && Config::$botsLogFile )
{
	file_put_contents(Config::$botsLogFile,
		date('l jS \of F Y h:i:s A')."\n".
		"-------------------\n".
		print_r($_SERVER,true)."\n".
		"-------------------\n\n"
		, FILE_APPEND);
}

try {
	// Load API object, load API entries, parse API call and print response as a JSON object.
	$api = new Api();

	$api->load(new GetPlayerContext());
	
	$api->load(new JoinGame());
	$api->load(new LeaveGame());
	
	$api->load(new SetOrders());
	$api->load(new ToggleVote());
	$api->load(new SetVote());
	
	$api->load(new SSEAuthentication());

	$api->load(new PushSubscribe());
	$api->load(new PushUnsubscribe());

	$api->load(new SendMessage());
	$api->load(new MessagesSeen());
	
	$api->load(new MarkBackFromLeft());

	$api->load(new ClientMetrics());
	$api->load(new ClientError());

	$api->load(new SandboxCreate());
	$api->load(new SandboxCopy());
	$api->load(new SandboxMoveTurnBack());
	$api->load(new SandboxDelete());

	// Track API call metrics
	$apiStartTime = microtime(true);

	// Reset database metrics before the API call
	if ($DB instanceof MetricsDatabase) {
		$DB->resetMetrics();
	}

	$jsonEncodedResponse = $api->run();

	// Calculate total API call time
	$apiEndTime = microtime(true);
	$apiTimeMs = round(($apiEndTime - $apiStartTime) * 1000);

	// Store metrics in Redis if available
	if ($Redis !== null && $DB instanceof MetricsDatabase) {
		try {
			// Get the route and normalize it for Redis key
			$route = strtoupper(str_replace('/', '_', $api->getRoute()));

			// Get database metrics
			$dbMetrics = $DB->getMetrics();

			// Increment counters and add times in Redis
			$increments = array(
				'METRICS_API_' . $route . '_COUNT' => 1,
				'METRICS_API_' . $route . '_TIME_MS' => $apiTimeMs,
				'METRICS_API_' . $route . '_DB_GET' => $dbMetrics['db_get'],
				'METRICS_API_' . $route . '_DB_PUT' => $dbMetrics['db_put'],
				'METRICS_API_' . $route . '_DB_TIME_MS' => $dbMetrics['db_time_ms'],
			);

			// Track bot API calls separately (only for API key authentication)
			if ($api->authClass === 'ApiKey') {
				$increments['METRICS_API_' . $route . '_BOTCOUNT'] = 1;
			}

			$Redis->incrementMany($increments);
		} catch (Exception $e) {
			// Silently ignore Redis errors to not break the API
		}
	}

	// Set JSON header.
	header('Content-Type: application/json');
	// Print response.
	print $jsonEncodedResponse;

/*
	$apiAuth = new $api->authClass($api->route);
	$userID = $apiAuth->getUserID();
	*/
	if( isset(Config::$botsLogFile) && Config::$botsLogFile )
	{
		$apiEntry = $api->entries[$api->route];
		$multiplexOffset = $apiEntry->getMultiplexOffsetOrNull() ?? -1;
		file_put_contents(Config::$botsLogFile,
			date('l jS \of F Y h:i:s A')."\n".
			"-------------------\n".
			$_SERVER['REQUEST_URI']."\n".
			"-------------------\n".
			"multiplexOffset: $multiplexOffset\n".
			"-------------------\n".
			json_encode($apiEntry->getArgs(), JSON_PRETTY_PRINT)."\n".
			"-------------------\n".
			json_encode(json_decode($jsonEncodedResponse), JSON_PRETTY_PRINT)."\n".
			"-------------------\n\n"
			, FILE_APPEND);
	}
}

// 4xx - User errors - No need to log
catch (RequestException $exc) {
	handleAPIError($exc->getMessage(), 400);
	// trigger_error($exc->getMessage()); // This generates errors like "Invalid phase, expected Retreats got Diplomacy" etc, 
	// might be worth looking into at some point
}
catch (ClientUnauthorizedException $exc) {
	handleAPIError($exc->getMessage(), 401);
	//trigger_error($exc->getMessage());
}
catch (ClientForbiddenException $exc) {
	handleAPIError($exc->getMessage(), 403);
	//trigger_error($exc->getMessage());
}

// 5xx - Server errors
catch (ServerInternalException $exc) {
	handleAPIError($exc->getMessage(), 500);
	trigger_error($exc->getMessage());
}
catch (NotImplementedException $exc) {
	// Unknown route: a client error (bots polling routes that don't exist, injection probes), not a server
	// error, so it is not written to the error log. The web server access log records the request.
	handleAPIError($exc->getMessage(), 404);
}
catch (Exception $exc) {
	handleAPIError("Internal error: ".$exc->getMessage(), 501);
    trigger_error($exc->getMessage());
}

?>