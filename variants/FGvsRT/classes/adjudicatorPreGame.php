<?php
/*
	Copyright (C) 2010 Cian O Rathaille

	This file is part of the France-Germany Vs Russia-Turkey variant for webDiplomacy

	The France-Germany Vs Russia-Turkey variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The France-Germany Vs Russia-Turkey variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class FGvsRTVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Frankland'  => array('Kiel'=>'Fleet', 'Munich'=>'Army', 'Berlin'=>'Army', 'Marseilles'=>'Army', 'Paris'=>'Army', 'Brest'=>'Fleet'),
		'Juggernaut' => array('St. Petersburg (South Coast)'=>'Fleet', 'Moscow'=>'Army', 'Warsaw'=>'Army', 'Sevastopol'=>'Fleet', 'Constantinople'=>'Army', 'Smyrna'=>'Army', 'Ankara'=>'Fleet')
	);

}