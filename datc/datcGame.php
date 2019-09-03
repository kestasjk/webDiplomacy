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
 * This is the DATC test game class, which sets the game up, gets it ready for the orders to be
 * entered via OrderInterface, then checks the orders and adjudicates and checks the game.
 *
 * Needs to be able to access many processGame protected class fields, and performs similar functions,
 * so it extends it
 *
 * @package DATC
 */
class datcGame extends processGame
{
	/**
	 * The DATC test ID
	 * @var int
	 */
	private $testID;

	/**
	 * Create a clean new game to apply the test to
	 *
	 * @var string $testID The test ID
	 */
	function __construct($testID)
	{
		global $DB, $Game, $Variant;

		/*
		 * Create a clean new game to apply the test to
		 */
		$this->testID = $testID;
		list($id) = $DB->sql_row("SELECT id FROM wD_Games WHERE name='DATC-Adjudicator-Test'");
		if ( $id )
		{
			$DB->sql_put("UPDATE wD_Games SET phase = 'Diplomacy', turn = ".$testID.", gameOver = 'No' WHERE id = ".$id);
		}
		else
		{
			$Game = processGame::create(1, 'DATC-Adjudicator-Test', '', 5,'Winner-takes-all', 30,30,'No','Regular', 'Normal', 'draw-votes-hidden', 0, 'Members');
			$id = $Game->id;
			$DB->sql_put("UPDATE wD_Games SET phase = 'Diplomacy', turn = ".$testID." WHERE id = ".$id);
		}

		if( !isset($_REQUEST['DATCResults']) )
		{
			self::wipe($id);

			$countries = array(); for($i=1; $i<=count($Variant->countries); $i++) $countries[]=$i;

			$DB->sql_put("INSERT INTO wD_Members (gameID, userID, countryID, bet, timeLoggedIn)
					VALUES ".Database::packArray("(".$id.", 2, ", $countries,", 5, ".time().")",",")
					);
		}

		parent::__construct($id);

		$GLOBALS['Game'] = $this;
		$Game = $this;
	}

	/**
	 * Wipe an existing game of data in external tables
	 * @param int $id
	 */
	private static function wipe($id)
	{
		global $DB;

		self::wipeCache($id);

		$tables = array('Members','Orders', 'TerrStatus',
			'TerrStatusArchive', 'Units', 'MovesArchive', 'GameMessages');

		foreach($tables as $table)
			$DB->sql_put("DELETE FROM wD_".$table." WHERE gameID = ".$id);
	}

	function terrNameByID($terrID) {
		global $DB;
		static $cache;

		if( !isset($cache) ) {
			$cache=array();

			$tabl=$DB->sql_tabl("SELECT id, name FROM wD_Territories WHERE mapID=".$this->Variant->mapID);
			while(list($id,$name)=$DB->tabl_row($tabl))
				$cache[$id]=$name;
		}

		return $cache[$terrID];
	}

	/**
	 * Output the test orders and success criteria
	 */
	function outputTest()
	{
		global $DB;

		$tabl = $DB->sql_tabl("SELECT * FROM wD_DATCOrders WHERE testID = ".$this->testID);

		$first=true;
		$alternate=2;
		print '<table class="credits">';
		while ( $hash = $DB->tabl_hash($tabl) )
		{
			if($first){
				print '<tr class="replyalternate'.$alternate.'"><th>'.implode('</th><th>', array_keys($hash)).'</th></tr>';
				$alternate = 3-$alternate;
				$first=false;
			}

			$hash['countryID'] .= ' ('.l_t($this->Variant->countries[$hash['countryID']-1]).')';

			if( $hash['terrID'] )
				$hash['terrID'] .= ' ('.l_t($this->terrNameByID($hash['terrID'])).')';

			if( $hash['toTerrID'] )
				$hash['toTerrID'] .= ' ('.l_t($this->terrNameByID($hash['toTerrID'])).')';

			if( $hash['fromTerrID'] )
				$hash['fromTerrID'] .= ' ('.l_t($this->terrNameByID($hash['fromTerrID'])).')';

			foreach(array('unitType','viaConvoy','criteria','legal') as $localizeColumn)
				$hash[$localizeColumn] = l_t($hash[$localizeColumn]);
			
			print '<tr class="replyalternate'.$alternate.'"><td>'.implode('</td><td>', $hash).'</td></tr>';

			$alternate = 3-$alternate;
		}
		print '</table>';
	}

	/**
	 * Initialize the test; load units, load map, create orders, load orders
	 */
	public function initialize()
	{
		self::wipeCache($this->id, $this->testID-1);

		$this->loadUnits();

		// Update the terrstatus table based on the new units
		$this->updateOwners();

		// Create empty orders
		$PO = $this->Variant->processOrderDiplomacy();
		$PO->create();
	}

