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
require_once('config.php');
require_once('global/definitions.php');
require_once('locales/layer.php');
require_once('objects/database.php');
require_once('board/orders/orderinterface.php');
require_once('api/responses/members_in_cd.php');
require_once('api/responses/unordered_countries.php');
require_once('api/responses/game_state.php');
require_once('objects/game.php');
require_once('lib/cache.php');
require_once('lib/html.php');
require_once('lib/time.php');
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
		$Variant = libVariant::loadFromGameID($gameID);
		libVariant::setGlobals($Variant);
		$gameRow = $DB->sql_hash('SELECT * from wD_Games WHERE id = '.$gameID);
		if (!$gameRow)
			throw new RequestException('Invalid game ID');
		return new Game($gameRow);
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
		$countryID = $args['countryID'];
		if ($gameID === null || !ctype_digit($gameID))
			throw new RequestException('Invalid game ID: '.$gameID);
		if ($countryID == null || !ctype_digit($countryID))
			throw new RequestException('Invalid country ID.');
		if (!empty(Config::$apiConfig['restrictToGameIDs']) && !in_array($gameID, Config::$apiConfig['restrictToGameIDs']))
		    throw new ClientForbiddenException('Game ID is not in list of gameIDs where API usage is permitted.');
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
	public function run($userID, $permissionIsExplicit) {
		global $DB;
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

		// Processing game
        if ($orderInterface->orderStatus->Ready && !$previousReadyValue) {
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
            {
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
        }

        // Returning current orders
		return json_encode($currentOrders);
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
	 * @return bool - a Boolean to indicate if permission is explicitly granted from API key (true)
	 * or if, either no permission is need, or permission is granted because user is a game member (false).
	 * @throws ServerInternalException
	 * @throws ClientForbiddenException
	 * @throws RequestException
	 */
	public function assertHasPermissionFor(ApiEntry $apiEntry) {
		$permissionIsExplicit = false;
		$permissionField = $apiEntry->getPermissionField();

		if ($permissionField == '') {
			// No permission field.
			// If game ID is required, then user must be member of this game.
			// Otherwise, any user can call this function.
			if ($apiEntry->requiresGameID() && !isset($apiEntry->getAssociatedGame()->Members->ByUserID[$this->userID]))
				throw new ClientForbiddenException(sprintf(
					"Access denied. User is not member of associated game. - API Key: %s - Game ID: %s - User ID: %s",
					substr($this->apiKey, 0, 8),
					$apiEntry->getAssociatedGame()->id,
					$this->userID));
		} else {
			// Permission field available.
			if (!in_array($permissionField, ApiKey::$permissionFields))
				throw new ServerInternalException('Unknown permission name');

			// Permission field must be set for this user.
			// Otherwise, game ID must be required and user must be member of this game.
			if ($this->permissions[$permissionField]) {
				$permissionIsExplicit = true;
			} else {
				if (!$apiEntry->requiresGameID())
					throw new ClientForbiddenException(sprintf(
						"Permission denied. - API Key: %s - UserID: %s - Missing permission: %s",
						substr($this->apiKey, 0, 8),
						$this->userID,
						$permissionField));

				if (!isset($apiEntry->getAssociatedGame()->Members->ByUserID[$this->userID]))
					throw new ClientForbiddenException(sprintf(
						"Permission denied, and user is not member of associated game. - API Key: %s - Game ID: %s - User ID: %s - Missing permission: %s",
						substr($this->apiKey, 0, 8),
						$apiEntry->getAssociatedGame()->id,
						$this->userID,
						$permissionField));
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
		$permissionIsExplicit = $apiKey->assertHasPermissionFor($apiEntry);
		// Execute request.
		return $apiEntry->run($apiKey->getUserID(), $permissionIsExplicit);
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
	$api->load(new GetGamesStates());
	$api->load(new SetOrders());
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