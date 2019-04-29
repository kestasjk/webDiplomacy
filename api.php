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

ini_set("log_errors", 1);
ini_set("error_log", "api-error.log");
define('IN_CODE', 1);
require_once('config.php');
require_once('global/definitions.php');
require_once('locales/layer.php');
require_once('objects/database.php');
require_once('board/orders/orderinterface.php');
require_once('api/responses/members_in_cd.php');
require_once('api/responses/unordered_countries.php');
require_once('api/responses/game_state.php');
require_once('objects/game.php');
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
 * Generate an error page with given HTTP error code and given message printed as a plain text.
 * @param string $message - Error message.
 * @param int $errorCode - HTTP error code for this error.
 */
function fatalError($message, $errorCode) {
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

	/**
	 * Return Game object for game associated to this API entry call.
	 * To get associated game, API entry must expect a parameter named `gameID`.
	 * @return Game
	 * @throws RequestException - if no gameID field in requirements, or if no valid game ID provided.
	 */
	public function getAssociatedGame() {
		global $DB;
		if (!in_array('gameID', $this->requirements))
			throw new RequestException('No game ID available for this request.');
		$args = $this->getArgs();
		$gameID = $args['gameID'];
		if ($gameID == null)
			throw new RequestException('Game ID not provided.');
		$gameID = intval($gameID);
		$variant = libVariant::loadFromGameID($gameID);
		libVariant::setGlobals($variant);
		$gameRow = $DB->sql_hash('SELECT * from wD_Games WHERE id = '.$gameID);
		if (!$gameRow)
			throw new RequestException('Invalid game ID');
		return new Game($gameRow);
	}

	/**
	 * Process API key. To override in derived classes.
	 * @param int $userID - ID of user who makes API call.
	 */
	abstract public function run($userID);
}

/**
 * API entry players/cd
 */
