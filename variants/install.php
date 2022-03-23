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

ini_set('memory_limit',"120M"); // 8M is the default
ini_set('max_execution_time','120');

/**
 * Helper code which install.php scripts can use to help make map installation easier and more consistant.
 *
 * This code takes the minimum amount of input data and decompresses it into full
 * Borders/CoastalBorders/Territories/UnitDestroyIndex tables with IDs etc.
 *
 * See author_utilities/packageMapData.php for code that can take loaded map data and package it into an
 * install.php file.
 *
 * See an install.php file with map data for examples of use.
 */

/**
 * Functions to cache data to text-files for later use. As opposed to InstallTerritory which saves to the database.
 */
class InstallCache {
	
	public static function terrJSONData($mapID){
		global $DB;

		$territories = array();
		$tabl=$DB->sql_tabl(
			"SELECT id, name, type, supply, countryID, coast, coastParentID, smallMapX, smallMapY
			FROM wD_Territories
			WHERE mapID=".$mapID."
			ORDER BY id ASC"
		);

		$selectVars = '';

		while($row=$DB->tabl_hash($tabl))
		{
			$row['Borders']=array();
			$row['CoastalBorders']=array();

			$row['name'] = l_t($row['name']);
			
			$territories[$row['id']] = $row;
		}

		$tabl=$DB->sql_tabl("SELECT * FROM wD_Borders WHERE mapID=".$mapID);
		while($row=$DB->tabl_hash($tabl))
		{
			// id, a, f saves space
			$territories[$row['fromTerrID']]['Borders'][] =
				array('id'=>$row['toTerrID'], 'a'=>$row['armysPass']=='Yes', 'f'=>$row['fleetsPass']=='Yes');
		}

		$tabl=$DB->sql_tabl("SELECT * FROM wD_CoastalBorders WHERE mapID=".$mapID);
		while($row=$DB->tabl_hash($tabl))
		{
			$territories[$row['fromTerrID']]['CoastalBorders'][] =
				array('id'=>$row['toTerrID'], 'a'=>$row['armysPass']=='Yes', 'f'=>$row['fleetsPass']=='Yes');
		}

		return $territories;
	}

	/**
	 * Saves the territories.js JSON file which the order-generation code need to know the board layout.
	 *
	 * @param string $jsonFileLocation Where the file will be saved to
	 * @param int $mapID
	 */
	public static function terrJSON($jsonFileLocation, $mapID)
	{
		$javascript = "function loadTerritories() {\n".'Territories = $H('.json_encode(self::terrJSONData($mapID)).');'."\n}\n";

		file_put_contents($jsonFileLocation, $javascript);
	}
}

/**
 * Using InstallTerritory:
 * - Load basic data about territories in via constructor (just create new objects, they will be managed automatically)
 * - Link territories via coast relationships and border relationships
 * - Run InstallTerritory::printSQL($mapID) to receive a dump of the SQL which will install or ::runSQL($mapID) to attempt to install live.
 *
 * @author kestasjk
 *
 */
class InstallTerritory {
	/**
	 * An array of territory objects, stored here as they are created, indexed by territoryID
	 * @var array[$terrID]=$Territory
	 */
	public static $Territories;
	/**
	 * Run the SQL to install territories as specified to the database
	 * @param $mapID The mapID
	 */
	public static function runSQL($mapID) {
		global $DB;
		$sql=self::variantSQL($mapID);
		foreach($sql as $statement)
			$DB->sql_put($statement);
	}
	/**
	 * Print the SQL to install territories as specified to the database
	 * @param int $mapID The mapID
	 */
	public static function printSQL($mapID) {
		$sql=self::variantSQL($mapID);
		foreach($sql as $statement)
			print $statement.';<br /><br />';
	}
	/**
	 * Generate the SQL to install territories as specified to the database. This is
	 * where territories get their ID numbers.
	 *
	 * @param int $mapID
	 * @return array[]=$sql_statement
	 */
	public static function variantSQL($mapID) {
		$sql=array();

		// Wipe stale data
		$wipe=array('Territories','Borders','CoastalBorders','UnitDestroyIndex');
		foreach($wipe as $wipeTable)
			$sql[] = "DELETE FROM wD_".$wipeTable." WHERE mapID=".$mapID;


		// Give territories IDs
		$i=1;
		foreach(self::$Territories as $Territory)
		{
			$Territory->id = $i;
			$Territory->mapID = $mapID;
			$i++;
		}

		foreach(self::$Territories as $Territory)
			$Territory->coastParentID = self::$Territories[$Territory->coastParent->name]->id;


		// Territory SQL
		$sqlRows=array();
		foreach(self::$Territories as $Territory)
			$sqlRows[] = $Territory->sqlTerritoryRow();
		$sql[] = 'INSERT INTO wD_Territories ('.implode(',',self::$territoryRowInclude).') VALUES '.implode(',',$sqlRows);


		// Borders SQL
		self::$staticMapID=$mapID;
		$sql[]=self::dumpBorderRowsSQL('Borders');
		$sql[]=self::dumpBorderRowsSQL('CoastalBorders');


		// Unit destroy SQL (may take a long time and require a deep recursion depth)
		$sql[] = self::unitDestroyIndexSQL($mapID);


		return $sql;
	}

