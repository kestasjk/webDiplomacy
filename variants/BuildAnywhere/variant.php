<?php

/**
 * This variant lets players build on any SC they own, it demos using variants
 * to change what orders are permitted.
 */
class BuildAnywhereVariant extends ClassicVariant {
	public $id=5;
	//public $mapID=1;
	public $name='BuildAnywhere';
	public $fullName='Classic - Build anywhere';
	public $description='The same as the standard map, except you can build on all supply centers you own.';
	//public $author='Avalon Hill';

	//public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');
	//public $variantClasses=array();

	public function __construct() {
		parent::__construct();

		// Order validation code, changed to validate builds on non-home SCs
		$this->variantClasses['userOrderBuilds'] = 'BuildAnywhere';

		// Order interface/generation code, changed to add javascript in resources which makes non-home SCs an option
		$this->variantClasses['OrderInterface'] = 'BuildAnywhere';
	}
}

?>