	/**
	 * Load units into the game from the DATC tables. The only data that's directly loaded from
	 * the DATC tables into the actual tables
	 */
	private function loadUnits()
	{
		global $DB;

		$DB->sql_put(
			"INSERT INTO wD_Units ( gameID, countryID, type, terrID )
			SELECT ".$this->id." as gameID, countryID, unitType, terrID
			FROM wD_DATCOrders
			WHERE testID=".$this->testID);
	}

	private function loadOI($memberID, $countryID){
		global $DB;

		$con=array();
		$con['gameID']=$this->id;
		$con['userID']=2;
		$con['variantID']=1;
		$con['memberID']=$memberID;
		$con['turn']=$this->turn;
		$con['phase']=$this->phase;
		$con['countryID']=$countryID;
		$con['orderStatus']='Saved';
		$con['tokenExpireTime']=time()+60*60*6;
		list($con['maxOrderID'])=$DB->sql_row("SELECT MAX(id)+100 FROM wD_Orders");

		$con=OrderInterface::getContext($con);
		return OrderInterface::newJSON($con['key'], $con['json']);
	}

	/**
	 * Submit the orders via OrderInterface
	 */
	public function submitOrders()
	{
		global $DB;

		// Create DATCResults to save ajax.php results to, and modify OrdersHTML to save results to it
		libHTML::$footerScript[] = 'DATCResults=new Hash();';

		// For each countryID with orders output the blank orders, then add the extra code to enter and submit those orders via JS.
		$tabl=$DB->sql_tabl("SELECT m.countryID, m.id FROM wD_Members m
			INNER JOIN wD_Orders o ON ( o.gameID = m.gameID AND o.countryID = m.countryID )
			WHERE m.gameID = ".$this->id." GROUP BY m.countryID, m.id ORDER BY m.countryID, m.id");
		while( list($countryID, $memberID) = $DB->tabl_row($tabl) )
		{
			libHTML::$footerScript[] = '(function() {';

			$OI = $this->loadOI($memberID, $countryID);
			$OI->load();

			print '<p><strong>'.l_t($this->Variant->countries[$countryID-1]).'</strong></p>';

			print '<div id="orderDiv'.$memberID.'">'.$OI->html().'</div>';

			print $this->submitOrdersForCountry($countryID);

			print '<div class="hr"></div>';

			libHTML::$footerScript[] = '})();';
		}

		if( !isset($_REQUEST['verySlowStep']) && !isset($_REQUEST['slowStep']) )
		{
			libHTML::$footerScript[] = '
				document.location.href="datc.php?'.( isset($_REQUEST['batchTest']) ? 'batchTest=on&':'').'DATCResults="+DATCResults.toJSON();
			';
		}
	}

	private function submitOrdersForCountry($countryID)
	{
		global $DB;

		libHTML::$footerScript[] = '
		var oHash = OrdersHTML.OrdersIndex;
		';

		$tabl = $DB->sql_tabl("SELECT o.id, d.moveType as type, d.toTerrID, d.fromTerrID, d.viaConvoy
			FROM wD_DATCOrders d
				INNER JOIN wD_Units u ON ( u.terrID = d.terrID )
				INNER JOIN wD_Orders o ON ( u.id = o.unitID )
			WHERE d.testID = ".$this->testID." AND o.countryID=".$countryID." AND o.gameID = ".$this->id);
		while ( $hash = $DB->tabl_hash($tabl) )
		{
			$orderID = $hash['id']; unset($hash['id']);

			foreach($hash as $name=>$value)
			{
				libHTML::$footerScript[] = '
			oHash.get('.$orderID.').inputValue(\''.$name.'\', \''.$value.'\');
			oHash.get('.$orderID.').reHTML(\''.$name.'\');
			';
			}
		}

		// OrdersHTML has to be altered in the same way repeatedly because a new one is loaded  for each new set of orders.
		libHTML::$footerScript[] = '
		OrdersHTML.onSemiSuccess = OrdersHTML.onSuccess;
		OrdersHTML.asynchronous=false; // Wait for each one to finish before proceeding to the next, otherwise contexts will overwrite.
		OrdersHTML.onFailure=function(response) {
			document.write(response.responseText);
		};
		OrdersHTML.onSuccess=function(response) {
			if( null == response.headerJSON )
			{
				OrdersHTML.onFailure(response);
			}
			else
			{
				$H(response.headerJSON.orders).each(function(p) {
					DATCResults.set(p[0],p[1].status);
				},this);
				this.onSemiSuccess(response);
			}
		};
		'.( isset($_REQUEST['verySlowStep']) ? '':'OrdersHTML.onSave([ ]);');

		return '';
	}

	/**
	 * Check whether any orders should have failed but didn't, or did fail but shouldn't have.
	 * Dies if it detects a failure
	 */
	function checkInvalidOrders()
	{
		global $DB;

		$tabl = $DB->sql_tabl("SELECT o.id, d.legal, d.terrID,
			o.type as oType, o.toTerrID as oToTerrID, o.fromTerrID as oFromTerrID, o.viaConvoy as oViaConvoy,
			d.moveType as dType, d.toTerrID as dToTerrID, d.fromTerrID as dFromTerrID, d.viaConvoy as dViaConvoy
			FROM wD_DATCOrders d
				INNER JOIN wD_Units u ON ( u.terrID = d.terrID AND u.gameID = ".$this->id." )
				INNER JOIN wD_Orders o ON ( u.id = o.unitID )
			WHERE d.testID = ".$this->testID);
		$failed = false;
		$compareParams=array('Type','ToTerrID','FromTerrID','ViaConvoy');

		$DATCResults=$_REQUEST['DATCResults'];
		$arr=array();
		foreach($DATCResults as $n=>$v)
		{
			$arr[intval($n)]=$v;
		}
		$_REQUEST['DATCResults']=$arr;

		/*
		 * Check all the different ways a submitted order can be different than expected:
		 * - Expected order not found,
		 * - expected legal order found to be incomplete,
		 * - expected legal order found complete but incorrect,
		 * - order found complete and matching expected illegal order.
		 */
		while ( $hash = $DB->tabl_hash($tabl) )
		{
			if( !isset($_REQUEST['DATCResults'][$hash['id']]) )
			{
				$failed = true;

				print l_t('Failed on the following order, failed criteria = legal=%s result not given '.
						':<br />%s<br /><br />',$hash['legal'],nl2br(print_r($hash,true)));
			}
			elseif( $_REQUEST['DATCResults'][$hash['id']] != 'Complete' )
			{
				if( $hash['legal']=='Yes' )
				{
					$failed = true;

					print l_t('Failed on the following order, failed criteria = legal=%s result was not complete'.
						':<br />%s<br /><br />',$hash['legal'],nl2br(print_r($hash,true)));
				}
			}
			elseif( $_REQUEST['DATCResults'][$hash['id']] == 'Complete' )
			{
				$invalid=false;
				foreach($compareParams as $param)
				{
					if( isset($hash['o'.$param]) && $hash['o'.$param] != $hash['d'.$param] )
					{
						$invalid = true;
						break;
					}
				}

				if( (!$invalid && $hash['legal']!='Yes') || ( $invalid && $hash['legal']=='Yes' ))
				{
					$failed = true;
					
					print l_t('Failed on the following order, failed criteria = legal=%s'.
						($invalid?' given order doesnt match received':'result was complete ').
						':<br />%s<br /><br />',$hash['legal'],nl2br(print_r($hash,true)));
				}
			}
		}

		if ( $failed )
			throw new Exception(l_t('Failed results test. <a href="datc.php">Re-run</a>').'</div>');
		else
			print l_t('Passed invalid orders test').'<br /><br />';
	}

	/**
	 * Check the Moves table to check that the right units Moved/Held/Got Dislodged
	 * Dies if there is a difference between what is there and what should be there
	 */
	function checkResults()
	{
		global $DB;

		/*
		 * Now the moves table can be used to determine whether the moves'
		 * Success/Hold/Dislodged status are as they should be
		 */
		$tabl = $DB->sql_tabl(
			"SELECT d.*, m.* FROM wD_DATCOrders d INNER JOIN wD_Moves m ON (
				".$this->Variant->deCoastCompare('m.terrID','d.terrID')."
				AND
				(
					/* We needed to move in, but have failed to */
					( d.moveType = 'Move' AND d.criteria = 'Success' AND m.success = 'No' )
					OR
					/* We needed to hold, but were dislodged */
					( d.criteria = 'Hold' AND m.dislodged = 'Yes' )
					OR
					/* We needed to get dislodged, but held */
					( d.criteria = 'Dislodged' AND m.dislodged = 'No' )
				)
			)
			WHERE d.testID = ".$this->testID." AND m.gameID = ".$GLOBALS['GAMEID']);
		$failed = false;
		while ( $hash = $DB->tabl_hash($tabl) )
		{
			$failed = true;
				print l_t('Failed on the following order, failed criteria =%s '.
						':<br />%s<br /><br />',$hash['criteria'],nl2br(print_r($hash,true)));
		}

		if ( $failed )
		{
			// Even if this fails we still want to draw a map to visualize it, so don't die
			throw new Exception('<h4>'.l_t('Failed results test. <a href="datc.php">Re-run</a>').'</h4>');
		}
		else
		{
			print l_t('Passed results test').'<br /><br />';
		}
	}

	/**
	 * Prepare the database for a map to be drawn; archive the moves, save the standoffs, update owners and archive them,
	 * move to a turn which will display what just happened
	 */
	public function mapPrepare(array $standOffTerrs)
	{
		global $DB;

		$PO = $this->Variant->processOrderDiplomacy();
		$PO->archiveMoves();

		$PO->apply($standOffTerrs);

		// Update the terrstatus table based on the new units
		$tmp=$this->turn;
		$this->turn=1; // So supply centers can be taken
		$this->updateOwners();
		$this->turn=$tmp;

		$this->archiveTerrStatus();

		// Don't let the map get confused
		$DB->sql_put("UPDATE wD_Games SET phase='Retreats' WHERE id = ".$this->id);
	}
}

?>
