<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class adminActionsVDip extends adminActionsForum
{
	public function __construct()
	{
		parent::__construct();

		$vDipActions = array(
			'changeReliability' => array(
				'name' => 'Change reliability',
				'description' => 'Enter the new phases played and missed and the new CD-count',
				'params' => array('userID'=>'User ID', 'missedMoves'=>'Moves missed','phasesPlayed'=>'Phases played','gamesLeft'=>'Games Left')
			),
			'changeTargetSCs' => array(
				'name' => 'Change target SCs.',
				'description' => 'Enter the new CD count needed for the win.',
				'params' => array('gameID'=>'Game ID', 'targetSCs'=>'New target SCs')
			),
			'changeMaxTurns' => array(
				'name' => 'Set a new EoG turn',
				'description' => 'Enter the new turn that ends the game.',
				'params' => array('gameID'=>'Game ID', 'maxTurns'=>'New Max Turns')
			),
			'changeGameReq' => array(
				'name' => 'Change the game requirements.',
				'description' => 'Enter the min. Rating / min. phases played and the max. games left needed to join this game.',
				'params' => array('gameID'=>'Game ID', 'minRating'=>'Min. Rating','minPhases'=>'Min. Phases played','maxLeft'=>'Max. Games Left')
			),
			'delCache' => array(
				'name' => 'Clean the cache directory.',
				'description' => 'Delete the cache files older than the given date (default = "-30 days").',
				'params' => array('keep'=>'deletion date')
			),
		);
		
		adminActions::$actions = array_merge(adminActions::$actions, $vDipActions);
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
	public function changeTargetSCs(array $params)
	{
		global $DB;

		$gameID   = (int)$params['gameID'];
		$targetSCs= (int)$params['targetSCs'];
		
		$DB->sql_put("UPDATE wD_Games SET targetSCs = ".$targetSCs." WHERE id=".$gameID);

		return 'The target SCs for the game was changed to: '.$targetSCs;
	}
	public function changeMaxTurns(array $params)
	{
		global $DB;

		$gameID   = (int)$params['gameID'];
		$maxTurns= (int)$params['maxTurns'];
		
		$DB->sql_put("UPDATE wD_Games SET maxTurns = ".$maxTurns." WHERE id=".$gameID);

		return 'The max. turns for the game was changed to: '.$targetSCs;
	}
	public function changeGameReq(array $params)
	{
		global $DB;

		$gameID    = (int)$params['gameID'];
		$minRating = (int)$params['minRating'];
		$minPhases = (int)$params['minPhases'];
		$maxLeft   = (int)$params['maxLeft'];
		
		$DB->sql_put("UPDATE wD_Games SET minRating = ".$minRating.", minPhases = ".$minPhases.", maxLeft = ".$maxLeft." WHERE id=".$gameID);

		return 'This games reliability requirements was changed to: minRating = '.$minRating.', minPhases = '.$minPhases.', maxLeft = '.$maxLeft;
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
