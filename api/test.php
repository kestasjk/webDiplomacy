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

namespace webdiplomacy_api;

/**
 * Simple class to test HTPP calls on WebDiplomacy API located at `/api.php`.
 * @package webdiplomacy_api
 */
class ApiCall {
	/**
	 * Available API calls.
	 * @var array
	*/
	static private $routes = array(
		'players/cd' => 'GET',
		'players/missing_orders' => 'GET',
		'game/status' => 'GET',
		'game/orders' => 'JSON'
	);

	/**
	 * URL of WebDiplomacy instance (e.g. `http://mysite.com` ).
	 * API access will be "$siteUrl/api.php" (e.g. `http://mysite.com/api.php` ).
	 * @var string
	 */
	private $siteUrl;

	/**
	 * API access key to be used to make API calls.
	 * @var string
	 */
	private $apiKey;

	/**
	 * Initialize an ApiCall object.
	 * @param string $siteUrl - URL of a WebDiplomacy instance.
	 * @param string $apiKey - API access key/
	 */
	public function __construct($siteUrl, $apiKey) {
		$this->siteUrl = trim($siteUrl, " /\t\n\r\0\x0B");
		$this->apiKey = $apiKey;
	}

	/**
	 * Convert given error info (error message + error HTTP code) into a JSON object
	 * and return it as a JSON-encoded string.
	 */
	static private function errorToJson($message, $code) {
		return json_encode(array(
			'error' => $message,
			'code' => $code
		));
	}

	/**
	 * Make an API call.
	 * @param string $route - API entry.
	 * @param array $params - associative array containing values for API entry parameters.
	 * @return string - JSON-encoded response.
	 */
	private function call($route, $params) {
		// References (to use CURL):
		// https://stackoverflow.com/a/48896992
		// https://codular.com/curl-with-php
		if (!isset(ApiCall::$routes[$route]))
			return $this->errorToJson('Unknown route.', 500);
		$method = ApiCall::$routes[$route];
		$authorization = "Authorization: Bearer ".$this->apiKey;
		$headers = array($authorization);
		$curl = curl_init();
		$url = $this->siteUrl.'/api.php?route='.$route;
		if ($method == 'JSON') {
			$json_string = json_encode($params);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($curl, CURLOPT_POSTFIELDS, $json_string);
			$headers[] = 'Content-Type: application/json';
		} else if ($method == 'POST') {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		} else if (!empty($params)) {
			$paramsArray = array();
			foreach ($params as $key => $value)
				$paramsArray[] = urlencode($key).'='.urlencode($value);
			$url .= '&'.implode('&', $paramsArray);
		}
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, $url);
		$response = curl_exec($curl);
		$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($httpCode != 200)
			$response = $this->errorToJson($response, $httpCode);
		curl_close($curl);
		return $response;
	}

	/**
	 * Make an API call to entry `players/cd`.
	 */
	public function listGamesWithPlayersInCD() {
		return $this->call('players/cd', array());
	}

	/**
	 * Make an API call to entry `players/missing_orders`.
	 */
	public function listGamesWithMissingOrders() {
		return $this->call('players/missing_orders', array());
	}

	/**
	 * Make an API call to entry `game/status`.
	 */
	public function getGamesStates($gameID, $countryID) {
		return $this->call('game/status', array('gameID' => $gameID, 'countryID' => $countryID));
	}

	/**
	 * Make an API call to entry `game/orders`.
	 */
	public function setOrders($gameID, $turn, $phase, $countryID, $orders, $ready = null) {
		return $this->call(
			'game/orders',
			array(
				'gameID' => $gameID,
				'turn' => $turn,
				'phase' => $phase,
				'countryID' => $countryID,
				'orders' => $orders,
				'ready' => $ready
			));
	}
}

/**
 * Usage example of ApiCall.
 * Make call to all current API entries.
*/
$output = array();
$apiCall = new ApiCall('http://127.0.0.1/www/private-webDiplomacy', 'xispFJujBFNwGzueLp9baDTAKfh9RIr6kYWavwTzfjS0SPZHf1PnHmtg5Qy4JU70vwNOJbFGgPjmrXR2');
$output['players/cd'] = json_decode($apiCall->listGamesWithPlayersInCD());
$output['players/missing_orders'] = json_decode($apiCall->listGamesWithMissingOrders());
$output['game/status'] = json_decode($apiCall->getGamesStates(1, 2));
$output['game/orders'] = json_decode($apiCall->setOrders(
	1,
	1,
	'Builds',
	2,
	array(
		array(
			'terrID' => 46,
			'type' => 'Build army',
			'fromTerrID' => null,
			'toTerrID' => 46,
			'viaConvoy' => null
		)
	), 'No'));
header('Content-Type: application/json');
print json_encode($output);
?>

