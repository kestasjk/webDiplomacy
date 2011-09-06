<?php
/*
	Copyright (C) 2011 Milan Mach

	This file is part of the Hussite variant for webDiplomacy

	The Hussite variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Hussite variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class HussiteVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Bavaria' 			=> array('München'	=>'Army' , 'Regensburg'  =>'Army' , 'Passau'	  =>'Fleet'			),
		'Catholic Landfrieden'   	=> array('Iglau'  	=>'Army' , 'Olmütz'      =>'Army' , 'Brünn'	  =>'Army' ,'Pilsen' =>'Army'	),
		'Hungary'  			=> array('Ofen'  	=>'Army' , 'Pressburg'   =>'Army' , 'Sillein'     =>'Army' 			),
		'Kingdom of Poland'  		=> array('Posen'	=>'Army' , 'Warschau'    =>'Army' , 'Krakau'      =>'Army'   			),
		'Margraviate of Brandenburg' 	=> array('Berlin' 	=>'Army' , 'Potsdam'     =>'Fleet', 'Frankfurt'   =>'Army'   			),
		'Orebites'   			=> array('Oreb'   	=>'Army' , 'Königgratz'  =>'Army' , 'Leitomischl' =>'Army'			),
		'Praguers'  			=> array('Schlan'  	=>'Army' , 'Neustadt'	 =>'Army' , 'Altstadt'    =>'Fleet' 			), 
		'Saxony' 			=> array('Leipzig'  	=>'Army' , 'Dresden'	 =>'Army' , 'Chemnitz'    =>'Army' 			), 
		'Taborites'  			=> array('Tabor'  	=>'Army' , 'Pisek'	 =>'Army' , 'Budweis'     =>'Army' 			) 
	);

}