	/**
	 * Stores the mapID for static code (primarily border SQL code)
	 * @var int
	 */
	private static $staticMapID;

	/**
	 * Generate unitdestroyindex SQL. Creates a list of which territory is the furthest away
	 * according to the DATC preferred definition, so that when a unit needs to be destroyed
	 * but the owner didn't specify which the correct unit can be quickly looked up using
	 * a pre-generated table.
	 *
	 * @param int $mapID
	 * @return string SQL
	 */
	public static function unitDestroyIndexSQL($mapID) {
		// Collect home supply centers by countryID, these are where the distance is measured from
		$HomeSCs=array();
		foreach(self::$Territories as $Territory)
		{
			if( $Territory->countryID>0 && ($Territory->supply=='Yes' || ( isset($Territory->coastParent) && $Territory->coastParent->supply=='Yes' ) ) ) 
			{
				if( !isset($HomeSCs[$Territory->countryID]) )
					$HomeSCs[$Territory->countryID]=array();

				$HomeSCs[$Territory->countryID][] = $Territory;
			}
		}

		$UnitDestroyIndexRows=array();
		$unitTypes=array('Army','Fleet');
		foreach($HomeSCs as $countryID=>$countryHomeSCs)
		{
			$sortBuffer=array(); // Territory keys collected for sorting here
			$terrBuffer=array(); // Territory ids with unittype collected, indexed by sortBuffer keys, here

			// Find the depths of all territories for each unit type
			$maxDepth=-1; // The max depth is needed to invert the sort, so that the deepest territories come first and are destroyed first
			$depths = array(); // Store the depths for each unit type, for each territory, in this array
			foreach($unitTypes as $unitType)
			{
				$depths[$unitType] = array();
				
				// When switching from army to fleet for a certain countryID the depths must be recalculated,
				// since the definition of distance is different for fleets than armies
				foreach(self::$Territories as $Territory)
					$Territory->depth=-1;

				// Start the recursive depth search at the home supply centers with a depth of 0
				foreach($countryHomeSCs as $HomeSC)
					$HomeSC->findDepth(0, ($unitType=='Fleet'));

				// Find the max depth, so we can invert the depth so that e.g. a depth of 15 becomes 0 and a depth of 0 becomes 15, so that the 0-depth comes last:
				foreach(self::$Territories as $Territory)
				{
					$depth=$Territory->depth; // Since $Territory->depth is temporary, only for calculation, we need to save it to the $depths array 
				
					if( $depth==-1 ) continue; // Unreachable territory
					
					if( $maxDepth < $depth ) $maxDepth = $depth;
					
					$depths[$unitType][$Territory->id] = $depth;
				}
			}
			
			// The depths need to be reversed, converted from $depth=0 means home supply center to $depth=0 means furthest possible distance:
			foreach($depths as $unitType=>$unitTypeDepths)
				foreach($unitTypeDepths as $terrID=>$depth)
					$depths[$unitType][$terrID] = $maxDepth - $depths[$unitType][$terrID]; 
			
			// Now that all the depths have been generated for all territories, enter them into the sort buffer so they can be sorted correctly:
			foreach($unitTypes as $unitType)
			{
				// Put new results into a buffer using a string so they can be easily sorted by depth
				foreach(self::$Territories as $Territory)
				{
					if( !isset($depths[$unitType][$Territory->id]) ) continue; // Territory was unreachable
					
					$depth = $depths[$unitType][$Territory->id];
						
					// Pad the depth so 9 won't come after 10
					$sortTerritory=($depth<1000?'0':'').($depth<100?'0':'').($depth<10?'0':'').$depth;
					// Make fleet come first (-> destroyed first) in alphabetical ordering by adding a number before the name
					$sortTerritory.=($unitType=='Fleet'?' 1Fleet ':' 2Army ');
					// Australia is destroyed before Zimbabwe, all else being equal; adding the territory name ensures sorting by name
					$sortTerritory.=$Territory->name;
					
					$sortBuffer[]=$sortTerritory;
					$terrBuffer[$sortTerritory]=array($Territory->id,$unitType); // Keep a reference to which sort string refers to which territory
				}
			}

			/*
			 * Sort the array of strings into numeric / alphabetical order. This gives an array which lists the locations and unit types which should be destroyed first
			 * e.g here are the last two inverted levels for Russia:
			 * 
			0006 Army Armenia
			0006 Army Barents Sea
			0006 Army Black Sea
			0006 Army Finland
			0006 Army Galicia
			0006 Army Gulf of Bothnia
			0006 Army Livonia
			0006 Army Norway
			0006 Army Prussia
			0006 Army Rumania
			0006 Army Silesia
			0006 Army Ukraine
			0006 Fleet Armenia
			0006 Fleet Barents Sea
			0006 Fleet Black Sea
			0006 Fleet Finland
			0006 Fleet Gulf of Bothnia
			0006 Fleet Livonia
			0006 Fleet Norway
			0006 Fleet Rumania
			0007 Army Moscow
			0007 Army Sevastopol
			0007 Army St. Petersburg
			0007 Army Warsaw
			0007 Fleet Moscow
			0007 Fleet Sevastopol
			0007 Fleet St. Petersburg (North Coast)
			0007 Fleet St. Petersburg (South Coast)
			0007 Fleet Warsaw
				
				Because of this for Russia a fleet in Warsaw is the last unit which will get destroyed (this doesn't exist, so in practice a fleet at St Petersburg South coast is the first
			 */
			sort($sortBuffer);
			//if( $countryID == 7 ) die(implode('<br />',$sortBuffer)); // For debugging
			
			$destroyIndex=1; // First means first chosen to disband
			foreach($sortBuffer as $sortKey)
			{
				list($terrID, $unitType)=$terrBuffer[$sortKey];
				$UnitDestroyIndexRows[]="(".$mapID.",".$countryID.",".$terrID.",'".$unitType."',".$destroyIndex.")";
				$destroyIndex++;
			}
		}

		return 'INSERT INTO wD_UnitDestroyIndex (mapID, countryID, terrID, unitType, destroyIndex) VALUES '.implode(',',$UnitDestroyIndexRows);
	}
	
