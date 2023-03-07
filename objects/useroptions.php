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
class UserOptions 
{
	/**
	 * User ID
	 * @var int
	 */
	public $id;

	public static $defaults = array(
		'mapUI' => 'Point and click',
        'colourblind' => 'No',
        'darkMode' => 'No',
		'displayUpcomingLive' => 'Yes',
		'showMoves' => 'Yes',
		'orderSort' => 'Convoys Last'
	);

	public static $titles = array(
		'mapUI' => 'Default map UI',
		'colourblind' => 'Colourblindness',
		'darkMode' => 'Dark Theme',
		'displayUpcomingLive' => 'Display upcoming live games',
		'showMoves' => 'Show move arrows on the game map',
		'orderSort' => 'Sort possible orders'
	);

	public static $possibleValues = array(
		'mapUI' => array('Point and click','Dropdown menus'),
		'colourblind' => array('No','Protanope','Deuteranope','Tritanope'),
		'darkMode' => array('Yes', 'No'),
		'displayUpcomingLive' => array('Yes','No'),
		'showMoves' => array('Yes','No'),
		'orderSort' => array('No Sort','Alphabetical','Convoys Last')
	);

	public $value;

	/**
	 * Load the UserOptions object. It is assumed that username is already escaped.
	 */
	function load($cachedOptions = null)
	{
		
		global $DB;
		if( $cachedOptions != null )
		{
			$this->value = $cachedOptions;
		}
		else
		{
			$this->value = UserOptions::$defaults;

			$row = $DB->sql_hash("SELECT * FROM wD_UserOptions WHERE userID=".$this->id );

			if ( ! isset($row['userID']) or ! $row['userID'] )
			{
				// No object was loaded
			} 
			else 
			{
				foreach( $row as $name=>$value )
				{
					if (isset(UserOptions::$defaults[$name]))
						$this->value[$name] = $value;
				}
			}
		}
	}

	function set($newValues)
	{
		global $DB;

		if( $this->id == 0 ) return; // Don't save settings for guest users

		$updates = array();

		// Sanitise array
		foreach(UserOptions::$defaults as $name=>$val)
		{
			if ( ! isset($newValues[$name]) )
			{
				$newValues[$name] = $val;
			}
			if( in_array($newValues[$name], UserOptions::$possibleValues[$name], true ))
			{
			    $updates[] = $name .'='.'"'.$newValues[$name].'"';
			}
		}

		$update = implode(',',$updates);

		// TODO: This might as well be in wD_Users, it's 1:1
		$row = $DB->sql_hash("SELECT * FROM wD_UserOptions WHERE userID=".$this->id );
		if ( ! isset($row['userID']) or ! $row['userID'] ) 
		{
			// create
			$DB->sql_put("INSERT INTO wD_UserOptions (userID) VALUES (".$this->id.")");
		}

		$DB->sql_put("UPDATE wD_UserOptions SET $update WHERE userID=".$this->id);

		// Refetch the saved data
		$this->load();
		// Save the new data to the cache
		$this->saveToCache();
	}

	function asJS()
	{
		return 'var useroptions='. json_encode($this->value).';';
	}

	/**
	 * Initialize a useroptions object
	 *
	 * @param int $id User ID
	 */
	function __construct($id=0, $cachedOptions=null)
	{
		if( $id == 0 )
		{
			// This is a guest user with default options
			$this->id = 0;
			$this->value = UserOptions::$defaults;
		}
		else
		{
			$this->id = intval($id);
			$this->load($cachedOptions);
		}
	}
	
	public static function fetchFromCache($id)
	{
		global $MC;
		if( !isset($MC) ) return false;

		$cachedOptions = $MC->get('userOptions_'.$id);
		if( $cachedOptions === false ) return false;
		
		$cachedOptions = self::unpack($cachedOptions);
		return new UserOptions($id, $cachedOptions);
	}
	public function saveToCache()
	{
		global $MC;
		if( isset($MC) )
		{
			// Try to save to memcache
			$packedOptions = self::pack($this->value);
			$MC->set('userOptions_'.$this->id, $packedOptions, 24*60*60);
		}
	}
	private static function unpack($optionsStr)
	{
		$optionsArr = explode('|',$optionsStr);
		$options = array();
		foreach($optionsArr as $optionStr)
		{
			$option = explode('=',$optionStr);
			$options[$option[0]] = $option[1];
		}
		return $options;
	}
	private static function pack($options)
	{
		$packedOptions = array();
		foreach($options as $k=>$v)
		{
			$packedOptions[] = $k.'='.$v;
		}
		return implode('|', $packedOptions);
	}
}
