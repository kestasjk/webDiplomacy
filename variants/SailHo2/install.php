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

require_once("variants/install.php");

InstallTerritory::$Territories=array();
$countries=$this->countries;
$territoryRawData=array(
	array('Village of Aeolus'             ,'Coast','Yes',576,254 ,288,127,'North'  ),
	array('Alcmene\\\'s Village'          ,'Coast','No' ,216,404 ,108,202,'Neutral'),
	array('Amazon Village'                ,'Coast','Yes',796,564 ,398,282,'East'   ),
	array('Aphrodite\\\'s Beach'          ,'Coast','Yes',592,518 ,296,259,'Neutral'),
	array('Palace of Ares'                ,'Land' ,'Yes',792,786 ,396,393,'Neutral'),
	array('Argo\\\'s Pasture'             ,'Coast','No' ,740,984 ,370,492,'Neutral'),
	array('Autolycus\\\' Hideout'         ,'Land' ,'No' ,540,1004,270,502,'Neutral'),
	array('Callisto\\\'s Stronghold'      ,'Land' ,'No' ,374,1018,187,509,'Neutral'),
	array('Cecrops\\\' Channel'           ,'Sea'  ,'No' ,732,898 ,366,449,'Neutral'),
	array('Centaur Forest'                ,'Coast','Yes',736,316 ,368,158,'East'   ),
	array('Charon\\\'s Crossing'          ,'Coast','No' ,114,948 ,57 ,474,'Neutral'),
	array('Chiron\\\'s Cave'              ,'Coast','No' ,672,252 ,336,126,'Neutral'),
	array('Cupid\\\'s Cloud'              ,'Coast','Yes',572,656 ,286,330,'Neutral'),
	array('Deianeira\\\'s Grave'          ,'Land' ,'No' ,282,98  ,141,49 ,'Neutral'),
	array('Eastern Ocean'                 ,'Sea'  ,'No' ,960,818 ,480,360,'Neutral'),
	array('Echo\\\'s Glade'               ,'Coast','No' ,744,408 ,372,204,'East'   ),
	array('Elysian Fields'                ,'Coast','No' ,32 ,1010,18 ,480,'Neutral'),
	array('Sea of Fire'                   ,'Sea'  ,'No' ,214,770 ,107,385,'Neutral'),
	array('Field of the Golden Fleece'    ,'Land' ,'No' ,120,132 ,60 ,66 ,'Neutral'),
	array('Realm of the 3 Furies'         ,'Land' ,'No' ,230,1028,115,514,'Neutral'),
	array('Gabrielle\\\'s Village'        ,'Coast','No' ,520,922 ,262,458,'South'  ),
	array('Glittering Gulf'               ,'Sea'  ,'No' ,568,324 ,284,162,'Neutral'),
	array('Gulf of Chains'                ,'Sea'  ,'No' ,330,492 ,165,249,'Neutral'),
	array('Depths of Hades'               ,'Coast','Yes',412,932 ,206,466,'South'  ),
	array('Hercules\\\' Respite'          ,'Coast','Yes',286,332 ,143,166,'North'  ),
	array('Shrine to Hestia'              ,'Coast','No' ,238,672 ,119,345,'West'   ),
	array('Shrine to Hestia (North Coast)','Coast','No' ,242,652 ,113,319,'Neutral'),
	array('Shrine to Hestia (South Coast)','Coast','No' ,232,692 ,116,346,'Neutral'),
	array('Forest of the Golden Hind'     ,'Coast','No' ,766,846 ,388,432,'Neutral'),
	array('Hippolyta\\\'s Girdle'         ,'Coast','No' ,898,478 ,449,250,'Neutral'),
	array('Jason\\\'s Kingdom'            ,'Coast','No' ,36 ,464 ,18 ,232,'Neutral'),
	array('Joxter\\\'s Retreat'           ,'Coast','No' ,708,1026,358,515,'Neutral'),
	array('Lesbian Sea'                   ,'Sea'  ,'No' ,58 ,720 ,29 ,360,'Neutral'),
	array('Isle of Lesbos'                ,'Coast','Yes',140,694 ,70 ,349,'West'   ),
	array('Lover\\\'s Lane'               ,'Sea'  ,'No' ,582,720 ,291,360,'Neutral'),
	array('Realm of King Midas'           ,'Coast','No' ,462,370 ,231,185,'Neutral'),
	array('Minotaur\\\'s Labyrinth'       ,'Coast','No' ,974,384 ,487,192,'Neutral'),
	array('Morpheus\\\' Palace'           ,'Coast','Yes',132,488 ,66 ,250,'Neutral'),
	array('Narcissus\\\' Reflection'      ,'Sea'  ,'No' ,652,320 ,326,160,'Neutral'),
	array('Nestor\\\'s Kingdom'           ,'Coast','Yes',708,766 ,354,390,'Neutral'),
	array('Mount Olympus'                 ,'Land' ,'No' ,416,222 ,208,111,'North'  ),
	array('Ocean of Peace'                ,'Sea'  ,'No' ,80 ,892 ,40 ,450,'Neutral'),
	array('Persephone\\\'s Garden'        ,'Coast','No' ,312,918 ,156,459,'Neutral'),
	array('Poseidon\\\'s Curse'           ,'Sea'  ,'No' ,586,858 ,293,429,'Neutral'),
	array('Prometheus\\\' Cliff'          ,'Coast','Yes',412,524 ,206,262,'Neutral'),
	array('Village of Psyche'             ,'Coast','No' ,560,616 ,280,308,'Neutral'),
	array('Village of Psyche (East Coast)','Coast','No' ,614,604 ,310,302,'Neutral'),
	array('Village of Psyche (West Coast)','Coast','No' ,514,596 ,257,298,'Neutral'),
	array('Salmonius\\\' Scheme'          ,'Coast','No' ,382,422 ,191,225,'Neutral'),
	array('Scholars Channel'              ,'Sea'  ,'No' ,722,592 ,361,296,'Neutral'),
	array('Serina\\\'s Village'           ,'Coast','No' ,880,736 ,442,370,'Neutral'),
	array('Sisyphus\\\' Hill'             ,'Land' ,'No' ,888,134 ,444,67 ,'Neutral'),
	array('Sea of Arrows'                 ,'Sea'  ,'No' ,336,582 ,175,294,'Neutral'),
	array('Sea of Dreams'                 ,'Sea'  ,'No' ,208,556 ,104,278,'Neutral'),
	array('Sea of Tears'                  ,'Sea'  ,'No' ,412,880 ,206,440,'Neutral'),
	array('South Sea'                     ,'Sea'  ,'No' ,872,984 ,436,492,'Neutral'),
	array('Sea of Waves'                  ,'Sea'  ,'No' ,592,482 ,310,242,'Neutral'),
	array('Strife\\\'s Cave'              ,'Coast','Yes',888,808 ,446,408,'Neutral'),
	array('Tantalus\\\' Pool'             ,'Land' ,'No' ,654,72  ,327,36 ,'Neutral'),
	array('Tartarus'                      ,'Coast','Yes',220,924 ,110,462,'Neutral'),
	array('Convent of the Vestal Virgins' ,'Coast','Yes',314,694 ,157,347,'West'   ),
	array('Western Ocean'                 ,'Sea'  ,'No' ,52 ,562 ,26 ,281,'Neutral'),
	array('Xena\\\'s Rest'                ,'Coast','Yes',600,916 ,300,458,'South'  ),
	array('Temple of Zeus'                ,'Land' ,'No' ,460,84  ,230,42 ,'Neutral'),
);

