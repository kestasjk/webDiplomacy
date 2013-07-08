<?php
/*
	Copyright (C) 2012 Oliver Auth

	This file is part of the Age of Pericles variant for webDiplomacy

	The Age of Pericles variant for webDiplomacy is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The Age of Pericles variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
	If you have questions or suggestions send me a mail: Oliver.Auth@rhoen.de

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class AgeOfPericlesVariant_adjudicatorPreGame extends adjudicatorPreGame
{
	protected $countryUnits = array(
		'Aetolia'  => array('Calydon'           =>'Fleet', 'Callium'  =>'Army' , 'Thermium'=>'Army'),
		'Arcolia'  => array('Iria (South Coast)'=>'Fleet', 'Epidaurus'=>'Army' , 'Mycenae' =>'Army'),
		'Attica'   => array('Caria'             =>'Fleet', 'Ionia'    =>'Fleet', 'Athenae' =>'Army'),
		'Boeotia'  => array('Helicon'           =>'Fleet', 'Delion'   =>'Army' , 'Opus'    =>'Army'),
		'Elia'     => array('Elis'              =>'Fleet', 'Pisatis'  =>'Army' , 'Dafni'   =>'Army'),
		'Laconia'  => array('Koidaunas'         =>'Fleet', 'Prastos'  =>'Army' , 'Sparta'  =>'Army'),
		'Messenia' => array('Pylos'             =>'Fleet', 'Messena'  =>'Army' , 'Ira'     =>'Army')
	);
}

