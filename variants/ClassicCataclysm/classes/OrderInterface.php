<?php

defined('IN_CODE') or die('This script can not be run by itself.');

// Build anywhere:
class ClassicCataclysmVariant_OrderInterface extends OrderInterface
{
	protected function jsLoadBoard()
	{
		global $Variant;
		parent::jsLoadBoard();
		
		$seaTerrs='Array("'.implode($Variant->seaTerrs, '","').'")';

		libHTML::$footerIncludes[] = '../variants/ClassicCataclysm/resources/units_and_icons_correct_V1.01.js';
		foreach(libHTML::$footerScript as $index=>$script)
			if(strpos($script, 'loadOrdersPhase();') )
				libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase(); NewUnitNames('.$seaTerrs.');', $script);			
		foreach(libHTML::$footerScript as $index=>$script)
			if(strpos($script, 'loadOrdersModel();') )
				libHTML::$footerScript[$index]=str_replace('loadOrdersModel();','loadOrdersModel();IconsCorrect('.$seaTerrs.');', $script);
	}
}
