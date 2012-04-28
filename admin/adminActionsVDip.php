<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class adminActionsVDip extends adminActions
{
	public function __construct()
	{
		parent::__construct();

		$vDipActions = array(
			'changeReliability' => array(
				'name' => 'Change reliability',
				'description' => 'Enter the new phases played and missed and the new CD-count',
				'params' => array('userID'=>'User ID', 'missedMoves'=>'Moves missed','phasesPlayed'=>'Phases played','gamesLeft'=>'Games Left','leftBalanced'=>'Left Balanced')
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
				'params' => array('gameID'=>'Game ID', 'minRating'=>'Min. Rating','minPhases'=>'Min. Phases played')
			),
			'extendPhase' => array(
				'name' => 'Extend the curent phase',
				'description' => 'How many days should the curent phase extend?',
				'params' => array('gameID'=>'Game ID', 'extend'=>'Days to extend')
			),
		);
		
		adminActions::$actions = array_merge(adminActions::$actions, $vDipActions);
	}

	public function changeReliability(array $params)
	{
		global $DB;
		
		$userID = (int)$params['userID'];

		list($missedMovesOld, $phasesPlayedOld, $gamesLeftOld, $leftBalancedOld) 
			= $DB->sql_row("SELECT missedMoves, phasesPlayed, gamesLeft, leftBalanced FROM wD_Users WHERE id=".$userID);

		$missedMoves = ($params['missedMoves'] =='' ? $missedMovesOld  : (int)$params['missedMoves'] );
		$phasesPlayed= ($params['phasesPlayed']=='' ? $phasesPlayedOld : (int)$params['phasesPlayed']);
		$gamesLeft   = ($params['gamesLeft']   =='' ? $gamesLeftOld    : (int)$params['gamesLeft']   );
		$leftBalanced= ($params['leftBalanced']=='' ? $leftBalancedOld : (int)$params['leftBalanced']);
		
		$DB->sql_put("UPDATE wD_Users SET 
			missedMoves = ".$missedMoves.", 
			phasesPlayed = ".$phasesPlayed.", 
			gamesLeft = ".$gamesLeft.",
			leftBalanced = ".$leftBalanced." 
			WHERE id=".$userID);

		return 'This users reliability was changed to:'.
			($params['missedMoves']  == '' ? '' : '<br>Missed Moves: ' .$missedMovesOld.'  => '.$missedMoves).
			($params['phasesPlayed'] == '' ? '' : '<br>Phases Played: '.$phasesPlayedOld.' => '.$phasesPlayed).
			($params['gamesLeft']    == '' ? '' : '<br>Games Left: '   .$gamesLeftOld.'    => '.$gamesLeft).
			($params['leftBalanced'] == '' ? '' : '<br>Left Balanced: '.$leftBalancedOld.' => '.$leftBalanced);
	}
	
	public function changeReliabilityConfirm(array $params)
	{
		global $DB;
		
		$userID = (int)$params['userID'];
		
		list($missedMovesOld, $phasesPlayedOld, $gamesLeftOld, $leftBalancedOld) 
			= $DB->sql_row("SELECT missedMoves, phasesPlayed, gamesLeft, leftBalanced FROM wD_Users WHERE id=".$userID);

		$missedMoves = ($params['missedMoves'] =='' ? $missedMovesOld  : (int)$params['missedMoves'] );
		$phasesPlayed= ($params['phasesPlayed']=='' ? $phasesPlayedOld : (int)$params['phasesPlayed']);
		$gamesLeft   = ($params['gamesLeft']   =='' ? $gamesLeftOld    : (int)$params['gamesLeft']   );
		$leftBalanced= ($params['leftBalanced']=='' ? $leftBalancedOld : (int)$params['leftBalanced']);

		return 'This users reliability will be changed:'.
			($params['missedMoves']  == '' ? '' : '<br>Missed Moves: ' .$missedMovesOld.'  => '.$missedMoves).
			($params['phasesPlayed'] == '' ? '' : '<br>Phases Played: '.$phasesPlayedOld.' => '.$phasesPlayed).
			($params['gamesLeft']    == '' ? '' : '<br>Games Left: '   .$gamesLeftOld.'    => '.$gamesLeft).
			($params['leftBalanced'] == '' ? '' : '<br>Left Balanced: '.$leftBalancedOld.' => '.$leftBalanced);
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
	public function extendPhase(array $params)
	{
		global $DB;

		$gameID = (int)$params['gameID'];
		$extend = (int)$params['extend'];
		
		$DB->sql_put("UPDATE wD_Games
			SET processTime = processTime + ". $extend * 86400 ."
			WHERE id = ".$gameID);
			
		return 'The target curend phase for the game was extended by '.$extend.' day(s).';
	}
	
	public function changeGameReq(array $params)
	{
		global $DB;

		$gameID    = (int)$params['gameID'];
		$minRating = (int)$params['minRating'];
		$minPhases = (int)$params['minPhases'];
		
		$DB->sql_put("UPDATE wD_Games SET minRating = ".$minRating.", minPhases = ".$minPhases." WHERE id=".$gameID);

		return 'This games reliability requirements was changed to: minRating = '.$minRating.', minPhases = '.$minPhases;
	}

}
?>
