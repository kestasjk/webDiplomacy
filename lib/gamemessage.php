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
 * Send a message to a member of a countryID in a game, from another member. Used by GameMaster in processGame, and
 * Chatbox
 *
 * @package Base
 * @subpackage Game
 */
class libGameMessage
{
	/**
	 * Send a game message. Messages are sanitized
	 *
	 * @param string $toCountryID The countryID being sent to. 'Global' sends to all.
	 * @param string $fromCountryID The county being sent from. 'GameMaster' can also be used.
	 * @param string|array $message The message(s) to be sent (Can be an array of messages for)
	 * @param int[optional] $gameID The game ID to use. If not given the current global Game is sent to.
	 * 
	 * @return int $timeSent The time of this message in the DB.
	 */
	static public function send($toCountryID, $fromCountryID, $message, $gameID=-1)
	{
		global $DB, $Game, $Redis;
		if ( ! is_object($Game) )
		{
			$Variant=libVariant::loadFromGameID($gameID);
			$Game = $Variant->Game($gameID);
		}

		$message = $DB->msg_escape($message);

		if ( !is_numeric($toCountryID) )
			$toCountryID=0;

		if ( !is_numeric($fromCountryID) )
		{
			$message = '<strong>'.$fromCountryID.':</strong> '.$message;
			$fromCountryID=0;
		}

		if( 65000 < strlen($message) )
		{
			throw new Exception(l_t("Message too long"));
		}
		$timeSent = time();

		if ($toCountryID == 0) {
			$Redis->set("lastmsgtime_{$Game->id}_0", $timeSent); // spectators
			foreach($Game->Members->ByCountryID as $countryID => $member) {
				$Redis->set("lastmsgtime_{$Game->id}_{$countryID}", $timeSent);
			}
		} else {
			$Redis->set("lastmsgtime_{$Game->id}_{$fromCountryID}", $timeSent);
			$Redis->set("lastmsgtime_{$Game->id}_{$toCountryID}", $timeSent);
		}

		$DB->sql_put("INSERT INTO wD_GameMessages
					(gameID, toCountryID, fromCountryID, turn, message, phaseMarker, timeSent)
					VALUES(".$Game->id.",
						".$toCountryID.",
						".$fromCountryID.",
						".$Game->turn.",
						'".$message."',
						'".$Game->phase."',
						".$timeSent.")");

		if ($toCountryID != $fromCountryID || $fromCountryID == 0)
		{
			libGameMessage::notify($toCountryID, $fromCountryID);
		}

		$channel = "private-game" . $Game->id . "-country";

		if ($toCountryID == 0) {
			foreach($Game->Members->ByCountryID as $countryID => $member) {
				$Redis->trigger($channel . $countryID, 'message', 'messageSent');
			}
		} else {
			$channel = $channel . $toCountryID;
			$Redis->trigger($channel, 'message', 'messageSent');
		}

		// Notify the recipient(s) via Web Push. Only the sending country's name is included, never
		// the message contents, so nothing is leaked that the recipient's game screen wouldn't show.
		try
		{
			require_once(l_r('lib/push.php'));
			$pushUserIDs = array();
			if ($toCountryID == 0) {
				foreach($Game->Members->ByCountryID as $countryID => $member)
					if( $countryID != $fromCountryID ) $pushUserIDs[] = $member->userID;
			} elseif ($toCountryID != $fromCountryID && isset($Game->Members->ByCountryID[$toCountryID])) {
				$pushUserIDs[] = $Game->Members->ByCountryID[$toCountryID]->userID;
			}
			if( count($pushUserIDs) )
			{
				$fromName = ($fromCountryID == 0) ? l_t('GameMaster') : $Game->Variant->countries[$fromCountryID-1];
				libPush::sendToUsers($pushUserIDs, $Game->name, l_t('New message from %s',$fromName),
					'/board.php?gameID='.$Game->id.'&msgCountryID='.($toCountryID == 0 ? 0 : $fromCountryID).'#chatbox',
					'game-'.$Game->id.'-msg');
			}
		}
		catch(\Throwable $e) { /* Push delivery must never break message sending */ }

		return $timeSent;
	}

	/**
	 * Notify a countryID that you sent them a message, uses the global Game
	 *
	 * @param string $toCountryID The countryID sent to, can be 'Global'
	 * @param string $fromCountryID The countryID sent from
	 * @param Game $Game The game being referred to
	 */
	private static function notify($toCountryID, $fromCountryID)
	{
		global $DB, $Game;

		$DB->sql_put("COMMIT"); // Prevent deadlocks

		if ( $toCountryID == 0 )
		{
			$DB->sql_put("UPDATE wD_Members
						SET newMessagesFrom = IF( (newMessagesFrom+0) = 0,
												'0',
												CONCAT_WS(',',newMessagesFrom,'0') )
						WHERE gameID = ".$Game->id." AND NOT countryID=".$fromCountryID);
		}
		else
		{
			$DB->sql_put("UPDATE wD_Members
						SET newMessagesFrom = IF( (newMessagesFrom+0) = 0,
												'".$fromCountryID."',
												CONCAT_WS(',',newMessagesFrom,'".$fromCountryID."') )
						WHERE gameID = ".$Game->id." AND countryID=".$toCountryID);
		}
		$DB->sql_put("COMMIT");

	}
}
?>
