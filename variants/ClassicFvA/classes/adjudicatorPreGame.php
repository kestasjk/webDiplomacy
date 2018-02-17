<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the France Vs Austria variant for webDiplomacy

	The France Vs Austria variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The France Vs Austria variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicFvAVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'France'  => array('Brest'  =>'Fleet', 'Paris' =>'Army', 'Marseilles'=>'Army'),
		'Austria' => array('Trieste'=>'Fleet', 'Vienna'=>'Army', 'Budapest'  =>'Army')
	);

}