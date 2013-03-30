<?php
/*
	Copyright (C) 2012 Gavin Atkinson / Oliver Auth

	This file is part of the Pirates variant for webDiplomacy

	The Pirates variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Pirates variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

// Custom Unit-Icons in javascript-code
class Hurricane_OrderInterface extends OrderInterface
{
	protected function jsLoadBoard()
	{
		global $Variant;
		parent::jsLoadBoard();
		if( $this->phase!='Builds' ) {
			libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/removeHurrican.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadBoardTurnData();') )
					libHTML::$footerScript[$index]=str_replace('loadBoardTurnData();','loadBoardTurnData();
					removeHurrican();', $script);
		}
	}
}
// Custom Unit-Icons in javascript-code
class CustomIcons_OrderInterface extends Hurricane_OrderInterface
{
	protected function jsLoadBoard()
	{
		global $Variant;
		parent::jsLoadBoard();
		if( $this->phase!='Builds' ) {
			libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/iconscorrect.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadOrdersPhase();') )
					libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase();IconsCorrect("'.$Variant->name.'");', $script);
		}
	}
}

// New Unit-names in javascript-code
class CustomIconNames_OrderInterface extends CustomIcons_OrderInterface
{
	protected function jsLoadBoard() {
		global $Variant;
		parent::jsLoadBoard();

		libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/unitnames.js';
		foreach(libHTML::$footerScript as $index=>$script)
			if(strpos($script, 'loadOrdersModel();') )
				libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase(); NewUnitNames();', $script);			
	}
}

// Build anywhere:
class BuildAnywhere_OrderInterface extends CustomIconNames_OrderInterface {

	protected function jsLoadBoard() {
		global $Variant;
		parent::jsLoadBoard();

		if( $this->phase=='Builds' )
		{
			if($this->turn == 0 && $this->countryID > 7)
				libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/initialbuild.js';
			else
				libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/buildanywhere.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadBoard();') )
					libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();SupplyCentersCorrect();', $script);
		}
	}
}

// Transform
class Transform_OrderInterface extends BuildAnywhere_OrderInterface
{
	// Allow Transform for 102=Trashure Island, 69=Old Spanish Armory, 80=Rum Distillery, 52=Mayan Gold
	protected function jsLoadBoard()
	{
		global $Variant;
		parent::jsLoadBoard();

		if( $this->phase=='Diplomacy' )
		{
			libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/transform.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadOrdersPhase();') )
					libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase();loadTransform(Array("7","102","69","80","52"));', $script);
		}
	}
}

class PiratesVariant_OrderInterface extends Transform_OrderInterface {}
