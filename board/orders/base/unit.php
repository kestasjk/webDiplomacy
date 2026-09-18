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

require_once(l_r('board/orders/base/territory.php'));
/**
 * Holds unit data for orders, including the territory which the unit is staying at.
 *
 * @package Base
 * @subpackage Game
 */
class Unit {
	/**
	 * Unit ID
	 *
	 * @var int
	 */
	var $id;

	/**
	 * Unit type: 'Army'/'Fleet'
	 *
	 * @var string
	 */
	var $type;

	/**
	 * Occupying territory, with coast data
	 *
	 * @var string
	 */
	var $terrID;

	/**
	 * CountryID owner
	 *
	 * @var string
	 */
	var $countryID;

	/**
	 * Game ID
	 *
	 * @var int
	 */
	var $gameID;

	/**
	 * Occupied Territory object
	 * @var Territory
	 */
	var $Territory;

	/**
	 * Unit rows by ID. The first unit loaded by ID loads every unit in its game with one query, as loading orders
	 * loads each order's unit. Units don't change while orders are being entered, only when the game is processed.
	 * @var array[]
	 */
	private static $rowsByID = array();

	/**
	 * Initialize a unit
	 *
	 * @param int $id Unit ID
	 */
	function __construct($row)
	{
		global $DB;

		if( !is_array($row) )
		{
			$id = intval($row);
			if( !isset(self::$rowsByID[$id]) )
			{
				$tabl = $DB->sql_tabl("SELECT * FROM wD_Units WHERE gameID = (SELECT gameID FROM wD_Units WHERE id = ".$id.")");
				while( $unitRow = $DB->tabl_hash($tabl) )
					self::$rowsByID[$unitRow['id']] = $unitRow;
			}

			$row = self::$rowsByID[$id] ?? false; // false, as the single-row query gave for an unknown ID
		}

		foreach ( $row as $name=>$value )
			$this->{$name} = $value;
	}
}
?>
