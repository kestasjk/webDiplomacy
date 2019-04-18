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

		$countryTabl = $DB->sql_tabl("SELECT m.gameID, m.countryID
                                      FROM wD_Members AS m
                                      LEFT JOIN wD_Games AS g ON ( g.id = m.gameID )
                                      WHERE (m.orderStatus IS NULL OR m.orderStatus = '')
                                            AND m.userID = $userID
                                            AND g.variantID in (1, 15, 23)
                                            AND g.processStatus = 'Not-processing'
                                            AND g.phase IN ('Diplomacy', 'Retreats', 'Builds');");

        while( $row = $DB->tabl_hash($countryTabl) )
        {
            array_push($this->value, new GameCountry($row['gameID'], $row['countryID']));
        }
	}

	function toJson()
	{
		return json_encode($this->value);
	}

	/**
	 * Finds games where the user is playing and orders have not been submitted yet.
	 */
	function __construct($userID)
	{
		$this->load($userID);
	}

}
