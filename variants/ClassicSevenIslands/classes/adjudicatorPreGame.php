<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the 7 Islands variant for webDiplomacy

	The 7 Islands variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The 7 Islands variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	This file is a 1:1 copy with small adjustments from Kestas J. Kuliukas
	code for the CustomStart - Variant

*/

class ClassicSevenIslandsVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'England' => array(),
		'France'  => array(),
		'Italy'   => array(),
		'Germany' => array(),
		'Austria' => array(),
		'Turkey'  => array(),
		'Russia'  => array()
		);

	// Disabled; no initial units or occupations
	protected function assignUnits() { }

	protected function assignUnitOccupations() { }
	
}