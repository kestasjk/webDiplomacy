<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Empire1on1Variant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), 	/* Neutral */
		1 => array(100, 100, 100), 	/* Confederacy */
		2 => array( 40, 108, 128), 	/* Union */
		3 => array(190, 190, 190), 	/* Pro-Confederacy American States */
		4 => array(121, 175, 198),	/* Pro-Union American States */
		5 => array(234, 234, 175), 	/* Neutral American States */
		6 => array(239, 196, 228),	/* Neutral North American States */
		7 => array(164, 196, 153),	/* Neutral South American States */
	);
	

	public function ColorTerritory($terrID, $countryID)	
	{
		if     ($terrID ==  1 && $countryID == 0 ) $countryID=6;
		elseif ($terrID ==  2 && $countryID == 0 ) $countryID=6;
		elseif ($terrID ==  3 && $countryID == 0 ) $countryID=6;
		elseif ($terrID ==  4 && $countryID == 0 ) $countryID=6;
		elseif ($terrID ==  5 && $countryID == 0 ) $countryID=6;
		elseif ($terrID ==  6 && $countryID == 0 ) $countryID=6;
		elseif ($terrID ==  7 && $countryID == 0 ) $countryID=6;
		elseif ($terrID ==  8 && $countryID == 0 ) $countryID=6;
		elseif ($terrID ==  9 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 10 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 11 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 12 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 13 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 14 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 15 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 16 && $countryID == 0 ) $countryID=3;
		elseif ($terrID == 17 && $countryID == 0 ) $countryID=3;
		elseif ($terrID == 18 && $countryID == 0 ) $countryID=3;
		elseif ($terrID == 19 && $countryID == 0 ) $countryID=3;
		elseif ($terrID == 20 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 21 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 22 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 23 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 24 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 25 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 26 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 27 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 28 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 31 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 32 && $countryID == 0 ) $countryID=3;
		elseif ($terrID == 33 && $countryID == 0 ) $countryID=3;
		elseif ($terrID == 34 && $countryID == 0 ) $countryID=3;
		elseif ($terrID == 35 && $countryID == 0 ) $countryID=3;
		elseif ($terrID == 36 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 37 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 38 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 39 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 40 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 41 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 42 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 43 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 44 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 45 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 46 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 47 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 48 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 49 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 50 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 51 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 52 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 53 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 54 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 55 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 56 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 57 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 58 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 59 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 60 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 61 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 62 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 63 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 64 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 65 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 66 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 67 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 68 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 69 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 70 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 71 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 72 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 73 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 74 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 75 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 76 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 77 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 78 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 79 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 80 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 81 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 82 && $countryID == 0 ) $countryID=3;
		elseif ($terrID == 83 && $countryID == 0 ) $countryID=3;
		elseif ($terrID == 85 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 86 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 92 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 93 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 94 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 95 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 96 && $countryID == 0 ) $countryID=3;
		elseif ($terrID == 97 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 98 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 100 && $countryID == 0 ) $countryID=3;
		elseif ($terrID == 102 && $countryID == 0 ) $countryID=3;
		elseif ($terrID == 104 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 105 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 106 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 107 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 108 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 109 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 110 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 111 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 112 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 113 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 114 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 115 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 116 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 117 && $countryID == 0 ) $countryID=5;
		elseif ($terrID == 118 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 163 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 164 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 165 && $countryID == 0 ) $countryID=6;
		elseif ($terrID == 166 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 167 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 168 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 169 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 170 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 171 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 172 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 173 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 174 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 175 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 176 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 177 && $countryID == 0 ) $countryID=7;
		elseif ($terrID == 178 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 179 && $countryID == 0 ) $countryID=4;
		elseif ($terrID == 180 && $countryID == 0 ) $countryID=4;
		
		parent::ColorTerritory($terrID, $countryID);
	}


	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Empire1on1/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/Empire1on1/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Empire1on1/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/Empire1on1/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
	
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

	
	// Draw the flags behind the units for a better readability
	public function countryFlag($terrName, $countryID)
	{
		$flagBlackback = $this->color(array(0, 0, 0));

		$flagColor = $this->color($this->countryColors[$countryID]);

		list($x, $y) = $this->territoryPositions[$terrName];

		$coordinates = array(
			'top-left' => array( 
							'x'=>$x-intval($this->fleet['width']/2+2)+1,
							'y'=>$y-intval($this->fleet['height']/2+2)+1
							),
			'bottom-right' => array(
							'x'=>$x+intval($this->fleet['width']/2+2)-1,
							'y'=>$y+intval($this->fleet['height']/2+2)-1
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

?>