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

require_once('header.php');

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


$results = array('status'=>'Invalid', 'notice'=>'No valid action specified');

if( isset($_REQUEST['context']) && isset($_REQUEST['contextKey']) && isset($_REQUEST['orderUpdates']) )
{
	require_once('board/orders/orderinterface.php');

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

		if( $newReady && !$oldReady )
		{
			$results['process']='Checked';

			$Game = libVariant::$Variant->Game($O->gameID);

			if( $Game->processStatus!='Crashed' && $Game->attempts > count($Game->Members->ByID)*2 )
			{
				$DB->sql_put("COMMIT");
				require_once('gamemaster/game.php');
				$Game =libVariant::$Variant->processGame($Game->id);
				$Game->crashed();
				$DB->sql_put("COMMIT");
			}
			elseif( $Game->needsProcess() )
			{
				$DB->sql_put("UPDATE wD_Games SET attempts=attempts+1 WHERE id=".$Game->id);
				$DB->sql_put("COMMIT");

				$results['process']='Attempted';

				require_once('gamemaster/game.php');
				$Game = libVariant::$Variant->processGame($O->gameID);
				if( $Game->needsProcess() )
				{
					$Game->process();
					$DB->sql_put("UPDATE wD_Games SET attempts=0 WHERE id=".$Game->id);
					$DB->sql_put("COMMIT");
					$results['process']='Success';
					$results['notice']='Game processed, click <a href="board.php?gameID='.$Game->id.'&nocache='.rand(0,1000).'">here</a> to refresh..';
				}
			}
		}
	}
	catch(Exception $e)
	{
		if( $e->getMessage() == "Abandoned" || $e->getMessage() == "Cancelled" )
			$DB->sql_put("COMMIT");
		else
			$DB->sql_put("ROLLBACK");

		$results = array('invalid'=>true, 'statusIcon'=>'<img src="images/icons/alert.png" alt="Error" title="Error alert" />',
			'statusText'=>'', 'notice'=>'Exception: '.$e->getMessage(), 'orders'=>array());
	}
}

header('X-JSON: ('.json_encode($results).')');

close();

?>