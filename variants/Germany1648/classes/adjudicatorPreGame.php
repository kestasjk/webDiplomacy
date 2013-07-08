<?php
/*
	Copyright (C) 2010 Emmanuele Ravaioli / 2011 Oliver Auth

	This file is part of the Germany1648 variant for webDiplomacy

	The Germany1648 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Germany1648 variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Germany1648Variant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Austrian Habsburg'     => array('Archduchy of Austria'=>'Army','Kingdom of Bohemia'  =>'Army','Breisgau'  =>'Army'),
		'Spanish Habsburg'      => array('Franche-Comte'=>'Army','Far Spanish Netherlands'   =>'Army','Duchy of Luxemburg'   =>'Army'),
		'Wettin'                => array('Electorate of Saxony'  =>'Army','Duchy of Saxony'  =>'Army','Lisatia'  =>'Army'),
		'Bavarian Wittelsbach'  => array('Electorate of Bavaria'  =>'Army','Bishopric of Regensburg'  =>'Army','Memmingen'  =>'Army'),
		'Palatinate Wittelsbach'=> array('Upper Electoral Palatinate'  =>'Army','Principality of Neuburg'  =>'Army','Duchy of Julich'  =>'Army','Western Electoral Palatinate'  =>'Army'),
		'Hohenzollern'          => array('Electorate of Brandenburg'  =>'Army','County of Mark'  =>'Army','Margraviate of Ansbach'  =>'Army','Hohenzollern'  =>'Army'),
		'Ecclesiastic Lands'    => array('Archbishopric of Trier'  =>'Army','Archbishopric of Mainz'  =>'Army','Archbishopric of Salzburg'  =>'Army','Bishopric of Munster'  =>'Army'),
		'Neutral units'         => array('Lubeck #1'  =>'Army','Hamburg #2'  =>'Army','Bremen #3'  =>'Army','Muhlhausen #4'  =>'Army','Cologne #5'  =>'Army','Aachen #6'  =>'Army','Frankfurt am Main #7'  =>'Army','Worms #8'  =>'Army','Speyer #9'  =>'Army','Strassburg #10'  =>'Army','Ravensburg #11'  =>'Army','Ulm #12'  =>'Army','Augsburg #13'  =>'Army','Nuremberg #14'  =>'Army','Regensburg #15'  =>'Army')
	);
}
