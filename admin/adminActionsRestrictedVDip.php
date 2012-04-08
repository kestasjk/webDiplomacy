<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class adminActionsRestrictedVDip extends adminActionsForum
{
	public function __construct()
	{
		parent::__construct();

		$vDipActionsRestricted = array(
			'changeReliability' => array(
				'name' => 'Change reliability',
				'description' => 'Enter the new phases played and missed and the new CD-count',
				'params' => array('userID'=>'User ID', 'missedMoves'=>'Moves missed','phasesPlayed'=>'Phases played','gamesLeft'=>'Games Left')
			),
			'delCache' => array(
				'name' => 'Clean the cache directory.',
				'description' => 'Delete the cache files older than the given date (default = "-30 days").',
				'params' => array('keep'=>'deletion date')
			),
		);
		
		adminActions::$actions = array_merge(adminActions::$actions, $vDipActionsRestricted);
	}

	public function changeReliability(array $params)
	{
		global $DB;

		$userID      = (int)$params['userID'];
		$missedMoves = (int)$params['missedMoves'];
		$phasesPlayed= (int)$params['phasesPlayed'];
		$gamesLeft   = (int)$params['gamesLeft'];
		
		$DB->sql_put("UPDATE wD_Users SET missedMoves = ".$missedMoves.", phasesPlayed = ".$phasesPlayed.", gamesLeft = ".$gamesLeft." WHERE id=".$userID);

		return 'This users reliability was changed to: missedMoves = '.$missedMoves.', phasesPlayed = '.$phasesPlayed.', gamesLeft = '.$gamesLeft;
	}
	
	public function delcache(array $params)
	{
		$keep = $params['keep'];
		$this->del_cache('cache', $keep);
		return 'Deleted files older than "'.$keep.'"';
	}
	public function delcacheConfirm(array $params)
	{
		$keep = $params['keep'];
		return 'Are you sure you want to delete files in the cache directory older than "'.$keep.'"';
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
