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

require_once(l_r('gamemaster/game.php'));

/**
 * This class handles sandbox game operations
 *
 * @package GameMaster
 */
class processSandboxGame extends processGame
{
	/**
	 * Game tables that should be cloned as part of creating a sandbox game from an existing game.
	 *
	 * @var array
	 */
	private static $sandboxGameTables=array(
			'TerrStatus',
			'Units',
			'TerrStatusArchive',
			'MovesArchive'
		);

	private static function createGameMemberRecords($variantID, $name, $turn = 0, $phase = 'Pre-game')
	{
		global $DB, $User, $Game;

		if( !$User->type['User'] ) throw new Exception("Non-users cannot create sandbox games.");
		
		$Game = processGame::create($variantID, $name,'',0,'Unranked', 24*60*60, -1, 24*60*60, -1, 60,'No','Regular','Wait','draw-votes-public',0,4,'MemberVsBots');
		$Game->sandboxCreatedByUserID = $User->id;
		for($i=0; $i<count($Game->Variant->countries); $i++)
			processMember::create($User->id, 0, $i+1);

		$DB->sql_put("UPDATE wD_Games SET 
			sandboxCreatedByUserID = ".(int)$User->id.",
			turn = ".$turn.",
			phase = '".$phase."'
		WHERE id = ".$Game->id);
		$Game->phase = $phase;
		$Game->turn = $turn;
		return $Game;
	}
	/**
	 * Create a new sandbox game
	 *
	 * @param $variantID
	 */
	static function newGame($variantID=1)
	{
		global $DB, $User, $Game;

		$Game = self::createGameMemberRecords($variantID, 'SB');
		$Game->process(); // This will initialize the game with the starting units etc
		$DB->sql_put("COMMIT");

		return $Game;
	}

	/**
	 * Sandbox a game, creating a clone of a game that can be played in sandbox mode
	 *
	 * @param $gameID
	 */
	static function copy($gameID)
	{
		global $DB, $User, $Game;

		if( !$User->type['User'] ) throw new Exception("Non-users cannot create sandbox games.");

		list($name, $variantID, $turn, $phase) = $DB->sql_row("SELECT name, variantID, turn, phase FROM wD_Games WHERE id = ".$gameID);
		
		if( $turn == 0 && $phase == 'Pre-game' ) throw new Exception("You cannot create a sandbox game from a game which hasn't started yet.");

		$Game = self::createGameMemberRecords($variantID, 'SB_'.$name, $turn, $phase);
		
		foreach(self::$gameTables as $tableName=>$idColName)
		{
			if( !in_array($tableName, self::$sandboxGameTables) ) continue;

			// Replace the gameID with the new sandbox game ID
			$columnArray = explode(';',self::$gameTableColumns[$tableName]);
			$columnArray = array_diff($columnArray, array($idColName,'id'));
			$cols = implode(',', $columnArray);
			
			$DB->sql_put(
				"INSERT INTO wD_".$tableName." (".$idColName.", ".$cols.")
				SELECT ".$Game->id." ".$idColName.", ".$cols." FROM wD_".$tableName." WHERE ".$idColName." = ".$gameID
			);
		}

		// Reassign unit IDs linked in the TerrStatus table:
		$DB->sql_put("UPDATE wD_TerrStatus tsNew
		INNER JOIN wD_TerrStatus tsOld ON tsNew.terrID = tsOld.terrID 
		INNER JOIN wD_Units uOld ON uOld.id = tsOld.retreatingUnitID
		INNER JOIN wD_Units uNew ON uNew.terrID = uOld.terrID AND uNew.countryID = uOld.countryID AND uNew.gameID = tsNew.gameID
		SET tsNew.retreatingUnitID = uNew.id
		WHERE tsNew.gameID = ".$Game->id." AND tsOld.gameID = ".$gameID);
		
		/*
		localhost:43000/board.php?gameID=535724&createSandboxGame=on#gamePanel

		SELECT t.gameID, t.id, tt.name, 
		t.occupyingUnitID, u.id, u.gameID, u.type, ut.name, u.countryID, 
		t.retreatingUnitID, r.id, r.gameID, r.type, rt.name, r.countryID
		FROM wd_terrstatus t 
		INNER JOIN wd_territories tt ON tt.id = t.terrID AND tt.mapID = 1
		LEFT JOIN wd_units u ON u.id = t.occupyingUnitID
		LEFT JOIN wd_territories ut ON ut.id = u.terrID AND ut.mapID = 1
		LEFT JOIN wd_units r ON r.id = t.retreatingUnitID
		LEFT JOIN wd_territories rt ON rt.id = r.terrID AND rt.mapID = 1
		WHERE t.gameID = 535724 AND (t.occupyingUnitID IS NOT NULL OR t.retreatingUnitID IS NOT NULL);
		*/
		$DB->sql_put("UPDATE wD_TerrStatus tsNew
		INNER JOIN wD_TerrStatus tsOld ON tsNew.terrID = tsOld.terrID 
		INNER JOIN wD_Units uOld ON uOld.id = tsOld.occupyingUnitID
		INNER JOIN wD_Units uNew ON uNew.terrID = uOld.terrID AND uNew.countryID = uOld.countryID AND uNew.gameID = tsNew.gameID
		SET tsNew.occupyingUnitID = uNew.id
		WHERE tsNew.gameID = ".$Game->id." AND tsOld.gameID = ".$gameID);

		require_once(l_r('gamemaster/orders/order.php'));
		require_once(l_r('gamemaster/orders/diplomacy.php'));
		require_once(l_r('gamemaster/orders/retreats.php'));
		require_once(l_r('gamemaster/orders/builds.php'));
		$Game->generateOrders();

		$DB->sql_put("COMMIT");

		return $Game;
	}
	public static function eraseGame($gameID)
	{
		global $DB, $User;

		if( !$User->type['User'] ) throw new Exception("Non-users cannot erase sandbox games.");
		list($sandboxCreatedByUserID) = $DB->sql_row("SELECT sandboxCreatedByUserID FROM wD_Games WHERE id = ".$gameID);
		if( $sandboxCreatedByUserID !== $User->id ) throw new Exception("You can only erase sandbox games you created.");

		processGame::eraseGame($gameID, false);
	}
}

?>
