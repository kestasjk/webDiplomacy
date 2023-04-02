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

require_once('variants/variantData.php');

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
	 * 
	 */
	public function generateSandboxSetupForm()
	{
		// For each supply center a unit can be assigned, and a supply center allocated

		$js = '{ ';
		$js .= 'getEmptyOptions: function () {
			let supplyCenterTargetOptions = [];';
			for($i=0; $i<= $this->supplyCenterCount; $i++)
			{
				$js .= 'supplyCenterTargetOptions['.$i.'] = {
					index: '.$i.',
					countryID: -1,
					unitPositionTerrID: -1,
					unitPositionTerrIDParent: -1,
					unitSCTerrID: -1,
					unitType: null,
				};
				';
			}
		$js .= 'return supplyCenterTargetOptions;
		},
		fetchTerritories: (callback) => downloadAndLoadTerritories("'.$this->territoriesJSONFile().'", callback),
		getSupplyCenters: function () {
			let supplyCenters = [];
			for(const terrID in Territories)
			{
				let terr = Territories[terrID];
				let parentTerr = terr;
				if( terr.coastParentID != terr.id )
				{
					parentTerr = Territories[terr.coastParentID];
				}
				if(terr.supply == "Yes" || parentTerr.supply == "Yes")
				{
					supplyCenters.push(terr);
				}
			}
			return supplyCenters;
		},
		';
		require_once('gamemaster/adjudicator/preGame.php');
		$adj=$this->adjudicatorPreGame();
		$countryUnits = $adj->getCountryUnits();
		$js .= '
		getCountryUnits: function () {
			return '.json_encode($countryUnits).';
		},
		getCountryNamesByID: function() {
			return '.json_encode($this->countries).';
		},
		getCountryIDByName: function (countryName) {
			let countryNamesByID = this.getCountryNamesByID();
			for(let i=0; i< countryNamesByID.length; i++)
			{
				if(countryNamesByID[i] == countryName)
				{
					return i+1;
				}
			}
			return -1;
		},
		getDefaultOptions: function () {
			let supplyCenterTargetOptions = this.getEmptyOptions(); // [{index, countryID, unitPositionTerrID, unitPositionTerrIDParent, unitSCTerrID, unitType}]
			let countryUnits = this.getCountryUnits(); // {countryName: {territoryName, unitType}}
			let supplyCenters = this.getSupplyCenters(); // {terrID: {id, name, type, supply, countryID, coast, coastParentID}}

			// Loop through countryUnits
			for (const countryName in countryUnits) {
			  const countryID = this.getCountryIDByName(countryName);
			  const units = countryUnits[countryName];
			
			  // Loop through units for each country
			  for (const territoryName in units) {
				const unitType = units[territoryName];
			
				// Loop through supplyCenters to find the matching territoryID
				for (const terrID in supplyCenters) {
				  const supplyCenter = supplyCenters[terrID];
			
				  if (supplyCenter.name === territoryName) {
					// Find the first empty slot in supplyCenterTargetOptions
					for (const option of supplyCenterTargetOptions) {
					  if (option.countryID === -1) {
						// Assign the values
						option.countryID = countryID;
						option.unitPositionTerrID = supplyCenter.id;
						option.unitPositionTerrIDParent = supplyCenter.coastParentID;
						option.unitSCTerrID = supplyCenter.coastParentID;
						option.unitType = unitType;
			
						// Break out of the loop as the empty slot is filled
						break;
					  }
					}
					// Break out of the loop as the territoryID is found
					break;
				  }
				}
			  }
			}

			return supplyCenterTargetOptions;
		},
		getEmptyFormHTML: function () {
			let supplyCenterTargetOptions = this.getEmptyOptions();
			let html = "<table class=\'hof variant'.$this->name.'\'><tr><th>Country</th><th>Unit type</th><th>Unit position</th><th>Supply center</th></tr>";
			for (const option of supplyCenterTargetOptions) {
				html += "<tr class=\'hof\'>";
				html += "<td id=\'scOptionID"+option.index+"Country\' class=\'hof\'></td>";
				html += "<td id=\'scOptionID"+option.index+"UnitType\' class=\'hof\'></td>";
				html += "<td id=\'scOptionID"+option.index+"UnitPosition\' class=\'hof\'></td>";
				html += "<td id=\'scOptionID"+option.index+"UnitSC\' class=\'hof\'></td>";
				html += "</tr>";
			}
			html += "</table>";
			return html;
		},
		applyOptionsToTable: function (supplyCenterTargetOptions) {
			countryNamesByID = this.getCountryNamesByID();
			for (const option of supplyCenterTargetOptions) {
				document.getElementById("scOptionID"+option.index+"Country").innerHTML = option.countryID;
				if( option.countryID > 0 ) {
					
					document.getElementById("scOptionID"+option.index+"Country").classList.add("country"+option.countryID);
					document.getElementById("scOptionID"+option.index+"Country").innerHTML = countryNamesByID[option.countryID-1];
					
					document.getElementById("scOptionID"+option.index+"UnitType").innerHTML = option.unitType;
					// if Territories doesn\'t contain the index put empty into the cell
					document.getElementById("scOptionID"+option.index+"UnitPosition").innerHTML = "";
					if( Territories[option.unitPositionTerrID] ) {
						document.getElementById("scOptionID"+option.index+"UnitPosition").innerHTML = Territories[option.unitPositionTerrID].name;
					}
					document.getElementById("scOptionID"+option.index+"UnitSC").innerHTML = "";
					if( Territories[option.unitSCTerrID] ) {
						document.getElementById("scOptionID"+option.index+"UnitSC").innerHTML = Territories[option.unitSCTerrID].name;
					}
					document.getElementById("scOptionID"+option.index+"Country").style = "";
					document.getElementById("scOptionID"+option.index+"UnitType").style = "";
					document.getElementById("scOptionID"+option.index+"UnitPosition").style = "";
					document.getElementById("scOptionID"+option.index+"UnitSC").style = "";
				}
				else {
					document.getElementById("scOptionID"+option.index+"Country").style = "display:none";
					document.getElementById("scOptionID"+option.index+"UnitType").style = "display:none";
					document.getElementById("scOptionID"+option.index+"UnitPosition").style = "display:none";
					document.getElementById("scOptionID"+option.index+"UnitSC").style = "display:none";
				}
			}
		},
		';
		
		require_once(l_r('map/drawMap.php'));
		
		$drawMap = $this->drawMap(true); // Set the doNotLoad flag so we can access the map colors without loading everything
		$colors = $drawMap->getColors();
		$js .= '
		getCountryColors: function() {
			return '.json_encode($colors).';
			let colors = [];
			// foreach country get the color:
			let countries = this.getCountryNamesByID();
			for(let i = 0; i < countries.length; i++) {
				colors.push(getCssSelectorColor(["variant'.$this->name.'","country"+(i+1),"occupationBar"+(i+1)]));
			}
			return colors;
		},
		smallMapURL: "variants/'.$this->name.'/resources/smallmap.png",
		cssURL: "variants/'.$this->name.'/resources/style.css",
		';
		
		$js .= 'armyURL: "'.$drawMap->getArmyURL().'",';
		$js .= 'fleetURL: "'.$drawMap->getFleetURL().'",';
		$js .= 'mapURL: "'.$drawMap->getMapURL().'",';
		$js .= 'namesURL: "'.$drawMap->getNamesURL().'",';

		$js .= '
	}';
		return $js;
	}

	/**
	 * Return the in-game turn in text format. This should be overridden
	 *
	 * @param int $turn The turn to textualize
	 * @return string The game turn in text format
	 */
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return l_t("Pre-game");
		else return l_t("Turn #%s",$turn);
	}

	/**
	 * A JavaScript function to return the in-game turn in text format. This should be overridden
	 *
	 * @return string A JavaScript function taking an integer and returning a string
	 */
	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "'.l_t("Pre-game").'";
			else return "'.l_t('Turn').' #"+turn;
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

		$name = l_vc($name);

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
			default: trigger_error(l_t("Too many variant object constructor arguments."));
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
			throw new Exception(l_t("Given country name '%s' does not exist in this variant.",$countryName));
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
		require_once(l_r('variants/'.$this->name.'/install.php'));

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
		return l_j(libVariant::cacheDir($this->name).'/territories.js');
	}

	public function link() {
		return '<a class="light" href="variants.php#'.$this->name.'">'.l_t($this->fullName).'</a>';
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
function variant_autoloader($classname) {

	if( !( $pos=strpos($classname,'Variant') ) || $pos==0 ) return;

	$variantName=substr($classname, 0, $pos);

	if( $classname==$variantName.'Variant' )
		require_once(l_r('variants/'.$variantName.'/variant.php'));
	else
		require_once(l_r('variants/'.$variantName.'/classes/'.substr($classname, ($pos+8)).'.php'));
}

spl_autoload_register('variant_autoloader');

