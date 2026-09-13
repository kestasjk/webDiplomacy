<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

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

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * Exception classes shared between the JSON API (api.php) and library code that is reachable both
 * from the API and from the classic web pages (objects/member.php, gamemaster/game.php, ...).
 *
 * api.php maps each class to an HTTP status code. The 4xx classes are returned to the client
 * without being written to the error log, so library code should throw them for conditions that
 * are the caller's problem (bad input, not permitted) rather than a plain Exception, which api.php
 * treats as an internal server error and logs. The classic pages catch them as ordinary Exceptions.
 *
 * @package Base
 */

/**
 * Missing credentials (API key). HTTP 401.
 */
class ClientUnauthorizedException extends Exception {
	public function __construct($message) {
		parent::__construct($message);
	}
}

/**
 * Access denied for the request sender. HTTP 403.
 */
class ClientForbiddenException extends Exception {
	public function __construct($message) {
		parent::__construct($message);
	}
}

/**
 * Server internal error. HTTP 500, logged.
 */
class ServerInternalException extends Exception {
	public function __construct($message) {
		parent::__construct($message);
	}
}

/**
 * The requested route does not exist. HTTP 404.
 */
class NotImplementedException extends Exception {
	public function __construct($message) {
		parent::__construct($message);
	}
}

/**
 * Bad request. HTTP 400.
 */
class RequestException extends Exception {
	public function __construct($message) {
		parent::__construct($message);
	}
}

?>
