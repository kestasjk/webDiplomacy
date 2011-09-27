<?php
/*
	Copyright (C) 2011 Milan Mach

	This file is part of the Hussite variant for webDiplomacy

	The Hussite variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Hussite variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

include('coords.txt');

class MoveFlags_drawMap extends Coords_drawMap
{
	public function countryFlag($terrID, $countryID)
	{
		$flagBlackback = $this->color(array(0, 0, 0));
		$flagColor = $this->color($this->countryColors[$countryID]);
		
		list($x, $y) = $this->territoryPositions[$terrID];

		$coordinates = array(
			'top-left' => array( 
						 'x'=>$x-intval($this->fleet['width']/2+1),
						 'y'=>$y-intval($this->fleet['height']/2+1)
						 ),
			'bottom-right' => array(
						 'x'=>$x+intval($this->fleet['width']/2+1),
						 'y'=>$y+intval($this->fleet['height']/2-1)
						 )
		);

		imagefilledrectangle($this->map['image'],
			$coordinates['top-left']['x'], $coordinates['top-left']['y'],
			$coordinates['bottom-right']['x'], $coordinates['bottom-right']['y'],
			$flagBlackback);
		imagefilledrectangle($this->map['image'],
			$coordinates['top-left']['x']+1, $coordinates['top-left']['y']+1,
			$coordinates['bottom-right']['x']-1, $coordinates['bottom-right']['y']-1,
			$flagColor);
	}
}

class NeutralScBox_drawMap extends MoveFlags_drawMap
{
	
	/**
	* An array containing the neutral support-center icon image resource, and its width and height.
	* $image['image'],['width'],['height']
	* @var array
	**/
	protected $sc=array();
	
	/**
	* An array containing the information if one of the first 9 territories 
	* still has a neutral support-center (So we might not need to draw a flag)
	**/
	protected $nsc=array();

	protected function loadImages()
	{
		parent::loadImages();
		$this->sc = $this->loadImage('variants/Hussite/resources/'.($this->smallmap ? 'small' : '').'sc.png');	
	}

	/**
	* There are some territories on the map that belong to a country but have a supply-center
	* that is considered "neutral".
	* They are set to owner "Neutral" in the installation-file, so we need to check if they are
	* still "neutal" and paint the territory in the color of the country they "should" belong to.
	* After that draw the "Neutral-SC-overloay" on the map.
	**/
	public function ColorTerritory($terrID, $countryID)
	{
		parent::ColorTerritory($terrID, $countryID);

		if ((isset($this->nsc_info[$terrID][0])) && $countryID==0)
		{
			parent::ColorTerritory($terrID, $this->nsc_info[$terrID][0]);
			$this->nsc[$terrID]=$countryID;
			$sx=($this->smallmap ? $this->nsc_info[$terrID][1] : $this->nsc_info[$terrID][3]);
			$sy=($this->smallmap ? $this->nsc_info[$terrID][2] : $this->nsc_info[$terrID][4]);
			$this->putImage($this->sc, $sx, $sy);
		}
	}
		
	/* No need to draw the country flags for "neural-SC-territories if they get occupied by 
	** the country they should belong to
	*/
	public function countryFlag($terrID, $countryID)
	{
		if (isset($this->nsc[$terrID]) && ($this->nsc[$terrID] == $countryID)) return;
		parent::countryFlag($terrID, $countryID);
	}

}

class HussiteVariant_drawMap extends NeutralScBox_drawMap {

    public function __construct($smallmap)
	{
		// LargeMap is too big, so up the memory-limit
		parent::__construct($smallmap);
		if ( !$this->smallmap )
			ini_set('memory_limit',"22M");
	}

	protected $countryColors = array(
		0 => array(226, 198, 158), // Neutral
		1 => array(176, 175, 174), // Bavaria
		2 => array(232, 232,  77), // Catholic Landfrieden
		3 => array(153, 104, 110), // Hungary
		4 => array(237, 135, 150), // Kingdom of Poland
		5 => array(140, 103,  76), // Margraviate of Brandenburg
		6 => array( 53, 130, 102), // Orebites
		7 => array(115, 123, 209), // Praguers
		8 => array(186, 171, 235), // Saxony
		9 => array(255, 173,  79), // Taborites
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/Hussite/resources/smallmap.png',
				'army'=>'variants/Hussite/resources/smallarmy.png',
				'fleet'=>'variants/Hussite/resources/smallfleet.png',
				'names'=>'variants/Hussite/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/Hussite/resources/map.png',
				'army'=>'variants/Hussite/resources/army.png',
				'fleet'=>'variants/Hussite/resources/fleet.png',
				'names'=>'variants/Hussite/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
	
	// The icons have a transparent background already
	protected function setTransparancies() {}

	protected function color(array $color, $image=false)
	{
		if ( ! is_array($image) )
		{
			$image = $this->map;
		}
		
		list($r, $g, $b) = $color;
		
		$colorRes = imagecolorexact($image['image'], $r, $g, $b);
		if ($colorRes == -1)
		{
		$colorRes = imageColorAllocate($image['image'], $r, $g, $b);
			if (!$colorRes)
				$colorRes = imageColorClosest($image['image'], $r, $g, $b);
		}
		
		return $colorRes; 
	}

}

?>