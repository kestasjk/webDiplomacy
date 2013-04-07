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

require_once(l_r('admin/adminActions.php'));
require_once(l_r('admin/adminActionsForum.php'));
require_once(l_r('admin/adminActionsRestricted.php'));

/**
 * This class uses data about admin tasks, provided by child classes, to
 * present a form for each task, send task parameters to the right task-code,
 * and save the results in the admin log table.
 *
 * @package Admin
 */
class adminActionsForms
{
	/**
	 * An array of admin/moderator task details; the friendly name,
	 * the description, a sub-array of friendly parameter names
	 * indexed by parameter code-names, all indexed by the
	 * task/action's code-name.
	 *
	 * @var array
	 */
	//public $actions;

	public static $globalGameID;
	public static $globalUserID;
	public static $globalPostID;

	/**
	 * Output the form which needs to be filled out to perform some action
	 *
	 * @param string $actionName The code-name for the desired task
	 * @param array $params The parameters which the task's code will accept. The friendly form-name indexed by the code-name
	 */
	private static function form($actionName, array $params, $description="")
	{
		print '<form action="admincp.php#'.$actionName.'" method="post">
			<input type="hidden" name="actionName" value="'.$actionName.'" />';

		if ( isset($_REQUEST['globalGameID']) )
			print '<input type="hidden" name="globalGameID" value="'.intval($_REQUEST['globalGameID']).'" />';
		if ( isset($_REQUEST['globalUserID']) )
			print '<input type="hidden" name="globalUserID" value="'.intval($_REQUEST['globalUserID']).'" />';
		if ( isset($_REQUEST['globalPostID']) )
			print '<input type="hidden" name="globalPostID" value="'.intval($_REQUEST['globalPostID']).'" />';
		
		if ($description)
			print '<li class="formlistdesc" style="margin-bottom:10px">'.l_t($description).'</li>';

		foreach( $params as $paramCode=>$paramName )
		{
			// Can we auto-fill the ID field?
			if ( isset($_REQUEST[$paramCode]) )
				$defaultValue = $_REQUEST[$paramCode];
			elseif ( $paramCode == 'gameID' && self::$globalGameID )
				$defaultValue = self::$globalGameID;
			elseif ( $paramCode == 'userID' && self::$globalUserID )
				$defaultValue = self::$globalUserID;
			elseif ( $paramCode == 'postID' && self::$globalPostID )
				$defaultValue = self::$globalPostID;
			else
				$defaultValue = '';

			print '<li class="formlistfield">
					<label for="'.$paramCode.'">'.l_t($paramName).'</label>:
					<input type="text" name="'.$paramCode.'" value="'.$defaultValue.'" length="50" />
					</li>';
		}

		print '<li class="formlistfield" style="margin-bottom:20px">
			<input class="form-submit" type="submit" name="Submit" value="'.l_t('Submit').'" /> '.
			( self::isActionDangerous($actionName) ? '<em>('.l_t('Careful; not confirmed!').')</em>' : '').'
			</li>';

		print '</form>';
	}

	private static function isActionDangerous($actionCode)
	{
		if ( method_exists('adminActions', $actionCode.'Confirm')||method_exists('adminActionsRestricted', $actionCode.'Confirm') )
			return false;
		else
			return true;
	}

