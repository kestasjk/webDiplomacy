<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class adminActionsRestrictedVDip extends adminActionsForum
{
	public function __construct()
	{
		parent::__construct();

		$vDipActionsRestricted = array(
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

	public function exportGameData(array $params)
	{
		global $DB;
		$gameID = (int)$params['gameID'];
 
		$tables = array('wD_Members','wD_Units','wD_TerrStatus', 'wD_Orders', 'wD_Games');

		$return = '';
			
		foreach($tables as $table)
		{
			if ($table=='wD_Games')
				$search=' WHERE id=';
			else
				$search=' WHERE gameID=';
			
			$result = $DB->sql_tabl('SELECT * FROM '.$table.$search.$gameID);
			
			while($row = $DB->tabl_row($result))
			{
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j<count($row); $j++) 
				{
					$row[$j] = addslashes($row[$j]);
					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j<(count($row)-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
			$return.="\n";
		}

		//save file
		$handle = fopen('db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql','w+');
		fwrite($handle,$return);
		fclose($handle);
		
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
