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

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * The public JSON files of a game (doc/gamedata/02-spec.md): game.json, status.json, history.json and
 * messages.json in the game's cache folder, and variant.json in the variant's cache folder. Together with the
 * game/playercontext API route they give a board or a bot everything it needs to show and play a game.
 *
 * The files are served by the web server to anyone, so nothing goes into them that board.php would hide from a
 * logged-out visitor. Every value is picked from a named column; objects are never encoded wholesale.
 *
 * This class is the only writer of the files. refresh() has to be called after the change it publishes has been
 * committed. It reads the game again under a per-game lock, so that when several requests change a game at once the
 * last one to write has seen what the others committed.
 *
 * Each file has a version, which changes whenever the file is rewritten and is what clients compare and put in the
 * file's URL, and a fingerprint of the database values it was built from, which is only kept in Redis. A file is
 * only rewritten when its fingerprint changes. game/playercontext compares the fingerprints on every request, so a
 * change that didn't call refresh() is picked up the next time anyone looks at the game.
 *
 * @package Base
 * @subpackage Game
 */
class libGameFiles
{
	const SCHEMA = 1;

	/**
	 * The per-game files, in the order they are written
	 */
	public static $files = array('game', 'status', 'history', 'messages');

	const VOTES = array('Draw', 'Pause', 'Cancel', 'Concede');

	/**
	 * The wD_Games columns the files and fingerprints are built from. The invite code is deliberately not among them.
	 */
	const GAME_COLUMNS = "id, variantID, name, turn, phase, gameOver, processTime, processStatus, pauseTimeRemaining,
		phaseMinutes, phaseMinutesRB, nextPhaseMinutes, phaseSwitchPeriod, startTime, finishTime, pot, potType, minimumBet,
		pressType, anon, drawType, missingPlayerPolicy, playerTypes, excusedMissedTurns, minimumReliabilityRating,
		(password IS NOT NULL AND password <> '') AS isPrivate, sandboxCreatedByUserID, directorUserID";

	const MEMBER_COLUMNS = "id, userID, countryID, status, orderStatus, votes, votesChanged, bet, pointsWon, missedPhases,
		excusedMissedTurns, timeLoggedIn, newMessagesFrom, supplyCenterNo, unitNo";

	/**
	 * @return array|false The game's row, or false if there is no such game
	 */
	public static function loadGameRow($gameID)
	{
		global $DB;

		$row = $DB->sql_hash("SELECT ".self::GAME_COLUMNS." FROM wD_Games WHERE id = ".intval($gameID));

		return $row ? $row : false;
	}

	/**
	 * @return array[] The game's member rows, by country (pre-game, when no-one has a country yet, in the order they joined)
	 */
	public static function loadMemberRows($gameID)
	{
		global $DB;

		$rows = array();
		$tabl = $DB->sql_tabl("SELECT ".self::MEMBER_COLUMNS." FROM wD_Members WHERE gameID = ".intval($gameID)." ORDER BY countryID, id");
		while( $row = $DB->tabl_hash($tabl) )
			$rows[] = $row;

		return $rows;
	}

	/**
	 * Whether the players' identities are hidden from the public; the rule board.php applies to a logged-out visitor.
	 */
	public static function identitiesHidden(array $gameRow)
	{
		return ( $gameRow['anon'] == 'Yes' && $gameRow['phase'] != 'Finished' );
	}

	public static function isSandbox(array $gameRow)
	{
		return !is_null($gameRow['sandboxCreatedByUserID']);
	}

	/**
	 * A database SET column's value as a list
	 */
	public static function setToList($set)
	{
		if( is_null($set) || $set === '' ) return array();

		return explode(',', $set);
	}

	/**
	 * A member's order status as the public may see it. While identities are hidden that is only whether the
	 * country has any orders to enter this phase, which is what board.php's padlock icon gives away.
	 *
	 * @return string[]|null Null if hidden
	 */
	public static function publicOrderStatus(array $gameRow, array $memberRow)
	{
		$orderStatus = self::setToList($memberRow['orderStatus']);

		if( !self::identitiesHidden($gameRow) ) return $orderStatus;

		return in_array('None', $orderStatus) ? array('None') : null;
	}

