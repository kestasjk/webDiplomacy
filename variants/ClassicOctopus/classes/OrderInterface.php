<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicOctopusVariant_OrderInterface extends OrderInterface {

	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		if ($this->phase=='Diplomacy') {
			// Fix an error in the webdip convoy-order generation
			libHTML::$footerIncludes[] = '../variants/ClassicOctopus/resources/convoydisplayfix.js';
			foreach(libHTML::$footerScript as $index=>$script)
				libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase(); ConvoyDisplayFix();', $script);
		}		
	}

}

?>