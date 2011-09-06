<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Sail Ho II variant for webDiplomacy

	The Sail Ho II variant for webDiplomacy" is free software: you can 
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The Sail Ho II variant for webDiplomacy is distributed in the hope that it
	will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class SailHo2Variant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'East'	=> array('Centaur Forest'    =>'Army' , 'Amazon Village'               =>'Fleet'),
		'North'	=> array('Hercules\' Respite'=>'Army' , 'Village of Aeolus'            =>'Army' ),
		'South'	=> array('Depths of Hades'   =>'Army' , 'Xena\'s Rest'                 =>'Fleet'),
		'West'	=> array('Isle of Lesbos'    =>'Fleet', 'Convent of the Vestal Virgins'=>'Fleet')
	);

}

?>
