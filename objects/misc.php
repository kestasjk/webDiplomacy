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

/**
 * An object which reads/writes global named integers in the misc table. Used to 
 * cache often used stats, to track the database version compared to the code 
 * version, and set dynamic configuration flags (such as whether the server is in 
 * panic mode)
 * 
 * @package Base
 */
class Misc
{
	private $updated = array();
	private $data = array();
	
	public function __construct()
	{
		$this->read();
	}
	
	public function __get($name)
	{
		// Open was renamed to Joinable due to the verb/noun confusion in translations
		if( $name == 'GamesJoinable' )
			$name = 'GamesOpen';
		
		return $this->data[$name];
	}
	
	public function __set($name, $value)
	{
		$this->data[$name] = $value;
		$this->updated[$name] = $name;
	}
	
	public function write()
	{
		global $DB;
		
		foreach($this->updated as $name)
		{
			$DB->sql_put("UPDATE wD_Misc SET value = ".$this->data[$name]." WHERE name = '".$name."'");
			unset($this->updated[$name]);
		}
	}
	
	public function read()
	{
		global $DB;
		
		$tabl = $DB->sql_tabl("SELECT name, value FROM wD_Misc");
		while ( list($name, $value) = $DB->tabl_row($tabl) )
		{
			$this->data[$name] = $value;
		}
		$this->updated=array();
	}
}

?>