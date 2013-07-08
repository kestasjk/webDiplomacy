<?php
/*
	Copyright (C) 2012 Oliver Auth / Scordatura

	This file is part of the Indians of the Great Lakes variant for webDiplomacy

	The Indians of the Great Lakes variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Indians of the Great Lakes variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class GreatLakesVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Algonquin' => array('Kioshkokwi'=>'Army' , 'Shawanaga'=>'Fleet', 'Wahwashkesh'=>'Army'),
		'Erie' => array('Essex'=>'Fleet'    , 'Imlay'=>'Army'    , 'Toledo'=>'Army'),
                'Huron' => array('Sanilac'=>'Fleet'  , 'Cheptow'=>'Army'   , 'Nile'=>'Army'),
                'Iroquois' => array('Tuscarora'=>'Fleet', 'Rochester'=>'Army' , 'Oswego'=>'Army'),
                'Kaskasia' => array('Waukesha'=>'Fleet' , 'Sussex'=>'Army'    , 'Kankakee'=>'Army'),
                'Mississauga' => array('Oshawa'=>'Fleet'   , 'Toronto'=>'Army'   , 'Klemburg'=>'Army'),
                'Ojibwe' => array('Whyte'=>'Fleet'    , 'Savanna'=>'Army'   , 'Croix'=>'Army'),
                'Otawatomi' => array('Aetna'=>'Fleet'    , 'Alcona'=>'Fleet'   , 'Boyee'=>'Army'),
                'Ottawa' => array('Vars'=>'Fleet'     , 'Matawin'=>'Army'   , 'Manotick'=>'Army'),
	);

}

?>