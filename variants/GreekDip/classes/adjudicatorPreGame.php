<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the GreekDip variant for webDiplomacy

	The GreekDip variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The GreekDip variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class GreekDipVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Athens'    => array('Coin Stack 11'=>'Army','Coin Stack 12'=>'Army','Coin Stack 13'=>'Army','Coin Stack 14'=>'Army'),
		'Byzantinum'=> array('Coin Stack 21'=>'Army','Coin Stack 22'=>'Army','Coin Stack 23'=>'Army','Coin Stack 24'=>'Army'),
		'Macedonia' => array('Coin Stack 31'=>'Army','Coin Stack 32'=>'Army','Coin Stack 33'=>'Army','Coin Stack 34'=>'Army'),
		'Persia'    => array('Coin Stack 41'=>'Army','Coin Stack 42'=>'Army','Coin Stack 43'=>'Army','Coin Stack 44'=>'Army'),
		'Rhodes'    => array('Coin Stack 51'=>'Army','Coin Stack 52'=>'Army','Coin Stack 53'=>'Army','Coin Stack 54'=>'Army'),
		'Sparta'    => array('Coin Stack 61'=>'Army','Coin Stack 62'=>'Army','Coin Stack 63'=>'Army','Coin Stack 64'=>'Army'),
	);

}