<?php

// Build anywhere
class BuildAnywhere_OrderInterface extends OrderInterface {

	protected function jsLoadBoard()
	{
		global $Variant;
		parent::jsLoadBoard();

		if( $this->phase=='Builds' )
		{
			libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/buildanywhere.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadBoard();') )
					libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();SupplyCentersCorrect();', $script);
		}
	}
}

// Coast convoy
class CoastConvoy_OrderInterface extends BuildAnywhere_OrderInterface
{
	protected function jsLoadBoard()
	{
		global $Variant;
		parent::jsLoadBoard();

		if( $this->phase=='Diplomacy' )
		{
			$convoyCoastsJS='Array("'.implode($Variant->convoyCoasts, '","').'")';
			
			libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/coastConvoy.js';
			foreach(libHTML::$footerScript as $index=>$script)
				libHTML::$footerScript[$index]=str_replace('loadModel();','loadModel();coastConvoy_loadModel('.$convoyCoastsJS.');', $script);
			foreach(libHTML::$footerScript as $index=>$script)
				libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();coastConvoy_loadBoard('.$convoyCoastsJS.');', $script);
			foreach(libHTML::$footerScript as $index=>$script)
				libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase();coastConvoy_loadOrdersPhase('.$convoyCoastsJS.');', $script);
		}
	}
}

class AfricaVariant_OrderInterface extends CoastConvoy_OrderInterface {}
