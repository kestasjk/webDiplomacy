<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class HabelyaVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Ruins of Holgii' => array('Dino' => 'Army','Great Holgii' => 'Army','South Holgii (South Coast)' => 'Fleet'),
		'Gernavia'        => array('Western Territory' => 'Army','Gernavian Colony' => 'Fleet','Great Magnii' => 'Army','Levio (East Coast)' => 'Fleet'),
		'Old Bramia'      => array('Olia' => 'Army','Nerr' => 'Fleet','Deoveland' => 'Army'),
		'Elenian Empire'  => array('Turri' => 'Army','Elenia' => 'Fleet','Ecce' => 'Fleet'),
		'Socialist Glock' => array('Glasch' => 'Army','Golni' => 'Army','Bulgi' => 'Army'),
		'Kingdom of Saltz'=> array('Utuk (West Coast)' => 'Fleet','Salitoss' => 'Army','Delim' => 'Army'),
		'The Hacklers'    => array('Gi' => 'Fleet','Grantuck' => 'Fleet','Tsumi' => 'Army'),
		'Trylika'         => array('Lesser Etvost' => 'Army','Trylia' => 'Army','Bagnikki' => 'Fleet','New Trylia' => 'Fleet')
	);

}

?>
