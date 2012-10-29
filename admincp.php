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

	require_once('objects/game.php');
	$Variant=libVariant::loadFromGameID($gameID);
	$Game=$Variant->Game($gameID);



	if( !($data=file_get_contents(libCache::dirID(Config::orderlogDirectory(), $gameID, true).'/'.$countryID.'.txt')) )
	{
		trigger_error("Couldn't open file ".$log.".txt");
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
		trigger_error("Couldn't open file ".$log.".txt");
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
		libHTML::notice('Wait',"Make sure you're in maintenance-mode and no-one
			else is using the system before running a system-task!<br />
			Maintenance mode has been set, please wait 3 mins to make sure all
			other users are done, then click
			<a href='admincp.php?systemTask=".$_REQUEST['systemTask']."'>here</a> to
			run the system-task safely.<br /><br />

			Once it has run successfully, maintenance-mode can be disabled.");
	}
	else
	{
		ini_set('memory_limit',"32M"); // 8M is the default
		ini_set('max_execution_time','120');

		switch($_REQUEST['systemTask'])
		{
			case 'defragTables':
				require_once('admin/systemTasks/defragTables.php');
				die();
			case 'resetCountryIDBalancer':
				require_once('admin/systemTasks/resetCountryIDBalancer.php');
				die();
		}
	}
}

libHTML::starthtml();
print '<div class="content">';

function adminCPTabs()
{
	$tabs = array(
			'Control Panel'=>'Perform admin tasks',
			'Mod notes'=>'Notes/reports left for/by the mod team',
			'Status lists'=>'View server status lists',
			'Control Panel Logs'=>'Log of recent admin tasks',
			'Multi-accounts'=>'Multi-account detector',
			'Chatlogs'=>'Check the ingame chat.'
		);

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

	print '<div class="gamelistings-tabs">';

	foreach($tabs as $tabChoice=>$tabTitle)
	{
		print '<a title="'.$tabTitle.'" alt="'.$tabChoice.'" href="admincp.php?tab='.$tabChoice;

		if ( $tab == $tabChoice )
			print '" class="current"';
		else
			print '"';

		print '>'.$tabChoice.'</a>';
	}

	print '</div>';

	return $tab;
}

$tab=adminCPTabs();

print '<h3>'.$tab.'</h3>';
switch($tab)
{
	case 'Control Panel':
		require_once('admin/adminActionsForms.php');
		break;
	case 'Mod notes':
		require_once('lib/modnotes.php');
		libModNotes::checkDeleteNote();
		libModNotes::checkInsertNote();
		print libModNotes::reportsDisplay('All');
		break;
	case 'Status lists':
		require_once('admin/adminStatusLists.php');
		break;
	case 'Control Panel Logs':
		require_once('admin/adminLog.php');
		break;
	case 'Multi-accounts':
		require_once('admin/adminMultiFinder.php');
		break;
	case 'Chatlogs':
		require_once('admin/adminChatAnalyser.php');
		break;
	default:
		print '<p>No admin panel tab selected</p>';
}
print '</div>';

libHTML::footer();

?>