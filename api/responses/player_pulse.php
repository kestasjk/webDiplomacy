<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas / Timothy Jones

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

defined('IN_CODE') or die('This script can not be run by itself.');

require_once ('config.php');

/**
 * A lightweight "pulse" of every active game for this user: everything a polling
 * client needs to decide whether anything has changed in a game (and so whether a
 * full game/status fetch is warranted), in a single call.
 *
 * Returns the same game set as ActiveGames (active_games.php), with per-game
 * change-detection fields (turn/phase/processTime/gameOver/orderStatus/votes) and
 * lastMessageTimeSent taken from the same Redis lastmsgtime_{gameID}_{countryID}
 * keys that game/getmessages uses (written by libGameMessage::send()).
 *
 * Cost: one wD_Members/wD_Games JOIN + one Redis GET per game; a batched
 * MAX(timeSent) fallback query only for games whose Redis key is missing.
 *
 * @package webdiplomacy_api
 */
class PlayerPulse {
	/**
	 * List of per-game pulse rows
	 * @var array
	 */
	public $value = array();

	/**
	 * Load the pulse rows for a given user's active games;
	 */
	function load($userID)
	{
		global $DB, $Redis;

		$userID = intval($userID);

        // Filter allowed variantIDs
        $apiVariants = implode(', ', array_map('intval', \Config::$apiConfig['variantIDs']));

        // Filter allowed gameIDs
        $filterGameClause = '';
        if (!empty(\Config::$apiConfig['restrictToGameIDs'])) {
            $filterGameIDs = implode(', ', array_map('intval', \Config::$apiConfig['restrictToGameIDs']));
            $filterGameClause = "AND g.id IN ($filterGameIDs)";
        }

        // Same filters as ActiveGames (active_games.php), so a client can switch between
        // players/active_games and players/pulse and see the identical game set.
		$countryTabl = $DB->sql_tabl("SELECT m.gameID, m.countryID, m.orderStatus, m.votes, m.status,
                                             g.variantID, g.turn, g.phase, g.gameOver, g.pressType, g.potType,
                                             g.drawType, g.processTime, g.phaseMinutes
                                      FROM wD_Members AS m
                                      LEFT JOIN wD_Games AS g ON ( g.id = m.gameID )
                                      WHERE m.status = 'Playing'
                                            AND m.userID = $userID
                                            AND g.variantID in ($apiVariants)
                                            " . $filterGameClause . "
                                            AND g.phase IN ('Diplomacy', 'Retreats', 'Builds')
                                      ORDER BY g.processTime ASC;");
        $gameIDs = array();
        while( $row = $DB->tabl_hash($countryTabl) )
        {
            // Ensure only one row per gameID, for sandbox games.
            if( in_array($row['gameID'], $gameIDs) )
                continue;

            $gameIDs[] = intval($row['gameID']);

            array_push($this->value, [
                'gameID' => intval($row['gameID']),
                'countryID' => intval($row['countryID']),
                'variantID' => intval($row['variantID']),
                'turn' => intval($row['turn']),
                'phase' => $row['phase'],
                'gameOver' => $row['gameOver'],
                'pressType' => $row['pressType'],
                'potType' => $row['potType'],
                'drawType' => $row['drawType'],
                'processTime' => ($row['processTime'] === null ? null : intval($row['processTime'])),
                'phaseLengthInMinutes' => intval($row['phaseMinutes']),
                'orderStatus' => $row['orderStatus'],
                'votes' => $row['votes'],
                'status' => $row['status'],
                'lastMessageTimeSent' => null,
            ]);
        }

        // Fetch lastMessageTimeSent per game from Redis (RedisInterface has no mget)
        $missed = array(); // gameID => index into $this->value
        foreach($this->value as $i => $g)
        {
            try {
                $val = $Redis->get("lastmsgtime_".$g['gameID']."_".$g['countryID']);
                if( $val !== false && $val !== null )
                {
                    $this->value[$i]['lastMessageTimeSent'] = intval($val);
                    continue;
                }
            } catch (\Exception $e) { /* Redis down; fall back to the DB below */ }
            $missed[$g['gameID']] = $i;
        }

        // For any games missing their Redis key (e.g. after a Redis flush) get the latest
        // message time from the DB in one batched query, and re-seed the keys so the
        // fallback is only paid once per game. The game-wide MAX is >= the per-country
        // value, so a client can never miss a message because of the re-seed.
        if( !empty($missed) )
        {
            $in = implode(', ', array_map('intval', array_keys($missed)));
            $msgTabl = $DB->sql_tabl("SELECT gameID, MAX(timeSent) AS t FROM wD_GameMessages
                                      WHERE gameID IN ($in) GROUP BY gameID");
            $maxByGame = array();
            while( $r = $DB->tabl_hash($msgTabl) )
                $maxByGame[intval($r['gameID'])] = intval($r['t']);

            foreach($missed as $gid => $i)
            {
                $t = isset($maxByGame[$gid]) ? $maxByGame[$gid] : 0; // 0 = no messages yet
                $this->value[$i]['lastMessageTimeSent'] = $t;
                try {
                    $Redis->set("lastmsgtime_".$gid."_".$this->value[$i]['countryID'], $t);
                } catch (\Exception $e) { /* ignore; next call will fall back again */ }
            }
        }
	}

	function toJson($gameIDMultiplexer)
	{
        $multiplexedValue = array();
        foreach($this->value as $gameData)
        {
            $newGameData = array();
            foreach($gameData as $k=>$v)
            {
                if ( $k == 'gameID' )
                    $v = $gameIDMultiplexer->gameIDToMultiplexedGameID($v);
                $newGameData[$k] = $v;
            }
            array_push($multiplexedValue, $newGameData);
        }
		return json_encode(['games' => $multiplexedValue]);
	}

	/**
	 * Finds the pulse of every active game the user is playing in.
	 */
	function __construct($userID)
	{
		$this->load($userID);
	}

}
