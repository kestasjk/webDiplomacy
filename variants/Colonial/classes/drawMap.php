<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Colonial variant for webDiplomacy

	The Colonial variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Colonial variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ColonialVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), /* Neutral */
		1 => array(239, 196, 228), /* Britain */
		2 => array(196, 143, 133), /* China   */
		3 => array(121, 175, 198), /* France  */
		4 => array(160, 138, 117), /* Holland */
		5 => array(164, 196, 153), /* Japan   */
		6 => array(168, 126, 159), /* Russia  */
		7 => array(234, 234, 175)  /* Turkey  */
	);
	
	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'army'     =>l_s('contrib/smallarmy.png')                        ,
				'fleet'    =>l_s('contrib/smallfleet.png')                       ,
				'names'    =>l_s('variants/Colonial/resources/smallmapNames.png'),
				'standoff' =>l_s('images/icons/cross.png')                       ,
				'map'      =>l_s('variants/Colonial/resources/smallmap.png')     ,
			);
		}
		else
		{
			return array(
				'army'     =>l_s('contrib/army.png'),
				'fleet'    =>l_s('contrib/fleet.png'),
				'names'    =>l_s('variants/Colonial/resources/mapNames.png'),
				'standoff' =>l_s('images/icons/cross.png'),
				'map'      =>l_s('variants/Colonial/resources/map.png'),
			);
		}
	}
	
	/**
	 * An array containing the neutral support-center icon image resource, and its width and height.
	 * $image['image'],['width'],['height']
	 * @var array
	 */
	protected $sc=array();
	
	/**
	 * An array containing the information if one of the first 9 territories 
	 * still has a neutral support-center (So we might not need to draw a flag)
	 */
	protected $nsc=array();

	protected function loadImages()
	{
		parent::loadImages();

		if( $this->smallmap )
			$this->sc = $this->loadImage(l_s('variants/Colonial/resources/sc_small.png'));
		else
			$this->sc = $this->loadImage(l_s('variants/Colonial/resources/sc_large.png'));
	}

	protected function setTransparancies()
	{
		parent::setTransparancies();
		$this->setTransparancy($this->sc);
	}	
	

	/* There are 8 territories on the map that belong to a country but have a supply-center that is considered
	** "neutral"
	** They are set to owner "Neutral" in the installation-file, so we need to check if they are still
	** "neutal" and paint the territory in the color of the country they "should" belong to.
	** after that draw the "Neutral-SC-overloay" on the map.
	*/
	public function ColorTerritory($terrID, $countryID)	
	{
		if     ($terrID == 1 && $countryID == 0 && $this->smallmap)  { $sx= 527; $sy= 50; $countryID=6; }
		elseif ($terrID == 1 && $countryID == 0 && !$this->smallmap) { $sx=1325; $sy=133; $countryID=6; }
		elseif ($terrID == 2 && $countryID == 0 && $this->smallmap)  { $sx= 221; $sy=101; $countryID=6; }
		elseif ($terrID == 2 && $countryID == 0 && !$this->smallmap) { $sx= 561; $sy=260; $countryID=6; }
		elseif ($terrID == 3 && $countryID == 0 && $this->smallmap)  { $sx= 338; $sy=214; $countryID=1; }
		elseif ($terrID == 3 && $countryID == 0 && !$this->smallmap) { $sx= 854; $sy=548; $countryID=1; }
		elseif ($terrID == 4 && $countryID == 0 && $this->smallmap)  { $sx= 268; $sy=149; $countryID=1; }
		elseif ($terrID == 4 && $countryID == 0 && !$this->smallmap) { $sx= 679; $sy=381; $countryID=1; }
		elseif ($terrID == 5 && $countryID == 0 && $this->smallmap)  { $sx= 301; $sy=293; $countryID=1; }
		elseif ($terrID == 5 && $countryID == 0 && !$this->smallmap) { $sx= 749; $sy=751; $countryID=1; }
		elseif ($terrID == 6 && $countryID == 0 && $this->smallmap)  { $sx= 347; $sy= 73; $countryID=2; }
		elseif ($terrID == 6 && $countryID == 0 && !$this->smallmap) { $sx= 876; $sy=191; $countryID=2; }
		elseif ($terrID == 7 && $countryID == 0 && $this->smallmap)  { $sx= 289; $sy=132; $countryID=2; }
		elseif ($terrID == 7 && $countryID == 0 && !$this->smallmap) { $sx= 726; $sy=336; $countryID=2; }
		elseif ($terrID == 8 && $countryID == 0 && $this->smallmap)  { $sx= 414; $sy=172; $countryID=2; }
		elseif ($terrID == 8 && $countryID == 0 && !$this->smallmap) { $sx=1040; $sy=440; $countryID=2; }
		elseif ($terrID == 9 && $countryID == 2 && $this->smallmap)  { $sx= 446; $sy=230;               }
		elseif ($terrID == 9 && $countryID == 2 && !$this->smallmap) { $sx=1133; $sy=588;               }
		
		parent::ColorTerritory($terrID, $countryID);
		$this->nsc[$terrID]=0;

		if (isset($sx))
		{
			$this->putImage($this->sc, $sx, $sy);
			$this->nsc[$terrID]=1;
		}
		
	}
	
	/* No need to draw the country flags for "neural-SC-territories if they get occupied by 
	** the country they should belong to
	*/
	public function countryFlag($terrID, $countryID)
	{
		if (($this->nsc[1] == 1) && ($terrID == 1) && ( $countryID == 6)) return;
		if (($this->nsc[2] == 1) && ($terrID == 2) && ( $countryID == 6)) return;
		if (($this->nsc[3] == 1) && ($terrID == 3) && ( $countryID == 1)) return;
		if (($this->nsc[4] == 1) && ($terrID == 4) && ( $countryID == 1)) return;
		if (($this->nsc[5] == 1) && ($terrID == 5) && ( $countryID == 1)) return;
		if (($this->nsc[6] == 1) && ($terrID == 6) && ( $countryID == 2)) return;
		if (($this->nsc[7] == 1) && ($terrID == 7) && ( $countryID == 2)) return;
		if (($this->nsc[8] == 1) && ($terrID == 8) && ( $countryID == 2)) return;
		
		parent::countryFlag($terrID, $countryID);
	}
	
	
	protected function color(array $color, $image=false)
	{

		if ( ! is_array($image) )
			$image = $this->map;

		list($r, $g, $b) = $color;

		$colorRes = imagecolorexact($image['image'], $r, $g, $b);
		if ($colorRes == -1)
			$colorRes = imageColorAllocate($image['image'], $r, $g, $b);

 		return $colorRes;
 	}
	
}

?>
