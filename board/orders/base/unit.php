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

require_once('board/orders/base/territory.php');
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
	 * Initialize a unit
	 *
	 * @param int $id Unit ID
	 */
	function __construct($row)
	{
		global $DB;

		if( !is_array($row) )
			$row = $DB->sql_hash("SELECT * FROM wD_Units WHERE id = ".$row);

		foreach ( $row as $name=>$value )
			$this->{$name} = $value;
	}
}
?>
