<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class RinascimentoVariant_OrderInterface extends OrderInterface {

	protected function jsLoadBoard() {
		global $DB, $Game;
		parent::jsLoadBoard();

		// Expand the allowed SupplyCenters array to include non-home SCs.
		if( $this->phase=='Builds' ) {
			libHTML::$footerIncludes[] = '../variants/Rinascimento/resources/supplycenterscorrect.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadBoard();') )
					libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();SupplyCentersCorrect();', $script);
		}
		
		// The Starting unit in Benevatto can't move...
		if( $this->phase=='Diplomacy' && $this->countryID==10) {
			list($nomove)=$DB->sql_row("SELECT text FROM wD_Notices WHERE toUserID=3 AND timeSent=0 AND fromID=".$Game->id);
			libHTML::$footerIncludes[] = '../variants/Rinascimento/resources/nomove.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadOrdersPhase();') )
					libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase();NoMove('.$nomove.');', $script);

		}

		// Unit-Icons in javascript-code		
		libHTML::$footerIncludes[] = '../variants/Rinascimento/resources/iconscorrect.js';
		foreach(libHTML::$footerScript as $index=>$script)
			if(strpos($script, 'loadOrdersModel();'))
				libHTML::$footerScript[$index]=str_replace('loadOrdersModel();','loadOrdersModel();IconsCorrect();', $script);
	}

}

?>