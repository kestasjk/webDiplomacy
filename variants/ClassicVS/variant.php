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

	If you have questions or suggestions send me a mail: Oliver.Auth@rhoen.de

	---
	
	Changelog:
	1.0:    first release
	1.0.1:  fixed a problem with the builds
	1.0.2:  fixed a problem with panelGame
	1.0.3:  chat now colored
	1.0.5:  fixed a issue loading the new country-array, game now assigns England as random country too..
	1.0.10: Big code cleanup.
	1.0.11: Fixed a bug in the build-phase
	1.0.12: converted rules.html in correct English... :-)
	1.0.13: bug in adjudicatorPreGame fixed
	1.0.14: bug in processOrderBuilds fixed
	1.0.15: Pot limitations now handled by the webdip-code
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicVSVariant extends WDVariant {
	public $id         =42;
	public $mapID      =42;
	public $name       ='ClassicVS';
	public $fullName   ='Classic - Pick your countries';
	public $description='The standard Diplomacy map of Europe, but you can choose what countries you want to be playable.';
	public $adapter    ='Oliver Auth';
	public $version    ='1.0.15';

	public $countries=array('Player_1', 'Player_2', 'Player_3', 'Player_4', 'Player_5', 'Player_6', 'Player_7');

	public function __construct() {
		parent::__construct();

		// Custom countries to choose
		$this->variantClasses['adjudicatorPreGame'] = 'ClassicVS';
		$this->variantClasses['Chatbox']            = 'ClassicVS';
		$this->variantClasses['drawMap']            = 'ClassicVS';
		$this->variantClasses['OrderInterface']     = 'ClassicVS';
		$this->variantClasses['panelGame']          = 'ClassicVS';
		$this->variantClasses['panelGameBoard']     = 'ClassicVS';
		$this->variantClasses['panelGameHome']      = 'ClassicVS';
		$this->variantClasses['processOrderBuilds'] = 'ClassicVS';
		$this->variantClasses['userOrderBuilds']    = 'ClassicVS';

	}
	
	public function __call($name, $args)
	{
		// If we call the Members-objects adjust the countries first.
		if (($name == 'Members') || ($name == 'processMembers') || ($name == 'panelMembers') || ($name == 'panelMembersHome'))
		{
			$this->countries = array();	

			if (preg_match('#\((.*?)\)#U', $args[0]->name, $match) != 0)
			{
				$country=$match[1];
				if (strpos($country,'E') !== false) $this->countries[] = 'England';
				if (strpos($country,'F') !== false) $this->countries[] = 'France';
				if (strpos($country,'I') !== false) $this->countries[] = 'Italy';
				if (strpos($country,'G') !== false) $this->countries[] = 'Germany';
				if (strpos($country,'A') !== false) $this->countries[] = 'Austria';
				if (strpos($country,'T') !== false) $this->countries[] = 'Turkey';
				if (strpos($country,'R') !== false) $this->countries[] = 'Russia';

				for ($i=0; $i<substr_count($country, '?'); $i++)
					$this->countries[] = 'Random';

			}
			if ((count($this->countries) < 2) || (count($this->countries) > 7))
				$this->countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');		
		}

		return parent::__call($name, $args);
	}
	
	public function css_restyle($text)
	{
		foreach ($this->countries as $id => $name)
		{
			$text=str_replace('class="country'      .($id + 1),'class="country'      .$name, $text);
			$text=str_replace('class="occupationBar'.($id + 1),'class="occupationBar'.$name, $text);
			$text=str_replace('class="right country'.($id + 1),'class="right country'.$name, $text);
		}
		return $text;
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