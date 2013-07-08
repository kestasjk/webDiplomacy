<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the ClassicGreyPress variant for webDiplomacy

	The ClassicGreyPress variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The ClassicGreyPress variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---


	Changelog:
	1.0: initial release
	1.1: PublicPress only works now with GreyPress too.

*/

class ClassicGreyPressVariant extends ClassicVariant {
	public $id         = 50;
	public $mapID      = 1;
	public $name       = 'ClassicGreyPress';
	public $fullName   = 'Classic - GreyPress';
	public $description= 'The same as the standard map, except you can send anonymous messages.';
	public $version    = '1.1';

	public function __construct() {
		parent::__construct();
		$this->variantClasses['Chatbox'] = 'ClassicGreyPress';
	}
}

?>