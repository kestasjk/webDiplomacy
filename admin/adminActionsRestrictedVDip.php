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
				'params' => array('keep'=>'File age (in days)')
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
		);
		
		adminActions::$actions = array_merge(adminActions::$actions, $vDipActionsRestricted);
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
		$keep = '-'.(int)$params['keep'].' days';
		$this->del_cache('cache', $keep);
		return 'Deleted files older than '.(int)$params['keep'].' days.';
	}
	public function delcacheConfirm(array $params)
	{
		$keep = (int)$params['keep'];
		return 'Are you sure you want to delete files in the cache directory older than '.$keep.' days?';
	}

	function del_cache($dirname, $keep) 
	{
		if(is_dir($dirname))
			$dir_handle=opendir($dirname); 
		while (false !== ($file=readdir($dir_handle)))
		{
			if($file!="." && $file!="..") 
			{ 
				if(!is_dir($dirname."/".$file))
				{
					if ((filemtime($dirname."/".$file)) < (strtotime($keep)))
					{
						unlink ($dirname."/".$file);
					}
				}
				else
				{
					$this->del_cache($dirname."/".$file, $keep);
				}
			} 
			
		} 
		closedir($dir_handle); 
		$files = @scandir($dirname);
		if (count($files) < 3) rmdir($dirname); 
	}
	
}
?>
