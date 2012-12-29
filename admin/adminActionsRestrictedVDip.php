<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class adminActionsRestrictedVDip extends adminActionsForum
{
	public function __construct()
	{
		parent::__construct();

		$vDipActionsRestricted = array(
			'clearAdvancedAccessLogs' => array(
				'name' => 'Clear advanced access logs',
				'description' => 'Clears advanced access log table of logs older than 60 days.',
				'params' => array(),
			),
			'delCache' => array(
				'name' => 'Clean the cache directory.',
				'description' => 'Delete the cache files older than the given date.',
				'params' => array('keepLarge'=>'File age (> 50 kB) (in days)',
									'keepSmall'=>'File age (files < 50 kB) (in days)')
			),
			'allReady' => array(
				'name' => 'Ready all orders.',
				'description' => 'Set the orderstatus of all countries to "Ready".',
				'params' => array('gameID'=>'GameID')
			),
			'toggleAdminLock' => array(
				'name' => 'Lock/unlock a game.',
				'description' => 'Lock (or unlock) a game to prevent users to enter orders.',
				'params' => array('gameID'=>'GameID')
			),
			'delVariantGameCache' => array(
				'name' => 'Clear cache of a given variant.',
				'description' => 'Clear all cache files of all games from a given variant.',
				'params' => array('variantID'=>'VariantID')
			),
			'delInactiveVariants' => array(
				'name' => 'Remove all inactive Variants',
				'description' => 'Remove all games from inactive variants...',
				'params' => array(),
			),			
			'makeDevGold' => array(
				'name' => 'Dev: gold',
				'description' => 'Give gold developer marker',
				'params' => array('userID'=>'User ID'),
			),
			'makeDevSilver' => array(
				'name' => 'Dev: silver',
				'description' => 'Give silver developer marker',
				'params' => array('userID'=>'User ID'),
			),
			'makeDevBronze' => array(
				'name' => 'Dev: bronze',
				'description' => 'Give bronze developer marker',
				'params' => array('userID'=>'User ID'),
			),
			'exportGameData' => array(
				'name' => 'Export game data',
				'description' => 'Save all relevant data of a given game.',
				'params' => array('gameID'=>'Game ID'),
			),
			
		);
		
		adminActions::$actions = array_merge(adminActions::$actions, $vDipActionsRestricted);
	}

	public function clearAdvancedAccessLogs(array $params)
	{
		global $DB;

		list($i) = $DB->sql_row("SELECT COUNT(userID) FROM wD_AccessLogAdvanced WHERE DATEDIFF(CURRENT_DATE, request) > 60");
		$DB->sql_put("DELETE FROM wD_AccessLogAdvanced WHERE DATEDIFF(CURRENT_DATE, request) > 60");
		$DB->sql_put("OPTIMIZE TABLE wD_AccessLogAdvanced");
		return 'Old advanced access logs cleared; '.$i.' records deleted.';
	}

	public function toggleAdminLock(array $params)
	{
		global $DB;
		$gameID = (int)$params['gameID'];
		list($status)=$DB->sql_row("SELECT adminLock FROM wD_Games WHERE id = ".$gameID);		
		$DB->sql_put("UPDATE wD_Games SET adminLock = '".($status == 'Yes' ? 'No' : 'Yes')."' WHERE id = ".$gameID);		
		
		return 'This game is now '.( $status == 'No' ? 'locked' : 'unlocked').'.';
	}
	
	public function RowAsString(array $row)
	{
		global $DB;
		
		$return = '';
		for($j=0; $j<count($row); $j++) 
		{
			$row[$j] = $DB->escape($row[$j]);
			
			if ($row[$j] == 'NULL' || substr($row[$j],0,1) == '@')
				$return .= $row[$j];
			else
				$return.= '"'.$row[$j].'"';
			
			if ($j<(count($row)-1)) { $return.= ','; }
		}
		return $return;
	}
	
	public function exportGameData(array $params)
	{
		global $DB, $User;
		$gameID = (int)$params['gameID'];

		// Export wD_Games
		$row = $DB->sql_row('SELECT * FROM wD_Games WHERE id='.$gameID);
		$row[1]='NULL';
		if ($row[4] == '') $row[4]= 'NULL';		// processTime
		$row[6]=$row[6]." (gameid=".$gameID.")";// name
		$row[9]= 'NULL';		                // password always empty
		if ($row[11] == '') $row[11]= 'NULL';	// pauseTimeRemaining
		if ($row[12] == '') $row[12]= 'NULL';	// minimumBet	
		$row[14]= 'No';			                // never anon
		$return = "INSERT INTO wD_Games VALUES (".$this->RowAsString($row).");\n";
		$return.= "SET @gameID = LAST_INSERT_ID();\n";

		// Export wD_Members
		$tabl = $DB->sql_tabl('SELECT * FROM wD_Members WHERE gameID='.$gameID);
		while($row = $DB->tabl_row($tabl))
		{
			$row[0] = 'NULL';
			$row[1]= $row[3] + 4;					// use UserID 5 and up
			$row[2]= '@gameID';						// gameID
			if ($row[11] == '') $row[11]= 'NULL';	// votes
			if ($row[12] == '') $row[12]= 'NULL';	// pointsWon	
			if ($row[13] == '') $row[13]= 'NULL';	// gameMessagesSent	
			$return .= "INSERT INTO wD_Members VALUES (".$this->RowAsString($row).");\n";
		}
		
		// Export wD_Units
		$tabl = $DB->sql_tabl('SELECT * FROM wD_Units WHERE gameID='.$gameID);
		while($row = $DB->tabl_row($tabl))
		{
			$unitID = $row[0];
			$row[0] = 'NULL';
			$row[4] = '@gameID';			
			$return .= "INSERT INTO wD_Units VALUES (".$this->RowAsString($row)."); ";
			$return .= "SET @unit_".$unitID." = LAST_INSERT_ID();\n";
		}
		
		// Export wD_Orders
		$tabl = $DB->sql_tabl('SELECT * FROM wD_Orders WHERE gameID='.$gameID);
		while($row = $DB->tabl_row($tabl))
		{
			$row[0]= 'NULL';
			$row[1]= '@gameID';
			$row[4]= '@unit_'.$row[4];		
			if ($row[5] == '') $row[5]= 'NULL';				
			if ($row[6] == '') $row[6]= 'NULL';				
			if ($row[7] == '') $row[7]= 'NULL';				
			$return .= "INSERT INTO wD_Orders VALUES (".$this->RowAsString($row).");\n";
		}
		
		// Export wD_TerrStatus
		$tabl = $DB->sql_tabl('SELECT * FROM wD_TerrStatus WHERE gameID='.$gameID);
		while($row = $DB->tabl_row($tabl))
		{
			$row[0]= 'NULL';
			$row[4]= '@gameID';													// gameID
			if ($row[2] == '') $row[2]= 'NULL';									// occupiedFromTerrID
			if ($row[5] != '') $row[5]= '@unit_'.$row[5]; else $row[5]='NULL';	// occupyingUnitID
			if ($row[6] != '') $row[6]= '@unit_'.$row[6]; else $row[6]='NULL';	// retreatingUnitID
			$return .= "INSERT INTO wD_TerrStatus VALUES (".$this->RowAsString($row).");\n";
		}
		
		// Export wD_TerrStatusArchive
		$tabl = $DB->sql_tabl('SELECT * FROM wD_TerrStatusArchive WHERE gameID='.$gameID);
		while($row = $DB->tabl_row($tabl))
		{
			$row[3]= '@gameID';
			$return .= "INSERT INTO wD_TerrStatusArchive VALUES (".$this->RowAsString($row).");\n";
		}
		
		// Export wD_MovesArchive
		$tabl = $DB->sql_tabl('SELECT * FROM wD_MovesArchive WHERE gameID='.$gameID);
		while($row = $DB->tabl_row($tabl))
		{
			$row[0] = '@gameID';
			if ($row[4] == '') $row[4]= 'NULL';				//
			if ($row[8] == '') $row[8]= 'NULL';				//
			if ($row[9] == '') $row[9]= 'NULL';				//
			$return .= "INSERT INTO wD_MovesArchive VALUES (".$this->RowAsString($row).");\n";
		}
		
		//save file
		$filename = libCache::dirID('users',$User->id).'/backup-'.$gameID.'-'.time().'.sql';
		$handle = fopen($filename,'w+');
		fwrite($handle,$return);
		fclose($handle);
		
		return "Gamedata exported. (<a href='".$filename."'>Click here for download</a>)";
		
	}
	
	private function makeDevType(array $params, $type='') {
		global $DB;

		$userID = (int)$params['userID'];

		$DB->sql_put("UPDATE wD_Users SET type = CONCAT_WS(',',type,'Dev".$type."') WHERE id = ".$userID);

		return 'User ID '.$userID.' given donator status.';
	}
	public function makeDevGold(array $params)
	{
		return $this->makeDevType($params,'Gold');
	}
	public function makeDevSilver(array $params)
	{
		return $this->makeDevType($params,'Silver');
	}
	public function makeDevBronze(array $params)
	{
		return $this->makeDevType($params,'Bronze');
	}
	
	public function delInactiveVariants(array $params)
	{
		global $DB;
		$DB->sql_put("DELETE wD_Members
						FROM wD_Members
						INNER JOIN wD_Games ON (wD_Games.id = wD_Members.gameID)
						WHERE wD_Games.variantID NOT IN (".implode(',',array_keys(Config::$variants)).")");		
		$DB->sql_put("DELETE FROM wD_Games WHERE variantID NOT IN (".implode(',',array_keys(Config::$variants)).")");
		return 'Removed all data of all inactive variants.';		
	}
	public function delInactiveVariantsConfirm(array $params)
	{
		return 'Do you really want to remove all data of all inactive variants (can\'t be undone)?';
	}

	public function delVariantGameCache(array $params)
	{
		global $DB;
		$variantID = (int)$params['variantID'];
		$Variant = libVariant::loadFromVariantID($variantID);
		$tabl=$DB->sql_tabl("SELECT id FROM wD_Games WHERE variantID = ".$variantID );
		$count = 0;
		while( list($gameID) = $DB->tabl_row($tabl) )
		{
			$gamesDir = libCache::dirID('games',$gameID);
			$this->del_cache($gamesDir, '0 days');
			$count++;
		}
		$VariantCache=opendir('variants/'.$Variant->name.'/cache');
		while (false !== ($file=readdir($VariantCache)))
			if($file[0]!=".") unlink ('variants/'.$Variant->name.'/cache/'.$file);
		return 'Cleared all cache data for the '.$Variant->name.'-variant ('.$count.' games).';
	}
	public function delVariantGameCacheConfirm(array $params)
	{
		global $DB;
		$variantID = (int)$params['variantID'];
		$Variant = libVariant::loadFromVariantID($variantID);
		list($runningGamesCount)=$DB->sql_row("SELECT count(*) FROM wD_Games WHERE variantID = ".$variantID );
		$tabl=$DB->sql_tabl("SELECT id FROM wD_Games WHERE variantID = ".$variantID );
		
		return 'Do you want to clear all cache data for the '.$Variant->name.'-variant ('.$runningGamesCount.' games)?';
	}
	
	
	public function allReady(array $params)
	{
		global $DB;
		$gameID = (int)$params['gameID'];
		$DB->sql_put("UPDATE wD_Members SET orderStatus = 'Ready',
			missedPhases=IF(missedPhases > 0 , missedPhases - 1, 0)
			WHERE gameID = ".$gameID);		
		return 'Orderstatus of all countries set to "Ready".';
	}
	public function allReadyConfirm(array $params)
	{
		$gameID = (int)$params['gameID'];
		return 'Are you sure you want to change the orderstatus of all countries to "Ready"';
	}
	
	public function delcache(array $params)
	{
		$keepLarge = '-'.(int)$params['keepLarge'].' days';
		$this->del_cache('cache', $keepLarge, 50);
		$keepSmall = '-'.(int)$params['keepSmall'].' days';
		$this->del_cache('cache', $keepSmall, 0);
		return 'Deleted files bigger 50k older than '.(int)$params['keepLarge'].' days, all other '.(int)$params['keepSmall'].' days.';
	}
	public function delcacheConfirm(array $params)
	{
		$keepSmall = (int)$params['keepSmall'];
		$keepLarge = (int)$params['keepLarge'];
		return 'Are you sure you want to delete files <ol><li>Bigger 50k older than '.$keepLarge.' days?</li><li>All other '.$keepSmall.' days?</li></ol>';
	}

	function del_cache($dirname, $keep, $filesize = 0) 
	{
		if(is_dir($dirname))
			$dir_handle=opendir($dirname); 
		while (false !== ($file=readdir($dir_handle)))
		{
			if($file!="." && $file!="..") 
			{ 
				if(!is_dir($dirname."/".$file))
				{
					if (filesize($dirname."/".$file) > $filesize * 1024)
					{
						if ((filemtime($dirname."/".$file)) < (strtotime($keep)))
						{
							unlink ($dirname."/".$file);
						}
					}
				}
				else
				{
					$this->del_cache($dirname."/".$file, $keep, $filesize);
				}
			} 
			
		} 
		closedir($dir_handle); 
		$files = @scandir($dirname);
		if (count($files) < 3) rmdir($dirname); 
	}
	
}
?>