class ListGamesWithPlayersInCD extends ApiEntry {
	public function __construct() {
		parent::__construct('players/cd', 'GET', 'listGamesWithPlayersInCD', array());
	}
	public function run($userID) {
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
	public function run($userID) {
		$unorderedCountries = new \webdiplomacy_api\UnorderedCountries($userID);
		return $unorderedCountries->toJson();
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
	public function run($userID) {
		$args = $this->getArgs();
		$gameID = $args['gameID'];
		$countryID = $args['countryID'];
		if ($gameID === null || !ctype_digit($gameID))
			throw new RequestException('Invalid game ID: '.$gameID);
		if ($countryID == null || !ctype_digit($countryID))
			throw new RequestException('Invalid country ID.');
		$gameState = new \webdiplomacy_api\GameState(intval($gameID), intval($countryID));
		return $gameState->toJson();
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
	public function run($userID) {
		global $DB;
		$args = $this->getArgs();
		$gameID = $args['gameID'];
		$turn = $args['turn'];
		$phase = $args['phase'];
		$countryID = $args['countryID'];
		$orders = $args['orders'];
		$ready = $args['ready'];
		if (!is_array($orders))
			throw new RequestException('Body field `orders` is not an array.');
		if ($ready && (!is_string($ready) || !in_array($ready, array('Yes', 'No'))))
			throw new RequestException('Body field `ready` is not either `Yes` or `No`.');
		if ($countryID != null) {
			$countryID = intval($countryID);
		}
		$game = $this->getAssociatedGame();
		if (!isset($game->Members->ByUserID[$userID]))
			throw new ClientForbiddenException('User is not member of this game.');
		$member = $game->Members->ByUserID[$userID]; /** @var Member $member */
		$memberID = $member->id;
		if ($countryID == null)
			$countryID = $member->countryID;
		else if ($countryID != $member->countryID)
			throw new ClientForbiddenException('User '.$userID.' not allowed to control country '.$countryID.'.');
		if ($turn == null)
			$turn = $game->turn;
		else if ($turn != $game->turn)
			throw new RequestException('Invalid turn, expected `'.$game->turn.'`, got `'.$turn.'`.');
		if ($phase == null)
			$phase = $game->phase;
		else if ($phase != $game->phase)
			throw new RequestException('Invalid phase, expected `'.$game->phase.'`, got `'.$phase.'`.');
		$territoryToOrder = array();
		$orderToTerritory = array();
		$updatedOrders = array();
		$sql = 'SELECT wD_orders.id AS orderID, wD_units.terrID AS terrID FROM wD_orders
				LEFT JOIN wD_units ON (wD_orders.gameID = wD_units.gameID AND wD_orders.countryID = wD_units.countryID AND wD_orders.unitID = wD_units.id) 
				WHERE wD_orders.gameID = '.$gameID.' AND wD_orders.countryID = '.$countryID;
		$res = $DB->sql_tabl($sql);
		while ($row = $DB->tabl_hash($res)) {
			$orderID = $row['orderID'];
			$terrID = $row['terrID'];
			$orderToTerritory[$orderID] = $terrID;
			// Order may not be associated to a territory ID in Builds phase.
			if ($terrID !== null)
				$territoryToOrder[$terrID] = $orderID;
		}
		foreach ($orders as $order) {
			$newOrder = array();
			foreach (array('terrID', 'type', 'fromTerrID', 'toTerrID', 'viaConvoy') as $bodyField) {
				if (!array_key_exists($bodyField, $order))
					throw new RequestException('Missing order info: ' . $bodyField);
				$newOrder[$bodyField] = $order[$bodyField];
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
			$newOrder['id'] = $territoryToOrder[$order['terrID']];
			$updatedOrders[] = $newOrder;
		}

		$orderInterface = new OrderInterface(
			$gameID,
			$game->variantID,
			$userID,
			$memberID,
			$turn,
			$phase,
			$countryID,
			$member->orderStatus,
			null,
			false
		);
		$orderInterface->load();
		$orderInterface->set(json_encode($updatedOrders));
		$results = $orderInterface->validate();

		if ($results['invalid'])
			throw new RequestException('Found some invalid orders.');

		$orderInterface->writeOrders();
		if ($ready)
			$orderInterface->orderStatus->Ready = ($ready == 'Yes');

		$orderInterface->writeOrderStatus();
		$DB->sql_put("COMMIT");
		$territoryResults = array();
		foreach ($results['orders'] as $orderID => $info) {
			$territoryResults[] = array(
				'terrID' => $orderToTerritory[$orderID],
				'status' => $info['status'],
				'changed' => $info['changed']
			);
		}
		return json_encode(array(
			'results' => $territoryResults,
			'status' => ''.$orderInterface->orderStatus
		));
	}
}

/**
 * Class to manage an API key and check associated permissions.
 */
class ApiKey {
	/**
	 * API access key.
	 * @var string
	 */
	private $apiKey;

	/**
	 * User ID associated to API key.
	 * @var int
	 */
	private $userID;

	/**
	 * Permissions associated to this API key.
	 * Associative array mapping permission name to a boolean.
	 * @var array
	 */
	private $permissions;

	/**
	 * List of current permissions names in database table `wD_ApiPermissions`.
	 */
	static private $permissionFields = array(
		'getStateOfAllGames',
		'submitOrdersForUserInCD',
		'listGamesWithPlayersInCD'
	);

	/**
	 * Load API key.
	 * @throws ClientUnauthorizedException - if associated user cannot be found.
	 */
	private function load() {
		global $DB;
		$rowUserId = $DB->sql_hash("SELECT userID from wD_ApiKeys WHERE apiKey = '".$DB->escape($this->apiKey)."'");
		if (!$rowUserId)
			throw new ClientUnauthorizedException('No user associated to this API key.');
		$this->userID = intval($rowUserId['userID']);
		$permissionRow = $DB->sql_hash("SELECT * FROM wD_ApiPermissions WHERE userID = ".$this->userID);
		if ($permissionRow) {
			foreach (ApiKey::$permissionFields as $permissionField) {
				if ($permissionRow[$permissionField] == 'Yes')
					$this->permissions[$permissionField] = true;
			}
		}
	}

	/**
	 * Initialize API key object.
	 * @param string $apiKey - API access key.
	 * @throws ClientUnauthorizedException - If associated user cannot be found.
	 */
	public function __construct($apiKey) {
		$this->apiKey = $apiKey;
		$this->userID = null;
		$this->permissions = array();
		foreach (ApiKey::$permissionFields as $permissionField)
			$this->permissions[$permissionField] = false;
		$this->load();
	}

	/**
	 * Check if this API key is allowed to call given API entry.
	 * Throw an exception if any problem occurs, meaning that either API key does not have enough permissions, or we are unable to check it.
	 * @param ApiEntry $apiEntry - instance of API entry object to check.
	 * @throws ServerInternalException
	 * @throws ClientForbiddenException
	 * @throws RequestException
	 */
	public function assertHasPermissionFor(ApiEntry $apiEntry) {
		$permissionField = $apiEntry->getPermissionField();

		if ($permissionField == '') {
			// No permission field.
			// If game ID is required, then user must be member of this game.
			// Otherwise, any user can call this function.
			if ($apiEntry->requiresGameID() && !isset($apiEntry->getAssociatedGame()->Members->ByUserID[$this->userID]))
				throw new ClientForbiddenException('Access denied. User is not member of associated game.');
		} else {
			// Permission field available.
			if (!in_array($permissionField, ApiKey::$permissionFields))
				throw new ServerInternalException('Unknown permission name');

			// Permission field must be set for this user.
			// Otherwise, game ID must be required and user must be member of this game.
			if (!$this->permissions[$permissionField]) {
				if (!$apiEntry->requiresGameID())
					throw new ClientForbiddenException("Permission denied.");

				if (!isset($apiEntry->getAssociatedGame()->Members->ByUserID[$this->userID]))
					throw new ClientForbiddenException('Permission denied, and user is not member of associated game.');
			}
		}
	}

	/**
	 * Return associated user ID.
	 */
	public function getUserID() {
		return $this->userID;
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
	 * Run API. Parse call and return a response as a JSON-encoded string.
	 * @return string
	 * @throws ClientForbiddenException
	 * @throws ClientUnauthorizedException
	 * @throws NotImplementedException
	 * @throws RequestException
	 * @throws ServerInternalException
	 */
	public function run() {
		// Get route.
		if (!isset($_GET['route']))
			throw new RequestException('No route provided.');
		$route = strtolower(trim($_GET['route']));
		if (!isset($this->entries[$route]))
			throw new NotImplementedException('Unknown route.');
		// Get user ID.
		$apiKeyString = getBearerToken();
		if ($apiKeyString == null)
			throw new ClientUnauthorizedException('No API key provided.');
		// Get API entry.
		$apiEntry = $this->entries[$route]; /** @var ApiEntry $apiEntry */
		$apiKey = new ApiKey($apiKeyString);
		// Check if request is authorized.
		$apiKey->assertHasPermissionFor($apiEntry);
		// Execute request.
		return $apiEntry->run($apiKey->getUserID());
	}
}

try {
	// Load API object, load API entries, parse API call and print response as a JSON object.
	$api = new Api();
	$api->load(new ListGamesWithPlayersInCD());
	$api->load(new ListGamesWithMissingOrders());
	$api->load(new GetGamesStates());
	$api->load(new SetOrders());
	$jsonEncodedResponse = $api->run();
	// Set JSON header.
	header('Content-Type: application/json');
	// Print response.
	print $jsonEncodedResponse;
}
catch (RequestException $exc) {
	fatalError($exc->getMessage(), 400);
}
catch (ClientUnauthorizedException $exc) {
	fatalError($exc->getMessage(), 401);
}
catch (ClientForbiddenException $exc) {
	fatalError($exc->getMessage(), 403);
}
catch (ServerInternalException $exc) {
	fatalError($exc->getMessage(), 500);
}
catch (NotImplementedException $exc) {
	fatalError($exc->getMessage(), 501);
}
catch (Exception $exc) {
	fatalError("Internal error: ".$exc->getMessage(), 501);
}

?>