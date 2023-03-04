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
 * Active games for this user
 * 
 * Note: copied with modification from unordered_countries.php
 * 
 * @package webdiplomacy_api
 */
class ActiveGames {
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
        // 3) Only if the game is still active (i.e. not pre-game, finished, paused, etc.)

		$countryTabl = $DB->sql_tabl("SELECT m.gameID, m.countryID, m.orderStatus, m.newMessagesFrom, m.unitNo, g.turn, g.phase, g.name, g.processTime, g.phaseMinutes, g.pressType, g.variantID
                                      FROM wD_Members AS m
                                      LEFT JOIN wD_Games AS g ON ( g.id = m.gameID )
                                      WHERE m.status = 'Playing'
                                            AND m.userID = $userID
                                            AND g.variantID in ($apiVariants)
                                            " . $filterGameClause . "
                                            AND g.phase IN ('Diplomacy', 'Retreats', 'Builds')
                                      ORDER BY g.processTime ASC;");

        while( $row = $DB->tabl_hash($countryTabl) )
        {
            array_push($this->value, [
                'gameID' => intval($row['gameID']),
                'countryID' => intval($row['countryID']),
                'orderStatus' => $row['orderStatus'],
                'pressType' => $row['pressType'],
                'newMessagesFrom' => (strlen($row['newMessagesFrom']) ? array_map('intval', explode(',', $row['newMessagesFrom'])) : array()),
                'unitNo' => intval($row['unitNo']),
                'name' => $row['name'],
                'turn' => intval($row['turn']),
                'phase' => $row['phase'],
                'processTime' => intval($row['processTime']),
                'phaseMinutes' => intval($row['phaseMinutes']),
                'variantID' => intval($row['variantID']),
            ]);
        }
	}

	function toJson($gameIDMultiplexer)
	{
        $multiplexedValue = array();
        foreach($this->value as $gameData)
        {
            $newGameData = array();
            foreach($gameData as $k=>$v)
            {
                if ( $k == 'gameID' )
                    $v = $gameIDMultiplexer->gameIDToMultiplexedGameID($v);
                $newGameData[$k] = $v;
            }
            array_push($multiplexedValue, $newGameData);
        }
		return json_encode(['games' => $multiplexedValue]);
	}

	/**
	 * Finds games where the user is playing and orders have not been submitted yet.
	 */
	function __construct($userID)
	{
		$this->load($userID);
	}

}
