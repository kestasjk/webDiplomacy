<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the Imperial2 variant for webDiplomacy

	The Imperial2 variant for webDiplomacy is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The Imperial2 variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
	If you have questions or suggestions send me a mail: Oliver.Auth@rhoen.de

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Imperial2Variant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Austria' => array('Croatia {CRT}'         => 'Fleet', 'Vienna {VIE}'    => 'Army' , 'Venice {VEN}'       => 'Army' , 'Budapest {BUD}'   => 'Army' ),
		'Brazil'  => array('Rio de Janeiro {RDJ}'  => 'Fleet', 'Recife {REC}'    => 'Fleet', 'Brasilia {BRA}'     => 'Army' , 'Oliveira {OLI}'   => 'Army' ),
		'Britain' => array('Falkland Islands {FLK}'=> 'Fleet', 'Edinburgh {EDI}' => 'Fleet', 'Nova Scotia {NVS}'  => 'Fleet', 'London {LON}'     => 'Fleet',
				           'Gibraltar {GB}'        => 'Fleet', 'Aden {ADE}'      => 'Fleet', 'Bombay {BOM}'       => 'Fleet', 'Singapore {SP}'   => 'Fleet',
						   'Hong Kong {HK}'        => 'Fleet', 'Dublin {DUB}'    => 'Army' , 'Quebec {QBC}'       => 'Army' , 'Vancouver {VNC}'  => 'Army' ,
						   'Delhi {DEL}'           => 'Army' , 'Perth {PRT}'     => 'Army'),
		'China'   => array('Peking {PEK}'          => 'Army' , 'Shanghai {SHA}'  => 'Army' , 'Canton {CAN}'       => 'Army' , 'Manchuria {MNC}'  => 'Army' ,
		                   'Wuhan {WUH}'           => 'Army' , 'Chungking {CHK}' => 'Army' , 'Sinkiang {SNK}'     => 'Army'),
		'CSA'     => array('Richmond {RCM}'        => 'Army' , 'Atlanta {ATL}'   => 'Army' , 'Austin {AUS}'       => 'Army' , 'New Orleans {NOL}'=> 'Army'),
		'France'  => array('Paris {PAR}'           => 'Army' , 'Marseilles {MRS}'=> 'Army' , 'Grain Coast {GCS}'  => 'Army' , 'Cambodia {CMB}'   => 'Army' ,
		                   'Nantes {NAN}'          => 'Fleet', 'Corsica {CRS}'   => 'Fleet', 'Cayenne {CYN}'      => 'Fleet', 'Monrovia {MNV}'   => 'Fleet',		   
						   'Society Islands {SCT}' => 'Fleet', 'Cochin {CCH}'    => 'Fleet'),
		'Holland' => array('Holland {HOL}'         => 'Fleet', 'Cape Town {CTN}' => 'Fleet', 'Ceylon {CEY}'       => 'Fleet', 'Sumatra {SUM}'    => 'Fleet',
		                   'Java {JVA}'            => 'Fleet', 'Paramaribo {PRM}'=> 'Army' , 'Transvaal {TRN}'    => 'Army' , 'Borneo {BOR}'     => 'Army'),
		'Japan'   => array('Edo {EDO}'             => 'Army' , 'Sapporo {SAP}'   => 'Fleet', 'Kagoshima {KG}'     => 'Fleet', 'Okinawa {OKI}'    => 'Fleet'),
		'Mexico'  => array('Mexico City {MXC}'     => 'Army' , 'Merida {MER}'    => 'Fleet', 'Mazatlan {MAZ}'     => 'Fleet'),
		'Prussia' => array('Berlin {BER}'          => 'Army' , 'Prussia {PRS}'   => 'Army' , 'Silesia {SIL}'      => 'Army' , 'Ruhr {RH}'        => 'Army'),
		'Russia'  => array('Moscow {MOS}'          => 'Army' , 'Warsaw {WRS}'    => 'Army' , 'Georgia {GRG}'      => 'Army' , 'Orenburg {ORN}'   => 'Army' ,
		                   'Port Arthur {PA}'      => 'Fleet', 'Irkutsk {IRK}'   => 'Army' , 'Vladivostok {VLA}'  => 'Army' , 'Anchorage {ANC}'  => 'Army' ,
						   'Sevastopol {SEV}'      => 'Fleet', 'Omsk {OMS}'      => 'Army' , 'St Petersburg {STP} (South Coast)' => 'Fleet'),
		'Turkey'  => array('Constantinople {CON}'  => 'Army' , 'Cyprus {CYP}'    => 'Fleet', 'Baghdad {BAG}'      => 'Fleet', 'Sofia {SOF}'      => 'Army' ,
                           'Cairo {CAI}'           => 'Army' , 'Angora {ANG} (North Coast)'=> 'Fleet'),
		'USA'     => array('San Francisco {SFR}'   => 'Army' , 'Detroit {DET}'   => 'Army' , 'Washington DC {WDC}'=> 'Army','Chicago {CHI}'      => 'Army' , 
						   'Oregon {ORG}'          => 'Fleet', 'New York {NYO}'  => 'Fleet')
		);
		
}