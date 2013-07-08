<?php
/*
	Copyright (C) 2010 Emmanuele Ravaioli / 2011 Oliver Auth

	This file is part of the Germany1648 variant for webDiplomacy

	The Germany1648 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Germany1648 variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class NewUnitNames_OrderArchiv extends OrderArchiv
{
	public function OutputOrders()
	{
		$ret=parent::OutputOrders();
		$ret = str_replace("fleet","knight",$ret);
		$ret = str_replace("army","man-at-arms",$ret);
		return $ret;
	}

}

class NeutralUnits_OrderArchiv extends NewUnitNames_OrderArchiv
{	
	public function __construct()
	{
		parent::__construct();
		$this->countryIDToName[]='Neutral units';
	}
}

class Germany1648Variant_OrderArchiv extends NeutralUnits_OrderArchiv {}
