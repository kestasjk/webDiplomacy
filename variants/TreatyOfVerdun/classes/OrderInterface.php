<?php

defined('IN_CODE') or die('This script can not be run by itself.');

// Medival unit icons in javascript-code
class CustomIcons_OrderInterface extends OrderInterface
{
	protected function jsLoadBoard() {
		parent::jsLoadBoard();
		
		libHTML::$footerIncludes[] = '../variants/TreatyOfVerdun/resources/iconscorrect.js';
		foreach(libHTML::$footerScript as $index=>$script)
			libHTML::$footerScript[$index]=str_replace('loadOrdersModel();','loadOrdersModel();IconsCorrect();', $script);
	}
}

// Build anywhere:
class BuildAnywhere_OrderInterface extends CustomIcons_OrderInterface {

	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		if( $this->phase=='Builds' )
		{
			libHTML::$footerIncludes[] = '../variants/TreatyOfVerdun/resources/buildanywhere.js';
			foreach(libHTML::$footerScript as $index=>$script)
				libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();SupplyCentersCorrect();', $script);
		}
	}
}

class TreatyOfVerdunVariant_OrderInterface extends BuildAnywhere_OrderInterface {}
