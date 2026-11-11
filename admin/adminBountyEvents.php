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
 * The admin CP's Bounty events tab: manage templates, schedule events from them, and see the occurrences to come and
 * those which have run. See lib/bounty.php.
 *
 * Moderators can see it all, to answer players' questions, but only admins can change anything: a bounty creates
 * points from nothing, which is an admin's call like the other point-changing actions in adminActionsRestricted.
 * Changes are recorded in the admin log.
 *
 * @package Admin
 */

require_once(l_r('lib/bounty.php'));

class adminBountyEvents
{
	const TAB_URL = 'admincp.php?tab=Bounty%20events';

	// Set when a template has just been saved, so its form shows the saved template rather than the submitted one
	private static $savedTemplateID = 0;

	/**
	 * Record a change in wD_AdminLog, as adminActionsForms does for its actions
	 */
	private static function log($name, array $paramValues, $details)
	{
		global $DB, $User;

		$DB->sql_put("INSERT INTO wD_AdminLog ( name, userID, time, details, params )
			VALUES ( '".$DB->escape($name)."', ".$User->id.", ".time().", '".$DB->escape($details, true)."', '".$DB->escape(serialize($paramValues))."' )");
	}

	/**
	 * Carry out a submitted change, if any
	 *
	 * @return string A message saying what happened
	 */
	public static function processActions()
	{
		global $User, $DB;

		if ( !isset($_POST['bountyAction']) ) return '';

		if ( !$User->type['Admin'] )
			throw new Exception(l_t('Only admins can change bounty events.'));

		libAuth::formToken_Valid();

		switch($_POST['bountyAction'])
		{
			case 'saveTemplate':
				$templateID = isset($_POST['templateID']) ? (int)$_POST['templateID'] : 0;
				if ( $templateID ) libBounty::getTemplate($templateID);
				$t = libBounty::validateTemplate(isset($_POST['template']) && is_array($_POST['template']) ? $_POST['template'] : array());
				$templateID = self::$savedTemplateID = libBounty::saveTemplate($templateID, $t);
				$message = l_t('Saved template %s: %s.', $templateID, $t['name']);
				self::log('Save bounty template', array('templateID'=>$templateID), $message);
				break;

			case 'copyTemplate':
				$templateID = libBounty::copyTemplate((int)$_POST['templateID']);
				$message = l_t('Copied template %s to template %s.', (int)$_POST['templateID'], $templateID);
				self::log('Copy bounty template', array('templateID'=>(int)$_POST['templateID']), $message);
				break;

			case 'deleteTemplate':
				$template = libBounty::getTemplate((int)$_POST['templateID']);
				libBounty::deleteTemplate($template['id']);
				$message = l_t('Deleted template %s: %s, and its events.', $template['id'], $template['name']);
				self::log('Delete bounty template', array('templateID'=>(int)$template['id']), $message);
				break;

			case 'scheduleEvents':
				$templateID = (int)$_POST['templateID'];
				$startTimes = libBounty::parseStartTimes(isset($_POST['startTimes']) ? $_POST['startTimes'] : '');
				$repeats = isset($_POST['repeats']) && $_POST['repeats'] == 'Once' ? 'Once' : 'Weekly';
				$eventIDs = libBounty::scheduleEvents($templateID, $startTimes, $repeats, isset($_POST['enabled']));
				$message = l_t('Scheduled template %s at %s (%s): event(s) %s.', $templateID,
					implode(', ', array_map(function($t) { return gmdate('Y-m-d H:i', $t); }, $startTimes)), $repeats, implode(', ', $eventIDs));
				self::log('Schedule bounty events', array('templateID'=>$templateID), $message);
				break;

			case 'toggleEvent':
				$eventID = (int)$_POST['eventID'];
				$DB->sql_put("UPDATE wD_BountyEvents SET enabled = 1 - enabled WHERE id = ".$eventID);
				list($enabled) = $DB->sql_row("SELECT enabled FROM wD_BountyEvents WHERE id = ".$eventID);
				if ( $enabled === null ) throw new Exception(l_t('Event %s not found.', $eventID));
				$message = l_t('Event %s is now %s.', $eventID, $enabled ? l_t('enabled') : l_t('disabled'));
				self::log('Toggle bounty event', array('eventID'=>$eventID), $message);
				break;

			case 'deleteEvent':
				$eventID = (int)$_POST['eventID'];
				libBounty::deleteEvent($eventID);
				$message = l_t('Deleted event %s. A pool it has open still runs.', $eventID);
				self::log('Delete bounty event', array('eventID'=>$eventID), $message);
				break;

			case 'editEvent':
				$eventID = (int)$_POST['eventID'];
				$startTimes = libBounty::parseStartTimes(isset($_POST['startTime']) ? $_POST['startTime'] : '');
				if ( count($startTimes) != 1 ) throw new Exception(l_t('Enter one start time for the event.'));
				$repeats = isset($_POST['repeats']) && $_POST['repeats'] == 'Once' ? 'Once' : 'Weekly';
				libBounty::editEvent($eventID, $startTimes[0], $repeats);
				$message = l_t('Event %s now runs %s.', $eventID, self::scheduleText(libBounty::getEvent($eventID)));
				self::log('Edit bounty event', array('eventID'=>$eventID), $message);
				break;

			case 'skipEvent':
				$eventID = (int)$_POST['eventID'];
				$skipped = libBounty::skipNextOccurrence($eventID);
				$message = l_t('Event %s will skip %s UTC.', $eventID, gmdate('D Y-m-d H:i', $skipped));
				self::log('Skip bounty event occurrence', array('eventID'=>$eventID), $message);
				break;

			case 'unskipEvent':
				$eventID = (int)$_POST['eventID'];
				libBounty::getEvent($eventID);
				$DB->sql_put("UPDATE wD_BountyEvents SET skipUntil = 0 WHERE id = ".$eventID);
				$message = l_t('Event %s is no longer skipping any weeks.', $eventID);
				self::log('Unskip bounty event', array('eventID'=>$eventID), $message);
				break;

			default:
				throw new Exception(l_t('Unknown bounty action.'));
		}

		$DB->sql_put("COMMIT");
		return $message;
	}

	private static function actionButton($action, $idName, $id, $label, $confirm = false)
	{
		return '<form method="post" action="'.self::TAB_URL.'" style="display:inline"'.
			( $confirm ? ' onsubmit="return confirm(\''.addslashes($confirm).'\');"' : '' ).'>'.
			libAuth::formTokenHTML().
			'<input type="hidden" name="bountyAction" value="'.$action.'" />'.
			'<input type="hidden" name="'.$idName.'" value="'.(int)$id.'" />'.
			'<input type="submit" class="form-submit" value="'.l_t($label).'" /></form> ';
	}

	private static function select($name, array $options, $selected)
	{
		$buf = '<select name="'.$name.'">';
		foreach($options as $value=>$label)
			$buf .= '<option value="'.htmlspecialchars($value).'"'.( (string)$value === (string)$selected ? ' selected' : '' ).'>'.l_t($label).'</option>';
		return $buf.'</select>';
	}

	private static function scheduleText(array $event)
	{
		if ( $event['repeats'] == 'Weekly' )
			return l_t('Weekly, %s', gmdate('l H:i', $event['startTime']).' UTC').
				( $event['startTime'] > time() ? ' '.l_t('from %s', gmdate('Y-m-d', $event['startTime'])) : '' );
		return l_t('Once, %s', gmdate('Y-m-d H:i', $event['startTime']).' UTC');
	}

	public static function templates($canEdit)
	{
		global $DB;

		print '<h3 class="modToolsHeadings">'.l_t('Templates').'</h3>';
		print '<div style="overflow-x:auto"><table class="modTools"><tr><th class="modTools">'.l_t('ID').'</th><th class="modTools">'.l_t('Name').'</th>
			<th class="modTools">'.l_t('Settings').'</th><th class="modTools">'.l_t('Bonus').'</th><th class="modTools">'.l_t('Consolation').'</th>
			<th class="modTools">'.l_t('Events').'</th>'.( $canEdit ? '<th class="modTools"></th>' : '' ).'</tr>';

		$tabl = $DB->sql_tabl("SELECT t.*, COUNT(e.id) events, SUM(e.enabled) enabledEvents
			FROM wD_BountyTemplates t LEFT JOIN wD_BountyEvents e ON e.templateID = t.id
			GROUP BY t.id ORDER BY t.name");
		while( $t = $DB->tabl_hash($tabl) )
		{
			print '<tr><td class="modTools">'.$t['id'].'</td><td class="modTools"><strong>'.$t['name'].'</strong></td>
				<td class="modTools">'.libBounty::settingsText($t).'</td>
				<td class="modTools">'.$t['bonus'].libHTML::points().'</td>
				<td class="modTools">'.( $t['consolationPoints'] > 0 ? $t['consolationPoints'].libHTML::points() : l_t('None') ).'</td>
				<td class="modTools">'.l_t('%s (%s enabled)', $t['events'], (int)$t['enabledEvents']).'</td>';
			if ( $canEdit )
				print '<td class="modTools"><a class="modTools" href="'.self::TAB_URL.'&editTemplate='.$t['id'].'#templateForm">'.l_t('Edit').'</a> '.
					self::actionButton('copyTemplate', 'templateID', $t['id'], 'Copy').
					self::actionButton('deleteTemplate', 'templateID', $t['id'], 'Delete',
						l_t('Delete this template and all of its events?')).'</td>';
			print '</tr>';
		}
		print '</table></div>';
	}

	public static function templateForm()
	{
		global $DB;

		$defaults = array('name'=>'', 'variantID'=>1, 'potType'=>'Winner-takes-all', 'phaseMinutes'=>5, 'phaseMinutesRB'=>-1,
			'nextPhaseMinutes'=>'', 'phaseSwitchPeriod'=>-1, 'anon'=>'No', 'pressType'=>'Regular',
			'drawType'=>'draw-votes-public', 'missingPlayerPolicy'=>'Normal', 'minimumReliabilityRating'=>0,
			'excusedMissedTurns'=>1, 'bonus'=>500, 'consolationPoints'=>0, 'poolOpenMinutes'=>15);

		$templateID = isset($_REQUEST['editTemplate']) ? (int)$_REQUEST['editTemplate'] : self::$savedTemplateID;
		if ( !self::$savedTemplateID && isset($_POST['bountyAction']) && $_POST['bountyAction'] == 'saveTemplate' && isset($_POST['template']) && is_array($_POST['template']) )
		{
			// Redisplay what was submitted, e.g. if it didn't validate
			$t = array_filter(array_intersect_key($_POST['template'], $defaults), 'is_scalar') + $defaults;
			$templateID = (int)$_POST['templateID'];
		}
		elseif ( $templateID )
		{
			// The template may have been deleted since the edit link was made
			try { $t = libBounty::getTemplate($templateID); }
			catch(Exception $e) { $t = $defaults; $templateID = 0; }
		}
		else
			$t = $defaults;

		$variants = array();
		foreach(Config::$variants as $variantID=>$variantName)
			$variants[$variantID] = libVariant::loadFromVariantID($variantID)->fullName;

		$field = function($label, $input, $help = '') {
			return '<li class="modToolsformlistfield"><label>'.l_t($label).'</label>: '.$input.
				( $help ? ' <em>'.l_t($help).'</em>' : '' ).'</li>';
		};
		$text = function($name, $value, $size = 6) {
			return '<input type="text" class="modTools" name="template['.$name.']" size="'.$size.'" value="'.htmlspecialchars((string)$value, ENT_QUOTES).'" />';
		};

		print '<a name="templateForm"></a><div class="modToolsCP">';
		print '<li class="modToolsformlisttitle">'.( $templateID ? l_t('Edit template %s', $templateID) : l_t('New template') ).'</li>';
		print '<form method="post" action="'.self::TAB_URL.'#templateForm">'.libAuth::formTokenHTML().
			'<input type="hidden" name="bountyAction" value="saveTemplate" />
			<input type="hidden" name="templateID" value="'.$templateID.'" />';
		print $field('Name', $text('name', html_entity_decode($t['name'], ENT_QUOTES, 'UTF-8'), 40), 'Games are named after it, with the date');
		print $field('Variant', self::select('template[variantID]', $variants, $t['variantID']), 'Each game has as many players as the variant has countries');
		print $field('Phase length (minutes)', $text('phaseMinutes', $t['phaseMinutes']));
		print $field('Retreat/build phase length (minutes)', $text('phaseMinutesRB', $t['phaseMinutesRB']), '-1 for the same as the phase length');
		print $field('Phase switch period (minutes)', $text('phaseSwitchPeriod', $t['phaseSwitchPeriod']), '-1 for none; for live games, when to switch to the phase length below');
		print $field('Phase length after the switch (minutes)', $text('nextPhaseMinutes', $t['nextPhaseMinutes']));
		print $field('Scoring', self::select('template[potType]', libBounty::$potTypes, $t['potType']));
		print $field('Chat', self::select('template[pressType]', libBounty::$pressTypes, $t['pressType']));
		print $field('Anonymous players', self::select('template[anon]', array('No'=>'No', 'Yes'=>'Yes'), $t['anon']), 'No-chat games are always anonymous');
		print $field('Draw votes', self::select('template[drawType]', libBounty::$drawTypes, $t['drawType']));
		print $field('Missing orders', self::select('template[missingPlayerPolicy]', libBounty::$missingPlayerPolicies, $t['missingPlayerPolicy']));
		print $field('Minimum reliability rating', $text('minimumReliabilityRating', $t['minimumReliabilityRating']));
		print $field('Excused missed turns', $text('excusedMissedTurns', $t['excusedMissedTurns']));
		print $field('Bonus (points)', $text('bonus', $t['bonus']), 'Added to the pot of every game; entry is free, so this is the whole pot');
		print $field('Consolation points', $text('consolationPoints', $t['consolationPoints']), 'For each player left out of a game; 0 for none');
		print $field('Pool opens (minutes before the start)', $text('poolOpenMinutes', $t['poolOpenMinutes']), 'How long players have to enter; 5 to 120');
		print '<li class="modToolsformlistfield"><input type="submit" class="form-submit" value="'.l_t('Save template').'" />'.
			( $templateID ? ' <a class="modTools" href="'.self::TAB_URL.'#templateForm">'.l_t('New template instead').'</a>' : '' ).'</li>';
		print '<li class="modToolsformlistfield"><em>'.l_t('An occurrence uses the template as it is when its pool opens.').'</em></li>';
		print '</form></div>';
	}

	public static function scheduleForm()
	{
		global $DB;

		$templates = array();
		$tabl = $DB->sql_tabl("SELECT id, name FROM wD_BountyTemplates ORDER BY name");
		while( list($id, $name) = $DB->tabl_row($tabl) )
			$templates[$id] = $name;
		if ( count($templates) == 0 ) return;

		print '<a name="scheduleForm"></a><div class="modToolsCP">';
		print '<li class="modToolsformlisttitle">'.l_t('Schedule events').'</li>';
		print '<form method="post" action="'.self::TAB_URL.'#scheduleForm">'.libAuth::formTokenHTML().
			'<input type="hidden" name="bountyAction" value="scheduleEvents" />';
		print '<li class="modToolsformlistfield"><label>'.l_t('Template').'</label>: '.self::select('templateID', $templates,
			isset($_POST['templateID']) ? (int)$_POST['templateID'] : 0).'</li>';
		print '<li class="modToolsformlistfield"><label>'.l_t('Start times, in UTC').'</label>:<br />
			<textarea class="modTools" name="startTimes" rows="4" cols="30" placeholder="Saturday 18:00&#10;Sunday 02:00">'.
			( isset($_POST['startTimes']) && $_POST['bountyAction'] == 'scheduleEvents' ? htmlspecialchars($_POST['startTimes']) : '' ).'</textarea>
			<br /><em>'.l_t('One per line, as a weekday and time (Saturday 18:00) or a date and time (2026-11-14 18:00); each becomes its own event. Now: %s UTC', gmdate('l Y-m-d H:i')).'</em></li>';
		print '<li class="modToolsformlistfield"><label>'.l_t('Repeats').'</label>: '.
			self::select('repeats', array('Weekly'=>'Weekly, on that weekday and time', 'Once'=>'Once, the next time it comes round'), 'Weekly').'</li>';
		print '<li class="modToolsformlistfield"><label><input type="checkbox" name="enabled" checked /> '.l_t('Enabled').'</label></li>';
		print '<li class="modToolsformlistfield"><input type="submit" class="form-submit" value="'.l_t('Schedule').'" /></li>';
		print '</form></div>';
	}

	public static function events($canEdit)
	{
		global $DB;

		print '<h3 class="modToolsHeadings">'.l_t('Events').'</h3>';
		print '<div style="overflow-x:auto"><table class="modTools"><tr><th class="modTools">'.l_t('ID').'</th><th class="modTools">'.l_t('Template').'</th>
			<th class="modTools">'.l_t('When').'</th><th class="modTools">'.l_t('Next start').'</th><th class="modTools">'.l_t('Status').'</th>'.
			( $canEdit ? '<th class="modTools"></th>' : '' ).'</tr>';

		$tabl = $DB->sql_tabl("SELECT e.*, t.name FROM wD_BountyEvents e INNER JOIN wD_BountyTemplates t ON t.id = e.templateID
			ORDER BY e.enabled DESC, t.name, e.startTime");
		while( $e = $DB->tabl_hash($tabl) )
		{
			$next = libBounty::nextStartTime($e, time());
			print '<tr><td class="modTools">'.$e['id'].'</td><td class="modTools">'.$e['name'].'</td>
				<td class="modTools">'.self::scheduleText($e).'</td>
				<td class="modTools">'.( $next === null ? l_t('None; it has run') : gmdate('D Y-m-d H:i', $next).' UTC' ).
					( $e['skipUntil'] >= time() ? '<br /><em>'.l_t('Skipping %s', gmdate('D Y-m-d', $e['skipUntil'])).'</em>' : '' ).'</td>
				<td class="modTools">'.( $e['enabled'] ? l_t('Enabled') : '<em>'.l_t('Disabled').'</em>' ).'</td>';
			if ( $canEdit )
			{
				// The time as it would be typed into the schedule form
				$when = $e['repeats'] == 'Weekly' ? gmdate('l H:i', $e['startTime']) : gmdate('Y-m-d H:i', $e['startTime']);
				print '<td class="modTools">'.
					'<form method="post" action="'.self::TAB_URL.'" style="white-space:nowrap">'.libAuth::formTokenHTML().
					'<input type="hidden" name="bountyAction" value="editEvent" />'.
					'<input type="hidden" name="eventID" value="'.$e['id'].'" />'.
					'<input type="text" class="modTools" name="startTime" size="16" value="'.$when.'" /> '.
					self::select('repeats', array('Weekly'=>'Weekly', 'Once'=>'Once'), $e['repeats']).
					' <input type="submit" class="form-submit" value="'.l_t('Change time').'" /></form>'.
					self::actionButton('toggleEvent', 'eventID', $e['id'], $e['enabled'] ? 'Disable' : 'Enable').
					( $e['repeats'] == 'Weekly' && $next !== null ? self::actionButton('skipEvent', 'eventID', $e['id'], 'Skip next') : '' ).
					( $e['skipUntil'] >= time() ? self::actionButton('unskipEvent', 'eventID', $e['id'], 'Unskip') : '' ).
					self::actionButton('deleteEvent', 'eventID', $e['id'], 'Delete', l_t('Delete this event?')).'</td>';
			}
			print '</tr>';
		}
		print '</table></div>';
	}

	public static function upcoming()
	{
		global $DB;

		print '<h3 class="modToolsHeadings">'.l_t('Upcoming occurrences').'</h3>';
		$occurrences = libBounty::upcomingOccurrences();
		if ( count($occurrences) == 0 )
		{
			print '<p class="modTools">'.l_t('None; no enabled events are scheduled.').'</p>';
			return;
		}

		print '<div style="overflow-x:auto"><table class="modTools"><tr><th class="modTools">'.l_t('Start').'</th><th class="modTools">'.l_t('Name').'</th>
			<th class="modTools">'.l_t('Event').'</th><th class="modTools">'.l_t('Pool').'</th></tr>';
		foreach($occurrences as $o)
		{
			if ( $o['pool'] )
			{
				list($entered) = $DB->sql_row("SELECT COUNT(*) FROM wD_BountyPoolMembers WHERE poolID = ".$o['pool']['id']." AND outcome = 'Waiting'");
				$poolText = l_t('Open (pool %s): %s player(s) in it', $o['pool']['id'], $entered);
			}
			else
				$poolText = l_t('Opens %s UTC', gmdate('D Y-m-d H:i', $o['startTime'] - libBounty::poolOpenSeconds($o['settings'])));

			print '<tr><td class="modTools">'.gmdate('D Y-m-d H:i', $o['startTime']).' UTC</td><td class="modTools">'.$o['name'].'</td>
				<td class="modTools">'.$o['eventID'].'</td><td class="modTools">'.$poolText.'</td></tr>';
		}
		print '</table></div>';
	}

	public static function past()
	{
		global $DB;

		print '<h3 class="modToolsHeadings">'.l_t('Past occurrences').'</h3>';
		print '<div style="overflow-x:auto"><table class="modTools"><tr><th class="modTools">'.l_t('Start').'</th><th class="modTools">'.l_t('Name').'</th>
			<th class="modTools">'.l_t('Pool / event').'</th><th class="modTools">'.l_t('Entered').'</th><th class="modTools">'.l_t('Games').'</th>
			<th class="modTools">'.l_t('Placed').'</th><th class="modTools">'.l_t('Left over').'</th><th class="modTools">'.l_t('Consolation paid').'</th></tr>';

		$tabl = $DB->sql_tabl("SELECT p.*, COUNT(m.userID) entered, SUM(m.consolationPoints) consolationPaid,
				GROUP_CONCAT(DISTINCT m.gameID ORDER BY m.gameID) gameIDs, SUM(m.outcome = 'Ineligible') ineligible
			FROM wD_BountyPools p LEFT JOIN wD_BountyPoolMembers m ON m.poolID = p.id
			WHERE p.status <> 'Open'
			GROUP BY p.id ORDER BY p.startTime DESC LIMIT ".(isset($_REQUEST['allPools']) ? 1000 : 50));
		$count = 0;
		while( $p = $DB->tabl_hash($tabl) )
		{
			$count++;
			$games = array();
			if ( $p['gameIDs'] )
				foreach( explode(',', $p['gameIDs']) as $gameID )
					$games[] = '<a class="modTools" href="board.php?gameID='.(int)$gameID.'">'.(int)$gameID.'</a>';

			print '<tr><td class="modTools">'.gmdate('D Y-m-d H:i', $p['startTime']).' UTC</td><td class="modTools">'.$p['name'].'</td>
				<td class="modTools">'.$p['id'].' / '.$p['eventID'].'</td>
				<td class="modTools">'.$p['entered'].( $p['ineligible'] > 0 ? ' '.l_t('(%s no longer eligible)', $p['ineligible']) : '' ).'</td>
				<td class="modTools">'.( $p['status'] != 'Done' ? '<strong>'.l_t($p['status'] == 'Missed' ? 'Missed; started too late' : 'Failed').'</strong>' : $p['gamesCreated'].( count($games) ? ': '.implode(', ', $games) : '' ) ).'</td>
				<td class="modTools">'.$p['playersPlaced'].'</td><td class="modTools">'.$p['playersLeftOver'].'</td>
				<td class="modTools">'.(int)$p['consolationPaid'].libHTML::points().'</td></tr>';
		}
		print '</table></div>';
		if ( $count == 0 )
			print '<p class="modTools">'.l_t('None yet.').'</p>';
		elseif ( !isset($_REQUEST['allPools']) && $count == 50 )
			print '<p class="modTools"><a class="modTools" href="'.self::TAB_URL.'&allPools=on">'.l_t('View more').'</a></p>';
	}
}

$canEdit = (bool)$User->type['Admin'];

print '<h2 class="modToolsHeadings">'.l_t('Bounty events').'</h2>';
print '<p class="modTools">'.l_t('Live games at fixed times with bonus points. Each time an event comes round its pool opens before the start, as long before as its template says; '.
	'at the start the pool is shuffled into as many full games as it fills, and the rest are paid the consolation points. All times are UTC.').
	( $canEdit ? '' : ' <em>'.l_t('Only admins can change bounty events.').'</em>' ).'</p>';

try
{
	$message = adminBountyEvents::processActions();
	if ( $message ) print '<p class="notice">'.$message.'</p>';
}
catch(Exception $e)
{
	$DB->sql_put("ROLLBACK");
	print '<p class="notice"><strong>'.l_t('Error').':</strong> '.$e->getMessage().'</p>';
}

adminBountyEvents::upcoming();
adminBountyEvents::templates($canEdit);
if ( $canEdit )
{
	adminBountyEvents::templateForm();
	adminBountyEvents::scheduleForm();
}
adminBountyEvents::events($canEdit);
adminBountyEvents::past();
