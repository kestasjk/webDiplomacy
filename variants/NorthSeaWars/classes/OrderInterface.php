<?php

defined('IN_CODE') or die('This script can not be run by itself.');

// Custom Unit-Icons in javascript-code
class CustomIcons_OrderInterface extends OrderInterface
{
	protected function jsLoadBoard()
	{
		global $Variant;
		parent::jsLoadBoard();
		if( $this->phase!='Builds' ) {
			libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/iconscorrect.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadOrdersPhase();') )
					libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase();IconsCorrect("'.$Variant->name.'");', $script);
		}
	}
}

class OneWay_OrderInterface extends CustomIcons_OrderInterface
{
	protected function jsLoadBoard()
	{
		parent::jsLoadBoard();

		if( $this->phase=='Diplomacy' )
		{
			libHTML::$footerIncludes[] = '../variants/NorthSeaWars/resources/oneway.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadOrdersPhase();') )
					libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase();OneWay_loadOrdersPhase();', $script);
		}
	}
}

class NorthSeaWarsVariant_OrderInterface extends OneWay_OrderInterface {}
