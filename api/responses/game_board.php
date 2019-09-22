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
require_once('api/responses/unit.php');

/**
 * Game Board - Board with units that can be moved to return correct unit location
 * @package API
 */
class GameBoard {
    private $board;                     // Mapping [countryID][terrID][unitType] = unit count (0 or 1)

    /**
     * GameBoard constructor.
     */
    public function __construct() {
        $this->board = array();
    }

    /**
     * Adds a unit to the board
     * @param int $countryID - The countryID owning the unit (e.g. 1)
     * @param int $terrID - The territory ID where the unit is located
     * @param string $unitType - The unit type ('Army' or 'Fleet')
     */
    public function add($countryID, $terrID, $unitType) {
        if (!isset($this->board[$countryID][$terrID][$unitType])) {
            $this->board[$countryID][$terrID][$unitType] = 0;
        }
        ++$this->board[$countryID][$terrID][$unitType];
    }

    /**
     * Removes a unit from the board
     * @param int $countryID - The countryID owning the unit (e.g. 1)
     * @param int $terrID - The territory ID where the unit is located
     * @param string $unitType - The unit type ('Army' or 'Fleet' or '')
     */
    public function remove($countryID, $terrID, $unitType) {
        if (!$unitType) {
            $this->remove($countryID, $terrID, 'Army');
            $this->remove($countryID, $terrID, 'Fleet');
            return;
        }

        // Reducing unit count
        if (isset($this->board[$countryID][$terrID][$unitType])) {
            --$this->board[$countryID][$terrID][$unitType];
            if ($this->board[$countryID][$terrID][$unitType] == 0) {
                unset($this->board[$countryID][$terrID][$unitType]);
            }
        }

        // Unsetting empty arrays
        if (isset($this->board[$countryID][$terrID]) && empty($this->board[$countryID][$terrID])) {
            unset($this->board[$countryID][$terrID]);
        }
        if (isset($this->board[$countryID]) && empty($this->board[$countryID])) {
            unset($this->board[$countryID]);
        }
    }

    /**
     * Moves a unit on the board
     * @param int $countryID - The countryID owning the unit (e.g. 1)
     * @param string $unitType - The unit type ('Army' or 'Fleet' or '')
     * @param int $fromTerrID - The territory ID from where the unit is moving (i.e. source)
     * @param int $toTerrID - The territory ID to where the unit is moving (i.e. destination)
     */
    public function move($countryID, $unitType, $fromTerrID, $toTerrID) {
        $this->remove($countryID, $fromTerrID, $unitType);
        $this->add($countryID, $toTerrID, $unitType);
    }

    /**
     * Returns the current units on the board
     * @throws ServerInternalException
     */
    public function getUnits() {
        $units = array();
        foreach ($this->board as $countryID => $countryData) {
            foreach ($countryData as $terrID => $terrData) {
                foreach ($terrData as $unitType => $unitCount) {
                    if ($unitCount != 1) {
                        throw new ServerInternalException(sprintf("Internal error. Got %d units for countryID %d, terrID %d", $unitCount, $countryID, $terrID));
                    }
                    $units[] = new Unit($unitType, $terrID, $countryID, 'No');
                }
            }
        }
        return $units;
    }
}
