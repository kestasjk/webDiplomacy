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

/**
 * @package Admin
 */

require_once('header.php');

ini_set('memory_limit',"128M"); // 8M is the default
ini_set('max_execution_time','240');

if ( $User->type['Moderator'] && isset($_REQUEST['viewOrderLogGame']) && isset($_REQUEST['viewOrderLogCountryID']) )
{
	$gameID=(int)$_REQUEST['viewOrderLogGame'];
	$countryID=(int)$_REQUEST['viewOrderLogCountryID'];

	require_once(l_r('objects/game.php'));
	$Variant=libVariant::loadFromGameID($gameID);
	$Game=$Variant->Game($gameID);



	if( !($data=file_get_contents(libCache::dirID(Config::orderlogDirectory(), $gameID, true).'/'.$countryID.'.txt')) )
	{
		trigger_error(l_t("Couldn't open file %s.txt",$log));
	}

	header('Content-type:text/plain');

	print $data;

	die();
}

if ( $User->type['Admin'] && isset($_REQUEST['viewErrorLog']) )
{
	$log=(int)$_REQUEST['viewErrorLog'];
	if( !($data=file_get_contents(Config::errorlogDirectory().'/'.$log.'.txt')) )
	{
		trigger_error(l_t("Couldn't open file %s.txt",$log));
	}

	header('Content-type:text/plain');

	print $data;

	die();
}

if ( $User->type['Admin'] && isset($_REQUEST['systemTask']) )
{
	if ( $Misc->Maintenance == 0 )
	{
		$Misc->Maintenance = 1;
		$Misc->write();
		libHTML::notice(l_t('Wait'),
			l_t("Make sure you're in maintenance-mode and no-one ".
			"else is using the system before running a system-task!").
			"<br /> ".
			l_t("Maintenance mode has been set, please wait 3 mins to make sure all ".
			"other users are done, then click ".
			"<a href='admincp.php?systemTask=%s'>here</a> to ".
			"run the system-task safely.",$_REQUEST['systemTask']).
			"<br /><br /> ".
			l_t("Once it has run successfully, maintenance-mode can be disabled."));
	}
	else
	{
		ini_set('memory_limit',"32M"); // 8M is the default
		ini_set('max_execution_time','120');

		switch($_REQUEST['systemTask'])
		{
			case 'defragTables':
				require_once(l_r('admin/systemTasks/defragTables.php'));
				die();
			case 'resetCountryIDBalancer':
				require_once(l_r('admin/systemTasks/resetCountryIDBalancer.php'));
				die();
		}
	}
}

libHTML::starthtml();
print '<div class="content">';

function adminCPTabs()
{
	global $User;
	
	if ($User->type['Admin'])
	{
		$tabs = array(
			'Control Panel'=>l_t('Perform admin tasks'),
			// 'Mod notes'=>l_t('Notes/reports left for/by the mod team'),
			'Status Info'=>l_t('View server status lists'),
			'Logs'=>l_t('Log of recent admin tasks'),
			'Groups'=>l_t('Management of Groups used for relationship / dispute management'),
			'Multi-accounts'=>l_t('Multi-account detector'),
			'Chatlogs'=>l_t('Check the ingame chat.'),
			'AccessLog'=>l_t('Check the user-actions sort by IP and Username.'),
			'Locales'=>l_t('Locale management')
		);
	}
	else
	{
		$tabs = array(
			'Control Panel'=>l_t('Perform admin tasks'),
			// 'Mod notes'=>l_t('Notes/reports left for/by the mod team'),
			'Status Info'=>l_t('View server status lists'),
			'Logs'=>l_t('Log of recent admin tasks'),
			'Groups'=>l_t('Management of Groups used for relationship / dispute management'),
			'Multi-accounts'=>l_t('Multi-account detector'),
			'Chatlogs'=>l_t('Check the ingame chat.'),
			'AccessLog'=>l_t('Check the user-actions sort by IP and Username.')
		);
	}

	$tab = 'Control Panel';
	$tabNames = array_keys($tabs);

	if( isset($_REQUEST['tab']) && in_array($_REQUEST['tab'], $tabNames) )
	{
		$tab = $_SESSION['adminCPTab'] = $_REQUEST['tab'];
	}
	elseif( isset($_SESSION['adminCPTab']) && in_array($_SESSION['adminCPTab'], $tabNames) )
	{
		$tab = $_SESSION['adminCPTab'];
	}

	print '<div class="topnav">';
	foreach($tabs as $tabChoice=>$tabTitle)
	{
		print '<a title="'.$tabTitle.'" alt="'.l_t($tabChoice).'" href="admincp.php?tab='.$tabChoice;

		if ( $tab == $tabChoice )
			print '"class="topnavActive"';
		else
			print '"class="topnav"';

		print '>'.l_t($tabChoice).'</a></li>';
	}
	print '</div>';

	return $tab;
}

$tab=adminCPTabs();

switch($tab)
{
	case 'Control Panel':
		require_once(l_r('admin/adminActionsForms.php'));
		break;
	case 'Mod notes':
		require_once(l_r('lib/modnotes.php'));
		libModNotes::checkDeleteNote();
		libModNotes::checkInsertNote();
		print libModNotes::reportsDisplay('All');
		break;
	case 'Status Info':
		require_once(l_r('admin/adminStatusLists.php'));
		break;
	case 'Logs':
		require_once(l_r('admin/adminLog.php'));
		break;
	case 'Groups':
		require_once(l_r('admin/adminGroups.php'));
		break;
	case 'Multi-accounts':
		require_once(l_r('admin/adminMultiFinder.php'));
		break;
	case 'Locales':
		require_once(l_r('admin/adminLocales.php'));
		break;
	case 'Chatlogs':
		require_once(l_r('admin/adminChatAnalyser.php'));
		break;
	case 'AccessLog':
		require_once(l_r('admin/adminAdvancedAccessLog.php'));
		break;
	default:
		print '<p>'.l_t('No admin panel tab selected').'</p>';
}
print '</div>';

libHTML::footer();

?>