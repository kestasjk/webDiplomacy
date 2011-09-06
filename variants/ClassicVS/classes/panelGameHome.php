<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the ClassicVS variant for webDiplomacy

	The ClassicVS variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The ClassicVS variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

// Use in all CSS-references the countryname instead of the countryID
class CustomCountries_panelGameHome extends panelGameHome
{
	
	function header()
	{
		return $this->Variant->css_restyle(parent::header());
	}
	
	function members()
	{
		return $this->Variant->css_restyle(parent::members());
	}
}

class ClassicVSVariant_panelGameHome extends CustomCountries_panelGameHome {}
