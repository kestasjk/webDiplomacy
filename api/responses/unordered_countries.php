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
 * Games with missing orders JSON response
 * @package webdiplomacy_api
 */
class UnorderedCountries {
	/**
	 * List of (gameID, countryID)
	 * @var array
	 */
	public $value = array();

	/**
	 * Load the countries with missing orders for a given user;
	 */
	function load($userID)
	{
		global $DB;

        // Filter allowed variantIDs
        $apiVariants = implode(', ', \Config::$apiConfig['variantIDs']);

        // Filter allowed gameIDs
        $filterGameClause = '';
        if (!empty(\Config::$apiConfig['restrictToGameIDs'])) {
            $filterGameIDs = implode(', ', \Config::$apiConfig['restrictToGameIDs']);
            $filterGameClause = "AND g.id IN ($filterGameIDs)";
        }

        // Finds powers (gameID, countryID) that
        // 1) Are played by the user linked to the API key making the request (m.userID = $userID)
        // 2) On a map (and a gameID) that is supported by the API
        // 3) Where orders have not yet been submitted (orderStatus is NULL or '') and player is playing (not defeated)
        // 4) Only if the game is still active (i.e. not pre-game, finished, paused, etc.)
        // 5) Only if the turn is < 100 (to avoid any games going past W1950A)

		$countryTabl = $DB->sql_tabl("SELECT m.gameID, m.countryID
                                      FROM wD_Members AS m
                                      LEFT JOIN wD_Games AS g ON ( g.id = m.gameID )
                                      WHERE (m.orderStatus IS NULL OR m.orderStatus = '')
                                            AND m.status = 'Playing'
                                            AND m.userID = $userID
                                            AND g.variantID in ($apiVariants)
                                            " . $filterGameClause . "
                                            AND g.processStatus = 'Not-processing'
                                            AND g.phase IN ('Diplomacy', 'Retreats', 'Builds')
                                            AND g.turn < 100
                                      ORDER BY g.processTime ASC;");

        while( $row = $DB->tabl_hash($countryTabl) )
        {
            array_push($this->value, new GameCountry($row['gameID'], $row['countryID']));
        }
	}

	function toJson($gameIDMultiplexer)
	{
        $multiplexedValues = array();
        foreach($this->value as $gameCountry)
        {
            array_push($multiplexedValues, new GameCountry($gameIDMultiplexer->gameIDToMultiplexedGameID($gameCountry->gameID), $gameCountry->countryID));
        }
		return json_encode($multiplexedValues);
	}

	/**
	 * Finds games where the user is playing and orders have not been submitted yet.
	 */
	function __construct($userID)
	{
		$this->load($userID);
	}

}
