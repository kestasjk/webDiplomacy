<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the WhoControlsAmericaV variant for webDiplomacy

	The WhoControlsAmericaV variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The WhoControlsAmerica variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class WhoControlsAmericaVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Liberal Interests' => array('AFL-CIO (Trade Unions)'=>'Army', 'Pro-Choice Movement'=>'Army', 'Environmentalists'=>'Army'),
		'Republican Party'  => array('Republican Convention'=>'Army', 'Republican Leadership'=>'Army', 'Republican Primaries'=>'Army'),
		'The Military'   => array('The Pentagon'=>'Army', 'Joint Chiefs of Staff'=>'Army', 'Central Intelligence Agency'=>'Army'),
		'Corporate America' => array('The Media'=>'Army', 'Wall Street'=>'Army', 'The Banks'=>'Army'),
		'Democratic Party' => array('Democratic Convention'=>'Army', 'Democratic Leadership'=>'Army', 'Democratic Primaries'=>'Army'),
		'Conservative Interests'  => array('Religious Right'=>'Army', 'Pro-Life Movement'=>'Army', 'National Rifle Association'=>'Army'),
		'The Underworld'  => array('The Mafia'=>'Army', 'South American Drug Cartels'=>'Army', 'Triads'=>'Army'),
		'Secret Societies'  => array('Free Masons'=>'Army', 'Scientologists'=>'Army', 'Skull and Bones'=>'Army')
	);

}