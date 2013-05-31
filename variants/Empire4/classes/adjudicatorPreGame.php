<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Fall of the American Empire IV variant for webDiplomacy

	The Fall of the American Empire IV variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Fall of the American Empire IV variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Empire4Variant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'British-Columbia'=> array('Anchorage' =>'Fleet','Calgary'     =>'Army' ,'Vancouver'         =>'Army' ),
		'California'      => array('San Diego' =>'Fleet','Los Angeles' =>'Army' ,'San Francisco'     =>'Army' ),
		'Mexico'          => array('Veracruz'  =>'Army' ,'Mexico'      =>'Army' ,'Guadalajara'       =>'Fleet'),
		'Florida'         => array('Miami'     =>'Fleet','Tampa'       =>'Army' ,'Jacksonville'      =>'Army' ),
		'Heartland'       => array('Chicago'   =>'Army' ,'Milwaukee'   =>'Army' ,'Minneapolis'       =>'Army' ),
		'New-York'        => array('New Jersey'=>'Army' ,'Philadelphia'=>'Army' ,'New York City'     =>'Fleet'),
		'Quebec'          => array('Montreal'  =>'Army' ,'Quebec'      =>'Fleet','Ungava'            =>'Fleet'),
		'Peru'            => array('Bogota'    =>'Army' ,'Lima'        =>'Fleet','Cali (North Coast)'=>'Fleet'),
		'Texas'           => array('Dallas'    =>'Army' ,'Houston'     =>'Fleet','San Antonio'       =>'Army' ),
		'Cuba'            => array('Havana'    =>'Fleet','Holguin'     =>'Army' ,'Kingston'          =>'Fleet')
	);

}