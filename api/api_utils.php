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

require_once('api/exceptions.php');

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
 * Get "Authorization" header
 * Reference: https://stackoverflow.com/a/40582472
 * */
function getAuthorizationHeader() {
    $headers = null;

    // Checking for Authorization header directly
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER["Authorization"]);

    // Nginx - fast CGI use 'HTTP_AUTHORIZATION'
    } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);

    // Apache 2
    // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
    } elseif (function_exists('apache_request_headers')) {
        $rawRequestHeaders = apache_request_headers();
        $requestHeaders = array();
        foreach ($rawRequestHeaders as $key => $value) {
            $requestHeaders[ucwords($key)] = $value;
        }
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }

    return $headers;
}

/**
 * Get "Bearer" access token from "Authorization" header
 * Reference: https://stackoverflow.com/a/40582472
 * */
function getBearerToken() {
    $headers = getAuthorizationHeader();
    if (!empty($headers)) {
        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) { return $matches[1]; }
    }
    return null;
}

/**
 * Return a proper version of API entry route string.
 * @param string $route - The name of the route to clean
 * @return string
 */
function cleanRoute($route) {
    return strtolower(trim($route, " /\t\n\r\0\x0B"));
}
