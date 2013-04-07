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

require_once(l_r('board/orders/order.php'));

require_once(l_r('board/orders/diplomacy.php'));
require_once(l_r('board/orders/retreats.php'));
require_once(l_r('board/orders/builds.php'));

/**
 * A class for submission/retrieval of orders; usable internally or externally
 *
 * __construct
 * => This data is required to load: [gameID, userID, memberID, turn, phase, countryID]
 * 		This must either be in the context of a GameMaster account, a loaded Game/User/userMember set, or a JSON token.
 *
 * loadOrders
 * => Current order data can then be loaded via a JSON token [tokenKey,{orderID,completeStatus,{data}}]
 * 		, if provided with the above token. Or from the DB.
 *
 * getToken / getOrders
 * Once current order data is loaded:
 * <= [gameID, ...] token can be returned
 * <= [tokenKey,{orderID,completeStatus,{data}}] can be returned for the given user.
 *
 * setOrders
 * => To alter orders once loaded this data is provided: lock,{orderID,{input}}
 * 		<= {orderID, completeStatus, error} will be returned
 *
 * @package Board
 * @subpackage Orders
 */
class OrderInterface
{
	public static function newBoard() {
		global $Game, $User, $Member;
		$OI = self::newContext($Game, $Member, $User);
		if( defined('AdminUserSwitch') && AdminUserSwitch != $User->id)
			$OI->userID=AdminUserSwitch;
		return $OI;
	}
	public static function newContext(Game $Game, userMember $Member, User $User) {
		$OI = $Game->Variant->OrderInterface($Game->id, $Game->Variant->id, $User->id, $Member->id, $Game->turn, $Game->phase, $Member->countryID,
			$Member->orderStatus, $Game->processTime+6*60*60);
		return $OI;
	}

	public static function newJSON($key, $json) {

		$inContext = (array)json_decode($json);
		$authContext = self::getContext($inContext);
		$inContext = $authContext['context'];

		if( $authContext['key'] != $key )
			throw new Exception("JSON token given is invalid");

		require_once(l_r('lib/variant.php'));
		$Variant=libVariant::loadFromVariantID($inContext['variantID']);
		libVariant::setGlobals($Variant);

		require_once(l_r('objects/basic/set.php'));
		$OI = $Variant->OrderInterface($inContext['gameID'],$inContext['variantID'],$inContext['userID'],$inContext['memberID'],
			$inContext['turn'],$inContext['phase'],$inContext['countryID'],
			new setMemberOrderStatus($inContext['orderStatus']), $inContext['tokenExpireTime'], $inContext['maxOrderID']);

		return $OI;
	}

	public $gameID;
	protected $variantID;
	protected $userID;
	protected $memberID;
	protected $turn;
	protected $phase;
	protected $countryID;
	public $orderStatus;
	protected $tokenExpireTime;
	protected $maxOrderID;

	public function __construct($gameID, $variantID, $userID, $memberID, $turn, $phase, $countryID,
		setMemberOrderStatus $orderStatus, $tokenExpireTime, $maxOrderID=false)
	{
		$this->gameID=(int)$gameID;
		$this->variantID=(int)$variantID;
		$this->userID=(int)$userID;
		$this->memberID=(int)$memberID;
		$this->turn=(int)$turn;
		$this->phase=$phase;
		$this->countryID=$countryID;
		$this->orderStatus=$orderStatus;
		$this->tokenExpireTime=$tokenExpireTime;
		$this->maxOrderID=$maxOrderID;
	}

	protected $Orders;

