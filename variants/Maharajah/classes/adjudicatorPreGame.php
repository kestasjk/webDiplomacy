<?php
/*
	Copyright (C) 2012 kaner406 / Oliver Auth

	This file is part of the Maharajah variant for webDiplomacy

	The Maharajah variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Maharajah variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MaharajahVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Vijayanagar'=> array('Pulicat'=>'Fleet'  , 'Calicut'=>'Army'   , 'Bangalore'=>'Army'),
		'Gondwana'   => array('Sambalpur'=>'Fleet', 'Raipur'=>'Army'    , 'Jabalpur'=>'Army'),
		'Persia'     => array('Hormuz'=>'Fleet'   , 'Isfahan'=>'Army'   , 'Meshed'=>'Army'),
		'Mughalistan'=> array('Balkh'=>'Army'     , 'Badakhshan'=>'Army', 'Kabul'=>'Army'),
		'Delhi'      => array('Agra'=>'Army'      , 'Awadh'=>'Army'     , 'Muzaffarpur'=>'Army'),
		'Rajputana'  => array('Jaisalmer'=>'Fleet', 'Multan'=>'Army'    , 'Jodhpur'=>'Army'),
		'Bahmana'    => array('Goa'=>'Fleet'      , 'Ahmadnagar'=>'Army', 'Bijapur'=>'Army'),
	);

}

?>
