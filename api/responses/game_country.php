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

/**
 * Game country, defined as a tuple of (gameID, countryID)
 * @package API
 */
class GameCountry {
    public $gameID;
    public $countryID;

    /**
     * Initialize the tuple (gameID, countryID)
     * @param int $gameID - Game ID
     * @param int $countryID - Country ID
     */
    function __construct($gameID, $countryID) {
        $this->gameID = intval($gameID);
        $this->countryID = intval($countryID);
    }

    /**
     * Encodes this object in JSON
     */
    function toJson() { return json_encode($this); }
}