	/**
	 * A member's votes as the public may see them: only those of members still playing an unfinished game, and
	 * not a vote to draw unless the game has public draw votes.
	 *
	 * @return string[]
	 */
	public static function publicVotes(array $gameRow, array $memberRow)
	{
		if( $memberRow['status'] != 'Playing' || $gameRow['phase'] == 'Finished' ) return array();

		$votes = array();
		foreach(self::setToList($memberRow['votes']) as $vote)
			if( $vote != 'Draw' || $gameRow['drawType'] == 'draw-votes-public' )
				$votes[] = $vote;

		return $votes;
	}

	/**
	 * The Redis key holding the id of the newest message that belongs in messages.json, set by libGameMessage::send()
	 */
	public static function newestPublicMessageKey($gameID)
	{
		return 'gamefilesmsg_'.intval($gameID);
	}

	/**
	 * Whether a message sent from a country to itself is the log of a vote, which messages.json lists for games
	 * with public draw votes.
	 */
	public static function isVoteLog($message)
	{
		return self::parseVoteLog($message) !== false;
	}

	/**
	 * @return array|false array(vote, on)
	 */
	private static function parseVoteLog($message)
	{
		foreach(self::VOTES as $vote)
		{
			if( $message === 'Voted for '.$vote ) return array($vote, true);
			if( $message === 'Un-Voted for '.$vote ) return array($vote, false);
		}
		return false;
	}

	/**
	 * To be called by libGameMessage::send() for every message, with the new message's id
	 */
	public static function messageSent($gameID, $toCountryID, $fromCountryID, $message, $messageID)
	{
		global $Redis;

		if( $toCountryID != 0 && !( $toCountryID == $fromCountryID && self::isVoteLog($message) ) ) return;

		try
		{
			$Redis->set(self::newestPublicMessageKey($gameID), intval($messageID));
		}
		catch(Exception $e) { }
	}

	private static function publicMessagesWhere($gameID)
	{
		$voteLogs = array();
		foreach(self::VOTES as $vote)
		{
			$voteLogs[] = "'Voted for ".$vote."'";
			$voteLogs[] = "'Un-Voted for ".$vote."'";
		}

		return "gameID = ".intval($gameID)." AND ( toCountryID = 0 OR ( toCountryID = fromCountryID AND message IN (".implode(',', $voteLogs).") ) )";
	}

	/**
	 * The id of the newest message which belongs in messages.json, from Redis, or from the database if Redis has lost it
	 */
	private static function newestPublicMessageID($gameID)
	{
		global $DB, $Redis;

		try
		{
			$id = $Redis->get(self::newestPublicMessageKey($gameID));
			if( $id !== false && !is_null($id) ) return intval($id);
		}
		catch(Exception $e) { }

		list($id) = $DB->sql_row("SELECT MAX(id) FROM wD_GameMessages WHERE ".self::publicMessagesWhere($gameID));
		$id = intval($id);

		try
		{
			// Only if it's still missing, as a message may have been sent since the query
			$Redis->setIfMissing(self::newestPublicMessageKey($gameID), $id, 30*24*60*60);
		}
		catch(Exception $e) { }

		return $id;
	}

	/**
	 * A fingerprint per file of the database values the file is built from, other than those which are left out so
	 * that they don't cause rewrites (when members were last seen, and their usernames and points).
	 *
	 * @return string[] Indexed by file name
	 */
	public static function fingerprints(array $gameRow, array $memberRows)
	{
		$hidden = self::identitiesHidden($gameRow);

		$gameValues = $gameRow;
		unset($gameValues['directorUserID']);
		$statusValues = array($gameRow['turn'], $gameRow['phase']);
		foreach($memberRows as $memberRow)
		{
			$gameValues[] = array($memberRow['countryID'], $memberRow['status'], $memberRow['supplyCenterNo'], $memberRow['unitNo'],
				$memberRow['bet'], $memberRow['pointsWon'],
				$hidden ? null : array($memberRow['userID'], $memberRow['missedPhases'], $memberRow['excusedMissedTurns']));
			$statusValues[] = array($memberRow['countryID'], self::publicOrderStatus($gameRow, $memberRow), self::publicVotes($gameRow, $memberRow));
		}

		return array(
			'game' => md5(self::SCHEMA.json_encode($gameValues)),
			'status' => md5(self::SCHEMA.json_encode($statusValues)),
			// processTime is included so that a sandbox game moved back and processed again to the same phase differs
			'history' => md5(self::SCHEMA.json_encode(array($gameRow['turn'], $gameRow['phase'], $gameRow['gameOver'],
				self::isSandbox($gameRow) ? $gameRow['processTime'] : null))),
			// Besides the newest message's id, which only this code keeps, two values which all code keeps up to date:
			// when the newest message to everyone was sent, and when anyone last changed their votes (which is when
			// a vote is logged). So messages.json is rewritten even when whatever sent the message didn't call
			// refresh(), e.g. older code on another site sharing the database.
			'messages' => md5(self::SCHEMA.json_encode(array(self::newestPublicMessageID($gameRow['id']),
				self::lastGlobalMessageTime($gameRow['id']),
				$gameRow['drawType'] == 'draw-votes-public' ? max(array_merge(array(0), array_column($memberRows, 'votesChanged'))) : null,
				$gameRow['drawType'], $gameRow['pressType'], $gameRow['phase'] == 'Finished'))),
		);
	}

