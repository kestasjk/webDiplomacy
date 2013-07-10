<?php
/*
	Copyright (C) 2011 Emmanuele Ravaioli / Oliver Auth

	This file is part of the Rinascimento variant for webDiplomacy

	The Rinascimento variant for webDiplomacy" is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either 
	version 3 of the License, or (at your option) any later version.

	The Rinascimento variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class RinascimentoVariant_adjudicatorPreGame extends adjudicatorPreGame {

	// Set the staring units:
	protected $countryUnits = array(
		'Ferrara'=> array('Ferrara'=>'Army' ),
		'Pisa'   => array('Pisa'   =>'Fleet'),
		'Siena'  => array('Siena'  =>'Army' ),
		'Firenze'=> array('Firenze'=>'Army' ,'Arezzo'     =>'Army'),
		'French' => array('Annecy' =>'Army' ,'Domodossola'=>'Army'),
		'Genova' => array('Genova' =>'Fleet','Ajaccio'    =>'Fleet'),
		'Savoia' => array('Torino' =>'Army' ,'Chambery'   =>'Army'),
		'Milano' => array('Milano' =>'Army' ,'Pavia'      =>'Army','Piacenza'=>'Army'),
		'Napoli' => array('Napoli' =>'Army' ,'Amalfi' =>'Fleet', 'Palermo'=>'Fleet','Brindisi'   =>'Fleet'),
		'Turkish'=> array('East Gateway'  =>'Fleet' ,'Outer Ionian Sea' =>'Fleet','Eastern Mediterranean Sea' =>'Fleet','Arcipelago di Spalato' =>'Fleet'),
		'Venezia'=> array('Venezia'       =>'Fleet' ,'Spalato'          =>'Fleet','Verona'                    =>'Army' ,'Brescia'                =>'Army'),
		'Stato della Chiesa' => array('ROMA'=>'Army','Bologna'          =>'Army','Benevento'                  =>'Army' ,'Perugia'                =>'Army'),
		'Neutral units'=> array('Geneve'=>'Army','Trieste'=>'Army','Trento'=>'Army')
	);
	
	// Save the UnitID from the Unit in Benevento to prevent "move" commands
	function adjudicate() {
		global $DB, $Game;
		parent::adjudicate();
		$DB->sql_put(
			"INSERT INTO wD_Notices (toUserID,fromID,text,linkName) 
				SELECT 3,GameID,occupyingUnitID,'Variant-Data'
				FROM wD_TerrStatus WHERE terrID=83 AND GameID=".$Game->id);
	}	
}

?>