	/**
	 * Create a new AdminLog entry so the admin/moderator action just performed can be viewed by others.
	 *
	 * @param string $name The friendly (non-code) name of the adminAction performed
	 * @param array $paramValues The parameters (if any) which were passed to the code, indexed by code-name
	 * @param string $details A details string containing a friendly message stating what happened
	 */
	private static function save($name, array $paramValues, $details)
	{
		global $DB, $User;

		$name = $DB->escape($name);

		$paramValues = $DB->escape(serialize($paramValues));

		$details = $DB->msg_escape($details);

		$DB->sql_put("INSERT INTO wD_AdminLog ( name, userID, time, details, params )
					VALUES ( '".$name."', ".$User->id.", ".time().", '".$details."', '".$paramValues."' )");
	}
	
	/**
	 * For the given task display the form, and run the task if data entered from the corresponding form
	 *
	 * @param string $actionName The code-name for the desired task
	 */
	public function process($actionName)
	{
		global $Misc;

		extract(adminActions::$actions[$actionName]);

		print '<li class="formlisttitle">
			<a name="'.$actionName.'"></a>'.l_t($name).'</li>';

		try
		{
			if ( isset($_REQUEST['actionName']) and $_REQUEST['actionName'] == $actionName )
			{
				print '<li class="formlistfield">';

				$paramValues = array();
				foreach($params as $paramName=>$paramFullName)
				{
					if ( isset($_REQUEST[$paramName]) )
						$paramValues[$paramName] = $_REQUEST[$paramName];
				}

				if ( isset($paramValues['gameID']) )
				{
					require_once(l_r('objects/game.php'));
					$Variant=libVariant::loadFromGameID((int)$paramValues['gameID']);
					$Game = $Variant->Game((int)$paramValues['gameID']);
					print '<p>'.l_t('Game link').': <a href="board.php?gameID='.$Game->id.'">'.$Game->name.'</a></p>';
				}

				if( isset($paramValues['userID']) )
				{
					$User = new User((int)$paramValues['userID']);
					print '<p>'.l_t('User link').': '.$User->profile_link().'</p>';
				}

				if( isset($paramValues['postID']) )
				{
					print '<p>'.l_t('Post link').': '.libHTML::threadLink($paramValues['postID']).'</p>';
				}

				// If it needs confirming but ( hasn't been confirmed or is being resubmitted ):
				if ( !self::isActionDangerous($actionName) && ( !isset($_REQUEST['actionConfirm']) || !libHTML::checkTicket() ) )
				{
					print '<strong>'.$this->{$actionName.'Confirm'}($paramValues).'</strong>';

					print '<form action="admincp.php#'.$actionName.'" method="post">
						<input type="hidden" name="actionName" value="'.$actionName.'" />
						<input type="hidden" name="actionConfirm" value="on" />
						<input type="hidden" name="formTicket" value="'.libHTML::formTicket().'" />';

					foreach($paramValues as $name=>$value)
						print '<input type="hidden" name="'.$name.'" value="'.$value.'" />';

					print '</li><li class="formlistfield" style="margin-bottom:20px">
						<input type="submit" class="form-submit" value="Confirm" />

						</form>';
				}
				else
				{
					$details = $this->{$actionName}($paramValues);

					self::save($name, $paramValues, $details);

					$description = '<p class="notice">'.$details.'</p>
									<p>'.l_t($description).'</p>';

					$Misc->LastModAction = time();
				}

				print '</li>';
			}
		}
		catch(Exception $e)
		{
			$description = '<p><strong>'.l_t('Error').':</strong> '.$e->getMessage().'</p>
							<p>'.l_t($description).'</p>';
		}

		self::form($actionName, $params, $description);
	}
}

class adminActionsLayout
{
	public static function printActionShortcuts()
	{
		global $User;

		$modActions=array();
		$modActions[] = '<a href="gamemaster.php" class="light">'.l_t('Run gamemaster').'</a><br />';
		$modActions[] = libHTML::admincp('panic', null, l_t('Toggle panic-mode'));

		if($User->type['Admin'])
		{
			$modActions[] = libHTML::admincp('notice', null, l_t('Toggle the site-wide notice'));
			$modActions[] = libHTML::admincp('maintenance', null, l_t('Toggle maintenance-mode')).'<br />';
			$modActions[] = libHTML::admincp('clearErrorLogs', null, l_t('Clear error-logs'));
			$modActions[] = libHTML::admincp('clearOrderLogs', null, l_t('Clear order-logs'));
			$modActions[] = libHTML::admincp('clearAccessLogs', null, l_t('Clear access-logs'));
			$modActions[] = libHTML::admincp('clearAdminLogs', null, l_t('Clear admin-logs')).'<br />';
			$modActions[] = libHTML::admincp('unCrashGames', array('excludeGameIDs'=>''), l_t('Un-crash any crashed games'));
		}

		if($modActions)
		{
			print '<p class="notice">';
			print implode(' - ', $modActions);
			print '</p>';
			print '<div class="hr"></div>';
		}
	}

