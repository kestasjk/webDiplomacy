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
 * Game Step - Mapping of [turn][phase] = phase data
 * @package API
 */
class GameSteps {
    private $steps;                     // Mapping [turn][phase] = data

    /**
     * GameSteps Constructor
     */
    public function __construct() {
        $this->steps = array();
    }

    /**
     * Add a data for a turn and a phase.
     * @param int $turn - The turn (e.g. 1)
     * @param string $phase - The phase ('Diplomacy', 'Retreats', 'Builds')
     * @param array $data - The data for the turn and phase
     */
    public function set($turn, $phase, $data) {
        $this->steps[$turn][$phase] = $data;
    }

    /**
     * Get data associated to a turn and phase.
     * If turn and phase is not currently associated to any data,
     * then associate default data to turn and phase and return default data.
     * @param int $turn - The turn (e.g. 1)
     * @param string $phase - The phase ('Diplomacy', 'Retreats', 'Builds')
     * @param array $defaultData - The default data to set if the turn/phase isn't set
     * @return array - The value set for the turn/phase
     */
    public function get($turn, $phase, $defaultData) {
        if (!isset($this->steps[$turn]) || !isset($this->steps[$turn][$phase])) {
            $this->steps[$turn][$phase] = $defaultData;
        }
        return $this->steps[$turn][$phase];
    }

    /**
     * Return an array of all collected game steps.
     * A game step will be itself an array with 3 elements: turn number, phase name and associated data.
     */
    public function toArray() {
        $arraySteps = array();
        foreach ($this->steps as $turn => $phases) {
            foreach ($phases as $phase => $data) {
                array_push($arraySteps, array($turn, $phase, $data));
            }
        }

        // Sorting by turn, then by phases
        usort($arraySteps, function($step1, $step2) {
            $phaseRanks = array('Diplomacy' => 0, 'Retreats' => 1, 'Builds' => 2);
            list($turn1, $phase1, $_) = $step1;
            list($turn2, $phase2, $_) = $step2;
            $t = intval($turn1) - intval($turn2);
            if ($t == 0)
                $t = $phaseRanks[$phase1] - $phaseRanks[$phase2];
            return $t;
        });

        return $arraySteps;
    }
}
