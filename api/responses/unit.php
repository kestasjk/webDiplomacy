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

require_once ('territory.php');

/**
 * Unit JSON response
 * @package API
 */
class Unit extends Territory {

    /**
     * The unit type - (Army, Fleet)
     * @var string
     */
    public $unitType;

    /**
     * Indicates if the unit is retreating (Yes, No)
     * @var string
     */
    public $retreating;

    /**
     * Initialize a unit object
     * @param string $unitType - The unit type
     * @param int $terrID - The territory ID
     * @param int $countryID - The country ID
     * @param string $retreating - Whether the unit is dislodged or not
     */
    function __construct($unitType, $terrID, $countryID, $retreating) {
        parent::__construct($terrID, $countryID);
        $this->unitType = strval($unitType);
        $this->retreating = strval($retreating);
    }
}