	/**
	* Assumes the map is already present in the database, and loads up the $Territories array from that map. Used for using the install routines to modify
	* installed variants (e.g. to re-run the unit destroy index generator).
	* 
	* There is a function in the restricted admin actions section which uses this to regenerate the unit destory indexes for existing maps
	*/
	public static function loadExistingTerritories($mapID)
	{
		global $DB;
		
		InstallTerritory::$Territories=array();
		$TerritoriesByID = array();
		
		$tabl = $DB->sql_tabl("SELECT id, name, type, supply, countryID, mapX, mapY, smallMapX, smallMapY FROM wD_Territories WHERE mapID = ".$mapID);
		while($row = $DB->tabl_hash($tabl))
		{
			$terr = new InstallTerritory($row['name'], $row['type'], $row['supply'], $row['countryID'], $row['mapX'], $row['mapY'], $row['smallMapX'], $row['smallMapY']);
			$terr->id = $row['id'];
			$terr->mapID = $mapID;
			
			$TerritoriesByID[$row['id']] = $terr; // Used to hook up borders
		}
		
		// fleetsPass & armysPass is 'Yes'/'No'
		$tabl = $DB->sql_tabl("SELECT fromTerrID, toTerrID, fleetsPass, armysPass FROM wD_Borders WHERE mapID = ".$mapID);
		while($row = $DB->tabl_hash($tabl))
		{
			$fromTerr = $TerritoriesByID[$row['fromTerrID']];
			$toTerr = $TerritoriesByID[$row['toTerrID']];
			InstallTerritory::$Territories[$fromTerr->name]->addBorder($toTerr, $row['fleetsPass'], $row['armysPass']);
		}
	}

	/**
	 * The distance this territory is from home supply centers. -1 means not yet found (-1 after completion means no path)
	 * @var int
	 */
	public $depth=-1;

