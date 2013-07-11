<?php
/*
	Copyright (C) 2011 Emmanuele Ravaioli / Oliver Auth

	This file is part of the Rinascimento variant for webDiplomacy

	The Rinascimento variant for webDiplomacy" is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either 
	version 3 of the License, or (at your option) any later version.

	The Rinascimento variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

// Expand the allowed SupplyCenters array to include non-home SCs.
class BuildAnywhere_OrderInterface extends OrderInterface
{
	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		if( $this->phase=='Builds' ) {
			libHTML::$footerIncludes[] = '../variants/Rinascimento/resources/supplycenterscorrect.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadBoard();') )
					libHTML::$footerScript[$index]=str_replace('loadBoard();','loadBoard();SupplyCentersCorrect();', $script);
		}
	}
}		
// The Starting unit in Benevatto can't move...
class NoMove_OrderInterface extends BuildAnywhere_OrderInterface
{
	protected function jsLoadBoard() {
		global $DB, $Game;
		parent::jsLoadBoard();

		if( $this->phase=='Diplomacy' && $this->countryID==10) {
			list($nomove)=$DB->sql_row("SELECT text FROM wD_Notices WHERE toUserID=3 AND timeSent=0 AND fromID=".$Game->id);
			libHTML::$footerIncludes[] = '../variants/Rinascimento/resources/nomove.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadOrdersPhase();') )
					libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase();NoMove('.$nomove.');', $script);

		}
	}
}		

// Unit-Icons in javascript-code		
class CustomIcons_OrderInterface extends NoMove_OrderInterface
{
	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		libHTML::$footerIncludes[] = '../variants/Rinascimento/resources/iconscorrect.js';
		foreach(libHTML::$footerScript as $index=>$script)
			if(strpos($script, 'loadOrdersModel();'))
				libHTML::$footerScript[$index]=str_replace('loadOrdersModel();','loadOrdersModel();IconsCorrect();', $script);
	}
}

class RinascimentoVariant_OrderInterface extends CustomIcons_OrderInterface {}
