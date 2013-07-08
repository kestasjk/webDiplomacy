<?php
/*
	Copyright (C) 2011 Kaner406 / Oliver Auth

	This file is part of the KnownWorld_901 variant for webDiplomacy

	The KnownWorld_901 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The KnownWorld_901 variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class KnownWorld_901Variant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
			'Arabia'    => array('Ardebil'   =>'Army' ,'Baghdad'       =>'Army' ,'Isfahan'     =>'Army' ,'Basra'     =>'Fleet' ),
			'Axum'      => array('Axum'      =>'Army' ,'Roha'          =>'Army' ,'Malao'       =>'Army' ,'Adulis'    =>'Fleet' ),
			'Byzantinum'=> array('Taranto'   =>'Fleet','Constantinople'=>'Army' ,'Attalia'     =>'Fleet','Cherson'   =>'Fleet' ),
  			'China'     => array('Guangzhou' =>'Fleet','Nanjing'       =>'Army' ,"Chang'an"    =>'Army' ,'Yanjing'   =>'Army' ),
  			'Denmark'   => array('Jelling'   =>'Fleet','Viken'         =>'Army' ,'Scania'      =>'Fleet','Jorvik (East Coast)'=>'Fleet' ),
  			'Egypt'     => array("Al-Qatta'i"=>'Army' ,'Alexandria'    =>'Army' ,'Barca'       =>'Fleet','Jerusalem' =>'Fleet' ),
  			'France'    => array('Paris'     =>'Fleet','Aquitaine'     =>'Army' ,'Gascony'     =>'Army' ,'Narbonne'  =>'Army' ),
  			'Germany'   => array('Bavaria'   =>'Army' ,'Swabia'        =>'Army' ,'Saxony'      =>'Army' ,'Bremen'    =>'Fleet' ),
			'Khazaria'  => array('Sarkel'    =>'Army' ,'Atil'          =>'Army' ,'Balanjar'    =>'Army' ,'Tamantarka'=>'Army' ),
  			'Russia'    => array('Novgorod'  =>'Fleet','Rostov'        =>'Army' ,'Smolensk'    =>'Army' ,'Kiev'      =>'Army' ),
  			'Spain'     => array('Cadiz'     =>'Fleet','Salamanca'     =>'Army' ,'Cordova'     =>'Army' ,'Valencia'  =>'Fleet' ),
			'Turan'     => array('Urgench'   =>'Army' ,'Herat'         =>'Army' ,'Bukhara'     =>'Army' ,'Samarkand' =>'Army' ),
  			'Srivijaya' => array('Cahaya'    =>'Fleet','Palembang'     =>'Fleet','Kalimantan'  =>'Fleet','Jambi'     =>'Army' ),
			'Wagadu'    => array('Awlil'     =>'Fleet','Niore'         =>'Army' ,'Kumbi Saleh' =>'Army' ,'Walata'    =>'Army' ),
  			'India'     => array('Ujjain'    =>'Fleet','Kannauj'       =>'Army' ,'Indraprastha'=>'Army' ,'Varanasi'  =>'Army' ),
  			'Neutral units'=> array(
				'Dublin'=>'Army' ,'Wessex'        =>'Army' ,'Brittany'    =>'Army' ,'Lothairingia'=>'Army',
				'Lower Burgundy'=>'Army','Pamplona'=>'Army', 'Mauretania'=>'Army','Corsica'=>'Army',
				'Sardinia'=>'Army','Rome'=>'Army','Sicily'=>'Army','Ifriqiya'=>'Army',
				'Crete'=>'Army','Cyprus'=>'Army','Thrace'=>'Army','Moravia'=>'Army',
				'Mazovia'=>'Army','Borussia'=>'Army','Esteland'=>'Army','Bjarmaland'=>'Army',
				'Bulgar'=>'Army','Georgia'=>'Army','Armenia'=>'Army','Azerbaijan'=>'Army',
				'Pechenega'=>'Army','Dalmatia'=>'Army','Bashkortostan'=>'Army','Ghuzz'=>'Army',
				'Ordu-Balyk'=>'Army', 'Uyghurstan'=>'Army','Tibet'=>'Army','Kashmir'=>'Army',
				'Mansurah'=>'Army','Chola'=>'Army','Serendib'=>'Army','Pagan'=>'Army',
				'Kambuja'=>'Army','Silla'=>'Army','Saikaido'=>'Army','Butuan'=>'Army',
				'Tkanaren'=>'Army','Zawila'=>'Army','Kanem'=>'Army','Jenne-Jeno'=>'Army',
				'Zimbabwe'=>'Army','Mahilaka'=>'Army','Makuran'=>'Army','Yemen'=>'Army',
				'Socotra'=>'Army'),
	);

}

?>