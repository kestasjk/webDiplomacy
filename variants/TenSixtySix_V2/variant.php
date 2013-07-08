<?php
/*
	Copyright (C) 2012 Oliver Auth

	This file is part of the 1066 (V2.0) variant for webDiplomacy

	The 1066 (V2.0) variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The 1066 (V2.0) variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	2.0: initial release

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class TenSixtySix_V2Variant extends TenSixtySixVariant {
	public $id         = 85;
	public $mapID      = 85;
	public $name       = 'TenSixtySix_V2';
	public $fullName   = '1066 (V2.0)';
	public $version    = '2.0';

	public function __construct() {
		parent::__construct();
		
		// Setup
		$this->variantClasses['adjudicatorPreGame'] = 'TenSixtySix_V2';
		$this->variantClasses['drawMap']            = 'TenSixtySix_V2';
	}
	
}

?>
