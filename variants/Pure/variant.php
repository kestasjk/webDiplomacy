<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Pure variant for webDiplomacy

	The Pure variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Pure variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Rules for the PURE Variant by Danny Loeb:
	http://www.variantbank.org/results/rules/p/pure.htm
	
	This is Version: 1.7.3
	
	Changelog:
	1.0: initial release
	1.1: fixed wrong colored sc on smallmap
	1.5: new webdip v.97 code
	1.6: minor tweaks
	1.7: fixed: forgot to add the build-anywhere code
	1.7.1: small adjustments for the new variant.php code
	1.7.3: updated the build anywhere code
	1.7.4: small map update
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class PureVariant extends WDVariant {
	public $id         =11;
	public $mapID      =11;
	public $name       ='Pure';
	public $fullName   ='Pure';
	public $description='A simple traditional variant of diplomacy';
	public $author     ='Danny Loeb';
	public $adapter    ='Oliver Auth';
	public $version    ='1.7.4';
	public $homepage   ='http://www.variantbank.org/results/rules/p/pure.htm';

	public $countries=array('Austria','England','France','Germany','Italy','Russia','Turkey');
	
	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Pure';
		$this->variantClasses['adjudicatorPreGame'] = 'Pure';
		$this->variantClasses['userOrderBuilds']    = 'Pure';
		$this->variantClasses['OrderInterface']     = 'Pure';
		$this->variantClasses['processOrderBuilds'] = 'Pure';

	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1901);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1901);
		};';
	}
}

?>