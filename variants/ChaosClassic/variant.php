<?php

/**
 * This variant lets players build on any SC they own, it demos using variants
 * to change what orders are permitted.
 */
class ChaosClassic extends BuildAnywhereVariant {
	public $id=24;
	//public $mapID=1;
	public $name='ChaosClassic';
	public $fullName='Chaos Classic';
	public $description='Chaos Classic';

	public $countries=array(
		'Edinburgh',
		'Liverpool',
		'London',
		'Portugal',
		'Spain',
		'Tunis',
		'Naples',
		'Rome',
		'Venice',
		'Greece',
		'Serbia',
		'Bulgaria',
		'Rumania',
		'Constantinople',
		'Smyrna',
		'Ankara',
		'Sevastopol',
		'Warsaw',
		'Moscow',
		'St. Petersburg',
		'Sweden',
		'Norway',
		'Denmark',
		'Kiel',
		'Berlin',
		'Munich',
		'Holland',
		'Belgium',
		'Brest',
		'Paris',
		'Marseilles',
		'Vienna',
		'Trieste',
		'Budapest'
		)
	//public $author='Avalon Hill';
	
	public function __construct() {
		parent::__construct();

		// Order validation code, changed to validate builds on non-home SCs
		$this->variantClasses['userOrderBuilds'] = 'BuildAnywhere';

		// Order interface/generation code, changed to add javascript in resources which makes non-home SCs an option
		$this->variantClasses['OrderInterface'] = 'BuildAnywhere';
			// Altered to disable the creation of starting units
		$this->variantClasses['adjudicatorPreGame'] = 'ChaosClassic';

		// Altered to change the starting order of a game's phases; Spring 1901 Pre-game|Unit-placing|Diplomacy|Retreats ->
		$this->variantClasses['processGame'] = 'CustomStart';
		
		$this->variantClasses['drawMap'] = 'ChaosClassic';

	}
}
}

?>