	/**
	 * When the newest message to everyone in the game was sent, which libGameMessage::send() keeps in Redis for the
	 * game's spectators; null if Redis doesn't have it
	 */
	private static function lastGlobalMessageTime($gameID)
	{
		global $Redis;

		try
		{
			$value = $Redis->get('lastmsgtime_'.intval($gameID).'_0');
			return ( $value === false || is_null($value) ) ? null : intval($value);
		}
		catch(Exception $e)
		{
			return null;
		}
	}

	private static function stateKey($gameID)
	{
		return 'gamefiles_'.intval($gameID);
	}

	/**
	 * What Redis holds about the game's files: array(file => array('v' => version, 'f' => fingerprint)). The SSE
	 * server reads the versions from the same key (sse-server/server.js).
	 *
	 * @return array Empty if nothing is held, or Redis is down
	 */
	public static function state($gameID)
	{
		global $Redis;

		try
		{
			$state = $Redis->get(self::stateKey($gameID));
			if( $state !== false && !is_null($state) )
			{
				$state = json_decode($state, true);
				if( is_array($state) ) return $state;
			}
		}
		catch(Exception $e) { }

		return array();
	}

	/**
	 * Sandbox games are only viewable by their creator, moderators and people given a link, so their files
	 * aren't given names that can be worked out from the game ID.
	 */
	private static function filename(array $gameRow, $file)
	{
		if( self::isSandbox($gameRow) )
			return $file.'-'.substr(md5('GameFiles_'.$gameRow['id'].'_'.$file.'_'.Config::$secret), 0, 20).'.json';
		else
			return $file.'.json';
	}

	private static function path(array $gameRow, $file)
	{
		return libCache::dirID('games', intval($gameRow['id'])).'/'.self::filename($gameRow, $file);
	}

	/**
	 * Which of a game's files are out of date: those whose fingerprint has changed, which Redis holds nothing
	 * about, or which are missing from the cache folder.
	 *
	 * @return string[] File names
	 */
	public static function staleFiles(array $gameRow, array $memberRows)
	{
		$state = self::state($gameRow['id']);
		$fingerprints = self::fingerprints($gameRow, $memberRows);

		$stale = array();
		foreach(self::$files as $file)
			if( !isset($state[$file]['f']) || $state[$file]['f'] !== $fingerprints[$file] || !file_exists(self::path($gameRow, $file)) )
				$stale[] = $file;

		return $stale;
	}

	/**
	 * The URL (relative to the site root) and version of each of a game's files and of its variant's file, for
	 * giving to a client which may view the game.
	 *
	 * @param array $state From state(), read after any refresh()
	 * @return array array(file => array('url' => .., 'version' => ..))
	 */
	public static function urls(array $gameRow, array $state)
	{
		$urls = array('variant' => self::variantURL($gameRow['variantID']));
		foreach(self::$files as $file)
			$urls[$file] = array(
				'url' => self::path($gameRow, $file),
				'version' => isset($state[$file]['v']) ? $state[$file]['v'] : null,
			);

		return $urls;
	}

