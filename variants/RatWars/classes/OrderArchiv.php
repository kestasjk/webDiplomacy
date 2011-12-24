<?php
/*
	Copyright (C) 2011 kaner406 / Oliver Auth

	This file is part of the Rat Wars variant for webDiplomacy

	The Rat Wars variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Rat Wars variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
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
		$ret = str_replace("fleet","Arrow Rat",$ret);
		$ret = str_replace("army","Spear Rat",$ret);
		return $ret;
	}

}

class Fog_OrderArchiv extends NewUnitNames_OrderArchiv
{
	// Hide the OrderLog
	public function outputOrderLogs(array $orders)
	{
		global $Game, $User;
		if ($Game->phase == 'Finished') return parent::outputOrderLogs($orders);
		if (($User->type['Moderator']) && (! $Game->Members->isJoined())) return parent::outputOrderLogs($orders);
		return;
	}

}

class RatWarsVariant_OrderArchiv extends Fog_OrderArchiv {}
