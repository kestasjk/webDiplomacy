<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class BuildAnywhere_OrderInterface extends OrderInterface {
 
	protected function jsLoadBoard() {
		parent::jsLoadBoard();
 
		global $Variant;
		if( $this->phase=='Builds' )
		{
			libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/buildanywhere.js';
			foreach(libHTML::$footerScript as $index=>$script)
				libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();SupplyCentersCorrect();', $script);
		}
	}
}

class EnlightenmentVariant_OrderInterface extends BuildAnywhere_OrderInterface {}
