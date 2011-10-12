<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class NewUnitNames_OrderArchiv extends OrderArchiv
{
	public function OutputOrders()
	{
		$ret=parent::OutputOrders();
		$ret = str_replace("fleet","knight",$ret);
		$ret = str_replace("army","man-at-arms",$ret);
		return $ret;
	}

}

class NeutralUnits_OrderArchiv extends NewUnitNames_OrderArchiv
{	
	public function __construct()
	{
		parent::__construct();
		$this->countryIDToName[]='Neutral units';
	}
}

class Germany1648Variant_OrderArchiv extends NeutralUnits_OrderArchiv {}
