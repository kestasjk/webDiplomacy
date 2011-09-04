<?php

class CustomStartVariant extends FleetRomeVariant {
	public $id=4;
	//public $mapID=1;
	public $name='CustomStart';
	public $fullName='Classic with a custom start';
	public $description='The same as the standard map, except the first phase is Builds, allowing a customized start.';
	//public $author='Avalon Hill';

	//public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');
	//public $variantClasses=array();

	public function __construct() {
		parent::__construct();

		// Altered to disable the creation of starting units
		$this->variantClasses['adjudicatorPreGame'] = 'CustomStart';

		// Altered to change the starting order of a game's phases; Spring 1901 Pre-game|Unit-placing|Diplomacy|Retreats ->
		$this->variantClasses['processGame'] = 'CustomStart';
	}
}

?>