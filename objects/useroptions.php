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

defined('IN_CODE') or die('This script can not be run by itself.');


/**
 * Holds user options and defaults.
 * 
 *
 * @package Base
 */
class UserOptions {
	/**
	 * User ID
	 * @var int
	 */
	public $id;

	public static $defaults = array(
        	'colourblind' => 'No',
		'displayUpcomingLive' => 'Yes',
		'showMoves' => 'Yes'
	);

	public static $titles = array(
		'colourblind' => 'Colourblindness',
		'displayUpcomingLive' => 'Display upcoming live games',
		'showMoves' => 'Show move arrows on the game map'
	);

	public static $possibleValues = array( 
		'colourblind' => array('No','Protanope','Deuteranope','Tritanope'),
		'displayUpcomingLive' => array('Yes','No'),
		'showMoves' => array('Yes','No')
	);

	public $value;

	/**
	 * Load the UserOptions object. It is assumed that username is already escaped.
	 */
	function load()
	{
		global $DB;
		$this->value = UserOptions::$defaults;

		$row = $DB->sql_hash("SELECT * FROM wD_UserOptions WHERE userID=".$this->id );

		if ( ! isset($row['userID']) or ! $row['userID'] ) 
		{
			// No object was loaded
		} else {
			foreach( $row as $name=>$value )
			{
				if (isset(UserOptions::$defaults[$name])) 
					$this->value[$name] = $value;
			}
		}
	}

	function set($newValues)
	{
		global $DB;
		
		$updates = array();

		// Sanitise array
		foreach(UserOptions::$defaults as $name=>$val)
		{
			if ( ! isset($newValues[$name]) )
			{
				$newValues[$name] = UserOptions::$defaults[$name];
			}
			if( in_array($newValues[$name], UserOptions::$possibleValues[$name], true )) 
			{
			     $updates[] = $name .'='.'"'.$newValues[$name].'"';   
			}
		}
		
		$update = implode(',',$updates);		

		$row = $DB->sql_hash("SELECT * FROM wD_UserOptions WHERE userID=".$this->id );
		if ( ! isset($row['userID']) or ! $row['userID'] ) {
			// create
			$DB->sql_put("INSERT INTO wD_UserOptions (userID) VALUES (".$this->id.")");
		} 
		
		$DB->sql_put("UPDATE wD_UserOptions SET $update WHERE userID=".$this->id);
	}

	function asJS()
	{
		return 'var useroptions='. json_encode($this->value).';';
	}
	static function defaultJS()
	{
		return 'var useroptions='. json_encode(UserOptions::$defaults).';';
	}
	
	/**
	 * Initialize a useroptions object
	 *
	 * @param int $id User ID
	 */
	function __construct($id, $username=false)
	{
		$this->id = intval($id);
		$this->load();
	}                  

}
