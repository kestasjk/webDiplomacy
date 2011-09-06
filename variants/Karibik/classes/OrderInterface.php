<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the Karibik variant for webDiplomacy

	The Karibik variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Karibik variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Transform_OrderInterface extends OrderInterface
{
	protected function jsLoadBoard()
	{
		parent::jsLoadBoard();

		if( $this->phase=='Diplomacy' )
		{
			libHTML::$footerIncludes[] = '../variants/Karibik/resources/transform.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadOrdersPhase();') )
					libHTML::$footerScript[$index]=str_replace('loadOrdersPhase();','loadOrdersPhase();loadTransform();', $script);
		}
	}
}

class KaribikVariant_OrderInterface extends Transform_OrderInterface {}
