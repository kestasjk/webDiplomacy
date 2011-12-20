<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Fog_userOrderDiplomacy extends userOrderDiplomacy
{
	// Don't check the JavaScript-Orders for support-moves and convoys
	protected function supportMoveFromTerrCheck()
	{
		return true;
	}

	protected function checkConvoyPath($startCoastTerrID, $endCoastTerrID, $mustContainTerrID=false, $mustNotContainTerrID=false)
	{
		return true;
	}
	
}

class RatWarsVariant_userOrderDiplomacy extends Fog_userOrderDiplomacy {}

?>