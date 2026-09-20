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
 * This class will enable adminActions and adminActionsForum moderator
 * tasks to be performed, but also allow tasks which only admins should
 * be able to perform.
 * This will be included anyway, but the class will only be initialized if
 * the user is an admin.
 *
 * @package Admin
 */
class adminActionsRestricted extends adminActionsSeniorMod
{
	public function __construct()
	{
		global $Misc;

		parent::__construct();

		$restrictedActions = array(
			'wipeVariants' => array(
				'name' => 'Clear variant caches',
				'description' => 'Clears out all variant data and cache files, forcing them all to be reinstalled.',
				'params' => array(),
			),
			'clearErrorLogs' => array(
				'name' => 'Clear error logs',
				'description' => 'Clears error log text files.</br>
					<em>ONLY A DEV</em> should run this function, to make sure those logs aren\'t needed for debugging.',
				'params' => array(),
			),
			'clearOrderLogs' => array(
				'name' => 'Clear order logs',
				'description' => 'Clears order log text files.',
				'params' => array(),
			),
			'testEmail' => array(
				'name' => 'Test e-mail sending',
				'description' => 'Sends a test e-mail to the address given, to check whether the server\'s mail settings are working.<br />
					If the mailer gives an error it is displayed here; if no error is given but nothing arrives the problem is
					past this server, so check the address\'s spam folder and the mail server\'s logs.',
				'params' => array('email'=>'E-mail address'),
			),
			'giveModerator' => array(
				'name' => 'Give moderator status',
				'description' => 'Gives moderator status to the specified user ID.',
				'params' => array('userID'=>'User ID'),
			),
			'giveSeniorModerator' => array(
				'name' => 'Give senior moderator status',
				'description' => 'Gives senior moderator status to the specified user ID.',
				'params' => array('userID'=>'User ID'),
			),
			'takeModerator' => array(
				'name' => 'Take moderator status',
				'description' => 'Takes moderator status from the specified user ID.',
				'params' => array('userID'=>'Mod User ID'),
			),
			'takeSeniorModerator' => array(
				'name' => 'Take senior moderator status',
				'description' => 'Takes senior moderator status from the specified user ID.',
				'params' => array('userID'=>'Mod User ID'),
			),
			'giveForumModerator' => array(
				'name' => 'Give forum moderator status',
				'description' => 'Gives forum moderator status to the specified user ID.',
				'params' => array('userID'=>'User ID'),
			),
			'takeForumModerator' => array(
				'name' => 'Take forum moderator status',
				'description' => 'Takes forum moderator status from the specified user ID.',
				'params' => array('userID'=>'Mod User ID'),
			),
			'changeEmail' => array(
				'name' => 'Change email',
				'description' => 'Update user\'s registered email.',
				'params' => array('userID'=>'User ID', 'email'=>'New Email', 'reason'=>'Reason')
			),
			'changeUsername' => array(
				'name' => 'Change username',
				'description' => 'Changes user\'s current name to the specified username.',
				'params' => array('userID'=>'User ID', 'username'=>'New Username', 'reason'=>'Reason'), 
			),
			'createUser' => array(
				'name' => 'Create user',
				'description' => 'Creates a new user with the given username and a random password, for use on test servers for quick account creation.',
				'params' => array('username'=>'Username')
			),
			'giveBot' => array(
				'name' => 'Give bot status',
				'description' => 'Gives bot status to the specified user ID.',
				'params' => array('userID'=>'User ID'),
			),
			'takeBot' => array(
				'name' => 'Take bot status',
				'description' => 'Takes bot status from the specified user ID.',
				'params' => array('userID'=>'User ID'),
			),
			'makeDonator' => array(
				'name' => 'Give donator benefits',
				'description' => 'Give donator benefits (in practical terms this just means opt-out of the distributed processing).<br />
					<em>Only for owner use.</em>',
				'params' => array('userID'=>'User ID'),
			),
			'makeDonatorPlatinum' => array(
				'name' => 'Donator: platinum',
				'description' => 'Give platinum donator marker<br />
					<em>Only for owner use.</em>',
				'params' => array('userID'=>'User ID'),
			),
			'makeDonatorGold' => array(
				'name' => 'Donator: gold',
				'description' => 'Give gold donator marker<br />
					<em>Only for owner use.</em>',
				'params' => array('userID'=>'User ID'),
			),
			'makeDonatorSilver' => array(
				'name' => 'Donator: silver',
				'description' => 'Give silver donator marker<br />
					<em>Only for owner use.</em>',
				'params' => array('userID'=>'User ID'),
			),
			'makeDonatorBronze' => array(
				'name' => 'Donator: bronze',
				'description' => 'Give bronze donator marker<br />
					<em>Only for owner use.</em>',
				'params' => array('userID'=>'User ID'),
			),
			'reprocessGame' => array(
				'name' => 'Reprocess game',
				'description' => 'Returns an active game to the last Diplomacy phase,
					along with that phase\'s orders, and sets the time so that the game will be reprocessed.',
				'params' => array('gameID'=>'Game ID'),
			),
			'restoreCancelledSandboxGames' => array(
				'name' => 'Restore cancelled sandbox games',
				'description' => 'Brings back sandbox games which were finished by the gamemaster\'s weekly
					cleanup while they were still being played. Only games whose board is still on the table are
					touched, which is what tells a game ended by a cleanup from one that was really played out.
					Leave the user ID empty for every such game, or give one to restore only that user\'s.',
				/* Not named userID: the admin panel builds a User object from a parameter of that name to show a
				   profile link beside the form, which throws when it is left empty, and here it is optional. */
				'params' => array('sandboxUserID'=>'Sandbox owner user ID (empty for all)'),
			),
			'updateDonators' => array(
				'name' => 'Update Donators',
				'description' => 'Will not do anything outside webdip, updates all donators to sync with Ranks on the new forum.',
				'params' => array(),
			),
			'notice' => array(
				'name' => 'Toggle site-wide notice',
				'description' => 'Toggle the notice which is displayed in a noticebar across the whole site.',
				'params' => array(),
			),
			'noticeMessage' => array(
				'name' => 'Change notice message',
				'description' => 'Sets the notice which is displayed in a noticebar across the whole site. Sample is: Excused missed turns have been added to all games. See more (<)a href="/contrib/phpBB3/viewtopic.php?f=5&t=1551">here(<)/a(>)(<)/br(>) (<)font color="red"(>)If you are seeing an error on games please clear your browsers cache.(<)/font(>)',
				'params' => array('message'=>'Message'),
			),
			'maintenance' => array(
				'name' => 'Toggle maintenance',
				'description' => 'Toggle maintenance mode, which makes the server inaccessible except to admins
					so changes can be made.',
				'params' => array(),
			),
			'maintenanceMessage' => array(
				'name' => 'Change maintenance message',
				'description' => 'Change the message that is displayed while the site is undergoing maintenance.',
				'params' => array('message'=>'Message'),
			),
			'panicMessage' => array(
				'name' => 'Change panic message',
				'description' => 'Changes the message that is displayed while the site is in panic mode.',
				'params' => array('message'=>'Message'),
			),
			'globalAddTime' => array(
				'name' => 'Add time to all games',
				'description' => 'Add extra time (in hours) to all games, or 0 for the game\'s phase time.',
				'params' => array('timeHours'=>'Extra time (hours)'),
			),
			'backupGame' => array(
				'name' => 'Backup a game',
				'description' => 'Save a game\'s data to the backup tables',
				'params' => array('gameID'=>'Game ID'),
			),
			'restoreGame' => array(
				'name' => 'Restore a game from backups',
				'description' => 'Restores a game from a backup',
				'params' => array('gameID'=>'Game ID'),
			),
			'wipeBackups' => array(
				'name' => 'Wipe game backups',
				'description' => 'Clear out all game backups',
				'params' => array(),
			),
			'checkPausedGames' => array(
				'name' => 'Check paused game process times',
				'description' => 'Sometimes after extending next process times and resetting the last process
						time some games are left with incorrectly set process/pause time values, which causes
						users to have errors when the game gets loaded on their home page / board page.<br />
						Until the cause can be tracked down and resolved this command can find and correct the
						invalid values, but it shouldn\'t be used unless errors are occurring since it may alter
						unaffected games.',
				'params' => array(),
			),
			'recreateUnitDestroyIndex' => array(
				'name' => 'Recreate the destroy unit indexes',
				'description' => 'Refreshes the unit destroy indexes for a certain map ID. This will generally only
					be run if there has been a bug found in the unit destroy index generation code which requires
					the indexes to be recreated.<br />
					Note that this uses the generic installation code, so if there are any variant-specific modifications
					running this may give unpredictable results. Please confirm with the variant maintainer before
					using this admin action.',
				'params' => array('mapID'=>'Map ID'),
			),
			'resetLastProcessTime' => array(
				'name' => 'Reset last process time',
				'description' => 'Once the reason for the period of no processing is known, and it\'s safe to reenable
					game processing, the last process time can be reset here.<br />
					<em>Only a dev</em> should run this function after they ensure the issue has been fixed.',
				'params' => array(),
			),
			'unCrashGames' => array(
				'name' => 'Uncrash games',
				'description' => 'Uncrashes all crashed games except the games specified (if any).<br />
					<em>ONLY A DEV</em> should run this function. The reason for the crash needs to be found out before the games are uncrashed.',
				'params' => array('excludeGameIDs'=>'Except Game ID list'),
            ),
			'addApiKey' => array(
				'name' => 'API - Add an API key for a user',
				'description' => 'Associate an API key to a user.',
				'params' => array('userID'=>'User ID'),
			),
			'deleteApiKey' => array(
				'name' => 'API - Delete all API keys for a user',
				'description' => 'Remove all API keys assiociated with a user.',
				'params' => array('userID'=>'User ID'),
			),
			'setApiPermission' => array(
				'name' => 'API - Set API key permission',
				'description' => 'Set an API permission for a user. (getStateOfAllGames, submitOrdersForUserInCD, listGamesWithPlayersInCD)',
				'params' => array('userID'=>'User ID', 'permissionName' => 'Permission name', 'permissionValue' => 'Permission value ("Yes" or "No").'),
			),
			'showApiKeys' => array(
				'name' => 'API - Show API key and permissions for a user',
				'description' => 'Display API key and permissions for a user.',
				'params' => array('userID'=>'User ID'),
			),
			'calculateGR' => array(
				'name' => 'Calculate Ghost Ratings',
				'description' => 'Calculate GR for next x games and populate out the database. Note that this only needs to be done when 
				first adding GR to the server. After that, games will automatically calculate GR adjustments as they finish.',
				'params' => array('batchSize'=>'Batch Size'),
			),
			'updateVariantInfo' => array(
				'name' => 'Update wD_VariantInfo',
				'description' => 'Will add or update the info in wD_VariantInfo for a given variantID. If left blank, it will loop through all variants.',
				'params' => array('variantID'=>'Variant ID'),
			)
		);

		adminActions::$actions = array_merge(adminActions::$actions, $restrictedActions);
	}
	public function wipeVariants(array $params) 
	{

		foreach(Config::$variants as $variantID=>$variantName)
			libVariant::wipe($variantName);

		return l_t('All variants wiped.');
	}

