<?php
/*
	Copyright (C) 2011 by kaner406 & Oliver Auth

	This file is part of the War in 2020 variant for webDiplomacy

	The War in 2020 variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The War in 2020 variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class CustomStartVariant_adjudicatorPreGame extends adjudicatorPreGame
{
	// Disabled; no initial units or occupations
	protected function assignUnits() { }
	protected function assignUnitOccupations() { }
}

class War2020Variant_adjudicatorPreGame extends CustomStartVariant_adjudicatorPreGame {}
