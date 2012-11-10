<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class NeutralUnits_OrderArchiv extends OrderArchiv
{	
	public function __construct()
	{
		parent::__construct();
		$this->countryIDToName[]='Neutral units';
	}
}

class AfricaVariant_OrderArchiv extends NeutralUnits_OrderArchiv {}
