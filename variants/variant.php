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
 * The class which all variants inherit. It represents the layer between requests for game-related
 * objects in the main code and responses which may come from the main code or may come from a
 * variant.
 *
 * So in board.php a panelGameBoard might be requested, but because all object requests are routed
 * through here any variant can return its own altered/extended panelGameBoard which alters functionality.
 *
 * It was renamed to WDVariant from Variant for 0.99, because in Windows IIS Variant is a reserved name.
 *
 * @author kestasjk
 *
 */
abstract class WDVariant {

	/**
	 * An array where the keys are the class names that this variant wants replaced with its own
	 * objects, and the values are the name of the variant which requested the replacement. (So
	 * that, say, FleetRome, which extends Classic and requires Classic's drawMap, isn't mistakenly
	 * thought to have its own drawMap which it wants to use.)
	 *
	 * @var array[$classname]=$variantName;
	 */
	public $variantClasses;

	/**
	 * Variant ID. Stored in game records and references in config.php. 1-255
	 * @var int
	 */
	public $id;

	/**
	 * Map ID, may be shared among variants. Territories are not referenced by variantID but by mapID.
	 * For map-variants this will usually be the same as the variantID for simplicity.
	 * @var int
	 */
	public $mapID;

	/**
	 * The version of the variant code. This is loaded into $cacheVersion on initialize, so that
	 * old caches can be detected and wiped. If this isn't set this functionality is not used.
	 * @var int
	 */
	public $codeVersion;

	/**
	 * The version number as loaded from the cache. If this is less than the code version the
	 * variant will be re-initialized.
	 * @var int
	 */
	public $cacheVersion;

	/**
	 * The simple truncated variant name. Determines the variant folder and naming scheme of variant classes.
	 * @var string
	 */
	public $name;

	/**
	 * Descriptive variables, appear in the game panel and new game page.
	 * @var string
	 */
	public $fullName, $description, $author;

	/**
	 * An array of country names. The first country is countryID=1 (countryID=0 is reserved for neutral and gamemaster-chat).
	 * @var array[$countryID-1]=$countryName
	 */
	public $countries;

	/**
	 * Array for finding parent coast territory IDs from child-coast IDs.
	 * @var array[$childCoastID]=$parentCoastID
	 */
	public $coastParentIDByChildID;
	/**
	 * Array for finding all child-coasts for a parent coast.
	 * @var array[$parentCoastID][]=$childID
	 */
	public $coastChildIDsByParentID;
	/**
	 * Array for finding territory IDs from names
	 * @var array[$terrName]=$terrID
	 */
	public $terrIDByName;
	/**
	 * A cached count of supply-centers
	 * @var int
	 */
	public $supplyCenterCount;
	/**
	 * The number of supply-centers needed to win the game
	 * @var int
	 */
	public $supplyCenterTarget;

