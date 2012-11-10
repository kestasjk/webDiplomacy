<?php
defined('IN_CODE') or die('This script can not be run by itself.');

class AfricaVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'DRC'           => array('Kinshasa'=>'Fleet'  , 'Lubumbashi'=>'Army'  , '&Eacute;quateur'=>'Army'),
		'Egypt'         => array('Cairo'=>'Fleet'     , 'Alexandria'=>'Army'  , 'Luxor'=>'Army'),
		'Ethiopia'      => array('Amhara'=>'Army'     , 'Addis Ababa'=>'Army' , 'Dire Dawa'=>'Army'),
		'Madagascar'    => array('Comoros'=>'Fleet'   , 'Mauritius'=>'Fleet'  , 'Antananarivo'=>'Fleet'),
		'Mali'          => array('Kayes'=>'Army'      , 'Sikasso'=>'Army'     , 'Bamako'=>'Army'),
		'Morocco'       => array('Casablanca'=>'Fleet', 'Marrakech'=>'Army'   , 'Eastern Morocco'=>'Army'),
		'Nigeria'       => array('Lagos'=>'Fleet'     , 'Abuja'=>'Army'       , 'Kano'=>'Army'),
		'South Africa'  => array('Cape Town'=>'Fleet' , 'Johannesburg'=>'Army', 'Durban'=>'Army'),
		'Neutral units' => array('Tripoli'=>'Army'    , 'Mogadishu'=>'Fleet'  , 'Zimbabwe'=>'Army', 'Gao'=>'Army', 'Timbuktu'=>'Army', 'Kivu'=>'Army', 'Tunisia'=>'Army'),
	);

}