	public function load()
	{
		global $DB;
		
		$DB->sql_put("SELECT * FROM wD_Members WHERE gameID = ".$this->gameID." AND countryID=".$this->countryID." ".UPDATE);

		$tabl = $DB->sql_tabl("SELECT id, type, unitID, toTerrID, fromTerrID, viaConvoy
			FROM wD_Orders WHERE gameID = ".$this->gameID." AND countryID=".$this->countryID);

		$this->Orders = array();
		$maxOrderID=0;
		while ( $row = $DB->tabl_hash($tabl) )
		{
			if( $row['id'] > $maxOrderID ) $maxOrderID = $row['id'];

			$Order = userOrder::load($this->phase,$row['id'],$this->gameID, $this->countryID);

			$Order->loadFromDB($row);

			$this->Orders[] = $Order;
		}

		list($checkTurn, $checkPhase, $adminLock) = $DB->sql_row("SELECT turn, phase, adminLock FROM wD_Games WHERE id=".$this->gameID);

		if( $adminLock == 'Yes' )
		{
			list($usertype) = $DB->sql_row("SELECT type FROM wD_Users WHERE id=".$this->userID);
			if (strpos($usertype,'Admin')===false)
				throw new Exception("Game is currently locked by an admin (usually to fix some errors).");
		}
		
		if( $checkTurn != $this->turn || $checkPhase != $this->phase )
			throw new Exception(l_t("The game has moved on, you can no longer alter these orders, please refresh."));

		if( $this->maxOrderID == false ) $this->maxOrderID = $maxOrderID;
		//elseif( $this->maxOrderID < $maxOrderID )

		//if( $this->tokenExpireTime < time() ) throw new Exception("The game has moved on, you can no longer alter these orders, please refresh.");
	}

	public function set($orderUpdates)
	{
		if( $this->orderStatus->Ready ) return;

		$this->log($orderUpdates);

		$orderUpdates = json_decode($orderUpdates);

		foreach($orderUpdates as $orderUpdate)
		{
			$orderUpdate = (array)$orderUpdate;
			foreach($this->Orders as $Order)
				if( $Order->id == $orderUpdate['id'] )
					$Order->loadFromInput($orderUpdate);
		}
	}

	protected function log($logData)
	{
		$orderlogDirectory = Config::orderlogDirectory();
		if ( false === $orderlogDirectory ) return;

		require_once(l_r('objects/game.php'));
		$directory = libCache::dirID($orderlogDirectory, $this->gameID, true);

		$file = $this->countryID.'.txt';

		if ( ! ($orderLog = fopen($directory.'/'.$file, 'a')) )
			trigger_error(l_t("Couldn't open order log file."));

		if( !fwrite($orderLog, 'Time: '.gmdate("M d Y H:i:s")." (UTC+0)\n".$logData."\n\n") )
			trigger_error(l_t("Couldn't write to order log file."));

		fflush($orderLog) or trigger_error(l_t("Couldn't write to order log file."));
		fclose($orderLog);
	}

	protected $results = array('orders'=>array(),'notice'=>'','statusIcon'=>'','statusText'=>'','invalid'=>false);
	public function validate() {

		if( count($this->Orders)==0 )
			$this->orderStatus->None=true;

		$complete=true;
		foreach($this->Orders as $Order)
		{
			$Order->validate();

			$result = $Order->results();
			if( $complete && $result['status'] != 'Complete' )
				$complete=false;

			if( $result['status'] == 'Invalid' )
			{
				$complete=false;
				$this->results['invalid'] = true;
			}

			$this->results['orders'][$Order->id] = $result;
		}
		$this->orderStatus->Completed = $complete;

		return $this->results;
	}

	public function readyToggle() {
		global $Member, $DB;

		if( !$this->orderStatus->Ready )
		{
			if( !$this->orderStatus->Completed )
				$this->results['notice'] .= l_t(' Could not set to ready, orders not complete and valid.');
			else
				$this->orderStatus->Ready=true;
		}
		else
			$this->orderStatus->Ready = false;

		return $this->orderStatus->Ready;
	}

	public function writeOrders() {
		$updated=false;
		foreach($this->Orders as $Order)
			if( $Order->commit() )
				$updated=true;

		if( $updated )
			$this->orderStatus->Saved=true;
	}

	public function writeOrderStatus() {
		global $DB, $Member;

		$this->results['statusIcon']=$this->orderStatus->icon();
		$this->results['statusText']=$this->orderStatus->iconText();

		if( $this->orderStatus->updated )
		{
			if( isset($Member) && $Member instanceof Member && $Member->id == $this->memberID )
				$Member->orderStatus = $this->orderStatus;

			$DB->sql_put("UPDATE wD_Members SET orderStatus = '".$this->orderStatus."' WHERE id = ".$this->memberID);

			$newContext = $this->getContext($this);
			$this->results['newContext'] = $newContext['context'];
			$this->results['newContextKey'] = $newContext['key'];
		}
	}

	public function getResults() {
		return $this->results;
	}

	protected static $contextVars=array('gameID','userID','memberID','variantID','turn','phase','countryID','tokenExpireTime','maxOrderID');
	public static function getContext($contextOf) {

		$context=array();
		foreach($contextOf as $name=>$val)
		{
			if(!in_array($name, self::$contextVars)) continue;

			$context[$name] = $val;
		}

		if( is_array($contextOf) )
			$context['orderStatus'] = ''.$contextOf['orderStatus'];
		else
			$context['orderStatus'] = ''.$contextOf->orderStatus;

		$json=json_encode($context);

		return array('context'=>$context, 'json'=>$json, 'key'=>md5(Config::$jsonSecret.$json).sha1(Config::$jsonSecret.$json));
	}

	protected function jsContextVars() {
		$context = self::getContext($this);
		libHTML::$footerScript[] = '
	context='.$context['json'].';
	contextKey="'.$context['key'].'";
	ordersData = '.json_encode($this->Orders).';
	';
	}

	protected function jsLoadBoard() {
		libHTML::$footerIncludes[] = l_j('board/model_vDip.js');
		libHTML::$footerIncludes[] = l_j('board/load.js');
		libHTML::$footerIncludes[] = l_j('orders/order.js');
		if ($this->phase == 'Diplomacy')
			libHTML::$footerIncludes[] = l_j('orders/phaseDiplomacy_vDip.js');
		else
			libHTML::$footerIncludes[] = l_j('orders/phase'.$this->phase.'.js');
		libHTML::$footerIncludes[] = l_s('../'.libVariant::$Variant->territoriesJSONFile());

		foreach(array('loadTerritories','loadBoardTurnData','loadModel','loadBoard','loadOrdersModel','loadOrdersForm','loadOrdersPhase') as $jf)
			libHTML::$footerScript[] = l_jf($jf).'();';
	}

	protected function jsInitForm() {
		libHTML::$footerIncludes[] = l_j('orders/form.js');
		libHTML::$footerScript[] = l_jf('OrdersHTML.formInit').'(context, contextKey);';
	}

	protected function jsLiveBoardData() {
		$jsonBoardDataFile=Game::mapFilename($this->gameID, ($this->phase=='Diplomacy'?$this->turn-1:$this->turn), 'json');

		if( !file_exists($jsonBoardDataFile) )
			$jsonBoardDataFile='map.php?gameID='.$this->gameID.'&turn='.$this->turn.'&phase='.$this->phase.'&mapType=json'.(defined('DATC')?'&DATC=1':'').'&nocache='.rand(0,1000);
		else
			$jsonBoardDataFile.='?phase='.$this->phase.'&nocache='.rand(0,10000);

		return '<script type="text/javascript" src="'.STATICSRV.$jsonBoardDataFile.'"></script>';
	}

	public function jsHTML() {

		$this->jsContextVars();
		$this->jsLoadBoard();
		$this->jsInitForm();
		return $this->jsLiveBoardData();
	}

	public function html()
	{
		//method="post" action="board.php?gameID='.$this->gameID.'#orders"
		$html = $this->jsHTML();

		$html .= '
	<form id="orderFormElement" onsubmit="return false;">
		<a name="orders"></a><table class="orders">';

		$alternate = false;
		foreach($this->Orders as $Order)
		{
			$alternate = ! $alternate;
			$html .= '<tr class="barAlt'.($alternate ? '1' : '2').'">
				<td class="uniticon"><span id="orderID'.$Order->id.'UnitIconArea"></span></td>
				<td class="order"><div id="orderID'.$Order->id.'">'.l_t('Loading order').'...</div></td>
				</tr>';
		}

		$html .= "</table>".'
		<div style="text-align:center;"><span id="ordersNoticeArea'.$this->memberID.'"></span>
			<input id="UpdateButton'.$this->memberID.'" type="Submit" class="form-submit" name="'.
				l_t('Update').'" value="'.l_t('Save').'" disabled />
			<input id="FinalizeButton'.$this->memberID.'" type="Submit" class="form-submit" name="'.
				l_t($this->orderStatus->Ready?'Not ready':'Ready').'" value="'.l_t($this->orderStatus->Ready?'Not ready':'Ready').'" disabled />
		</div>
	</form>';

		return $html;
	}
}

?>