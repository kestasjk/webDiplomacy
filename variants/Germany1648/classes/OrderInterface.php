<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Germany1648Variant_OrderInterface extends OrderInterface {

	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		if( $this->phase=='Builds' )
		{
			// Expand the allowed SupplyCenters array to include non-home SCs.
			libHTML::$footerIncludes[] = '../variants/Germany1648/resources/supplycenterscorrect.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadBoard();') )
					libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();SupplyCentersCorrect();', $script);
		}
		
		// Unit-Icons in javascript-code
		libHTML::$footerIncludes[] = '../variants/Germany1648/resources/iconscorrect.js';
		foreach(libHTML::$footerScript as $index=>$script)
			if(strpos($script, 'loadOrdersModel();') )
				libHTML::$footerScript[$index]=str_replace('loadOrdersModel();','loadOrdersModel();IconsCorrect();', $script);

		// New Unit-names in javascript-code
		libHTML::$footerIncludes[] = '../variants/Germany1648/resources/unitnames.js';
		foreach(libHTML::$footerScript as $index=>$script)
			if(strpos($script, 'loadOrdersModel();') )
				libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase(); NewUnitNames();', $script);
				
	}


}