	/**
	 * Recursive function to determine depth either by fleet or army method according to the DATC recommendations.
	 *
	 * @param int $depth Depth of this territory via a certain route (disregarded if higher than existing value)
	 * @param boolean $byFleet True to use fleet path traversal rules, false for army rules
	 */
	public function findDepth($depth, $byFleet=false) {
		if( $byFleet && $this->coast=='Parent' ) return;
		elseif( !$byFleet && $this->coast!='No'&&$this->coast!='Parent' ) return;

		if( $this->depth==-1 || $this->depth>$depth )
			$this->depth=$depth;
		else
			return;

		if( !$byFleet )
			foreach($this->ArmyBorders as $BorderTerritory)
				$BorderTerritory->findDepth($depth+1, $byFleet);

		foreach($this->FleetBorders as $BorderTerritory)
			$BorderTerritory->findDepth($depth+1, $byFleet);
	}

	/**
	 * Basic territory data, more or less as given
	 */
	public $mapID, $name, $type, $supply, $countryID, $mapX, $mapY, $smallMapX, $smallMapY, $coastParentID;

	/**
	 * The coastParent object ($this if not a child-coast). Used for coastParentID after ID allocation.
	 * @var Territory
	 */
	public $coastParent;

	/**
	 * Territories reachable from this one by army
	 * @var array[$terrID]=$Territory
	 */
	public $ArmyBorders=array();
	/**
	 * Territories reachable from this one by fleet
	 * @var array[$terrID]=$Territory
	 */
	public $FleetBorders=array();

	/**
	 * Load initial territory data, loaded into the InstallTerritory::$Territories array.
	 *
	 * This data is used to determine coast relationships, but it is assumed
	 * coast territories will always come after their parent coasts, and it is assumed they will have a name
	 * of the form "[coastParentName] ([Something unimportant] Coast)".
	 */
	public function __construct($name, $type, $supply, $countryID, $mapX, $mapY, $smallMapX, $smallMapY) {

		if( $supply!='Yes' && $supply!='No' )
			throw new Exception("Invalid value for supply '".$supply."', should be Yes/No.");

		if( isset(self::$Territories[$name]) )
			throw new Exception("Duplicate territory name '".$name."'.");

		$this->name=$name;
		$this->type=$type;
		$this->supply=$supply;
		$this->countryID=$countryID;
		$this->mapX=$mapX;
		$this->mapY=$mapY;
		$this->smallMapX=$smallMapX;
		$this->smallMapY=$smallMapY;

		$this->coast='No';
		$this->coastParent=$this;

		self::$Territories[$name] = $this;

		$deCoastName=array();
		if( preg_match('/^(.+) \(.+ Coast\)$/', $this->name, $deCoastName) )
		{
			$deCoastName=$deCoastName[1];
			self::$Territories[$deCoastName]->addCoast($this);
		}
	}

	/**
	 * Create a coast relationship, used by the constructor
	 * @param InstallTerritory $CoastChildTerritory
	 */
	protected function addCoast(InstallTerritory $CoastChildTerritory) {
		$CoastChildTerritory->coastParent=$this;
		$CoastChildTerritory->coast='Child';
		$this->coast='Parent';
	}

	/**
	 * Add a border relationship
	 *
	 * @param InstallTerritory $BorderTerritory Territory bordering
	 * @param string $fleetsPass 'Yes' if they can pass, No if they can't
	 * @param string $armysPass 'Yes' if they can pass, No if they can't
	 */
	public function addBorder(InstallTerritory $BorderTerritory, $fleetsPass, $armysPass) {
		if( $fleetsPass=='Yes' )
		{
			$this->FleetBorders[$BorderTerritory->name] = $BorderTerritory;
			$BorderTerritory->FleetBorders[$this->name] = $this;
		}

		if( $armysPass=='Yes' )
		{
			$this->ArmyBorders[$BorderTerritory->name] = $BorderTerritory;
			$BorderTerritory->ArmyBorders[$this->name] = $this;
		}
	}

	/**
	 * Things to write to the territories table, used by sqlTerritoryRow
	 * @var array[]=$colName
	 */
	private static $territoryRowInclude=array('mapID', 'id', 'name', 'type', 'supply', 'mapX', 'mapY', 'smallMapX', 'smallMapY', 'countryID', 'coast', 'coastParentID');
	/**
	 * Returns a wD_Territories VALUE row, to be combined and inserted in bulk.
	 * @return string
	 */
	public function sqlTerritoryRow() {

		$cols=array();

		foreach(self::$territoryRowInclude as $includeCol)
			$cols[] = "'".$this->{$includeCol}."'";

		return '('.implode(',',$cols).')';
	}