	/**
	 * Bring a game's files up to date, and tell connected clients which ones changed. Call it after committing a
	 * change to anything the files contain; it must not be called with uncommitted changes, as it would publish them.
	 *
	 * It never throws: the files are a cache, and failing to write one must not break the request which changed
	 * the game. A file which couldn't be written stays out of date until the next refresh.
	 *
	 * @param int $gameID
	 * @param string[]|null $files The files which may have changed; all of them if null
	 * @return array The state() after the refresh
	 */
	public static function refresh($gameID, $files = null)
	{
		global $DB, $Redis;

		$gameID = intval($gameID);
		if( is_null($files) ) $files = self::$files;

		// Config::$gameFilesDisabled turns off writing the files without a redeploy, if they ever cause trouble
		if( property_exists('Config', 'gameFilesDisabled') && Config::$gameFilesDisabled )
			return self::state($gameID);

		// This runs after order saves, votes, messages and game processing, which must not fail because of it. The
		// site's error handler ends the request on any PHP warning, so while this runs warnings are turned into
		// exceptions, which the catch below logs and ignores.
		set_error_handler(function($level, $message, $file, $line) {
			if( !( error_reporting() & $level ) || $level == E_DEPRECATED || $level == E_USER_DEPRECATED )
				return true;
			throw new ErrorException($message, 0, $level, $file, $line);
		});

		$lockName = 'gamefiles_'.$gameID;
		$locked = false;
		try
		{
			// Normally held for milliseconds; after a long game is processed, for as long as its history takes to build
			list($locked) = $DB->sql_row("SELECT GET_LOCK('".$lockName."', 2)");
			if( $locked != 1 )
			{
				// Whoever holds the lock may have read the game before this request's change was committed, so the
				// files may be left out of date; game/playercontext will find that they are.
				$locked = false;
				return self::state($gameID);
			}

			$gameRow = self::loadGameRow($gameID);
			if( $gameRow === false )
			{
				self::delete($gameID);
				return array();
			}

			$memberRows = self::loadMemberRows($gameID);
			$fingerprints = self::fingerprints($gameRow, $memberRows);
			$state = self::state($gameID);

			$changed = array();
			foreach($files as $file)
			{
				if( !in_array($file, self::$files) ) continue;

				$path = self::path($gameRow, $file);
				if( isset($state[$file]['f']) && $state[$file]['f'] === $fingerprints[$file] && file_exists($path) )
					continue;

				$version = dechex(time()).bin2hex(random_bytes(3));

				$contents = call_user_func(array('libGameFiles', 'build'.ucfirst($file)), $gameRow, $memberRows);
				if( $contents === false ) continue;

				$contents = array_merge(array(
					'schema' => self::SCHEMA,
					'gameID' => intval($gameRow['id']),
					'file' => $file,
					'version' => $version,
					'generated' => time(),
				), $contents);

				if( !self::write($path, $contents) ) continue;

				$state[$file] = array('v' => $version, 'f' => $fingerprints[$file]);
				$changed[$file] = $version;
			}

			if( count($changed) )
			{
				$Redis->set(self::stateKey($gameID), json_encode($state), 30*24*60*60);
				// On a channel of its own, which the older boards don't listen to (they take any event mentioning
				// "message" as a new message). Only clients holding a token for this game can listen to it.
				$Redis->trigger('private-game'.$gameID.'-files', 'files', $changed);
			}

			return $state;
		}
		catch(Throwable $e)
		{
			error_log('libGameFiles::refresh('.$gameID.'): '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine());
			return self::state($gameID);
		}
		finally
		{
			if( $locked )
			{
				try { $DB->sql_row("SELECT RELEASE_LOCK('".$lockName."')"); } catch(Throwable $e) { }
			}
			restore_error_handler();
		}
	}

	private static $deferred = array();

	/**
	 * For code which changes a game but leaves committing to the end of the page (close() in header.php): refresh the
	 * game's files once the page has committed. If the page fails and doesn't commit, nothing is refreshed.
	 *
	 * @param int $gameID
	 * @param string[]|null $files As for refresh()
	 */
	public static function refreshAfterCommit($gameID, $files = null)
	{
		$gameID = intval($gameID);
		if( is_null($files) ) $files = self::$files;

		if( !isset(self::$deferred[$gameID]) ) self::$deferred[$gameID] = array();

		self::$deferred[$gameID] = array_values(array_unique(array_merge(self::$deferred[$gameID], $files)));
	}

	/**
	 * Called by close() in header.php, after it has committed
	 */
	public static function runDeferred()
	{
		$deferred = self::$deferred;
		self::$deferred = array();

		foreach($deferred as $gameID => $files)
			self::refresh($gameID, $files);
	}