	/**
	 * Return the in-game turn in text format. This should be overridden
	 *
	 * @param int $turn The turn to textualize
	 * @return string The game turn in text format
	 */
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return "Turn #"+$turn;
	}

	/**
	 * A JavaScript function to return the in-game turn in text format. This should be overridden
	 *
	 * @return string A JavaScript function taking an integer and returning a string
	 */
	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return "Turn #"+turn;
		};';
	}

	// --- Below this point are variant utility functions which are unlikely to need modification by variants ---

	/**
	 * This is the function which intercepts requests for game objects and replaces them with
	 * variant specific objects as required
	 *
	 * @param string $name Name of the class to load
	 * @param array[] $args Args to pass to the constructor
	 * @return object Some game-related object
	 */
	public function __call($name, $args) {

		if( isset($this->variantClasses[$name]) )
			$classname=$this->variantClasses[$name].'Variant_'.$name;
		else
			$classname=$name;

		// Not elegant, but I know no other way and this does the job
		switch(count($args))
		{
			case 0: return new $classname();
			case 1: return new $classname($args[0]);
			case 2: return new $classname($args[0], $args[1]);
			case 3: return new $classname($args[0], $args[1], $args[2]);
			case 4: return new $classname($args[0], $args[1], $args[2], $args[3]);
			case 5: return new $classname($args[0], $args[1], $args[2], $args[3], $args[4]);
			case 6: return new $classname($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]);
			case 7: return new $classname($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6]);
			case 8: return new $classname($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7]);
			case 9: return new $classname($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8]);
			case 10: return new $classname($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9]);
			case 11: return new $classname($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9], $args[10]);
			case 12: return new $classname($args[0], $args[1], $args[2], $args[3], $args[4], $args[5], $args[6], $args[7], $args[8], $args[9], $args[10], $args[11]);
			default: trigger_error("Too many variant object constructor arguments.");
		}
	}

	/**
	 * Gives the list of class fields which should be saved to a text file for easier loading. Things not specified here
	 * and not specified in the class itself (i.e. stuff initialized via the constructor) will disappear.
	 * @return array
	 */
	public function __sleep() {
		return array('cacheVersion','variantClasses','coastParentIDByChildID','coastChildIDsByParentID','terrIDByName','supplyCenterCount','supplyCenterTarget');
	}
	/**
	 * Actions to perform when loaded from a serialized cache
	 */
	public function __wakeup() {
		if( !isset($GLOBALS['Variants']) )
			$GLOBALS['Variants'] = array();
		$GLOBALS['Variants'][$this->id] = $this;
	}

	/**
	 * The constructor is called quite rarely, only when the serialized cache isn't available which should only be on
	 * installation. It will check that everything is installed and recreate the serialized cache after loading cache data.
	 */
	public function __construct() {

		if( !isset($GLOBALS['Variants']) )
			$GLOBALS['Variants'] = array();
		$GLOBALS['Variants'][$this->id] = $this;

		$this->initialize();
	}

	/**
	 * Returns the country ID for a given country name
	 * @param string $countryName
	 * @return int
	 */
	public function countryID($countryName) {

		$countryID = array_search($countryName, $this->countries);

		if( false===$countryID )
			throw new Exception("Given country name '".$countryName."' does not exist in this variant.");
		else
			return ($countryID+1);
	}

	/**
	 * Saves a datastructure to a PHP file in the cache which makes deCoasting terrIDs fast and
	 * independant of the database. This is run if the deCoasting datastructures aren't detected,
	 * and if run it will end and require the user to refresh the page.
	 */
	public function initialize() {
		global $DB;

		// This will wipe the variant if it is already present and install it
		require_once('variants/'.$this->name.'/install.php');

		// This only gets called when there's no serialized variant cache available for this
		// variant, so prepare the data to be serialized & saved now.
		$tabl = $DB->sql_tabl("SELECT id, coastParentID FROM wD_Territories WHERE mapID=".$this->mapID." AND NOT id = coastParentID");
		while(list($coastChildID, $coastParentID) = $DB->tabl_row($tabl))
		{
			$this->coastParentIDByChildID[$coastChildID]=$coastParentID;

			if( !isset($this->coastChildIDsByParentID[$coastParentID]) )
				$this->coastChildIDsByParentID[$coastParentID]=array();
			$this->coastChildIDsByParentID[$coastParentID][]=$coastChildID;
		}

		list($this->supplyCenterCount) = $DB->sql_row("SELECT COUNT(id) FROM wD_Territories WHERE mapID=".$this->mapID." AND supply='Yes'");

		$this->supplyCenterTarget = round((18.0/34.0)*$this->supplyCenterCount);

		if( isset($this->codeVersion) && $this->codeVersion != null && $this->codeVersion > 0 )
			$this->cacheVersion = $this->codeVersion;
	}

	/**
	 * Remove coast info from a territory name
	 *
	 * @param string $coast The coastal territory to de-coast
	 * @return string Territory name without coast info
	 */
	public function deCoast($terrID)
	{
		if( isset($this->coastParentIDByChildID[$terrID]) )
			return $this->coastParentIDByChildID[$terrID];
		else
			return $terrID;
	}

	/**
	 * Used by deCoastSelect
	 * @var string
	 */
	private $deCoastSelectCache;
	/**
	 * Generate the SQL to make a terrID column being selected non-coastal
	 *
	 * @param string $column The name of the column to deCoast
	 * @return string
	 */
	public function deCoastSelect($column)
	{
		if( !is_array($this->coastChildIDsByParentID) )
		{
			return $column;
		}

		if( !isset($this->deCoastSelectCache) )
		{
			$sql = '%COL%';

			foreach( $this->coastChildIDsByParentID as $parentID=>$childIDs)
			{
				$where=array();
				foreach($childIDs as $childID)
					$where[]='%COL%='.$childID;

				$sql = 'IF('.implode(' OR ',$where).','.$parentID.','.$sql.')';
			}

			$this->deCoastSelectCache = $sql;
		}

		return str_replace('%COL%',$column,$this->deCoastSelectCache);
	}

	/**
	 * Generate the SQL needed to compare a terrID to a column
	 *
	 * @param string $terrID
	 * @param string $columnName
	 * @return string
	 */
	public function deCoastCompareText($terrID, $columnName)
	{
		if( !is_array($this->coastChildIDsByParentID) )
		{
			return $terrID.'='.$columnName;
		}

		$parentID = $this->deCoast($terrID);

		$where = array($columnName.'='.$parentID);

		if( isset($this->coastChildIDsByParentID[$parentID]) )
			foreach($this->coastChildIDsByParentID[$parentID] as $childID)
				$where[]=$columnName.'='.$childID;

		return '('.implode(' OR ', $where).')';
	}

	/**
	 * Generate the SQL needed to compare a *non-coastal* territory column to a coastal territory column
	 *
	 * @param string $nonCoastalColumn The name of the non-coastal column
	 * @param string $coastalColumn The name of the coastal column
	 * @return string
	 */
	public function deCoastCompare($nonCoastalColumn, $coastalColumn)
	{
		if( !is_array($this->coastChildIDsByParentID) )
		{
			return $nonCoastalColumn.'='.$coastalColumn;
		}

		$where = array($nonCoastalColumn.'='.$coastalColumn);

		foreach($this->coastChildIDsByParentID as $parentID=>$childIDs)
		{
			$subWhere=array();
			foreach($childIDs as $childID)
				$subWhere[]=$coastalColumn.'='.$childID;
			$where[] = '('.$nonCoastalColumn.'='.$parentID.' AND ('.implode(' OR ',$subWhere).') )';
		}

		return '('.implode(' OR ', $where).')';
	}

	/**
	 * The location of the territories JSON file which gives the order-generation JavaScript the board layout it needs.
	 * Some variants need to extend this function to point to another variant which defines their shared map.
	 *
	 * @return string
	 */
	public function territoriesJSONFile() {
		return libVariant::cacheDir($this->name).'/territories'.(isset($this->codeVersion)?'-'.$this->codeVersion:'').'.js';
	}

	public function link() {
		// Changed the link so it displays only the variant, not the whole list.
		// return '<a class="light" href="variants.php#'.$this->name.'">'.$this->fullName.'</a>';
		return '<a class="light" href="variants.php?variantID='.$this->id.'">'.$this->fullName.'</a>';
	}
}

/**
 * This function is called whenever a new class is requested but hasn't been declared. It will
 * load the class' code based on the name of the class, to limit redundant request_once()s being
 * all throughout variant code.
 *
 * It also results in a sane naming scheme being required. This is why all variant extended classes
 * look like "FoobarVariant_standardGameClass" -> variants/Foobar/classes/standardGameClass.php
 *
 *
 * Class names matching the following two patterns will be auto-loaded according to these rules:
 *
 * [Name]Variant -> variants/[Name]/variant.php
 * [Name]Variant_[Class] -> variants/[Name]/classes/[Class].php
 */
function __autoload($classname) {

	if( !( $pos=strpos($classname,'Variant') ) || $pos==0 ) return;

	$variantName=substr($classname, 0, $pos);

	if( $classname==$variantName.'Variant' )
		require_once('variants/'.$variantName.'/variant.php');
	else
		require_once('variants/'.$variantName.'/classes/'.substr($classname, ($pos+8)).'.php');
}

?>