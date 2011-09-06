<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class WhoControlsAmericaVariant_OrderInterface extends OrderInterface {

	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		// custom unit-icons in javascript-orderinterface
		libHTML::$footerIncludes[] = '../variants/WhoControlsAmerica/resources/iconscorrect.js';
		// custom unit-names in javascript-orderinterface
		libHTML::$footerIncludes[] = '../variants/WhoControlsAmerica/resources/unitnames.js';

		foreach(libHTML::$footerScript as $index=>$script)
			if(strpos($script, 'loadOrdersModel();') )
				libHTML::$footerScript[$index]=str_replace('loadOrdersModel();','loadOrdersModel(); IconsCorrect();', $script);	
		foreach(libHTML::$footerScript as $index=>$script)
			if(strpos($script, 'loadOrdersPhase();') )
				libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase(); NewUnitNames();', $script);

		// build anywhere
		if($this->phase=='Builds') {
			// Expand the allowed SupplyCenters array to include non-home SCs.
			libHTML::$footerIncludes[] = '../variants/WhoControlsAmerica/resources/supplycenterscorrect.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadBoard();') )
					libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();SupplyCentersCorrect();', $script);
		}


	}


}

?>