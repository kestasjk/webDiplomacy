<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class OctopusVariant_OrderInterface extends OrderInterface {

	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		if ($this->phase=='Diplomacy') {
			// Fix an error in the webdip convoy-order generation
			libHTML::$footerIncludes[] = '../variants/DutchRevolt/resources/convoydisplayfix.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadOrdersPhase();'))
					libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase(); ConvoyDisplayFix();', $script);
		}		
	}

}

?>