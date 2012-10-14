<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Haven variant for webDiplomacy

	The Haven variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Haven variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class HavenVariant_drawMap extends drawMap {

	protected $countryColors = array(
		 0=> array(238,205,155), // Neutral
		 1=> array(  0,  0,254), // Archers
		 2=> array(212,212,128), // Barbarians
		 3=> array(148,102, 48), // Centaurs
		 4=> array(  0,127,127), // Dwarves
		 5=> array(  0,255,255), // Elves
		 6=> array(255,255,  0), // Faeries
		 7=> array(126,126,  0), // Gnomes
		 8=> array(255,102, 51), // Hobbits
		 9=> array( 84, 84, 84), // Knights
		10=> array(  0,126,  0), // Leprechauns
		11=> array(255,  0,255), // Magicians
		12=> array(255,  0,  0), // Nomads
		13=> array(205,147,102), // Ogres
		14=> array(171,171,171), // Pirates
		15=> array(252,213,211), // Rogues
		16=> array(128,128,252), // Samurai
		17=> array(  0,255,  0), // Trolls
		18=> array(253,252,252), // Undead
		19=> array(128,  0,128), // Wizards
	);
	
	protected $underworld=array(
		'Cave of Ordeals', 'Dragons Teeth Mtns', 'Hoarluk','Mount Nimro', 'Nowwhat', 'Hidden Grotto',
		'Tymwyvenne', 'Yggdrasil', 'Khaz Modan', 'Knurremurre', 'Carpantha', 'Hollow Earth',
		'Undermountain', 'Hall of Echoes', 'Twisted Tunnels', 'Caverns of the Snow Witch',
		'Spirit Pond', 'Venatori Umbrarum', 'Ancient Necropolis', 'Temple of Doom', 'Diamond Mines'
	);
	
	protected $warp_A=array(
		'Abby Normal', 'Allerleirauh', 'Bikini Bottom', 'Cave of Ordeals', 'High Seas', 'Thon Thalas',
		'Way the Heck', 'Ancient Necropolis', 'Hall of Echoes', 'Anvard', 'Crystal Lake', 'Immoren',
		'Cave of Ordeals (Underworld)', 'Yggdrasil'
	);
	protected $warp_B=array(
		'Grief Reef', 'Riku', 'Mermaids Lagoon', 'Far Far Away', 'Never Never Land', 'Newa River',
		'Sea Of Fallen Stars', 'Enchanted Isles', 'Babel Beach', 'Fjord', 'Magrathea', 'Venatori Umbrarum',
		'Cathal', 'Arctic Barrens', 'Sleepy Hollow', 'Hidden Grotto'
	);
	
	public function __construct($smallmap)
	{
		$this->mapID = MAPID;
		ini_set('memory_limit',"35M");

		$this->smallmap = true;
		$this->loadTerritories();
		$this->loadImages();
		$this->loadColors();
		$this->loadFont();
		$this->loadOrderArrows();
		$this->smallmap = false;
		$this->army['height'] = 18;
		$this->army['width']  = 18;
		
	}

	public function drawStandoff($terrName)
	{
		$this->smallmap=true;
		parent::drawStandoff($terrName);
		$this->smallmap=false;
	}
	
	public function drawFailure(array $from, array $to)
	{
		$this->smallmap=true;
		parent::drawFailure($from, $to);
		$this->smallmap=false;
	}

	// Arrays for the custom icons:
	protected $unit_c =array(); // An array to store the owner of each territory
	protected $army_c =array(); // Custom army icons
	protected $fleet_c=array(); // Custom fleet icons

	// Load the custom icons and set the transparency too...
	protected function loadImages()
	{
		$this->map      = $this->loadImage('variants/Haven/resources/map_1.png');
		$this->map2     = $this->loadImage('variants/Haven/resources/map_2.png');
		$this->standoff = $this->loadImage('images/icons/cross.png');
		$this->mapNames = 'variants/Haven/resources/mapNames.png';

		for ($i=0; $i<count($this->countryColors); $i++)
		{
			$this->army_c[$i] = $this->loadImage('variants/Haven/resources/army_country_'.$i.'.png');
			$this->fleet_c[$i] = $this->loadImage('variants/Haven/resources/fleet_country_'.$i.'.png');
		}
	}
	
	// Combine the 2 maps.
	public function write($filename)
	{
		$w = $this->map['width'];
		$h = $this->map['height'];
		$im = imagecreate($this->map['width'], $this->map['height']);
		imagecopyresampled($im, $this->map2['image'], 0, 0, 0, 0, $w, $h, $w, $h);
		imagecopyresampled($im, $this->map['image'], 0, 0, 0, 0, $w, $h, $w, $h);
		imagetruecolortopalette($im, true, 256);
		imagepng($im, $filename);
		imagedestroy($im);
	}
	
	// Save the countryID for every colored Territory (and their coasts)
	public function colorTerritory($terrID, $countryID)
	{
		$terrName=$this->territoryNames[$terrID];
		if (strpos($terrName,')') === false)
		{
			$this->unit_c[$terrID]=$countryID;
			$this->unit_c[array_search($terrName. " (North Coast)" ,$this->territoryNames)]=$countryID;
			$this->unit_c[array_search($terrName. " (East Coast)"  ,$this->territoryNames)]=$countryID;
			$this->unit_c[array_search($terrName. " (South Coast)" ,$this->territoryNames)]=$countryID;
			$this->unit_c[array_search($terrName. " (West Coast)"  ,$this->territoryNames)]=$countryID;
			$this->unit_c[array_search($terrName. " (Underworld)"  ,$this->territoryNames)]=$countryID;
			$this->unit_c[array_search($terrName. " (Underworld) (2)",$this->territoryNames)]=$countryID;
			$this->unit_c[array_search($terrName. " (2)"           ,$this->territoryNames)]=$countryID;
			list($x, $y) = $this->territoryPositions[$terrID];

			$territoryColor = imagecolorat($this->map2['image'], $x, $y);
			list($r, $g, $b) = $this->countryColors[$countryID];

			imagecolorset($this->map2['image'], $territoryColor, $r, $g, $b);
		}
	}
	
	// Overwrite the country if a unit needs to draw a flag (and don't draw the flag) -> we use custom icons instead
	public function countryFlag($terrName, $countryID)
	{
		$this->unit_c[$terrName]=$countryID;
		
		$flagBlackback = $this->color(array(0, 0, 0));

		$flagColor = $this->color($this->countryColors[$countryID]);

		list($x, $y) = $this->territoryPositions[$terrName];

		$coordinates = array(
			'top-left'     => array( 'x'=>$x-15, 'y'=>$y+7 ),
			'bottom-right' => array( 'x'=>$x+15, 'y'=>$y+15 )
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
	
	public function addUnit($terrName, $unitType)
	{
		// Draw the custom icons:
		$this->army  = $this->army_c[$this->unit_c[$terrName]];
		$this->fleet = $this->fleet_c[$this->unit_c[$terrName]];
		parent::addUnit($terrName, $unitType);
		
		// Add 2nd icons for the underworld gateways:
		if (in_array($this->territoryNames[$terrName].' (Underworld)' ,$this->territoryNames))
			parent::addUnit(array_search($this->territoryNames[$terrName].' (Underworld)',$this->territoryNames), $unitType);
			
		$this->army['height'] = 18;
		$this->army['width']  = 18;
			
	}


	// All order arrows needs adjustment for the underworld-map and for the warparound
	public function drawMove($fromTerrID, $toTerrID, $success)
	{
		list($from, $to, $from2, $to2)=$this->adjustArrows($fromTerrID,$toTerrID);
		parent::drawMove($from, $to, $success);
		if ($from2 != 0)
			parent::drawMove($from2, $to2, $success);
	}
	
	public function drawRetreat($fromTerrID, $toTerrID, $success)
	{
		list($from, $to, $from2, $to2)=$this->adjustArrows($fromTerrID,$toTerrID);
		parent::drawRetreat($from, $to, $success);
		if ($from2 != 0)
			parent::drawRetreat($from2, $to2, $success);
	}
	
	public function drawSupportHold($fromTerrID, $toTerrID, $success)
	{
		list($from, $to, $from2, $to2)=$this->adjustArrows($fromTerrID,$toTerrID);
		parent::drawSupportHold($from, $to, $success);
		if ($from2 != 0)
			parent::drawSupportHold($from2, $to2, $success);
	}
	
	public function drawSupportMove($terrID, $fromTerrID, $toTerrID, $success)
	{
		list($from, $to, $terr2, $to2)=$this->adjustArrows($fromTerrID,$toTerrID,$terrID);
		parent::drawSupportMove($terrID, $from, $to, $success);
		if ($terr2 != 0)
			parent::drawSupportMove($terr2, $to2, $to2, $success);
	}
	
	private function adjustArrows($fromID, $toID, $terrID=0)
	{
		$fromName = $this->territoryNames[$fromID];
		$toName   = $this->territoryNames[$toID];
		if ($terrID > 0)
			$terrName = $this->territoryNames[$terrID];
			
		// Special case support-move: move is not drawn in the underworld, but supporting unit is in the underworld, and vice versa
		if ($terrID != 0)
		{	
			if (( in_array($terrName, $this->underworld) && !in_array($fromName, $this->underworld) && in_array($toName,$this->underworld)) ||
				(!in_array($terrName, $this->underworld) && in_array($fromName, $this->underworld) && in_array($toName,$this->underworld)))
			
			{
				if (in_array($terrName, $this->underworld))
				{
					$toName .= ' (Underworld)';
					$toID=array_search($toName,$this->territoryNames);				
				}

				$terrID2 = $toID2 = 0;
				if ((in_array($terrName, $this->warp_A) && in_array($toName, $this->warp_B)) || (in_array($terrName, $this->warp_B) && in_array($toName, $this->warp_A)))
				{
					$toID2    = $toID;
					$toID     = array_search($toName.  ' (2)',$this->territoryNames);
					$terrID2  = array_search($terrName.' (2)',$this->territoryNames);					
				}

				return array ($toID,$toID,$terrID2,$toID2);
			}
			
		}
			
		// Adjust the fromTerrID and toTerrID for the extra underworld map
		if (in_array($fromName, $this->underworld) && in_array($toName, $this->underworld))
		{
			if (in_array($fromName.' (Underworld)' ,$this->territoryNames))
			{
				$fromName .= ' (Underworld)';
				$fromID=array_search($fromName,$this->territoryNames);
			}
			if (in_array($toName.' (Underworld)' ,$this->territoryNames))
			{
				$toName .= ' (Underworld)';
				$toID=array_search($toName,$this->territoryNames);
			}
		}
		
		// Warp
		$fromID2 = $toID2 = 0;
		if ((in_array($fromName, $this->warp_A) && in_array($toName, $this->warp_B)) || (in_array($fromName, $this->warp_B) && in_array($toName, $this->warp_A)))
		{
			$toID2    = $toID;
			$toID     = array_search($toName.  ' (2)',$this->territoryNames);
			$fromID2  = array_search($fromName.' (2)',$this->territoryNames);
			
			if ( $terrID !=0 ) // Support-Move drawn to the nearest of the 2 move-arrows
			{
				list($x     ,$y     )=$this->territoryPositions[$terrID];
				list($fromx1,$fromy1)=$this->territoryPositions[$fromID];
				list($tox1  ,$toy1  )=$this->territoryPositions[$toID];
				list($fromx2,$fromy2)=$this->territoryPositions[$fromID2];
				list($tox2  ,$toy2  )=$this->territoryPositions[$toID2];
				
				$diff1 = abs($x-$fromx1)+abs($y-$fromy1)+abs($x-$tox2)+abs($y-$toy2);
				$diff2 = abs($x-$fromx2)+abs($y-$fromy2)+abs($x-$tox1)+abs($y-$toy1);
				
				if ($diff1 < $diff2)
				{
					$fromID = $fromID2;
					$toID   = $toID2;
				}	
			}
		}		
		
		return array($fromID, $toID, $fromID2, $toID2);
	}

	protected function color(array $color, $image=false)
	{
		if ( ! is_array($image) )
			$image = $this->map;
		
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