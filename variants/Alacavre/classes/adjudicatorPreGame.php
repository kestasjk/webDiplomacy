<?php
/*

    Map Encoding Copyright (c) Figlesquidge 2010


	This file is part of webDiplomacy, Copyright (C) 2004-2009 Kestas J. Kuliukas
    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');
class AlacavreVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Ithsomn' => array(
					'Uark'=>'Army', 'Vaarle'=>'Army', 'Nuit'=>'Fleet'
				),
		'Shinto' => array(
					'Heashoult'=>'Army', 'Knie (West Coast)'=>'Fleet', 'Hawyo'=>'Army', 'Ayetok'=>'Fleet'
				),
		'Quiom' => array(
					'Kreiasth'=>'Army', 'Lik'=>'Fleet', 'Quok'=>'Army'
				),
		'Maroe' => array(
					'Metpri'=>'Army', 'Lireo'=>'Fleet', 'Orkali'=>'Army'
				),
		'Oz' => array(
					'Northern Terror'=>'Fleet', 'Old North Wales'=>'Army', 'Southern Oz'=>'Fleet'
				),
		'Namaq' => array(
					'Lehas'=>'Fleet', 'Olbucfor'=>'Army', 'Mianey'=>'Army'
				),
		'Payashk' => array(
					'Prithski'=>'Fleet', 'Vorulov'=>'Army', 'Kelpuct'=>'Army'
				)
		);
}