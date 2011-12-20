<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class NewUnitNames_OrderArchiv extends OrderArchiv
{
	public function OutputOrders()
	{
		$ret=parent::OutputOrders();
		$ret = str_replace("fleet","Arrow Rat",$ret);
		$ret = str_replace("army","Spear Rat",$ret);
		return $ret;
	}

}

class Fog_OrderArchiv extends NewUnitNames_OrderArchiv
{
	// Hide the OrderLog
	public function outputOrderLogs(array $orders)
	{
		global $Game, $User;
		if ($Game->phase == 'Finished') return parent::outputOrderLogs($orders);
		if (($User->type['Moderator']) && (! $Game->Members->isJoined())) return parent::outputOrderLogs($orders);
		return;
	}

}

class RatWarsVariant_OrderArchiv extends Fog_OrderArchiv {}
