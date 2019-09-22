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
define('IN_CODE', 1);

require_once('config.php');
require_once('global/definitions.php');
require_once('locales/layer.php');
require_once('objects/database.php');
require_once('objects/game.php');
require_once('objects/member.php');
require_once('objects/user.php');
require_once('lib/cache.php');
require_once('lib/html.php');
require_once('lib/time.php');

require_once('api/api_key.php');
require_once('api/api_route.php');
require_once('api/api_utils.php');
require_once('api/exceptions.php');

require_once('api/routes/game_message.php');
require_once('api/routes/game_orders.php');
require_once('api/routes/game_ready.php');
require_once('api/routes/game_status.php');
require_once('api/routes/game_vote.php');
require_once('api/routes/players_cd.php');
require_once('api/routes/players_missing_orders.php');
require_once('api/routes/players_my_games.php');

USE API\ApiKey;
USE API\ApiRoute;
USE API\ClientForbiddenException;
use API\ClientUnauthorizedException;
use API\NotImplementedException;
use API\RequestException;
use API\ServerInternalException;

use function API\getBearerToken;
use function API\handleAPIError;

$DB = new Database();

/**
 * API main call to manage calls.
 */
class Api {

    /**
     * API routes. Array mapping API route name to ApiRoute instance.
     * @var array
     */
    private $routes;

    /**
     * API constructor.
     */
    public function __construct() {
        $this->routes = array();
    }

    /**
     * Load an API route.
     * @param ApiRoute $apiRoute - The API route to load
     */
    public function load(ApiRoute $apiRoute) {
        $this->routes[$apiRoute->route] = $apiRoute;
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
        if (!isset($_GET['route'])) { throw new RequestException('No route provided.'); }
        $route = strtolower(trim($_GET['route']));
        if (!isset($this->routes[$route])) { throw new NotImplementedException('Unknown route.'); }

        // Get user ID.
        $apiKeyString = getBearerToken();
        if ($apiKeyString == null) { throw new ClientUnauthorizedException('No API key provided.'); }

        // Get API Route.
        $apiKey = new ApiKey($apiKeyString);
        $apiRoute = $this->routes[$route];                  /** @var ApiRoute $apiRoute */
        return $apiRoute->run($apiKey);                     // Checks for authorization, then runs
    }
}

try {
    if (!property_exists('Config', 'apiConfig') || !Config::$apiConfig['enabled']) {
        http_response_code(404);
        die('API is not enabled.');
    }

    // Building list of valid API routes
    $api = new Api();
    $api->load(new API\Route\GameMessage());                // To send a game message
    $api->load(new API\Route\GameOrders());                 // To submit orders
    $api->load(new API\Route\GameReady());                  // To mark the orders as ready or not
    $api->load(new API\Route\GameStatus());                 // To retrieve the game status (incl. your messages)
    $api->load(new API\Route\GameVote());                   // To vote to draw, pause, cancel, or conceded game
    $api->load(new API\Route\PlayersCD());                  // To retrieve games with players in CD status (Left)
    $api->load(new API\Route\PlayersMissingOrders());       // To retrieve a list of your games with missing orders
    $api->load(new API\Route\PlayersMyGames());             // To retrieve a list of your games with certain filters

    // Running and returning JSON
    $jsonEncodedResponse = $api->run();
    header('Content-Type: application/json');
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
