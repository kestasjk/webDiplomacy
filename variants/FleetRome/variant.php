<?php

class FleetRomeVariant extends ClassicVariant {
	public $id=3;
	//public $mapID=1;
	public $name='FleetRome';
	public $fullName='Classic - With fleet in Rome';
	public $description='The same as the standard map, except initially Rome has a fleet instead of an army.';
	//public $author='Avalon Hill';

	//public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');
	//public $variantClasses=array();

	public function __construct() {
		parent::__construct();

		// Contains the starting-unit alteration
		$this->variantClasses['adjudicatorPreGame'] = 'FleetRome';
	}
}

?>