	/**
	 * Borders in a static array to allow child/parent coasts to easily influence each others' borders.
	 * @var array[$fromTerrID][$toTerrID]['fleetsPass'/'armysPass']
	 */
	public static $borders=array();
	/**
	 * Add a relationship to self::$borders, will update it if it exists
	 *
	 * @param int $fromTerrID
	 * @param int $toTerrID
	 * @param string $unitType Army/Fleet
	 */
	public static function addBorderRow($fromTerrID, $toTerrID, $unitType)
	{
		if( !isset(self::$borders[$fromTerrID]) )
			self::$borders[$fromTerrID] = array();

		if( !isset(self::$borders[$fromTerrID][$toTerrID]) )
			self::$borders[$fromTerrID][$toTerrID] = array('fleetsPass'=>'No', 'armysPass'=>'No');

		if( $unitType=='Fleet' && self::$borders[$fromTerrID][$toTerrID]['fleetsPass']=='No' )
			self::$borders[$fromTerrID][$toTerrID]['fleetsPass']='Yes';
		elseif( $unitType=='Army' && self::$borders[$fromTerrID][$toTerrID]['armysPass']=='No' )
			self::$borders[$fromTerrID][$toTerrID]['armysPass']='Yes';
	}
	/**
	 * Dump self::$borders into a SQL statement
	 * @param string $tableName Borders/CoastalBorders
	 * @return string the SQL
	 */
	public static function dumpBorderRowsSQL($tableName) {
		$sqlRows=array();

		self::$borders=array();

		foreach(self::$Territories as $Territory)
			if( $tableName=='CoastalBorders' )
				$Territory->coastalBordersRows();
			else
				$Territory->bordersRows();

		foreach(self::$borders as $fromTerrID=>$toTerrRows)
			foreach( $toTerrRows as $toTerrID=>$unitTypes )
				$sqlRows[] = '('.self::$staticMapID.','.$fromTerrID.', '.$toTerrID.", '".$unitTypes['fleetsPass']."', '".$unitTypes['armysPass']."')";

		return "INSERT INTO wD_".$tableName." (mapID, fromTerrID, toTerrID, fleetsPass, armysPass) VALUES ".implode(',',$sqlRows);
	}

	/**
	 * Convert ArmyBorders/FleetBorders into self::$borders, for use in the Borders table, respecting coastal conventions.
	 */
	public function bordersRows() {
		/*
		 * Regular borders: Coast children can link to other things, but other things will link to
		 * the coast parent
		 * - Coast parents may have fleets moving to or from
		 * 	- Coast children moving to another place become from the coast parent in addition
		 * 	- Places moving to coast children instead move to the coast parent
		 * - Coast children may have fleets moving from but not to
		 */
		foreach($this->ArmyBorders as $name=>$Territory)
		{
			if( $Territory->coast=='Child' ) continue;

			self::addBorderRow($this->id, $Territory->id, 'Army');
		}

		foreach($this->FleetBorders as $name=>$Territory)
		{
			if( $Territory->coast=='Child' )
				$Territory = $Territory->coastParent;

			if( $this->coast=='Child' )
				self::addBorderRow($this->coastParent->id, $Territory->id, 'Fleet');

			self::addBorderRow($this->id, $Territory->id, 'Fleet');
		}
	}

	/**
	 * Convert ArmyBorders/FleetBorders into self::$borders, for use in the CoastalBorders table, respecting coastal conventions.
	 */
	public function coastalBordersRows() {
		/*
		 * Coastal borders: Coast children link out to their neighbours, other things link to them.
		 * - Coast parents have no fleets moving to or from them
		 * - Coast children have no armies
		 */
		foreach($this->ArmyBorders as $name=>$Territory)
		{
			if( $this->coast=='Child' || $Territory->coast=='Child' ) continue;

			self::addBorderRow($this->id, $Territory->id, 'Army');
		}

		foreach($this->FleetBorders as $name=>$Territory)
		{
			if( $this->coast=='Parent' || $Territory->coast=='Parent' ) continue;

			self::addBorderRow($this->id, $Territory->id, 'Fleet');
		}
	}
}
?>