	private static function sortedActionCodes()
	{
		$actionNames = array();
		$actionCodesByName = array();
		foreach(adminActions::$actions as $actionCode=>$action)
		{
			$actionNames[] = $action['name'];
			$actionCodesByName[$action['name']] = $actionCode;
		}

		sort($actionNames);
		$sortedActionCodes = array();
		foreach($actionNames as $actionName)
			$sortedActionCodes[] = $actionCodesByName[$actionName];

		return $sortedActionCodes;
	}

	public static function actionCodesByType()
	{
		$actionCodes = self::sortedActionCodes();

		$actionCodesByType = array('Game'=>array(), 'User'=>array(), 'Misc'=>array() );
		foreach($actionCodes as $actionCode)
		{
			if ( isset(adminActions::$actions[$actionCode]['params']['gameID']) )
				$type = 'Game';
			elseif ( isset(adminActions::$actions[$actionCode]['params']['userID']) )
				$type = 'User';
			else
				$type = 'Misc';

			$actionCodesByType[$type][] = $actionCode;
		}

		return $actionCodesByType;
	}

	public static function printActionLinks( array $actionCodes )
	{
		$actionCount = count($actionCodes);
		$actionMidPoint = ceil($actionCount/2);

		print '<div style="width:90%; margin-left:auto; margin-right:auto">';

		print '<div style="float:right; width:50%; text-align:left">';
		for($i=$actionMidPoint; $i<$actionCount; $i++)
		{
			print '<a href="#'.$actionCodes[$i].'">'.l_t(adminActions::$actions[$actionCodes[$i]]['name']).'</a><br />';
		}
		print '</div>';

		print '<div style="width:45%">';
		for($i=0; $i<$actionMidPoint; $i++)
		{
			print '<a href="#'.$actionCodes[$i].'">'.l_t(adminActions::$actions[$actionCodes[$i]]['name']).'</a><br />';
		}
		print '</div>';
		print '<div style="clear:both"></div>';

		print '</div>';
	}
}

if ( isset($_REQUEST['globalGameID']) )
	adminActionsForms::$globalGameID = (int)$_REQUEST['globalGameID'];
if ( isset($_REQUEST['globalUserID']) )
	adminActionsForms::$globalUserID = (int)$_REQUEST['globalUserID'];
if ( isset($_REQUEST['globalPostID']) )
	adminActionsForms::$globalPostID = (int)$_REQUEST['globalPostID'];

print '<div class="hr"></div>';

// A shortcut command area
adminActionsLayout::printActionShortcuts();

require_once(l_r('lib/gamemessage.php'));

// Include the admin-only tasks?
if ( $User->type['Admin'] )
	$adminActions = new adminActionsRestricted();
elseif ( $User->type['ForumModerator'] )
	$adminActions = new adminActionsForum();
else
	$adminActions = new adminActions();


// Create a bullet-point set of anchor shortcuts to each task

$actionCodesByType = adminActionsLayout::actionCodesByType();

print '<h3>'.l_t('Admin actions').'</h3>';
foreach($actionCodesByType as $type=>$actionCodes)
{
	print '<a name="'.strtolower($type).'Actions"></a><h4>'.l_t($type.' actions').'</h4>';
	adminActionsLayout::printActionLinks($actionCodes);
}

print '<div class="hr"></div>';

print '<h3>'.l_t('Forms').'</h3>';
// For each task display the form, and run the task if data entered from the corresponding form
print '<ul class="formlist">';
foreach($actionCodesByType as $type=>$actionCodes)
{
	print '<h4>'.l_t($type.' actions').'</h4>';

	foreach($actionCodes as $actionCode)
		$adminActions->process($actionCode);
}
print '</ul>';

?>
