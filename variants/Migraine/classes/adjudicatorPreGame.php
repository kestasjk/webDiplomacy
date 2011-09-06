<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Migraine variant for webDiplomacy

	The Migraine variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Migraine variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General 
	Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MigraineVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Beta'   => array(),
		'Delta'  => array(),
		'Gamma'  => array(),
		'Kappa'  => array(),
		'Lambda' => array(),
		'Sigma'  => array(),
		'Theta'  => array(),
		'Zeta'   => array()
	);		

	// Disabled; no initial units or occupations
	protected function assignUnits() { }

	protected function assignUnitOccupations() { }

}