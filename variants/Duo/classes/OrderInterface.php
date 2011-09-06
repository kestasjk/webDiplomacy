<?php

class DuoVariant_OrderInterface extends OrderInterface {

	/**
	 * Call the parent constructor transparently to keep things working
	 */
	public function __construct($gameID, $variantID, $userID, $memberID, $turn, $phase, $countryID,
		setMemberOrderStatus $orderStatus, $tokenExpireTime, $maxOrderID=false)
	{
		parent::__construct($gameID, $variantID, $userID, $memberID, $turn, $phase, $countryID,
			$orderStatus, $tokenExpireTime, $maxOrderID);
	}

/*	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		if( $this->phase=='Diplomacy' )
		{
			// Expand the allowed SupplyCenters array to include non-home SCs.
			libHTML::$footerIncludes[] = '../variants/Duo/resources/transform.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadOrdersPhase();') )
					libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase();loadTransform();', $script);
		}
	}
*/

}