	public function backupGameConfirm(array $params)
	{
		global $DB;

		require_once(l_r('objects/game.php'));
		$Variant=libVariant::loadFromGameID($params['gameID']);
		$Game = $Variant->Game($params['gameID']);

		return l_t('Are you sure you want to backup this game?');
	}

	public function backupGame(array $params)
	{
		global $DB;

		require_once(l_r('objects/game.php'));

		$gameID = (int)$params['gameID'];

		$DB->sql_put("BEGIN");

		processGame::backupGame($gameID);

		return l_t('Game backed up');
	}

	public function restoreGameConfirm(array $params)
	{
		global $DB;

		require_once(l_r('objects/game.php'));
		$Variant=libVariant::loadFromGameID($params['gameID']);
		$Game = $Variant->Game($params['gameID']);

		return l_t('Are you sure you want to restore this game?');
	}

	public function restoreGame(array $params)
	{
		global $DB;

		require_once(l_r('gamemaster/game.php'));

		$gameID = (int)$params['gameID'];

		$DB->sql_put("BEGIN");

		processGame::restoreGame($gameID);

		return l_t('Game restored');
	}

	public function wipeBackupsConfirm(array $params)
	{
		return l_t('Are you sure you want to wipe backups?');
	}

	public function wipeBackups(array $params)
	{
		global $DB;

		require_once(l_r('gamemaster/game.php'));

		processGame::wipeBackups();

		return l_t('Backups wiped');
	}

