<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the Karibik variant for webDiplomacy

	The Karibik variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Karibik variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class KaribikVariant_adjudicatorPreGame extends adjudicatorPreGame
{
	protected $countryUnits = array(
		'Cuba'     => array('Havanna' =>'Fleet','Santiago' =>'Fleet','Santo Domingo' =>'Fleet'),
		'Brasil'   => array('Belo Horizonte' =>'Army','Manaus' =>'Army','Belem' =>'Fleet'),
		'Mexico'   => array('Mexico City' =>'Army','Mazatlan' =>'Fleet','Merida' =>'Fleet'),
		'Columbia' => array('Bogota' =>'Army','Medellin' =>'Army','Cartagena' =>'Fleet'),
		'Peru'     => array('Cuzco' =>'Army','Iquitos' =>'Army','Lima' =>'Fleet'),
		'USA'      => array('Phoenix' =>'Army','Houston' =>'Fleet','Miami' =>'Fleet'),
		'Venezuela'=> array('Caracas' =>'Army','Ciudad Bolivar' =>'Army','Cumana' =>'Fleet'),
		'Paraguay' => array('Asuncion' =>'Army','Cuiaba' =>'Army','Randonia' =>'Army')
	);
}