	/**
	 * Remove a game's files, for a game which has been erased.
	 */
	public static function delete($gameID)
	{
		global $Redis;

		$gameID = intval($gameID);

		try
		{
			$Redis->delete(self::stateKey($gameID));
			$Redis->delete(self::newestPublicMessageKey($gameID));
		}
		catch(Exception $e) { }

		$dir = 'cache/games/'.floor($gameID/100).'/'.$gameID;
		if( !is_dir($dir) ) return;

		foreach(self::$files as $file)
			foreach(glob($dir.'/'.$file.'*.json') as $path)
				@unlink($path);
	}

	/**
	 * Whether a file in a game's cache folder is one of these, for Game::wipeCache() to leave alone.
	 */
	public static function isGameFile($path)
	{
		return (bool)preg_match('/\.json(\.[0-9a-f]+\.tmp)?$/', $path);
	}

	/**
	 * Write a file so that a reader sees either the old contents or the new, never part of a file.
	 */
	private static function write($path, array $contents)
	{
		$json = json_encode($contents, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
		if( $json === false ) return false;

		$tmp = $path.'.'.bin2hex(random_bytes(4)).'.tmp';
		if( file_put_contents($tmp, $json) === false ) return false;
		@chmod($tmp, 0664);

		if( !rename($tmp, $path) )
		{
			@unlink($tmp);
			return false;
		}

		return true;
	}

	private static function intOrNull($value)
	{
		return ( is_null($value) || $value === '' ) ? null : intval($value);
	}

	/**
	 * A territory ID column in which 0 or NULL means none
	 */
	private static function terrIDOrNull($value)
	{
		return intval($value) > 0 ? intval($value) : null;
	}

	private static function loadVariant(array $gameRow)
	{
		require_once(l_r('lib/variant.php'));

		return libVariant::loadFromVariantID(intval($gameRow['variantID']));
	}

	/**
	 * game.json: the game's settings, its members and the board as it stands
	 */
	private static function buildGame(array $gameRow, array $memberRows)
	{
		global $DB;

		$gameID = intval($gameRow['id']);
		$hidden = self::identitiesHidden($gameRow);
		$Variant = self::loadVariant($gameRow);
		$variantURL = self::variantURL($gameRow['variantID']);

		$users = array();
		if( !$hidden )
		{
			$tabl = $DB->sql_tabl("SELECT u.id, u.username, u.points, u.type FROM wD_Members m
				INNER JOIN wD_Users u ON ( u.id = m.userID ) WHERE m.gameID = ".$gameID);
			while( $row = $DB->tabl_hash($tabl) )
				$users[$row['id']] = array(
					'userID' => intval($row['id']),
					'username' => $row['username'],
					'points' => intval($row['points']),
					'type' => self::setToList($row['type']),
				);
		}

		$members = array();
		foreach($memberRows as $memberRow)
		{
			$countryID = intval($memberRow['countryID']);

			if( $hidden )
			{
				$daysSinceSeen = ( time() - intval($memberRow['timeLoggedIn']) ) / (24*60*60);
				$lastSeen = ( $daysSinceSeen < 1 ? 'day' : ( $daysSinceSeen < 7 ? 'week' : 'older' ) );
			}
			else
				$lastSeen = intval($memberRow['timeLoggedIn']);

			$members[] = array(
				'countryID' => $countryID,
				'country' => ( $countryID > 0 && isset($Variant->countries[$countryID-1]) ) ? $Variant->countries[$countryID-1] : null,
				'status' => $memberRow['status'],
				'supplyCenterNo' => intval($memberRow['supplyCenterNo']),
				'unitNo' => intval($memberRow['unitNo']),
				'bet' => intval($memberRow['bet']),
				'pointsWon' => self::intOrNull($memberRow['pointsWon']),
				'user' => ( !$hidden && isset($users[$memberRow['userID']]) ) ? $users[$memberRow['userID']] : null,
				'missedPhases' => $hidden ? null : intval($memberRow['missedPhases']),
				'excusedMissedTurns' => $hidden ? null : intval($memberRow['excusedMissedTurns']),
				'lastSeen' => $lastSeen,
			);
		}

		$retreatingUnitIDs = array();
		$territories = array();
		$tabl = $DB->sql_tabl("SELECT terrID, countryID, occupyingUnitID, retreatingUnitID, standoff, occupiedFromTerrID
			FROM wD_TerrStatus WHERE gameID = ".$gameID." ORDER BY terrID");
		while( $row = $DB->tabl_hash($tabl) )
		{
			if( !is_null($row['retreatingUnitID']) ) $retreatingUnitIDs[intval($row['retreatingUnitID'])] = true;

			$territories[] = array(
				'terrID' => intval($row['terrID']),
				'ownerCountryID' => self::intOrNull($row['countryID']),
				'unitID' => self::intOrNull($row['occupyingUnitID']),
				'retreatingUnitID' => self::intOrNull($row['retreatingUnitID']),
				'standoff' => ( $row['standoff'] == 'Yes' ),
				'occupiedFromTerrID' => self::terrIDOrNull($row['occupiedFromTerrID']),
			);
		}

		$units = array();
		$tabl = $DB->sql_tabl("SELECT id, countryID, type, terrID FROM wD_Units WHERE gameID = ".$gameID." ORDER BY id");
		while( $row = $DB->tabl_hash($tabl) )
			$units[] = array(
				'id' => intval($row['id']),
				'countryID' => intval($row['countryID']),
				'type' => $row['type'],
				'terrID' => intval($row['terrID']),
				'retreating' => isset($retreatingUnitIDs[intval($row['id'])]),
			);

		// Who left the game in civil disorder names users, so it is only for finished games, as on board.php
		$civilDisorders = null;
		if( $gameRow['phase'] == 'Finished' )
		{
			$civilDisorders = array();
			$tabl = $DB->sql_tabl("SELECT c.userID, u.username, c.countryID, c.turn, c.SCCount FROM wD_CivilDisorders c
				INNER JOIN wD_Users u ON ( u.id = c.userID ) WHERE c.gameID = ".$gameID." ORDER BY c.turn, c.countryID");
			while( $row = $DB->tabl_hash($tabl) )
				$civilDisorders[] = array(
					'userID' => intval($row['userID']),
					'username' => $row['username'],
					'countryID' => intval($row['countryID']),
					'turn' => intval($row['turn']),
					'supplyCenterNo' => intval($row['SCCount']),
				);
		}

		return array(
			'name' => $gameRow['name'],
			'variant' => array(
				'id' => intval($gameRow['variantID']),
				'name' => $Variant->name,
				'url' => $variantURL['url'],
				'version' => $variantURL['version'],
			),
			'turn' => intval($gameRow['turn']),
			'phase' => $gameRow['phase'],
			'turnText' => $Variant->turnAsDate(intval($gameRow['turn'])),
			'gameOver' => $gameRow['gameOver'],
			'processTime' => self::intOrNull($gameRow['processTime']),
			'processStatus' => $gameRow['processStatus'],
			'pauseTimeRemaining' => self::intOrNull($gameRow['pauseTimeRemaining']),
			'phaseMinutes' => intval($gameRow['phaseMinutes']),
			'phaseMinutesRB' => intval($gameRow['phaseMinutesRB']),
			'nextPhaseMinutes' => intval($gameRow['nextPhaseMinutes']),
			'phaseSwitchPeriod' => intval($gameRow['phaseSwitchPeriod']),
			'startTime' => self::intOrNull($gameRow['startTime']),
			'finishTime' => self::intOrNull($gameRow['finishTime']),
			'pot' => intval($gameRow['pot']),
			'potType' => $gameRow['potType'],
			'minimumBet' => self::intOrNull($gameRow['minimumBet']),
			'pressType' => $gameRow['pressType'],
			'anon' => ( $gameRow['anon'] == 'Yes' ),
			'drawType' => $gameRow['drawType'],
			'missingPlayerPolicy' => $gameRow['missingPlayerPolicy'],
			'playerTypes' => $gameRow['playerTypes'],
			'excusedMissedTurns' => intval($gameRow['excusedMissedTurns']),
			'minimumReliabilityRating' => intval($gameRow['minimumReliabilityRating']),
			'isPrivate' => (bool)$gameRow['isPrivate'],
			'isSandbox' => self::isSandbox($gameRow),
			'identitiesHidden' => $hidden,
			'members' => $members,
			'units' => $units,
			'territories' => $territories,
			'civilDisorders' => $civilDisorders,
		);
	}

	/**
	 * status.json: what changes when a player saves orders or votes
	 */
	private static function buildStatus(array $gameRow, array $memberRows)
	{
		$members = array();
		foreach($memberRows as $memberRow)
			$members[] = array(
				'countryID' => intval($memberRow['countryID']),
				'orderStatus' => self::publicOrderStatus($gameRow, $memberRow),
				'votes' => self::publicVotes($gameRow, $memberRow),
			);

		return array(
			'turn' => intval($gameRow['turn']),
			'phase' => $gameRow['phase'],
			'members' => $members,
		);
	}

	/**
	 * history.json: every phase of the game, as the game/status API's GameState builds them, so that the two can't
	 * differ; here they are only given consistent types. While the game is running the last phase is the one being
	 * played, with its units and supply centers and no orders. (Its units and centers are fixed until the game is
	 * next processed, so including it doesn't make the file change any more often.) Clients which replay a game,
	 * like the bots, need that phase to come from the same source as the others.
	 */
	private static function buildHistory(array $gameRow, array $memberRows)
	{
		if( $gameRow['phase'] == 'Pre-game' ) return array('phases' => array());

		require_once(l_r('api/responses/territory.php'));
		require_once(l_r('api/responses/game_state.php'));

		try
		{
			$gameState = new \webdiplomacy_api\GameState(intval($gameRow['id']), null);
		}
		catch(Exception $e)
		{
			// GameState can find the archive tables part-written while a game is being processed
			error_log('libGameFiles history for game '.$gameRow['id'].': '.$e->getMessage());
			return false;
		}

		// GameState was loaded after $gameRow; if the game has moved on in between this history doesn't belong to
		// the fingerprint it would be saved against
		if( $gameState->turn != $gameRow['turn'] || $gameState->phase != $gameRow['phase'] ) return false;

		$Variant = self::loadVariant($gameRow);

		$phases = array();
		foreach($gameState->phases as $phase)
		{
			$units = array();
			foreach($phase['units'] as $unit)
				$units[] = array(
					'countryID' => intval($unit->countryID),
					'type' => $unit->unitType,
					'terrID' => intval($unit->terrID),
					'retreating' => ( $unit->retreating == 'Yes' ),
				);

			$centers = array();
			foreach($phase['centers'] as $center)
				$centers[] = array('terrID' => intval($center->terrID), 'countryID' => intval($center->countryID));

			$orders = array();
			foreach($phase['orders'] as $order)
				$orders[] = array(
					'countryID' => intval($order->countryID),
					'terrID' => self::terrIDOrNull($order->terrID),
					'unitType' => ( $order->unitType === '' ? null : $order->unitType ),
					'type' => $order->type,
					'toTerrID' => self::terrIDOrNull($order->toTerrID),
					'fromTerrID' => self::terrIDOrNull($order->fromTerrID),
					'viaConvoy' => ( $order->viaConvoy == 'Yes' ),
					'success' => ( $order->success == 'Yes' ),
					'dislodged' => ( $order->dislodged == 'Yes' ),
				);

			$phases[] = array(
				'turn' => intval($phase['turn']),
				'phase' => $phase['phase'],
				'turnText' => $Variant->turnAsDate(intval($phase['turn'])),
				'units' => $units,
				'centers' => $centers,
				'orders' => $orders,
			);
		}

		return array('phases' => $phases);
	}

	/**
	 * messages.json: the messages sent to everyone, which any visitor can read in the messages archive, and for
	 * games with public draw votes the log of who voted for what and when.
	 */
	private static function buildMessages(array $gameRow, array $memberRows)
	{
		global $DB;

		$votesPublic = ( $gameRow['drawType'] == 'draw-votes-public' );

		$messages = array();
		$voteLog = array();
		$tabl = $DB->sql_tabl("SELECT id, turn, phaseMarker, timeSent, fromCountryID, toCountryID, message
			FROM wD_GameMessages WHERE ".self::publicMessagesWhere($gameRow['id'])." ORDER BY id");
		while( $row = $DB->tabl_hash($tabl) )
		{
			if( intval($row['toCountryID']) == 0 )
			{
				$messages[] = array(
					'id' => intval($row['id']),
					'turn' => intval($row['turn']),
					'phase' => $row['phaseMarker'],
					'timeSent' => intval($row['timeSent']),
					'fromCountryID' => intval($row['fromCountryID']),
					'toCountryID' => 0,
					'message' => $row['message'],
				);
			}
			elseif( $votesPublic )
			{
				$vote = self::parseVoteLog($row['message']);
				if( $vote === false ) continue;

				$voteLog[] = array(
					'id' => intval($row['id']),
					'turn' => intval($row['turn']),
					'phase' => $row['phaseMarker'],
					'timeSent' => intval($row['timeSent']),
					'countryID' => intval($row['fromCountryID']),
					'vote' => $vote[0],
					'on' => $vote[1],
				);
			}
		}

		return array('messages' => $messages, 'voteLog' => $voteLog);
	}

	/**
	 * @return array array('url' => .., 'version' => ..) of the variant's variant.json, which is written if it is missing
	 */
	public static function variantURL($variantID)
	{
		static $cache = array();

		$variantID = intval($variantID);
		if( isset($cache[$variantID]) ) return $cache[$variantID];

		if( !isset(Config::$variants[$variantID]) ) return array('url' => null, 'version' => null);

		$path = 'variants/'.Config::$variants[$variantID].'/cache/variant.json';
		if( !file_exists($path) ) self::writeVariant($variantID);

		// The file only changes when the variant is reinstalled, so when it was written will do for a version
		$cache[$variantID] = array('url' => $path, 'version' => file_exists($path) ? dechex(filemtime($path)) : null);

		return $cache[$variantID];
	}

	/**
	 * variant.json: the map, and what else about the variant a client needs. Written when the variant is installed,
	 * and by variantURL() if it is missing.
	 */
	public static function writeVariant($variantID)
	{
		global $DB;

		require_once(l_r('lib/variant.php'));

		try
		{
			$Variant = libVariant::loadFromVariantID(intval($variantID));
			$mapID = intval($Variant->mapID);

			$territories = array();
			$tabl = $DB->sql_tabl("SELECT id, name, type, supply, countryID, coast, coastParentID, smallMapX, smallMapY
				FROM wD_Territories WHERE mapID = ".$mapID." ORDER BY id");
			while( $row = $DB->tabl_hash($tabl) )
				$territories[intval($row['id'])] = array(
					'id' => intval($row['id']),
					'name' => $row['name'], // Not translated, so that clients can match their map to it by name
					'type' => $row['type'],
					'supply' => ( $row['supply'] == 'Yes' ),
					'homeCountryID' => intval($row['countryID']) > 0 ? intval($row['countryID']) : null,
					'coast' => $row['coast'],
					'coastParentID' => intval($row['coastParentID']),
					'smallMapX' => intval($row['smallMapX']),
					'smallMapY' => intval($row['smallMapY']),
					'borders' => array(),
					'coastalBorders' => array(),
				);

			foreach(array('wD_Borders' => 'borders', 'wD_CoastalBorders' => 'coastalBorders') as $table => $key)
			{
				$tabl = $DB->sql_tabl("SELECT fromTerrID, toTerrID, armysPass, fleetsPass FROM ".$table."
					WHERE mapID = ".$mapID." ORDER BY fromTerrID, toTerrID");
				while( $row = $DB->tabl_hash($tabl) )
				{
					if( !isset($territories[intval($row['fromTerrID'])]) ) continue;

					$territories[intval($row['fromTerrID'])][$key][] = array(
						'id' => intval($row['toTerrID']),
						'army' => ( $row['armysPass'] == 'Yes' ),
						'fleet' => ( $row['fleetsPass'] == 'Yes' ),
					);
				}
			}

			if( !count($territories) ) return false;

			$countries = array();
			foreach($Variant->countries as $index => $country)
				$countries[] = array('countryID' => $index + 1, 'name' => $country);

			$dir = 'variants/'.$Variant->name.'/cache';
			if( !is_dir($dir) ) return false;

			return self::write($dir.'/variant.json', array(
				'schema' => self::SCHEMA,
				'variantID' => intval($Variant->id),
				'mapID' => $mapID,
				'name' => $Variant->name,
				'fullName' => $Variant->fullName,
				'countries' => $countries,
				'supplyCenterCount' => intval($Variant->supplyCenterCount),
				'supplyCenterTarget' => intval($Variant->supplyCenterTarget),
				'rules' => new stdClass(),
				'territories' => array_values($territories),
			));
		}
		catch(Throwable $e)
		{
			error_log('libGameFiles::writeVariant('.$variantID.'): '.$e->getMessage());
			return false;
		}
	}
}

?>