	public function globalAddTime(array $params)
	{
		global $DB;

		$timeHours=$params['timeHours'];

		/*
		 * Paused/Finished - No change
		 *
		 * 	Pre-game -
		 * 		processTime-time() > phaseMinutes*60 - No change, a scheduled time is set
		 * 		processTime-time() <= phaseMinutes*60 - Add the requested time
		 * 	Active -
		 * 		phaseMinutes*60 < downtime - Pause
		 * 		phaseMinutes*60 >= downtime - Add
		 * 			downtime>phaseMinutes*60Add time up to phaseMinutes*60
		 */

		$DB->sql_put('UPDATE wD_Games SET processTime = processTime+'.round($timeHours*60*60).'
			WHERE processStatus="Not-processing" AND NOT phase="Finished" AND
				(
				( phase="Pre-game" AND processTime<=phaseMinutes*60+'.time().' )
				OR (NOT phase="Pre-game" AND '.round($timeHours*60*60).'<=phaseMinutes*60 )
				)');

		$DB->sql_put('UPDATE wD_Games SET processStatus="Paused", processTime=NULL,
				pauseTimeRemaining=IF((processTime-'.time().')<=0, phaseMinutes*60, processTime-'.time().')
			WHERE processStatus="Not-processing" AND NOT phase="Finished" AND
				NOT phase="Pre-game" AND '.round($timeHours*60*60).'>phaseMinutes*60');
		$DB->sql_put("COMMIT");

		if($timeHours==0)
			return l_t('All game process times have been set to their phase time');
		else
			return l_t('All games have had %s hours added to them',$timeHours);
	}

	public function maintenance(array $params)
	{
		global $Misc;

		$Misc->Maintenance = 1-$Misc->Maintenance;
		$Misc->write();

		return l_t('Maintenance mode '.($Misc->Maintenance?'turned on':'turned off'));
	}

	public function maintenanceMessage(array $params)
	{
		global $DB;
		$message = $params['message'];
		$message = $DB->escape($message, $htmlAllowed=true);
		$DB->sql_put("UPDATE wD_Config SET message='".$message."' WHERE name = 'Maintenance'");
		return l_t('The maintenance message has been updated.');
	}

	public function notice(array $params)
	{
		global $Misc;

		$Misc->Notice = 1-$Misc->Notice;
		$Misc->write();

		return l_t('Site-wide notice '.($Misc->Notice?'turned on':'turned off'));
	}

	public function noticeMessage(array $params)
	{
		global $DB;
		$message = $params['message'];
		$message = $DB->escape($message, $htmlAllowed=true);
		$DB->sql_put("UPDATE wD_Config SET message='".$message."' WHERE name = 'Notice'");
		return l_t('The site-wide notice message has been updated.');
	}

	public function panicMessage(array $params)
	{
		global $DB;
		$message = $params['message'];
		$message = $DB->escape($message, $htmlAllowed=true);
		$DB->sql_put("UPDATE wD_Config SET message='".$message."' WHERE name = 'Panic'");
		return l_t('The panic message has been updated.');
	}

	public function clearErrorLogs(array $params)
	{
		global $Misc;

		$oldCount = $Misc->ErrorLogs;

		libError::clear();

		return l_t('The error logs were cleared, %s files deleted.',$oldCount);
	}

	public function clearOrderLogs(array $params)
	{
		$logDir = Config::orderlogDirectory();

		if ( ! is_dir($logDir) or ! ( $handle = opendir($logDir) ) )
		{
			throw new Exception(l_t("Could not open log directory"));
		}

		$logs = array();
		$exclude = array('.','..','index.html');

		while ( false !== ( $file = readdir($handle) ) )
		{
			if( in_array($file, $exclude) ) continue;

			$logs[] = $file;
		}
		closedir($handle);

		$i=0;
		foreach ( $logs as $log )
		{
			unlink($logDir.'/'.$log);
			$i++;
		}

		return l_t('The order logs were cleared, %s files deleted.',$i);
	}

	public function testEmailConfirm(array $params)
	{
		$email = self::testEmailAddress($params);

		return l_t('Are you sure you want to send a test e-mail to %s?', htmlspecialchars($email));
	}

	/**
	 * Check the e-mail address given to the test e-mail task, and return it ready to be mailed.
	 *
	 * @param array $params The task's parameters
	 * @return string The e-mail address given
	 */
	private static function testEmailAddress(array $params)
	{
		$email = isset($params['email']) ? trim((string)$params['email']) : '';

		if( $email == '' )
			throw new Exception(l_t("No e-mail address was given to send the test e-mail to."));

		if( !filter_var($email, FILTER_VALIDATE_EMAIL) )
			throw new Exception(l_t("%s isn't a valid e-mail address.", htmlspecialchars($email)));

		return $email;
	}

	public function testEmail(array $params)
	{
		global $User;

		$email = self::testEmailAddress($params);

		if( Config::$mailerConfig['UseSendmail'] )
			$mailerUsed = l_t('the sendmail binary at %s', Config::$mailerConfig['SendmailSettings']['Location']);
		elseif( Config::$mailerConfig['UseSMTP'] )
			$mailerUsed = l_t('SMTP via %s', Config::$mailerConfig['SMTPSettings']['Host'].':'.Config::$mailerConfig['SMTPSettings']['Port']);
		elseif( Config::$mailerConfig['UseMail'] )
			$mailerUsed = l_t("PHP's mail() function");
		else
			throw new Exception(l_t("No mailer type is set up; one of UseMail, UseSendmail or UseSMTP has to be enabled in the mailer config before e-mail can be sent."));

		require_once(l_r('objects/mailer.php'));
		$Mailer = new Mailer();

		$sentAt = gmdate("Y-m-d H:i:s");

		// Any problem the mailer runs into is thrown, and displayed above this task's form
		$Mailer->Send(array($email=>$email), l_t('E-mail test'),
			l_t("This is a test e-mail, sent by %s from the admin control panel at %s (GMT+0) to check that e-mail sending is working.",
				$User->username, $sentAt)."<br /><br />".
			l_t("If you received this then e-mail sending is working, and nothing further needs to be done."));

		if( Config::$mailerConfig['UseDebug'] )
			return l_t('The mailer is in debug mode, so nothing was sent; the message which would have gone to %s is printed above.', htmlspecialchars($email));

		return l_t('A test e-mail was sent to %s using %s, and the mailer gave no error. '.
			'If it doesn\'t arrive check that address\'s spam folder and the mail server\'s logs.',
			htmlspecialchars($email), $mailerUsed);
	}

	public function wipeDATCTestGame(array $params)
	{
		global $DB;

		require_once('gamemaster/game.php');

		$DB->sql_put("BEGIN");
		list($gameID) = $DB->sql_row("SELECT id FROM wD_Games WHERE name='DATC-Adjudicator-Test'");
		processGame::eraseGame($gameID);
		$DB->sql_put("COMMIT");

		return l_t("DATC test game and associated data removed.");
	}
	public function clearAccessLogs(array $params)
	{
		// global $DB;
		// list($i) = $DB->sql_row("SELECT COUNT(userID) FROM wD_AccessLog WHERE DATEDIFF(CURRENT_DATE, lastRequest) > 30");
		// $DB->sql_put("DELETE FROM wD_AccessLog WHERE DATEDIFF(CURRENT_DATE, lastRequest) > 30");
		// $DB->sql_put("OPTIMIZE TABLE wD_AccessLog");

		return l_t('Disabled, do NOT clear access logs for ANY reason.');
	}

	public function clearAdminLogs(array $params)
	{
		// global $DB;
		// $DB->sql_put("BEGIN");
		// list($i) = $DB->sql_row("SELECT COUNT(userID) FROM wD_AdminLog");
		// $DB->sql_put("DELETE FROM wD_AdminLog");
		// $DB->sql_put("COMMIT");

		return l_t('Disabled, do not clear admin log.');
	}

	public function giveModerator(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];

		$modUser = new User($userID);

		if( $modUser->type['Moderator'] )
			throw new Exception(l_t("This user is already a moderator"));

		$DB->sql_put("UPDATE wD_Users SET type = CONCAT_WS(',',type,'Moderator') WHERE id = ".$userID);

		return l_t('This user was given moderator status.');
	}

	public function giveSeniorModerator(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];

		$modUser = new User($userID);

		if( $modUser->type['SeniorMod'] )
			throw new Exception(l_t("This user is already a senior moderator"));
		
		if( ! $modUser->type['Moderator'] )
			throw new Exception(l_t("This user is not a moderator"));

		$DB->sql_put("UPDATE wD_Users SET type = CONCAT_WS(',',type,'SeniorMod') WHERE id = ".$userID);

		return l_t('This user was given senior moderator status.');
	}

