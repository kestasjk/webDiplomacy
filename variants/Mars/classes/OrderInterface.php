<?php
/*
	Copyright (C) 2012 Kaner406 / Oliver Auth

	This file is part of the Mars variant for webDiplomacy

	The Mars variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Mars variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');


class CoastConvoy_OrderInterface extends OrderInterface
{
	protected function jsLoadBoard()
	{
		global $Variant;
		parent::jsLoadBoard();

		if( $this->phase=='Diplomacy' )
		{
			$convoyCoastsJS='Array("'.implode($Variant->convoyCoasts, '","').'")';
			
			libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/coastConvoy_V1.3.js';
			foreach(libHTML::$footerScript as $index=>$script)
				libHTML::$footerScript[$index]=str_replace('loadModel();','loadModel();coastConvoy_loadModel('.$convoyCoastsJS.');', $script);
			foreach(libHTML::$footerScript as $index=>$script)
				libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();coastConvoy_loadBoard('.$convoyCoastsJS.');', $script);
			foreach(libHTML::$footerScript as $index=>$script)
				libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase();coastConvoy_loadOrdersPhase('.$convoyCoastsJS.');', $script);
		}
	}
}

// Transform
class Transform_OrderInterface extends CoastConvoy_OrderInterface
{
	protected function jsLoadBoard()
	{
		global $Variant;
		parent::jsLoadBoard();
		if( $this->phase=='Diplomacy' )
		{
			libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/transform.js';
			foreach(libHTML::$footerScript as $index=>$script)
					libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase();loadTransform();', $script);
		}
	}
}

// Build anywhere:
class BuildAnywhere_OrderInterface extends Transform_OrderInterface {

	protected function jsLoadBoard() {
		global $Variant;
		parent::jsLoadBoard();
		if( $this->phase=='Builds' )
		{
			libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/buildanywhere.js';
			foreach(libHTML::$footerScript as $index=>$script)
				libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();SupplyCentersCorrect();', $script);
		}
	}
}

class MarsVariant_OrderInterface extends BuildAnywhere_OrderInterface {}
