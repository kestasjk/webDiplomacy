<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class DutchRevoltVariant_OrderInterface extends OrderInterface {

	public function __construct($gameID, $variantID, $userID, $memberID, $turn, $phase, $countryID,
		setMemberOrderStatus $orderStatus, $tokenExpireTime, $maxOrderID=false)
	{
		parent::__construct($gameID, $variantID, $userID, $memberID, $turn, $phase, $countryID,
			$orderStatus, $tokenExpireTime, $maxOrderID);
	}

	// Save the turn number in the global variant-data, so I can acess it from userOrderBuilds
	public function load() {
		parent::load();
		$GLOBALS['Variants'][$this->variantID]->turn = $this->turn;
	}
	
	
	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		if( ($this->phase=='Builds') && ($this->turn==0) && ($this->countryID == 1) ) {
			// Change the build-territories in the first Build phase:
			libHTML::$footerIncludes[] = '../variants/DutchRevolt/resources/englandcustomstart.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadOrdersModel();') )
					libHTML::$footerScript[$index]=str_replace('loadOrdersModel();','loadOrdersModel();CustomBuild(Array("1","4","5"));', $script);
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadOrdersPhase();') )
					libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase(); EnglandStartSC();', $script);

		}
		if( ($this->phase=='Builds') && ($this->countryID == 3) ) {
			// Only Fleets for Spain in the Spain territory
			libHTML::$footerIncludes[] = '../variants/DutchRevolt/resources/onlyfleets.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadOrdersPhase();'))
					libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase(); OnlyFleets(Array("9"));', $script);
		}
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