	public function takeModerator(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];

		$modUser = new User($userID);

		if( ! $modUser->type['Moderator'] )
			throw new Exception(l_t("This user isn't a moderator"));

		if( $modUser->type['SeniorMod'] )
			throw new Exception(l_t("Remove Senior Mod status first."));

		$DB->sql_put("UPDATE wD_Users SET type = REPLACE(type,'Moderator','') WHERE id = ".$userID);

		return l_t('This user had their moderator status taken.');
	}

	public function takeSeniorModerator(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];

		$modUser = new User($userID);

		if( ! $modUser->type['SeniorMod'] )
			throw new Exception(l_t("This user isn't a senior moderator"));

		$DB->sql_put("UPDATE wD_Users SET type = REPLACE(type,'SeniorMod','') WHERE id = ".$userID);

		return l_t('This user had their senior moderator status taken.');
	}

	public function giveForumModerator(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];

		$modUser = new User($userID);

		if( $modUser->type['ForumModerator'] )
			throw new Exception(l_t("This user is already a forum moderator"));

		$DB->sql_put("UPDATE wD_Users SET type = CONCAT_WS(',',type,'ForumModerator') WHERE id = ".$userID);

		return l_t('This user was given forum moderator status.');
	}

	public function takeForumModerator(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];

		$modUser = new User($userID);

		if( ! $modUser->type['ForumModerator'] )
			throw new Exception(l_t("This user isn't a forum moderator"));

		$DB->sql_put("UPDATE wD_Users SET type = REPLACE(type,'ForumModerator','') WHERE id = ".$userID);

		return l_t('This user had their forum moderator status taken.');
	}

	public function changeEmail(array $params)
	{
		global $DB;
		global $User;

		$userID = (int)$params['userID'];
		$newEmail = (string)$params['email'];

		if( !isset($params['reason']) || strlen($params['reason'])==0 )
		{
			return l_t("Could not change email because no reason was given.");
		}

		$changeReason = $DB->msg_escape($params['reason']);

		// check if new email is claimed by another account
		list($result) = $DB->sql_row("SELECT username FROM wD_Users WHERE email = '".$newEmail."'");
		if (!empty($result)) 
		{
			return l_t("This email has already been taken by another user: %s.", $result);
		}

		// get and store user's old email
		$oldEmail = $DB->sql_hash("SELECT username, email FROM wD_Users WHERE id = ".$userID);

		if (empty($oldEmail))
		{
			return l_t("User id %s has not been found.", $userID);
		}

		$time = time();
		$changedBy = $User->username;

		$DB->sql_put(
			'INSERT INTO wD_EmailHistory (userID, oldEmail, newEmail, date, reason, changedBy) 
			VALUES ('.$userID.', "'.$oldEmail['email'].'", "'.$newEmail.'", '.$time.', "'.$changeReason.'", "'.$changedBy.'")'
		);
		$DB->sql_put(
			"UPDATE wD_Users SET email = '".$newEmail."' WHERE id = ".$userID." limit 1"
		);

		return l_t("%s's email has been changed from %s to %s.", $oldEmail['username'], $oldEmail['email'], $newEmail);

	}

	public function changeUsername(array $params)
	{
		global $DB;
		global $User;
		
		$userID = (int)$params['userID'];
		$newUsername = (string)$params['username'];

		if( !isset($params['reason']) || strlen($params['reason'])==0 )
		{
			return l_t("Could not change username because no reason was given.");
		}

		$changeReason = $DB->msg_escape($params['reason']);

		// check if username exists
		list($result) = $DB->sql_row("SELECT username FROM wD_Users WHERE username = '".$newUsername."'");
		if (!empty($result)) 
		{
			return l_t("This username has already been taken by another user.");
		}
		
		// get and store old username and if it exists set new username
		list($oldUsername) = $DB->sql_row("SELECT username FROM wD_Users WHERE id = ".$userID);
		if (empty($oldUsername))
		{
			return l_t("User id %s has not been found.", $userID);
		}

		$time = time();
		$changedBy = $User->username;

		$DB->sql_put(
			'INSERT INTO wD_UsernameHistory (userID, oldUsername, newUsername, date, reason, changedBy) 
			VALUES ("'.$userID.'", "'.$oldUsername.'", "'.$newUsername.'", "'.$time.'", "'.$changeReason.'", "'.$changedBy.'")'
		);
		$DB->sql_put("UPDATE wD_Users SET username = '".$newUsername."' WHERE id = '".$userID."' limit 1");

		// update new forum on webdip
		if (isset(Config::$customForumURL))
		{
			$newUsernameClean = strtolower($newUsername);

			$DB->sql_put(
				"UPDATE phpbb_users SET username = '".$newUsername."', username_clean = '".$newUsernameClean."' 
				WHERE username = '".$oldUsername."' AND webdip_user_id = '".$userID."' limit 1"
			);
		}

		return l_t("This user's username has been changed from %s to %s.",$oldUsername, $newUsername);
	}
	
	public function createUser(array $params)
	{
		global $DB;
		$username = $DB->escape($params['username']);
		$DB->sql_put("INSERT INTO wD_Users (username,email,points,timeJoined,locale,type) VALUES ('" . $username . "','" . $username . "',100,".time().",'English','User')");
		$userID = $DB->last_inserted();

		return l_t("<a href='profile.php?userID=".$userID."'>User #".$userID."</a> created.");
	}
	public function giveBot(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];

		$botUser = new User($userID);

		if( $botUser->type['Bot'] )
			throw new Exception(l_t("This user is already a bot"));

		$DB->sql_put("UPDATE wD_Users SET type = CONCAT_WS(',',type,'Bot') WHERE id = ".$userID);

		return l_t('This user was given bot status.');
	}

	public function takeBot(array $params)
	{
		global $DB;

		$userID = (int)$params['userID'];

		$botUser = new User($userID);

		if( ! $botUser->type['Bot'] )
			throw new Exception(l_t("This user isn't a bot"));

		$DB->sql_put("UPDATE wD_Users SET type = REPLACE(type,'Bot','') WHERE id = ".$userID);

		return l_t('This user had their bot status taken.');
	}

	public function checkPausedGames(array $params)
	{
		global $DB;

		list($minTime) = $DB->sql_row(
			"SELECT MIN(processTime) FROM wD_Games
			WHERE NOT phase='Finished' AND processStatus='Not-processing'
				AND processTime > 123456789"
		);

		$affected = 0;
		$DB->sql_put(
			"UPDATE `wD_Games` SET
				processTime=NULL,
				pauseTimeRemaining=phaseMinutes*60
			WHERE pauseTimeRemaining IS NULL AND processStatus='Paused'"
		);
		$affected += $DB->last_affected();

		$DB->sql_put(
			"UPDATE `wD_Games` SET
				processTime=(".$minTime."+phaseMinutes*60),
				pauseTimeRemaining=NULL
			WHERE processTime IS NULL AND processStatus='Not-processing'"
		);
		$affected += $DB->last_affected();

		return l_t('Any invalid next-process/pause-length times have been reset; %s game(s) affected.',$affected);
	}

	public function reprocessGame(array $params)
	{
		global $DB, $Game;

		$gameID = (int)$params['gameID'];

		/*
		 * - Check that the game is still active and can be turned back
		 *
		 * - Delete current turn values
		 * - Calculate the turn being moved back to
		 *
		 * - Move the old MovesArchive back to Units
		 * - Move the old MovesArchive (JOIN Units) back to Orders
		 * - Move the old TerrStatusArchive (JOIN Units) back to TerrStatus
		 *
		 * - Update the game turn, phase and next process time
		 *
		 * - Delete Archive values if we have moved back a turn
		 * - Remove the invalid maps in the mapstore
		 */
		$DB->sql_put("BEGIN");

		require_once(l_r('gamemaster/game.php'));
		$Variant=libVariant::loadFromGameID($gameID);
		$Game = $Variant->processGame($gameID);

		$oldPhase = $Game->phase;
		$oldTurn = $Game->turn;

		$Game->moveTurnBack();

		return l_t('This game was moved from %s, %s back to Diplomacy, %s, and is ready to be reprocessed.',
			$oldPhase,$Game->datetxt($oldTurn),$Game->datetxt($Game->turn)); // moveTurnBack() reloads $Game at the turn moved back to
	}

	/**
	 * Restore sandbox games which a cleanup finished while they were still in use.
	 *
	 * The gamemaster's weekly sandbox cleanup used to take a game whose *any* member row had a
	 * timeLoggedIn over a week old. A sandbox's countries all belong to its creator and only the one
	 * they last opened on board.php had that kept up to date, so every sandbox was drawn a week after
	 * it was created however much it was being played.
	 *
	 * It is recoverable because the cleanup is a single UPDATE of wD_Games: the units, territories and
	 * orders were never deleted, as they are when a game really finishes (see Game::process(), which
	 * clears them once the phase becomes Finished). A finished game with units still on the table was
	 * therefore ended by a cleanup and not by being played out, which is what this selects on.
	 */
	public function restoreCancelledSandboxGames(array $params)
	{
		global $DB;

		require_once(l_r('gamemaster/game.php'));

		$userID = ( isset($params['sandboxUserID']) && trim($params['sandboxUserID']) !== '' ) ? (int)$params['sandboxUserID'] : null;

		$tabl = $DB->sql_tabl("SELECT g.id, g.variantID, g.turn, g.name, g.sandboxCreatedByUserID
			FROM wD_Games g
			WHERE g.sandboxCreatedByUserID IS NOT NULL
				AND g.phase = 'Finished'
				".( is_null($userID) ? "" : "AND g.sandboxCreatedByUserID = ".$userID )."
				AND EXISTS ( SELECT 1 FROM wD_Units u WHERE u.gameID = g.id )
			ORDER BY g.id");

		$games = array();
		while($row = $DB->tabl_hash($tabl)) $games[] = $row;

		if( count($games) == 0 )
			return l_t('No cancelled sandbox games with a board still on the table were found%s.',
				is_null($userID) ? '' : l_t(' for user %s', $userID));

		/*
		 * Each game is restored in its own transaction, and a run stops with time to spare rather than
		 * being cut off by the request timing out: whatever has been committed stays restored and the
		 * rest are picked up by running this again, as a restored game no longer matches the search.
		 */
		$restored = array();
		$startTime = time();
		foreach($games as $row)
		{
			if( ( time() - $startTime ) > 20 ) break;

			$gameID = (int)$row['id'];

			/*
			 * Which phase it was in. The cleanup overwrote the phase but nothing else, so the orders
			 * waiting to be entered say what it was: builds and retreats orders exist only in their own
			 * phases. A retreating unit is the same answer from the other direction, for a retreats phase
			 * whose orders had not been generated yet.
			 */
			list($retreatOrders) = $DB->sql_row("SELECT COUNT(1) FROM wD_Orders
				WHERE gameID = ".$gameID." AND type IN ('Retreat','Disband')");
			list($buildOrders) = $DB->sql_row("SELECT COUNT(1) FROM wD_Orders
				WHERE gameID = ".$gameID." AND type IN ('Build Army','Build Fleet','Wait','Destroy')");
			list($retreating) = $DB->sql_row("SELECT COUNT(1) FROM wD_TerrStatus
				WHERE gameID = ".$gameID." AND retreatingUnitID IS NOT NULL");

			if( $retreatOrders > 0 || $retreating > 0 ) $phase = 'Retreats';
			elseif( $buildOrders > 0 ) $phase = 'Builds';
			else $phase = 'Diplomacy';

			$DB->sql_put("UPDATE wD_Games
				SET phase = '".$phase."', gameOver = 'No', finishTime = NULL,
					processStatus = 'Not-processing', pauseTimeRemaining = NULL, processTime = 2000000000
				WHERE id = ".$gameID);

			/*
			 * The same cleanup run set every Playing member to Survived. A sandbox's members are all its
			 * creator, so they go back to Playing, except for countries with nothing left on the board,
			 * which are Defeated - the same rule moveTurnBack() uses.
			 */
			$DB->sql_put("UPDATE wD_Members m
				SET m.status = IF( EXISTS ( SELECT 1 FROM wD_Units u WHERE u.gameID = m.gameID AND u.countryID = m.countryID ), 'Playing', 'Defeated' ),
					m.votes = ''
				WHERE m.gameID = ".$gameID." AND m.status IN ('Survived','Drawn','Won','Resigned')");

			$DB->sql_put("COMMIT");

			Game::cacheTurnPhase($gameID, (int)$row['turn'], $phase); // For the SSE server
			Game::wipeCache($gameID);

			/*
			 * The game's public JSON files still describe a finished game, but they are not rewritten here:
			 * game/playercontext rewrites whichever of them are out of date when the game is next opened
			 * (see api/responses/player_context.php), and rebuilding the history of a long game is slow
			 * enough that doing it for every game restored is what made this time out.
			 */

			$restored[] = $gameID.' ('.$row['name'].', '.$phase.')';
		}

		$left = count($games) - count($restored);
		if( count($restored) > 25 )
			$restored = array_merge(array_slice($restored, 0, 25), array(l_t('and %s more', count($restored)-25)));

		return l_t('Restored %s sandbox game(s): %s.%s', count($games) - $left, implode(', ', $restored),
			$left > 0 ? ' '.l_t('%s still to go; run this again to carry on.', $left) : '');
	}

	public function recreateUnitDestroyIndex(array $params)
	{
		global $DB;

		$mapID = (int)$params['mapID'];

		require_once("variants/install.php");

		InstallTerritory::loadExistingTerritories($mapID);

		// Generate the SQL before wiping & reinserting it
		$unitDestroyIndexRecreateSQL = InstallTerritory::unitDestroyIndexSQL($mapID);

		$DB->sql_put("BEGIN");

		list($entriesBefore) = $DB->sql_row("SELECT COUNT(*) FROM wD_UnitDestroyIndex WHERE mapID = ".$mapID);

 		$DB->sql_put("DELETE FROM wD_UnitDestroyIndex WHERE mapID = ".$mapID);

 		$DB->sql_put($unitDestroyIndexRecreateSQL);

 		list($entriesAfter) = $DB->sql_row("SELECT COUNT(*) FROM wD_UnitDestroyIndex WHERE mapID = ".$mapID);

		$DB->sql_put("COMMIT");

		return l_t('The unit destroy indexes were recreated for map ID #%s ; there were %s entries before and there are currently %s entries.', $mapID, $entriesBefore, $entriesAfter);
	}

	private function makeDonatorType(array $params, $type='') 
	{
		global $DB;

		$userID = (int)$params['userID'];

		$DB->sql_put("UPDATE wD_Users SET type = CONCAT_WS(',',type,'Donator".$type."') WHERE id = ".$userID);

		// If we're using the new forum then add a rank to the user. 
		if( isset(Config::$customForumURL) && ($type == 'Gold' || $type == 'Silver' || $type == 'Bronze') ) 
		{
			// Make sure the user has a new forum profile before trying an insert into the custom tables.
			list($newForumID) = $DB->sql_row("SELECT user_id FROM `phpbb_users` WHERE webdip_user_id = ".$userID);
			if ($newForumID > 0)
			{
				$rank = 12;
				switch ($type) 
				{
					case 'Gold':
						$rank = 12;
						break;
					case 'Silver':
						$rank = 13;
						break;
					case 'Bronze':
						$rank = 14;
						break;
				}
				
				$DB->sql_put("UPDATE phpbb_users SET user_rank = ".$rank." WHERE user_rank = 0 and webdip_user_id = ".$userID);
			}
		}

		return l_t('User ID %s given donator status.',$userID);
	}

	public function updateDonators(array $params)
	{
		global $DB;
		
		if( isset(Config::$customForumURL) ) 
		{
			$DB->sql_put("UPDATE phpbb_users p INNER JOIN wD_Users u ON u.id = p.webdip_user_id SET p.user_rank = 12 WHERE p.user_rank in (0,13,14) and u.type like '%DonatorGold%'");
			$DB->sql_put("UPDATE phpbb_users p INNER JOIN wD_Users u ON u.id = p.webdip_user_id SET p.user_rank = 13 WHERE p.user_rank in (0,14) and u.type like '%DonatorSilver%'");
			$DB->sql_put("UPDATE phpbb_users p INNER JOIN wD_Users u ON u.id = p.webdip_user_id SET p.user_rank = 14 WHERE p.user_rank in (0) and u.type like '%DonatorBronze%'");

			return l_t('Donator ranks synced with User tables');
		}
		else
		{
			return 'This tool is not for use outside webdip';
		}
	}

	public function makeDonator(array $params)
	{
		return $this->makeDonatorType($params);
	}

	public function makeDonatorPlatinum(array $params)
	{
		return $this->makeDonatorType($params,'Platinum');
	}

	public function makeDonatorGold(array $params)
	{
		return $this->makeDonatorType($params,'Gold');
	}

	public function makeDonatorSilver(array $params)
	{
		return $this->makeDonatorType($params,'Silver');
	}

	public function makeDonatorBronze(array $params)
	{
		return $this->makeDonatorType($params,'Bronze');
	}

	public function resetLastProcessTime(array $params)
	{
		global $Misc;

		$Misc->LastProcessTime = time();
		$Misc->write();

		return l_t('Last process time reset');
	}

	public function unCrashGames(array $params)
	{
		global $DB;

		require_once(l_r('gamemaster/game.php'));

		$excludeGameIDs = explode(',', $params['excludeGameIDs']);

		foreach($excludeGameIDs as $index=>$gameID)
		{
			$gameID = (int)$gameID;
			$excludeGameIDs[$index] = $gameID;
		}
		$excludeGameIDs = implode(',', $excludeGameIDs);

		$tabl = $DB->sql_tabl("SELECT * FROM wD_Games WHERE processStatus = 'Crashed' ".( $excludeGameIDs ? "AND id NOT IN (".$excludeGameIDs.")" : "" )." FOR UPDATE");

		$count=0;
		while($row=$DB->tabl_hash($tabl))
		{
			$count++;

			$Variant=libVariant::loadFromVariantID($row['variantID']);
			$Game = $Variant->processGame($row);

			if( $Game->phase == 'Finished' )
			{
				$DB->sql_put("UPDATE wD_Games SET processStatus = 'Not-processing', pauseTimeRemaining=NULL, processTime = ".time()." WHERE id = ".$Game->id);
				continue;
			}

			if ( $Game->phaseMinutes < 12*60 )
			{
				if( $Game->phase == 'Pre-game' )
				{
					$newTimeDetails = "";
					$DB->sql_put("UPDATE wD_Games SET processStatus = IF(pauseTimeRemaining IS NULL,'Not-processing','Paused') WHERE id = ".$Game->id);
				}
				else
				{
					$newTimeDetails = l_t(", and the game has been paused, since it's a fast game, to give players a chance to regroup");
					$Game->processStatus = 'Not-processing';
					$Game->togglePause();
				}
			}
			else
			{
				$newTimeDetails = l_t(", and the game's next process time has been reset");
				$DB->sql_put("UPDATE wD_Games SET processStatus = 'Not-processing', processTime = ".time()." + 60*phaseMinutes, pauseTimeRemaining=NULL WHERE id = ".$Game->id);
			}

			$Game->Members->send('No',l_t("This game has been uncrashed%s. Thanks for your patience.",$newTimeDetails));
		}

		$details = l_t('All crashed games were un-crashed');

		if ( $excludeGameIDs )
			$details .= l_t(', except: %s',$excludeGameIDs);

		$details .= l_t('. %s games in total.',$count);

		return $details;
	}

	public function addApiKey($params) 
	{
		global $DB;

		// Generating API Key
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';

		for ($i = 0; $i < 80; $i++) 
		{
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}

		$userID = intval($params['userID']);
		$apiKey = strval($randomString);
		$row = $DB->sql_hash('SELECT COUNT(userID) AS hasUserID FROM wD_ApiKeys WHERE userID = '.$userID);

		if ($row['hasUserID'])
			throw new Exception(l_t('An API key is already associated to user ID %s.', $userID));

		$row = $DB->sql_hash('SELECT COUNT(apiKey) AS hasApiKey FROM wD_ApiKeys WHERE apiKey = '.$userID);

		if ($row['hasApiKey'])
			throw new Exception(l_t('This API key is already associated with a user ID.'));

		$DB->sql_put('INSERT INTO wD_ApiKeys (userID, apiKey) VALUES ('.$userID.', "'.$apiKey.'")');
		$DB->sql_put("COMMIT");

		return l_t('API key %s successfully added for user ID %s.', $randomString, $userID);
	}

	public function deleteApiKey($params) 
	{
		global $DB;

		$userID = intval($params['userID']);

		$DB->sql_put('DELETE FROM wD_ApiPermissions WHERE userID = '.$userID);
		$DB->sql_put('DELETE FROM wD_ApiKeys WHERE userID = '.$userID);
		$DB->sql_put("COMMIT");

		return l_t('API key(s) removed for user ID %s.', $userID);
	}

	public function setApiPermission($params) 
	{
		global $DB;

		$userID = intval($params['userID']);
		$permissionName = strval($params['permissionName']);
		$permissionValue = strval($params['permissionValue']);

		$currentPermissions = array(
			'getStateOfAllGames',
			'submitOrdersForUserInCD',
			'listGamesWithPlayersInCD',
		);

		if (!in_array($permissionName, $currentPermissions))
			throw new Exception('Unknown permission "'.$permissionName.'". Should be one of: ['.implode(', ', $currentPermissions).'].');

		if (!in_array($permissionValue, array('Yes', 'No')))
			throw new Exception('Invalid permission value "'.$permissionValue.'". Should be either "Yes" or "No".');

		$row = $DB->sql_hash('SELECT COUNT(userID) AS hasUserID FROM wD_ApiKeys WHERE userID = '.$userID);

		if (!$row['hasUserID'])
			throw new Exception(l_t('No API key for user %s. You should create an API key for this user before setting permissions for him.', $userID));

		$row = $DB->sql_hash('SELECT COUNT(userID) AS hasPermissionsEntry FROM wD_ApiPermissions WHERE userID = '.$userID);

		if ($row['hasPermissionsEntry']) 
		{
			$DB->sql_put("UPDATE wD_ApiPermissions SET $permissionName = '$permissionValue' WHERE userID = $userID;");
		} 

		else 
		{
			$DB->sql_put("INSERT INTO wD_ApiPermissions (userID, $permissionName) VALUES ($userID, '$permissionValue');");
		}

		$DB->sql_put("COMMIT");
		return l_t('Permissions successfully set.');
	}

	public function showApiKeys($params) 
	{
		global $DB;

		$userID = intval($params['userID']);

		$row = $DB->sql_hash("
		SELECT k.apiKey, IFNULL(p.getStateOfAllGames, 'No') as getStateOfAllGames, IFNULL(p.listGamesWithPlayersInCD, 'No') as listGamesWithPlayersInCD,
		IFNULL(p.submitOrdersForUserInCD, 'No') as submitOrdersForUserInCD
		FROM wD_ApiKeys AS k
		LEFT JOIN wD_ApiPermissions AS p ON (k.userID = p.userID)
		WHERE k.userID = ".$userID);

		if (!$row)
			return l_t('No api Key for user %s.', $userID);

		return "
		<div><strong>User ID</strong>: ".$userID."</div>
		<div><strong>API key</strong>: ".$row['apiKey']."</div>
		<div><strong>getStateOfAllGames</strong>: ".$row['getStateOfAllGames']."</div>
		<div><strong>listGamesWithPlayersInCD</strong>: ".$row['listGamesWithPlayersInCD']."</div>
		<div><strong>submitOrdersForUserInCD</strong>: ".$row['submitOrdersForUserInCD']."</div>
		";
	}

	public function calculateGR($params)
	{
		require_once(l_r('ghostratings/calculations.php'));
		global $DB;
		$batch_size = (int)$params['batchSize'];
		if ($batch_size <= 0)
		{
			return l_t('Invalid batch size. Please enter a positive integer.');
		}
		$data = $DB->sql_tabl("SELECT id, variantID, pressType, potType, turn, gameOver, phaseMinutes, processTime, playerTypes FROM wD_Games WHERE gameOver <> 'No' AND grCalculated <> 1 ORDER BY processTime ASC LIMIT ".$batch_size.";");
		while (list($gameID, $variantID, $pressType, $potType, $turn, $gameOver, $phaseMinutes, $time, $playerTypes) = $DB->tabl_row($data))
		{
			$winnerID = 0;
			list($SCTarget, $SCTotal) = $DB->sql_row("SELECT supplyCenterTarget, supplyCenterCount FROM wD_VariantInfo WHERE variantID=".$variantID);
			$players = $DB->sql_tabl("SELECT userID, supplyCenterNo, status FROM wD_Members WHERE gameID =".$gameID);
			$SCcounts = array();
			$memberStatus = array();
			while(list($userID, $SCcount, $status) = $DB->tabl_row($players))
			{
				$SCcounts[$userID] = $SCcount;
				$memberStatus[$userID] = $status;
				if ($status == 'Won')
				{
					$winnerID = $userID;
				}
			}
			$botGame = True;
			if($playerTypes == 'Members')
			{
				$botGame = False;
			}
			$ghostRatings = new GhostRatings($gameID, $SCcounts, $memberStatus, $variantID, $pressType, $potType, $turn, $gameOver, $phaseMinutes, $SCTarget, $SCTotal, $winnerID, $botGame, $time);
		  $ghostRatings->processGR();
		}
		return l_t('GR Calculated');
	}

	public function updateVariantInfo($params) 
	{
		global $DB;

		$variantID = (int)$params['variantID'];
		$variantIDs = array();

		if ($variantID <> 0)
		{
			$variantIDs[] = $variantID;
		}
		else 
		{
			foreach(Config::$variants as $id => $name)
			{
				$variantIDs[] = $id;
			}
		}

		foreach($variantIDs as $key => $value)
		{
			$sql = "INSERT INTO wD_VariantInfo(variantID, mapID, supplyCenterTarget, supplyCenterCount, countryCount, name, fullName, description, author";
			$Variant=libVariant::loadFromVariantID($value);
			$mapID = $Variant->mapID;
			$SCCount = $Variant->supplyCenterCount;
			$SCTarget = $Variant->supplyCenterTarget;
			$name = $Variant->name;
			$fullName = $Variant->fullName;
			$description = $Variant->description;
			$author = $Variant->author;
			$countryCount = count($Variant->countries);
			$sql2 = "VALUES(".$value.", ".$mapID.", ".$SCTarget.", ".$SCCount.", ".$countryCount.", '".$name."', '".$fullName."', '".$description."', '".$author."'";
			
			$adapter = '';
			if(isset($Variant->$adapter))
			{
				$sql .= ", adapter";
				$adapter = $Variant->adapter;
				$sql2 = $sql2.", '".$adapter."'";
			}

			$version = '';
			if(isset($Variant->$version))
			{
				$sql .= ", version";
				$version = $Variant->version;
				$sql2 = $sql2.", '".$version."'";
			}

			$codeVersion = '';
			if(isset($Variant->$codeVersion))
			{
				$sql .= ", codeVersion";
				$codeVersion = $Variant->codeVersion;
				$sql2 = $sql2.", '".$codeVersion."'";
			}

			$homepage = '';
			if(isset($Variant->$homepage))
			{
				$sql .= ", homepage";
				$homepage = $Variant->homepage;
				$sql2 = $sql2.", '".$homepage."'";
			}

			$countryList = implode(",",$Variant->countries);
			$sql2 = $sql2.", '".$countryList."')";
			$sql .= ", countriesList) ";
			$sql .= $sql2;

			$DB->sql_put("DELETE FROM wD_VariantInfo WHERE variantID=".$value);
			$DB->sql_put($sql);
		}
		return l_t('Variant Info Updated');
	}
}

?>
