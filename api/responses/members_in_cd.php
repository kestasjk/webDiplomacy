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

defined('IN_CODE') or die('This script can not be run by itself.');

require_once ('config.php');
require_once ('game_country.php');

/**
 * Games in CD JSON response
 * @package webdiplomacy_api
 */
class CountriesInCivilDisorder {
	/**
	 * List of (gameID, countryID)
	 * @var array
	 */
	public $value = array();

	/**
	 * Load the countries in CD;
	 */
	function load()
	{
		global $DB;

		$apiVariants = implode(', ', \Config::$apiConfig['variantIDs']);
		$countryTabl = $DB->sql_tabl("SELECT m.gameID, m.countryID
                                      FROM wD_Members AS m
                                      LEFT JOIN wD_Orders AS o ON ( o.gameID = m.gameID AND o.countryID = m.countryID )
                                      LEFT JOIN wD_Games AS g ON ( g.id = m.gameID )
                                      WHERE NOT o.id IS NULL
                                            AND g.variantID in ($apiVariants)
                                            AND g.phaseMinutes >= 10
                                            AND m.missedPhases > 0
                                            AND g.processStatus = 'Not-processing'
                                            AND g.phase IN ('Diplomacy', 'Retreats', 'Builds')
                                            AND g.processTime >= UNIX_TIMESTAMP()
                                            AND g.processTime <= (UNIX_TIMESTAMP() + 60);");

        while( $row = $DB->tabl_hash($countryTabl) )
        {
            array_push($this->value, new \webdiplomacy_api\GameCountry($row['gameID'], $row['countryID']));
        }
	}

	function toJson()
	{
		return json_encode($this->value);
	}

	/**
	 * Finds games with a country in civil disorder that will process soon
	 */
	function __construct()
	{
		$this->load();
	}

}
