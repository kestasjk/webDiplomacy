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

define('AJAX', true); // Makes header.php ignore some of the unneeded stuff, mainly loading $User

define('IN_CODE', 1);
require_once('config.php');
if( Config::isOnPlayNowDomain() ) define('PLAYNOW',true);

require_once('header.php');

/*
 * This function logs a huge amount of javascript errors, sent from javascript/utility.js on error,
 * but it seems to log trivial errors in lots of different languages, and isn't very useful except
 * perhaps for development.
function logJavaScriptError() {
	$errorVars=array('Location','Message','URL','Line');
	$errorVals=array();
	foreach($errorVars as $varName)
	{
		if( !isset($_REQUEST['error'.$varName]) ) return;

		$errorVals[$varName] = $_REQUEST['error'.$varName];
	}

	if( isset($_SERVER['HTTP_USER_AGENT']) )
		$errorVars['UserAgent'] = $_SERVER['HTTP_USER_AGENT'];

	trigger_error('JavaScript error logged');
}
logJavaScriptError();
*/

$results = array('status'=>'Invalid', 'notice'=>'No valid action specified');

// footer JS needs to contain a cached 

// Check for group link changes. Has to be a user at least, and specifying a group type:
if( isset($User) && $User->type['User'] && isset($_GET['groupType']) && isset($_GET['userID']) && isset($_GET['groupID']) )
{
	// Someone is making a group link change.
	if( isset($_GET['setActive']) )
	{

	}
}
if( isset($_GET['groupID']) && isset($_GET['userID']) && isset($User) && $User->type['User'] )
{
}
else if( isset($_GET['sendSMSToken']) ) {
	if( libAuth::sendSMSToken_Valid($_GET['sendSMSToken']) ) {
		
		$token = explode('_', $_GET['sendSMSToken']);
		$number = $token[0];
		$message = $token[1];
		
		require_once('lib/sms.php');
		
		libSMS::send($number, $message);
		
		$results = "Success";
	}
}
else if( isset($_GET['likeMessageToggleToken']) ) {
	if( libAuth::likeToggleToken_Valid($_GET['likeMessageToggleToken']) ) {
		
		$token = explode('_', $_GET['likeMessageToggleToken']);
		$userID = (int) $token[0];
		$likeMessageID = (int) $token[1];
		
		$DB->sql_put("BEGIN");
		
		list($likeExists) = $DB->sql_row("SELECT COUNT(*) FROM wD_LikePost WHERE userID = ".$userID." AND likeMessageID = ".$likeMessageID);
		
		if( $likeExists == 0  )
		{
			$DB->sql_put("UPDATE wD_ForumMessages SET likeCount = likeCount + 1 WHERE id = ".$likeMessageID);
			$DB->sql_put("INSERT INTO wD_LikePost ( userID, likeMessageID ) VALUES ( ".$userID.", ".$likeMessageID." )");
		}
		else
		{
			$DB->sql_put("UPDATE wD_ForumMessages SET likeCount = likeCount - 1 WHERE id = ".$likeMessageID);
			$DB->sql_put("DELETE FROM wD_LikePost WHERE userID = ".$userID." AND likeMessageID = ".$likeMessageID);
		}
		
		$DB->sql_put("COMMIT");
	}
}
elseif( isset($_REQUEST['context']) && isset($_REQUEST['contextKey']) && isset($_REQUEST['orderUpdates']) )
{
	require_once(l_r('board/orders/orderinterface.php'));

	try
	{
		$O = OrderInterface::newJSON($_REQUEST['contextKey'], $_REQUEST['context']);
		$O->load();

		$newReady=$oldReady=$O->orderStatus->Ready;

		if( $O->orderStatus->Ready && isset($_REQUEST['notready']) )
			$newReady=$O->readyToggle();

		$O->set($_REQUEST['orderUpdates']);
		$O->validate();

		if( !$O->orderStatus->Ready && isset($_REQUEST['ready']) )
			$newReady=$O->readyToggle();

		$O->writeOrders();
		$O->writeOrderStatus();
		$DB->sql_put("COMMIT");

		$results = $O->getResults();
		if( $newReady != $oldReady) {
			$Game = libVariant::$Variant->Game($O->gameID);//, UPDATE); // No need to lock game for update to check whether it needs a process
			require_once('lib/pusher.php');
			libPusher::trigger("private-game" . $Game->id, 'overview', 'processed');
		}

		if( $newReady && !$oldReady )
		{
			$results['process']='Checked';
			$Game = libVariant::$Variant->Game($O->gameID);//, UPDATE); // No need to lock game for update to check whether it needs a process
			if( $Game->needsProcess() )
			{
				$MC->append('processHint',','.$Game->id);
			}
			
			/*
			Old instant-process code which was disabled as it was causing deadlocks
			\
			if( $Game->processStatus!='Crashed' && $Game->attempts > count($Game->Members->ByID)*2 )
			{
				$DB->sql_put("COMMIT");
				require_once(l_r('gamemaster/game.php'));
				$Game =libVariant::$Variant->processGame($Game->id);
				$Game->crashed();
				$DB->sql_put("COMMIT");
			}
			elseif( $Game->needsProcess() )
			{
				$DB->sql_put("UPDATE wD_Games SET attempts=attempts+1 WHERE id=".$Game->id);
				$DB->sql_put("COMMIT");

				$results['process']='Attempted';

				require_once(l_r('gamemaster/game.php'));
				$Game = libVariant::$Variant->processGame($O->gameID);
				if( $Game->needsProcess() )
				{
					$Game->process();
					$DB->sql_put("UPDATE wD_Games SET attempts=0 WHERE id=".$Game->id);
					$DB->sql_put("COMMIT");
					$results['process']='Success';
					$results['notice']=l_t('Game processed, click <a href="%s">here</a> to refresh..','board.php?gameID='.$Game->id.'&nocache='.rand(0,1000));
				}
			}*/
		}
	}
	catch(Exception $e)
	{
		if( $e->getMessage() == "Abandoned" || $e->getMessage() == "Cancelled" )
			$DB->sql_put("COMMIT");
		else
			$DB->sql_put("ROLLBACK");

		$results = array('invalid'=>true, 'statusIcon'=>'<img src="'.l_s('images/icons/alert.png').'" alt="'.l_t('Error').'" title="'.l_t('Error alert').'" />',
			'statusText'=>'', 'notice'=>l_t('Exception: ').$e->getMessage(), 'orders'=>array());
	}
}
/*
file_put_contents(
	'bot_ajax_requestlog.txt', 
	date('l jS \of F Y h:i:s A')."\n".
	"UserID: ".$User->id."\n".
	"-------------------\n".
	$_SERVER['REQUEST_URI']."\n".
	"-------------------\n".
	json_encode($results, JSON_PRETTY_PRINT)."\n".
	"-------------------\n\n", 
	FILE_APPEND);
*/
header('X-JSON: ('.json_encode($results).')');

close();

?>
