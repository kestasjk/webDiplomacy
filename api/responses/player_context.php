<?php
/*
	Copyright (C) 2004-2026 Kestas J. Kuliukas

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

namespace webdiplomacy_api;

use libGameFiles;
use libAuth;
use Config;

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * The game/playercontext API route (doc/gamedata/02-spec.md): everything about a game that depends on who is asking.
 * What is the same for everyone is in the game's public JSON files (lib/gamefiles.php), whose URLs and versions
 * this gives out.
 *
 * It reads the game's row and its member rows and nothing else unless orders or messages are asked for; no Game,
 * Members or Variant objects are built. Those two reads also tell it whether the public files are up to date, and
 * it rewrites them if they aren't, so a change which forgot to refresh them is put right by the next request.
 *
 * Nothing private is read until the caller has been matched to a member row by user ID. The country is always
 * taken from that row, never from the request.
 *
 * @package webdiplomacy_api
 */
class PlayerContext
{
	/**
	 * How far behind the clock the messages cursor is kept, in seconds. A message's timeSent is set before its
	 * insert is committed, so a message can appear with a time slightly in the past; clients ask again from the
	 * cursor and drop the messages they already have by id.
	 */
	const MESSAGE_CURSOR_LAG = 10;

	/**
	 * @param \ApiEntry $apiEntry For multiplexing game IDs
	 * @param int $userID The authenticated user
	 * @param bool $isSessionAuth Whether the caller is a logged-in user's browser rather than an API key
	 * @param array $args gameID, countryID, orders, messages, messagesSince, messageTurns, sbToken
	 * @return array
	 */
	public static function forGame($apiEntry, $userID, $isSessionAuth, array $args)
	{
		global $DB, $User;

		$userID = intval($userID);
		$gameID = intval($args['gameID']);
		if( $gameID <= 0 ) throw new \RequestException('Invalid game ID.');

		if( !empty(Config::$apiConfig['restrictToGameIDs']) && !in_array($gameID, Config::$apiConfig['restrictToGameIDs']) )
			throw new \ClientForbiddenException('Game ID is not in list of gameIDs where API usage is permitted.');

		$gameRow = libGameFiles::loadGameRow($gameID);
		if( $gameRow === false ) throw new \RequestException('Unknown game ID.');

		$memberRows = libGameFiles::loadMemberRows($gameID);

		// Who is asking. The global $User is only them when they are logged in; for an API key it is the guest user.
		$isModerator = ( $isSessionAuth && isset($User) && intval($User->id) == $userID && $User->type['Moderator'] );

		$ownRows = array();
		foreach($memberRows as $memberRow)
			if( intval($memberRow['userID']) == $userID )
				$ownRows[] = $memberRow;

		$isSandbox = libGameFiles::isSandbox($gameRow);
		$isSandboxOwner = ( $isSandbox && intval($gameRow['sandboxCreatedByUserID']) == $userID );

		if( $isSandbox && !$isSandboxOwner && !$isModerator
			&& !( isset($args['sbToken']) && is_string($args['sbToken']) && hash_equals(libAuth::sandboxToken_Key($gameID), $args['sbToken']) ) )
			throw new \ClientForbiddenException("You can't view this game, it is a sandbox game which you didn't create.");

		// A user only has several countries in their own sandbox game, where the request may say which one is meant
		$memberRow = null;
		if( count($ownRows) )
		{
			$memberRow = $ownRows[0];
			if( isset($args['countryID']) )
				foreach($ownRows as $ownRow)
					if( intval($ownRow['countryID']) == intval($args['countryID']) )
						$memberRow = $ownRow;
		}

		// A member who left and is blocked from rejoining is treated as a spectator, as on board.php
		$isTempBanned = false;
		if( !is_null($memberRow) && $memberRow['status'] == 'Left' )
		{
			list($tempBan) = $DB->sql_row("SELECT tempBan FROM wD_Users WHERE id = ".$userID);
			$isTempBanned = ( intval($tempBan) > time() );
		}
		$isMember = ( !is_null($memberRow) && !$isTempBanned );

		$isDirector = ( !is_null($gameRow['directorUserID']) && intval($gameRow['directorUserID']) == $userID );
		if( !$isDirector )
		{
			list($isDirector) = $DB->sql_row("SELECT COUNT(*) FROM wD_TournamentGames tg
				INNER JOIN wD_Tournaments t ON ( t.id = tg.tournamentID )
				WHERE tg.gameID = ".$gameID." AND ( t.directorID = ".$userID." OR t.coDirectorID = ".$userID." )");
			$isDirector = ( $isDirector > 0 );
		}

		// Bring the public files up to date if anything has changed without refreshing them
		$stale = libGameFiles::staleFiles($gameRow, $memberRows);
		$state = count($stale) ? libGameFiles::refresh($gameID, $stale) : libGameFiles::state($gameID);

		$context = array(
			'schema' => libGameFiles::SCHEMA,
			'game' => array(
				'gameID' => $apiEntry->gameIDToMultiplexedGameID($gameID),
				'realGameID' => $gameID,
				'variantID' => intval($gameRow['variantID']),
				'name' => $gameRow['name'],
				'turn' => intval($gameRow['turn']),
				'phase' => $gameRow['phase'],
				'gameOver' => $gameRow['gameOver'],
				'processTime' => is_null($gameRow['processTime']) ? null : intval($gameRow['processTime']),
				'processStatus' => $gameRow['processStatus'],
				'pressType' => $gameRow['pressType'],
				'potType' => $gameRow['potType'],
				'phaseMinutes' => intval($gameRow['phaseMinutes']),
			),
			'files' => libGameFiles::urls($gameRow, $state),
			'viewer' => array(
				'userID' => $userID,
				'isMember' => $isMember,
				'isModerator' => (bool)$isModerator,
				'isDirector' => $isDirector,
				'isSandboxOwner' => $isSandboxOwner,
			),
			'member' => null,
			// Members get their country's channel as well as the game's; anyone else only the game's
			'sseAuth' => libAuth::sseToken($gameID, $isMember ? intval($memberRow['countryID']) : 0),
			'orders' => null,
			'messages' => null,
			'hiddenMembers' => null,
		);

		if( $isMember )
		{
			$context['member'] = self::member($gameRow, $memberRows, $memberRow, $userID);

			if( !empty($args['orders']) && in_array($gameRow['phase'], array('Diplomacy', 'Retreats', 'Builds')) && intval($memberRow['countryID']) > 0 )
				$context['orders'] = self::orders($gameRow, $memberRow, $userID);

			if( !empty($args['messages']) && intval($memberRow['countryID']) > 0 )
				$context['messages'] = self::messages($gameRow, $memberRow, $isSessionAuth,
					isset($args['messagesSince']) ? intval($args['messagesSince']) : null,
					isset($args['messageTurns']) ? intval($args['messageTurns']) : null);
		}
		elseif( $isTempBanned )
		{
			$context['viewer']['isTempBanned'] = true;
		}

		// A moderator who isn't playing, and the game's directors, see who the players of an anonymous game are
		if( libGameFiles::identitiesHidden($gameRow) && ( ( $isModerator && !count($ownRows) ) || $isDirector ) )
			$context['hiddenMembers'] = self::hiddenMembers($gameID, $memberRows);

		return $context;
	}

	/**
	 * The caller's own member row, and what else only they may know
	 */
	private static function member(array $gameRow, array $memberRows, array $memberRow, $userID)
	{
		global $DB, $Redis;

		$gameID = intval($gameRow['id']);
		$countryID = intval($memberRow['countryID']);

		$mutedCountryIDs = array();
		$tabl = $DB->sql_tabl("SELECT muteCountryID FROM wD_MuteCountry WHERE userID = ".intval($userID)." AND gameID = ".$gameID);
		while( list($muteCountryID) = $DB->tabl_row($tabl) )
			$mutedCountryIDs[] = intval($muteCountryID);

		// board.php's "At least 1 country still needs to enter orders!", which members see even in anonymous games
		$ordersPending = false;
		if( in_array($gameRow['phase'], array('Diplomacy', 'Retreats', 'Builds')) )
			foreach($memberRows as $row)
				if( $row['status'] != 'Defeated' && $row['status'] != 'Left' && !count(libGameFiles::setToList($row['orderStatus'])) )
					$ordersPending = true;

		// When the country's newest message was sent, which libGameMessage::send() keeps in Redis. If Redis has lost
		// it, it is set again from the game's newest message (never older than the country's), as the SSE server
		// needs it to tell a reconnecting client whether it missed a message.
		$lastMessageTime = null;
		$lastMessageKey = 'lastmsgtime_'.$gameID.'_'.$countryID;
		try
		{
			$value = $Redis->get($lastMessageKey);
			if( $value !== false && !is_null($value) ) $lastMessageTime = intval($value);
		}
		catch(\Exception $e) { }
		if( is_null($lastMessageTime) )
		{
			list($lastMessageTime) = $DB->sql_row("SELECT COALESCE(MAX(timeSent), 0) FROM wD_GameMessages WHERE gameID = ".$gameID);
			$lastMessageTime = intval($lastMessageTime);
			try { $Redis->setIfMissing($lastMessageKey, $lastMessageTime, 30*24*60*60); } catch(\Exception $e) { }
		}

		return array(
			'countryID' => $countryID,
			'memberID' => intval($memberRow['id']),
			'status' => $memberRow['status'],
			'orderStatus' => libGameFiles::setToList($memberRow['orderStatus']),
			'votes' => libGameFiles::setToList($memberRow['votes']),
			'missedPhases' => intval($memberRow['missedPhases']),
			'excusedMissedTurns' => intval($memberRow['excusedMissedTurns']),
			'bet' => intval($memberRow['bet']),
			'newMessagesFrom' => array_map('intval', libGameFiles::setToList($memberRow['newMessagesFrom'])),
			'mutedCountryIDs' => $mutedCountryIDs,
			'ordersPending' => $ordersPending,
			'lastMessageTime' => $lastMessageTime,
		);
	}

	/**
	 * The caller's orders for this phase, and the signed context ajax.php needs to save them. The context is given
	 * as the exact string which was signed; it has to be posted back unchanged.
	 */
	private static function orders(array $gameRow, array $memberRow, $userID)
	{
		global $DB;

		require_once(l_r('objects/basic/set.php'));
		require_once(l_r('board/orders/orderinterface.php'));

		$gameID = intval($gameRow['id']);
		$isSandbox = libGameFiles::isSandbox($gameRow);

		$orders = array();
		$maxOrderID = 0;
		$tabl = $DB->sql_tabl("SELECT o.id, o.countryID, o.unitID, u.type AS unitType, u.terrID, o.type, o.toTerrID, o.fromTerrID, o.viaConvoy
			FROM wD_Orders o LEFT JOIN wD_Units u ON ( u.id = o.unitID )
			WHERE o.gameID = ".$gameID.( $isSandbox ? "" : " AND o.countryID = ".intval($memberRow['countryID']) )."
			ORDER BY o.id");
		while( $row = $DB->tabl_hash($tabl) )
		{
			if( intval($row['id']) > $maxOrderID ) $maxOrderID = intval($row['id']);

			$orders[] = array(
				'id' => intval($row['id']),
				'countryID' => intval($row['countryID']),
				'unitID' => is_null($row['unitID']) ? null : intval($row['unitID']),
				'unitType' => $row['unitType'],
				'terrID' => is_null($row['terrID']) ? null : intval($row['terrID']),
				'type' => $row['type'],
				'toTerrID' => intval($row['toTerrID']) > 0 ? intval($row['toTerrID']) : null,
				'fromTerrID' => intval($row['fromTerrID']) > 0 ? intval($row['fromTerrID']) : null,
				'viaConvoy' => ( $row['viaConvoy'] == 'Yes' ),
			);
		}

		// The same context board.php gives its order form, without loading the orders as objects
		$OI = new \OrderInterface(
			$gameID, intval($gameRow['variantID']), intval($userID), intval($memberRow['id']),
			intval($gameRow['turn']), $gameRow['phase'], intval($memberRow['countryID']),
			new \setMemberOrderStatus($memberRow['orderStatus']),
			is_null($gameRow['processTime']) ? null : intval($gameRow['processTime']) + 6*60*60,
			$maxOrderID, $isSandbox
		);
		$context = \OrderInterface::getContext($OI);

		return array(
			'context' => $context['json'],
			'contextKey' => $context['key'],
			'orders' => $orders,
		);
	}

	/**
	 * The messages between the caller's country and one other country or the GameMaster, and the country's notes to
	 * itself. Messages to everyone are public and are in the game's messages.json.
	 */
	private static function messages(array $gameRow, array $memberRow, $isSessionAuth, $since, $turns)
	{
		global $DB, $Redis;

		$gameID = intval($gameRow['id']);
		$countryID = intval($memberRow['countryID']);
		$cursor = time() - self::MESSAGE_CURSOR_LAG;

		if( !is_null($since) )
		{
			// The time the country's newest message was sent; nothing to read if the caller has seen up to then
			try
			{
				$lastMessageTime = $Redis->get('lastmsgtime_'.$gameID.'_'.$countryID);
				if( $lastMessageTime !== false && !is_null($lastMessageTime) && intval($lastMessageTime) < $since )
					return array('messages' => array(), 'cursor' => $cursor);
			}
			catch(\Exception $e) { }
		}

		// API keys get the redacted copy of the messages unless the installation allows them the originals
		$table = ( $isSessionAuth || !empty(Config::$allowBotsAccessToUnredactedMessages) ) ? 'wD_GameMessages' : 'wD_GameMessages_Redacted';

		$messages = array();
		$tabl = $DB->sql_tabl("SELECT id, turn, phaseMarker, timeSent, fromCountryID, toCountryID, message
			FROM ".$table."
			WHERE gameID = ".$gameID." AND toCountryID <> 0 AND ( toCountryID = ".$countryID." OR fromCountryID = ".$countryID." )"
			.( is_null($since) ? "" : " AND timeSent >= ".$since )
			.( is_null($turns) ? "" : " AND turn >= ".max(0, intval($gameRow['turn']) - max(0, $turns)) )."
			ORDER BY id");
		while( $row = $DB->tabl_hash($tabl) )
			$messages[] = array(
				'id' => intval($row['id']),
				'turn' => intval($row['turn']),
				'phase' => $row['phaseMarker'],
				'timeSent' => intval($row['timeSent']),
				'fromCountryID' => intval($row['fromCountryID']),
				'toCountryID' => intval($row['toCountryID']),
				'message' => $row['message'],
			);

		return array('messages' => $messages, 'cursor' => $cursor);
	}

	/**
	 * What an anonymous game's public files leave out about its members, for the few who may see it
	 */
	private static function hiddenMembers($gameID, array $memberRows)
	{
		global $DB;

		$users = array();
		$tabl = $DB->sql_tabl("SELECT u.id, u.username, u.points, u.type FROM wD_Members m
			INNER JOIN wD_Users u ON ( u.id = m.userID ) WHERE m.gameID = ".intval($gameID));
		while( $row = $DB->tabl_hash($tabl) )
			$users[$row['id']] = $row;

		$hiddenMembers = array();
		foreach($memberRows as $memberRow)
		{
			if( !isset($users[$memberRow['userID']]) ) continue;
			$user = $users[$memberRow['userID']];

			$hiddenMembers[] = array(
				'countryID' => intval($memberRow['countryID']),
				'userID' => intval($user['id']),
				'username' => $user['username'],
				'points' => intval($user['points']),
				'type' => libGameFiles::setToList($user['type']),
				'orderStatus' => libGameFiles::setToList($memberRow['orderStatus']),
				'votes' => libGameFiles::setToList($memberRow['votes']),
				'missedPhases' => intval($memberRow['missedPhases']),
				'excusedMissedTurns' => intval($memberRow['excusedMissedTurns']),
				'lastSeen' => intval($memberRow['timeLoggedIn']),
			);
		}

		return $hiddenMembers;
	}

	/**
	 * One row for each active game the user is playing in, with the versions of its public files, so that a client
	 * with many games can tell with one request which games have changed, and what about them.
	 *
	 * @return array
	 */
	public static function forUser($apiEntry, $userID)
	{
		global $DB, $Redis;

		$userID = intval($userID);

		// The same set of games as players/active_games gave
		$apiVariants = implode(', ', array_map('intval', Config::$apiConfig['variantIDs']));
		$filterGameClause = '';
		if( !empty(Config::$apiConfig['restrictToGameIDs']) )
			$filterGameClause = "AND g.id IN (".implode(', ', array_map('intval', Config::$apiConfig['restrictToGameIDs'])).")";

		$games = array();
		$tabl = $DB->sql_tabl("SELECT m.gameID, m.countryID, m.status, m.orderStatus, m.votes, m.newMessagesFrom,
				g.variantID, g.name, g.turn, g.phase, g.gameOver, g.processTime, g.processStatus, g.pressType,
				g.sandboxCreatedByUserID
			FROM wD_Members m
			INNER JOIN wD_Games g ON ( g.id = m.gameID )
			WHERE m.userID = ".$userID." AND m.status = 'Playing'
				AND g.variantID IN (".$apiVariants.") ".$filterGameClause."
				AND g.phase IN ('Diplomacy', 'Retreats', 'Builds')
			ORDER BY g.processTime ASC, m.countryID ASC");
		while( $row = $DB->tabl_hash($tabl) )
		{
			$gameID = intval($row['gameID']);
			if( isset($games[$gameID]) ) continue; // One row per game, for the user's own sandbox games

			$state = libGameFiles::state($gameID);

			$lastMessageTime = null;
			try
			{
				$value = $Redis->get('lastmsgtime_'.$gameID.'_'.intval($row['countryID']));
				if( $value !== false && !is_null($value) ) $lastMessageTime = intval($value);
			}
			catch(\Exception $e) { }

			$games[$gameID] = array(
				'gameID' => $apiEntry->gameIDToMultiplexedGameID($gameID),
				'realGameID' => $gameID,
				'countryID' => intval($row['countryID']),
				'variantID' => intval($row['variantID']),
				'name' => $row['name'],
				'turn' => intval($row['turn']),
				'phase' => $row['phase'],
				'gameOver' => $row['gameOver'],
				'processTime' => is_null($row['processTime']) ? null : intval($row['processTime']),
				'processStatus' => $row['processStatus'],
				'pressType' => $row['pressType'],
				'status' => $row['status'],
				'orderStatus' => libGameFiles::setToList($row['orderStatus']),
				'votes' => libGameFiles::setToList($row['votes']),
				'newMessagesFrom' => array_map('intval', libGameFiles::setToList($row['newMessagesFrom'])),
				'lastMessageTime' => $lastMessageTime,
				// A version is null for a file which has never been written; game/playercontext for the game writes it
				'files' => libGameFiles::urls(array('id' => $gameID, 'variantID' => $row['variantID'],
					'sandboxCreatedByUserID' => $row['sandboxCreatedByUserID']), $state),
			);
		}

		return array('schema' => libGameFiles::SCHEMA, 'games' => array_values($games));
	}
}

?>
