<?php

class BuildAnywhereVariant_OrderInterface extends OrderInterface {

	/**
	 * Call the parent constructor transparently to keep things working
	 */
	public function __construct($gameID, $variantID, $userID, $memberID, $turn, $phase, $countryID,
		setMemberOrderStatus $orderStatus, $tokenExpireTime, $maxOrderID=false,$isSandboxMode=false)
	{
		parent::__construct($gameID, $variantID, $userID, $memberID, $turn, $phase, $countryID,
			$orderStatus, $tokenExpireTime, $maxOrderID,$isSandboxMode);
	}

	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		if( $this->phase=='Builds' )
		{
			// Expand the allowed SupplyCenters array to include non-home SCs.
			libHTML::$footerIncludes[] = l_jf('../variants/BuildAnywhere/resources/supplycenterscorrect.js');
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, l_jf('loadBoard').'();') )
					libHTML::$footerScript[$index]=str_replace(l_jf('loadBoard').'();',l_jf('loadBoard').'();'.l_jf('SupplyCentersCorrect').'();', $script);
		}
	}


}