foreach($territoryRawData as $territoryRawRow)
{
	list($name, $type, $supply, $x, $y, $sx, $sy, $country)=$territoryRawRow;
	if( $country=='Neutral' )
		$countryID=0;
	else
		$countryID=$this->countryID($country);
		
	new InstallTerritory($name, $type, $supply, $countryID, $x, $y, $sx, $sy);
}
unset($territoryRawData);

$bordersRawData=array(
	array('Chiron\\\'s Cave'              ,'Village of Aeolus'            ,'Yes','Yes'),
	array('Realm of King Midas'           ,'Village of Aeolus'            ,'Yes','Yes'),
	array('Hercules\\\' Respite'          ,'Alcmene\\\'s Village'         ,'Yes','Yes'),
	array('Morpheus\\\' Palace'           ,'Alcmene\\\'s Village'         ,'Yes','Yes'),
	array('Hippolyta\\\'s Girdle'         ,'Amazon Village'               ,'Yes','Yes'),
	array('Echo\\\'s Glade'               ,'Amazon Village'               ,'Yes','Yes'),
	array('Joxter\\\'s Retreat'           ,'Argo\\\'s Pasture'            ,'Yes','Yes'),
	array('Xena\\\'s Rest'                ,'Argo\\\'s Pasture'            ,'Yes','Yes'),
	array('Echo\\\'s Glade'               ,'Centaur Forest'               ,'Yes','Yes'),
	array('Chiron\\\'s Cave'              ,'Centaur Forest'               ,'Yes','Yes'),
	array('Elysian Fields'                ,'Charon\\\'s Crossing'         ,'Yes','Yes'),
	array('Tartarus'                      ,'Charon\\\'s Crossing'         ,'Yes','Yes'),
	array('Xena\\\'s Rest'                ,'Gabrielle\\\'s Village'       ,'Yes','Yes'),
	array('Persephone\\\'s Garden'        ,'Depths of Hades'              ,'Yes','Yes'),
	array('Salmonius\\\' Scheme'          ,'Hercules\\\' Respite'         ,'Yes','Yes'),
	array('Strife\\\'s Cave'              ,'Forest of the Golden Hind'    ,'Yes','Yes'),
	array('Nestor\\\'s Kingdom'           ,'Forest of the Golden Hind'    ,'Yes','Yes'),
	array('Minotaur\\\'s Labyrinth'       ,'Hippolyta\\\'s Girdle'        ,'Yes','Yes'),
	array('Morpheus\\\' Palace'           ,'Jason\\\'s Kingdom'           ,'Yes','Yes'),
	array('Prometheus\\\' Cliff'          ,'Realm of King Midas'          ,'Yes','Yes'),
	array('Serina\\\'s Village'           ,'Nestor\\\'s Kingdom'          ,'Yes','Yes'),
	array('Tartarus'                      ,'Persephone\\\'s Garden'       ,'Yes','Yes'),
	array('Salmonius\\\' Scheme'          ,'Prometheus\\\' Cliff'         ,'Yes','Yes'),
	array('Strife\\\'s Cave'              ,'Serina\\\'s Village'          ,'Yes','Yes'),
	array('Gabrielle\\\'s Village'        ,'Depths of Hades'              ,'Yes','Yes'),
	array('Mount Olympus'                 ,'Village of Aeolus'            ,'No' ,'Yes'),
	array('Tantalus\\\' Pool'             ,'Village of Aeolus'            ,'No' ,'Yes'),
	array('Temple of Zeus'                ,'Village of Aeolus'            ,'No' ,'Yes'),
	array('Jason\\\'s Kingdom'            ,'Alcmene\\\'s Village'         ,'No' ,'Yes'),
	array('Field of the Golden Fleece'    ,'Alcmene\\\'s Village'         ,'No' ,'Yes'),
	array('Forest of the Golden Hind'     ,'Palace of Ares'               ,'No' ,'Yes'),
	array('Strife\\\'s Cave'              ,'Palace of Ares'               ,'No' ,'Yes'),
	array('Serina\\\'s Village'           ,'Palace of Ares'               ,'No' ,'Yes'),
	array('Nestor\\\'s Kingdom'           ,'Palace of Ares'               ,'No' ,'Yes'),
	array('Gabrielle\\\'s Village'        ,'Autolycus\\\' Hideout'        ,'No' ,'Yes'),
	array('Xena\\\'s Rest'                ,'Autolycus\\\' Hideout'        ,'No' ,'Yes'),
	array('Callisto\\\'s Stronghold'      ,'Autolycus\\\' Hideout'        ,'No' ,'Yes'),
	array('Joxter\\\'s Retreat'           ,'Autolycus\\\' Hideout'        ,'No' ,'Yes'),
	array('Depths of Hades'               ,'Callisto\\\'s Stronghold'     ,'No' ,'Yes'),
	array('Realm of the 3 Furies'         ,'Callisto\\\'s Stronghold'     ,'No' ,'Yes'),
	array('Persephone\\\'s Garden'        ,'Callisto\\\'s Stronghold'     ,'No' ,'Yes'),
	array('Gabrielle\\\'s Village'        ,'Callisto\\\'s Stronghold'     ,'No' ,'Yes'),
	array('Minotaur\\\'s Labyrinth'       ,'Centaur Forest'               ,'No' ,'Yes'),
	array('Sisyphus\\\' Hill'             ,'Centaur Forest'               ,'No' ,'Yes'),
	array('Tantalus\\\' Pool'             ,'Chiron\\\'s Cave'             ,'No' ,'Yes'),
	array('Temple of Zeus'                ,'Deianeira\\\'s Grave'         ,'No' ,'Yes'),
	array('Field of the Golden Fleece'    ,'Deianeira\\\'s Grave'         ,'No' ,'Yes'),
	array('Hercules\\\' Respite'          ,'Deianeira\\\'s Grave'         ,'No' ,'Yes'),
	array('Mount Olympus'                 ,'Deianeira\\\'s Grave'         ,'No' ,'Yes'),
	array('Hippolyta\\\'s Girdle'         ,'Echo\\\'s Glade'              ,'No' ,'Yes'),
	array('Minotaur\\\'s Labyrinth'       ,'Echo\\\'s Glade'              ,'No' ,'Yes'),
	array('Tartarus'                      ,'Elysian Fields'               ,'No' ,'Yes'),
	array('Realm of the 3 Furies'         ,'Elysian Fields'               ,'No' ,'Yes'),
	array('Hercules\\\' Respite'          ,'Field of the Golden Fleece'   ,'No' ,'Yes'),
	array('Jason\\\'s Kingdom'            ,'Field of the Golden Fleece'   ,'No' ,'Yes'),
	array('Tartarus'                      ,'Realm of the 3 Furies'        ,'No' ,'Yes'),
	array('Persephone\\\'s Garden'        ,'Realm of the 3 Furies'        ,'No' ,'Yes'),
	array('Mount Olympus'                 ,'Hercules\\\' Respite'         ,'No' ,'Yes'),
	array('Xena\\\'s Rest'                ,'Joxter\\\'s Retreat'          ,'No' ,'Yes'),
	array('Mount Olympus'                 ,'Realm of King Midas'          ,'No' ,'Yes'),
	array('Salmonius\\\' Scheme'          ,'Realm of King Midas'          ,'No' ,'Yes'),
	array('Sisyphus\\\' Hill'             ,'Minotaur\\\'s Labyrinth'      ,'No' ,'Yes'),
	array('Temple of Zeus'                ,'Mount Olympus'                ,'No' ,'Yes'),
	array('Tantalus\\\' Pool'             ,'Sisyphus\\\' Hill'            ,'No' ,'Yes'),
	array('Temple of Zeus'                ,'Tantalus\\\' Pool'            ,'No' ,'Yes'),
	array('Shrine to Hestia'              ,'Convent of the Vestal Virgins','No' ,'Yes'),
	array('Shrine to Hestia'              ,'Isle of Lesbos'               ,'No' ,'Yes'),
	array('Village of Psyche'             ,'Cupid\\\'s Cloud'             ,'No' ,'Yes'),
	array('Village of Psyche'             ,'Aphrodite\\\'s Beach'         ,'No' ,'Yes'),
	array('Chiron\\\'s Cave'              ,'Sisyphus\\\' Hill'            ,'No' ,'Yes'),
	array('Salmonius\\\' Scheme'          ,'Mount Olympus'                ,'No' ,'Yes'),
	array('Eastern Ocean'                 ,'Serina\\\'s Village'          ,'Yes','No' ),
	array('Eastern Ocean'                 ,'Strife\\\'s Cave'             ,'Yes','No' ),
	array('Eastern Ocean'                 ,'South Sea'                    ,'Yes','No' ),
	array('Eastern Ocean'                 ,'Minotaur\\\'s Labyrinth'      ,'Yes','No' ),
	array('Eastern Ocean'                 ,'Hippolyta\\\'s Girdle'        ,'Yes','No' ),
	array('Glittering Gulf'               ,'Village of Aeolus'            ,'Yes','No' ),
	array('Narcissus\\\' Reflection'      ,'Village of Aeolus'            ,'Yes','No' ),
	array('Sea of Dreams'                 ,'Alcmene\\\'s Village'         ,'Yes','No' ),
	array('Gulf of Chains'                ,'Alcmene\\\'s Village'         ,'Yes','No' ),
	array('Scholars Channel'              ,'Amazon Village'               ,'Yes','No' ),
	array('Eastern Ocean'                 ,'Amazon Village'               ,'Yes','No' ),
	array('Sea of Waves'                  ,'Amazon Village'               ,'Yes','No' ),
	array('Sea of Waves'                  ,'Aphrodite\\\'s Beach'         ,'Yes','No' ),
	array('Scholars Channel'              ,'Aphrodite\\\'s Beach'         ,'Yes','No' ),
	array('Sea of Arrows'                 ,'Aphrodite\\\'s Beach'         ,'Yes','No' ),
	array('Cecrops\\\' Channel'           ,'Argo\\\'s Pasture'            ,'Yes','No' ),
	array('South Sea'                     ,'Argo\\\'s Pasture'            ,'Yes','No' ),
	array('Forest of the Golden Hind'     ,'Cecrops\\\' Channel'          ,'Yes','No' ),
	array('Xena\\\'s Rest'                ,'Cecrops\\\' Channel'          ,'Yes','No' ),
	array('South Sea'                     ,'Cecrops\\\' Channel'          ,'Yes','No' ),
	array('Strife\\\'s Cave'              ,'Cecrops\\\' Channel'          ,'Yes','No' ),
	array('Poseidon\\\'s Curse'           ,'Cecrops\\\' Channel'          ,'Yes','No' ),
	array('Eastern Ocean'                 ,'Cecrops\\\' Channel'          ,'Yes','No' ),
	array('Narcissus\\\' Reflection'      ,'Centaur Forest'               ,'Yes','No' ),
	array('Ocean of Peace'                ,'Charon\\\'s Crossing'         ,'Yes','No' ),
	array('Narcissus\\\' Reflection'      ,'Chiron\\\'s Cave'             ,'Yes','No' ),
	array('Lover\\\'s Lane'               ,'Cupid\\\'s Cloud'             ,'Yes','No' ),
	array('Sea of Arrows'                 ,'Cupid\\\'s Cloud'             ,'Yes','No' ),
	array('Scholars Channel'              ,'Cupid\\\'s Cloud'             ,'Yes','No' ),
	array('Narcissus\\\' Reflection'      ,'Echo\\\'s Glade'              ,'Yes','No' ),
	array('Sea of Waves'                  ,'Echo\\\'s Glade'              ,'Yes','No' ),
	array('Ocean of Peace'                ,'Elysian Fields'               ,'Yes','No' ),
	array('Isle of Lesbos'                ,'Sea of Fire'                  ,'Yes','No' ),
	array('Tartarus'                      ,'Sea of Fire'                  ,'Yes','No' ),
	array('Convent of the Vestal Virgins' ,'Sea of Fire'                  ,'Yes','No' ),
	array('Lesbian Sea'                   ,'Sea of Fire'                  ,'Yes','No' ),
	array('Persephone\\\'s Garden'        ,'Sea of Fire'                  ,'Yes','No' ),
	array('Ocean of Peace'                ,'Sea of Fire'                  ,'Yes','No' ),
	array('Lover\\\'s Lane'               ,'Sea of Fire'                  ,'Yes','No' ),
	array('Sea of Tears'                  ,'Gabrielle\\\'s Village'       ,'Yes','No' ),
	array('Poseidon\\\'s Curse'           ,'Gabrielle\\\'s Village'       ,'Yes','No' ),
	array('Narcissus\\\' Reflection'      ,'Glittering Gulf'              ,'Yes','No' ),
	array('Realm of King Midas'           ,'Glittering Gulf'              ,'Yes','No' ),
	array('Sea of Waves'                  ,'Glittering Gulf'              ,'Yes','No' ),
	array('Prometheus\\\' Cliff'          ,'Gulf of Chains'               ,'Yes','No' ),
	array('Sea of Dreams'                 ,'Gulf of Chains'               ,'Yes','No' ),
	array('Hercules\\\' Respite'          ,'Gulf of Chains'               ,'Yes','No' ),
	array('Sea of Arrows'                 ,'Gulf of Chains'               ,'Yes','No' ),
	array('Salmonius\\\' Scheme'          ,'Gulf of Chains'               ,'Yes','No' ),
	array('Sea of Tears'                  ,'Depths of Hades'              ,'Yes','No' ),
	array('Poseidon\\\'s Curse'           ,'Forest of the Golden Hind'    ,'Yes','No' ),
	array('Western Ocean'                 ,'Jason\\\'s Kingdom'           ,'Yes','No' ),
	array('South Sea'                     ,'Joxter\\\'s Retreat'          ,'Yes','No' ),
	array('Isle of Lesbos'                ,'Lesbian Sea'                  ,'Yes','No' ),
	array('Western Ocean'                 ,'Lesbian Sea'                  ,'Yes','No' ),
	array('Ocean of Peace'                ,'Lesbian Sea'                  ,'Yes','No' ),
	array('Western Ocean'                 ,'Isle of Lesbos'               ,'Yes','No' ),
	array('Sea of Arrows'                 ,'Isle of Lesbos'               ,'Yes','No' ),
	array('Nestor\\\'s Kingdom'           ,'Lover\\\'s Lane'              ,'Yes','No' ),
	array('Poseidon\\\'s Curse'           ,'Lover\\\'s Lane'              ,'Yes','No' ),
	array('Sea of Arrows'                 ,'Lover\\\'s Lane'              ,'Yes','No' ),
	array('Sea of Tears'                  ,'Lover\\\'s Lane'              ,'Yes','No' ),
	array('Scholars Channel'              ,'Lover\\\'s Lane'              ,'Yes','No' ),
	array('Sea of Dreams'                 ,'Morpheus\\\' Palace'          ,'Yes','No' ),
	array('Western Ocean'                 ,'Morpheus\\\' Palace'          ,'Yes','No' ),
	array('Sea of Waves'                  ,'Narcissus\\\' Reflection'     ,'Yes','No' ),
	array('Scholars Channel'              ,'Nestor\\\'s Kingdom'          ,'Yes','No' ),
	array('Poseidon\\\'s Curse'           ,'Nestor\\\'s Kingdom'          ,'Yes','No' ),
	array('Tartarus'                      ,'Ocean of Peace'               ,'Yes','No' ),
	array('Sea of Tears'                  ,'Persephone\\\'s Garden'       ,'Yes','No' ),
	array('Xena\\\'s Rest'                ,'Poseidon\\\'s Curse'          ,'Yes','No' ),
	array('Sea of Tears'                  ,'Poseidon\\\'s Curse'          ,'Yes','No' ),
	array('Sea of Tears'                  ,'Sea of Fire'                  ,'Yes','No' ),
	array('Sea of Arrows'                 ,'Prometheus\\\' Cliff'         ,'Yes','No' ),
	array('Sea of Waves'                  ,'Prometheus\\\' Cliff'         ,'Yes','No' ),
	array('Sea of Waves'                  ,'Scholars Channel'             ,'Yes','No' ),
	array('Scholars Channel'              ,'Serina\\\'s Village'          ,'Yes','No' ),
	array('Convent of the Vestal Virgins' ,'Sea of Arrows'                ,'Yes','No' ),
	array('Sea of Dreams'                 ,'Sea of Arrows'                ,'Yes','No' ),
	array('Western Ocean'                 ,'Sea of Dreams'                ,'Yes','No' ),
	array('Lover\\\'s Lane'               ,'Convent of the Vestal Virgins','Yes','No' ),
	array('Prometheus\\\' Cliff'          ,'Glittering Gulf'              ,'Yes','No' ),
	array('Eastern Ocean'                 ,'Scholars Channel'             ,'Yes','No' ),
	array('Sea of Waves'                  ,'Sea of Arrows'                ,'Yes','No' ),
	array('Western Ocean'                 ,'Sea of Arrows'                ,'Yes','No' ),
	array('Village of Psyche (East Coast)','Cupid\\\'s Cloud'             ,'Yes','No' ),
	array('Village of Psyche (East Coast)','Aphrodite\\\'s Beach'         ,'Yes','No' ),
	array('Village of Psyche (East Coast)','Scholars Channel'             ,'Yes','No' ),
	array('Village of Psyche (West Coast)','Cupid\\\'s Cloud'             ,'Yes','No' ),
	array('Village of Psyche (West Coast)','Aphrodite\\\'s Beach'         ,'Yes','No' ),
	array('Village of Psyche (West Coast)','Sea of Arrows'                ,'Yes','No' ),
	array('Shrine to Hestia (North Coast)','Convent of the Vestal Virgins','Yes','No' ),
	array('Shrine to Hestia (North Coast)','Isle of Lesbos'               ,'Yes','No' ),
	array('Shrine to Hestia (North Coast)','Sea of Arrows'                ,'Yes','No' ),
	array('Shrine to Hestia (South Coast)','Convent of the Vestal Virgins','Yes','No' ),
	array('Shrine to Hestia (South Coast)','Isle of Lesbos'               ,'Yes','No' ),
	array('Shrine to Hestia (South Coast)','Sea of Fire'                  ,'Yes','No' )
);
	
foreach($bordersRawData as $borderRawRow)
{
	list($from, $to, $fleets, $armies)=$borderRawRow;
	InstallTerritory::$Territories[$from]->addBorder(InstallTerritory::$Territories[$to] ,$fleets,$armies);
	InstallTerritory::$Territories[$to]->addBorder(InstallTerritory::$Territories[$from] ,$fleets,$armies);
	
}
unset($bordersRawData);

InstallTerritory::runSQL($this->mapID);
InstallCache::terrJSON($this->territoriesJSONFile(),$this->mapID);

?>