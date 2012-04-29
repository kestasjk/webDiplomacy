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
				'params' => array('keep'=>'File age (in days):')
			),
			'allReady' => array(
				'name' => 'Ready all orders.',
				'description' => 'Set the orderstatus of all countries to "Ready".',
				'params' => array('gameID'=>'GameID:')
			),
		);
		
		adminActions::$actions = array_merge(adminActions::$actions, $vDipActionsRestricted);
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
