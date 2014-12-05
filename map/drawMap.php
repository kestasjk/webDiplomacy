<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * Draws a map based on the instructions it recieves. Typically takes arguments like
 * orders, territory names and countryID, and draws that onto the map image. Will write
 * to a given filename once finished
 *
 * @package Map
 */
abstract class drawMap
{
	private function addBorder(array $image) {
		$black=$this->color(array(0,0,0),$image['image']);
		self::imagelinethick($image['image'],0,0,$image['width'],0,$black,2);
		self::imagelinethick($image['image'],$image['width'],0,$image['width'],$image['height'],$black,2);
		self::imagelinethick($image['image'],$image['width'],$image['height'],0,$image['height'],$black,2);
		self::imagelinethick($image['image'],0,$image['height'],0,0,$black,2);
	}

	public function saveThumbnail($location) {
		$thumbnail = array('width'=>300,'height'=>300);
		$thumbnailRatio = ($thumbnail['width']/$thumbnail['height']);

		if( defined('DATC') )
		{
			$map = array(
				'image'=>$this->map['image'],
				'width'=>(80+$this->boundaries['maxX']-$this->boundaries['minX']),
				'height'=>(80+$this->boundaries['maxY']-$this->boundaries['minY']),
				'startX'=>($this->boundaries['minX']-40),
				'startY'=>($this->boundaries['minY']-40)
			);

			if( $map['startX'] < 0 ) $map['startX'] = 0;
			if( $map['startY'] < 0 ) $map['startY'] = 0;
			if( $map['width'] > $this->map['width'] )
			{
				$map['startX'] = 0;
				$map['width'] = $this->map['width'];
			}
			if( $map['height'] > $this->map['height'] )
			{
				$map['startY'] = 0;
				$map['height'] = $this->map['height'];
			}

			if( $thumbnail['width']>$map['width'] )
				$thumbnail['width']=$map['width'];
			if( $thumbnail['height']>$map['height'] )
				$thumbnail['height']=$map['height'];
		}
		else
		{
			$map = $this->map;
			$map['startX']=0;
			$map['startY']=0;
		}

		if( $thumbnailRatio > ( $map['width']/$map['height']) )
			// Map too thin and tall, height = 100
			$thumbnail['width'] = round($map['width']*($thumbnail['height']/$map['height']));
		else
			// Map too far, width=100
			$thumbnail['height'] = round($map['height']*($thumbnail['width']/$map['width']));

		$thumbnail['image'] = imagecreatetruecolor($thumbnail['width'], $thumbnail['height']);

		imagecopyresampled($thumbnail['image'], $map['image'], 0, 0, $map['startX'], $map['startY'],
			$thumbnail['width'], $thumbnail['height'], $map['width'], $map['height']);

		$this->addBorder($thumbnail);

		if( defined('DATC') )
		{
			$tmpmap = imagecreatetruecolor($map['width'], $map['height']);

			imagecopyresampled($tmpmap, $map['image'], 0, 0, $map['startX'], $map['startY'],
				$map['width'], $map['height'], $map['width'], $map['height']);
			imagedestroy($map['image']);

			$this->map['image'] = $tmpmap;
			$this->map['width'] = $map['width'];
			$this->map['height'] = $map['height'];
			$this->addBorder($this->map);
		}

		imagepng($thumbnail['image'], $location);
	}

	protected $boundaries=array('maxX'=>-1,'maxY'=>-1,'minX'=>-1,'minY'=>-1);
	protected function updateBoundaries($x, $y) {
		if( $this->boundaries['maxX']==-1 || $x>$this->boundaries['maxX'] )
			$this->boundaries['maxX']=$x;

		if( $this->boundaries['minX']==-1 || $x<$this->boundaries['minX'] )
			$this->boundaries['minX']=$x;

		if( $this->boundaries['maxY']==-1 || $y>$this->boundaries['maxY'] )
			$this->boundaries['maxY']=$y;

		if( $this->boundaries['minY']==-1 || $y<$this->boundaries['minY'] )
			$this->boundaries['minY']=$y;
	}


	/*
	 * Functions which will help make the map look better/clearer:
	 *
	 * imagefilter/imageconvolution for greyscale retreaters,
	 * imagepolygon/imagefilledpolygon for arrows, imagearc for support moves and convoys, imagesetbrush,
	 * imagesetstyle for failed orders and to make more clear arrows
	 *
	 */


	/**
	 * Draw a retreat arrow
	 * @param string $fromTerrID Retreating from
	 * @param string $toTerrID Retreating to
	 * @param bool $success Retreat successful or not
	 */
	public function drawRetreat($fromTerrID, $toTerrID, $success)
	{
		list($fromX, $fromY) = $this->territoryPositions[$fromTerrID];
		list($toX, $toY) = $this->territoryPositions[$toTerrID];

		// Rotate the arrow slightly, so that head-to-heads are more clear
		//list($fromX, $fromY, $toX, $toY) = self::rotate(array($fromX, $fromY, $toX, $toY),
		//	array($fromX-($fromX-$toX)/2, $fromY-($fromY-$toY)/2), M_PI/15);

		$this->drawOrderArrow(array($fromX, $fromY), array($toX, $toY), 'Retreat');

		if ( !$success )
		{
			$this->drawFailure(array($fromX, $fromY), array($toX, $toY));
			$this->drawDestroyedUnit($fromTerrID);
		}
	}

	/**
	 * An array of territory coordinates, e.g. $tp['Paris'] = array(123,456);
	 * @var array
	 */
	protected $territoryPositions=array();

	/**
	 * True if we are displaying a small map, false otherwise
	 * @var bool
	 */
	public $smallmap;

	/**
	 * An array containing the map image resource, and its width and height.
	 * $image['image'],['width'],['height']
	 * @var array
	 */
	protected $map=array();

	/**
	 * An array containing the army icon image resource, and its width and height.
	 * $image['image'],['width'],['height']
	 * @var array
	 */
	protected $army=array();
	/**
	 * An array containing the fleet icon image resource, and its width and height.
	 * $image['image'],['width'],['height']
	 * @var array
	 */
	protected $fleet=array();
	/**
	 * An array containing the territory names overlay image resource,
	 * and its width and height.
	 * $image['image'],['width'],['height']
	 * @var array
	 */
	protected $mapNames=array();
	/**
	 * An array containing the standoff icon image resource, and its width and height.
	 * $image['image'],['width'],['height']
	 * @var array
	 */
	protected $standoff=array();

	/**
	 * ['path']=>path to font file, ['size']=>font point size, ['color']=>font color
	 * ['largeSize']=>large font point size
	 * @var array
	 */
	protected $font=array();

	/**
	 * An array of territory names by ID
	 * @var string[]
	 */
	protected $territoryNames=array();

	/**
	 * Initialize the image resource, getting it ready to be drawn to
	 *
	 * @param bool $smallmap True if displaying the smallmap, false if the large map
	 */
	public function __construct($smallmap)
	{
		global $Game;

		$this->mapID = MAPID;

		$this->smallmap = (bool) $smallmap;

		if ( !$this->smallmap )
			ini_set('memory_limit',"20M");

		$this->loadTerritories();
		$this->loadImages();
		$this->loadColors();
		$this->setTransparancies();
		$this->loadFont();
		$this->loadOrderArrows();
	}

	protected $mapID;

	/**
	 * Widely used colors
	 * @var array
	 */
	protected $colors = array(
						'border'=>array(0,0,0),
						'standoff'=>array(200,20,20)
					);
	/**
	 * Initialize the RGB color arrays into proper GD color resources
	 */
	protected function loadColors()
	{
		foreach( $this->colors as &$color )
			$color = $this->color($color);
	}

	/**
	 * Clean up the memory intensive image resources
	 */
	public function __destruct()
	{
		/*
		 * More could be deallocated here, but this is by far the largest, and the
		 * script is probably about to end anyway.
		 */

		imagedestroy($this->map['image']);
	}

	/**
	 * Load all the image resources required, and measure their width and length
	 */
	protected function loadImages()
	{
		$resources = $this->resources();

		$this->map = $this->loadImage($resources['map']);
		$this->army = $this->loadImage($resources['army']);
		$this->fleet = $this->loadImage($resources['fleet']);
		$this->standoff = $this->loadImage($resources['standoff']);
		$this->mapNames=$resources['names'];
	}

	/**
	 * Load a particular image resource and measure its width and length
	 * @param string $location A path to a PNG file to load
	 * @return array
	 */
	protected function loadImage($location)
	{
		$image = array();

		$image['image'] = imagecreatefrompng($location);
		$image['width'] = imagesx($image['image']);
		$image['height'] = imagesy($image['image']);

		return $image;
	}

	/**
	 * Make various images have transparent areas
	 */
	protected function setTransparancies()
	{
		$this->setTransparancy($this->army);
		$this->setTransparancy($this->fleet);
	}

	/**
	 * Make a particular image transparent
	 * @param array $image The image to make transparent
	 * @param array[optional] $color The color to make transparent, white by default
	 */
	protected function setTransparancy(array $image, array $color=array(255,255,255))
	{
		$transparentColor = $this->color($color, $image);
		imageColorTransparent($image['image'], $transparentColor);
		imagecolordeallocate($image['image'], $transparentColor);
	}

	/**
	 * Load the $this->font array
	 */
	protected function loadFont()
	{
		$this->font = array();
		$this->font['file'] = 'contrib/VeraBd.ttf';
		$this->font['size'] = 7;
		$this->font['largeSize'] = ( $this->smallmap ? 14 : 28 );
		$this->font['color'] = $this->color(array(0, 0, 0));
	}

	/**
	 * Set up colors, filter out settings for a different smallmap setting:
	 */
	protected function loadOrderArrows()
	{
		foreach($this->orderArrows as &$orderArrow)
		{
			foreach($orderArrow as $name => &$param)
			{
				if ( $name == 'headAngle' ) $param = M_PI/$param;

				if ( ! is_array($param) ) continue;

				if ( count($param) == 3 )
					$param = $this->color($param);

				if ( count($param) == 2 )
					$param = $param[ $this->smallmap ? 0 : 1 ];
			}
		}
	}

	/**
	 * Load the $this->territoryPositions array
	 */
	protected function loadTerritories()
	{
		global $DB;

		$territoryPositionsSQL = "SELECT id, name, ";
		if ( $this->smallmap )
			$territoryPositionsSQL .= 'smallMapX, smallMapY';
		else
			$territoryPositionsSQL .= 'mapX, mapY';
		$territoryPositionsSQL .= " FROM wD_Territories WHERE mapID=".$this->mapID;

		$this->territoryPositions = array();
		$tabl = $DB->sql_tabl($territoryPositionsSQL);
		while ( list($terrID, $terrName, $x, $y) = $DB->tabl_row($tabl) )
		{
			$this->territoryPositions[$terrID] = array($x,$y);
			$this->territoryNames[$terrID]=l_t($terrName);
		}
	}

	/**
	 * Color a territory the color of a given countryID. This must be done
	 * before anything else is written to the image
	 *
	 * @param string $terrName The name of the territory to color
	 * @param string $countryID The name of the countryID
	 */
	public function colorTerritory($terrID, $countryID)
	{
		/*
		 * The map files are both color coded so that each territory has its own
		 * unique color. When coloring a territory the territory's color is
		 * selected from the map, and imagecolorset() is used to change the
		 * territory's unique color to the desired color
		 */
		list($x, $y) = $this->territoryPositions[$terrID];

		$territoryColor = imagecolorat($this->map['image'], $x, $y);

		list($r, $g, $b) = $this->countryColors[$countryID];

		imagecolorset($this->map['image'], $territoryColor, $r, $g, $b);
	}

	/**
	 * Add a small square to a territory, colored according to a countryID's name.
	 * This is used when a unit is occupying a coastal territory or a territory
	 * which is hasn't occupied, to show who the unit belongs to.
	 * @param string $terrName The name of the territory
	 * @param string $countryID The name of the countryID
	 */
	public function countryFlag($terrName, $countryID)
	{
		$flagBlackback = $this->color(array(0, 0, 0));

		$flagColor = $this->color($this->countryColors[$countryID]);

		list($x, $y) = $this->territoryPositions[$terrName];

		$coordinates = array(
			'top-left' => array(
							'x'=>$x-intval($this->fleet['width']/2),
							'y'=>$y-$this->fleet['height']
							),
			'bottom-right' => array(
							'x'=>$x+intval($this->fleet['width']/2),
							'y'=>$y
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

	/**
	 * Put the given image array into the given position
	 * @param array $image An image array containing image resource, width and height
	 * @param int $x The x position on the map
	 * @param int $y The y position on the map
	 */
	protected function putImage(array $image, $x, $y)
	{
		imagecopymerge($this->map['image'], $image['image'],
			$x, $y, 0, 0, $image['width'], $image['height'], 100);
	}

	/**
	 * Draw a unit icon on the map
	 * @param string $terrName The territory to draw it at
	 * @param string $unitType The unit type
	 */
	public function addUnit($terrName, $unitType)
	{
		list($x, $y) = $this->territoryPositions[$terrName];

		$this->updateBoundaries($x, $y);

		if ( $unitType == 'Army' )
			$unit = $this->army;
		else
			$unit = $this->fleet;

		list($x,$y) = $this->absolutePosition($x, $y, $unit['width'], $unit['height']);

		$this->putImage($unit, $x, $y);
	}

	/**
	 * Draw a standoff icon on the map
	 * @param string $terrName The territory to draw it at
	 */
	public function drawStandoff($terrName)
	{
		list($x, $y) = $this->territoryPositions[$terrName];

		$width = $this->army['width']/2;

		if ( $this->smallmap )
		{
			$outerThickness = 5;
			$innerThickness = 4;
		}
		else
		{
			$outerThickness = 9;
			$innerThickness = 6;
		}

		$this->drawCross(array($x, $y), $this->colors['border'], $outerThickness, $width);
		$this->drawCross(array($x, $y), $this->colors['standoff'], $innerThickness, $width);
	}

	/**
	 * Draw a cross
	 * @param array $coords The coordinate matrix
	 * @param resource $color The color resource to use
	 * @param int $thickness Thickness of the cross
	 * @param int[optional] $width Width of the cross
	 */
	protected function drawCross(array $coords, $color, $thickness, $width=null)
	{
		if ( count($coords) == 2 )
		{
			list( $x, $y ) = $coords;

			$offset = round($width/2);

			$coords = array($x-$offset,$y-$offset,$x+$offset,$y+$offset,
							$x-$offset,$y+$offset,$x+$offset,$y-$offset);
			self::imagesquareline($this->map['image'],$coords[0],$coords[1],$coords[2],$coords[3],$color, $thickness);
			self::imagesquareline($this->map['image'],$coords[4],$coords[5],$coords[6],$coords[7],$color, $thickness);
		}
		else
		{
			self::imagelinethick($this->map['image'],$coords[0],$coords[1],$coords[2],$coords[3],$color, $thickness);
			self::imagelinethick($this->map['image'],$coords[4],$coords[5],$coords[6],$coords[7],$color, $thickness);
		}
	}

	/**
	 * Draw text on the map
	 * @param string $text The text to draw
	 * @param int $x The x position to center it at
	 * @param int $y The y position to center it at
	 * @param bool[optional] $large If true the text will be large, default is false
	 */
	protected function drawText($text, $x, $y, $large=false, $topRight=false)
	{
		$size = ( $large ? 'largeSize' : 'size' );

		$boundingBox = imageftbbox($this->font[$size],
									0, $this->font['file'], $text);

		$width = $boundingBox[4];
		$height = $boundingBox[5];

		if( $topRight )
		{
			$x = ( !$this->smallmap ? 35 : 10 );
			$y = -$height+( !$this->smallmap ? 17 : 5 );;
		}
		else
			list($x, $y) = $this->absolutePosition($x, $y, $width, $height);

		imagefttext($this->map['image'], $this->font[$size],
					0.0, $x, $y, $this->font['color'], $this->font['file'], $text);
	}

	/**
	 * Convert a co-ordinate that points to where the center of an image should be
	 * placed, to a co-ordinate that points to where the top-left of an image should
	 * be placed.
	 * @param int $x The desired center x coordinate in the destination image
	 * @param int $y The desired center y coordinate in the destination image
	 * @param int $width The width of the source image
	 * @param int $height The height of the source image
	 * @return array Returns a coordinate array
	 */
	protected function absolutePosition($x, $y, $width, $height)
	{
		$absolutePosition = array($x - intval($width/2), $y - intval($height/2) );

		return $absolutePosition;
	}

	/**
	 * Rotate an array of coordinates around a central point. The array of coordinates
	 * is array(x,y,x,y,x,y ..etc), $rotateAround is array(x,y) of the position to
	 * rotate the other coordinates around, and $rotateBy is the amount to rotate by
	 * in radians
	 *
	 * @param array $coordinates x,y,x,y,etc coordinate matrix to rotate
	 * @param array $rotateAround x,y Coordinate to rotate around
	 * @param float $rotateBy Radians to rotate the coordinates by
	 *
	 * @return array x,y,x,y,etc rotated coordinate matrix
	 */
	protected static function rotate(array $coordinates, array $rotateAround, $rotateBy)
	{
		/*
		 * y gets lower as it goes up, which can cause confusion
		 */
		$transformMatrix = array(
				array(cos($rotateBy),-1*sin($rotateBy)),
				array(sin($rotateBy),cos($rotateBy))
			);

		$coordNum = count($coordinates);
		for($i=0; $i<$coordNum; $i+=2)
		{
			$x = $coordinates[$i];
			$y = $coordinates[$i+1];

			$x -= $rotateAround[0];
			$y -= $rotateAround[1];

			$newX = $x*$transformMatrix[0][0] + $y*$transformMatrix[0][1];
			$y = $x*$transformMatrix[1][0] + $y*$transformMatrix[1][1];
			$x = $newX;

			$x += $rotateAround[0];
			$y += $rotateAround[1];

			$coordinates[$i] = $x;
			$coordinates[$i+1] = $y;
		}

		return $coordinates;
	}

	/**
	 * Draw a support move arrow
	 * @param string $terrID Territory supporting move from
	 * @param string $fromTerrID Territory supported unit moving from
	 * @param string $toTerrID Territory supported unit moving to
	 * @param bool $success Support move successful or not
	 */
	public function drawSupportMove($terrID, $fromTerrID, $toTerrID, $success)
	{
		if ( $this->smallmap and !$success ) return;

		// Our toX and toY are 1/3 of the way between the two territories
		list($fromX, $fromY) = $this->territoryPositions[$fromTerrID];
		list($toX, $toY) = $this->territoryPositions[$toTerrID];

		$toX -= ( $toX - $fromX ) / 3;
		$toY -= ( $toY - $fromY ) / 3;

		list($fromX, $fromY) = $this->territoryPositions[$terrID];

		$this->drawOrderArrow(array($fromX, $fromY), array($toX, $toY), 'Support move');

		if ( !$success ) $this->drawFailure(array($fromX, $fromY), array($toX, $toY));
	}

	/**
	 * Draw a convoy arrow
	 * @param string $terrID Territory convoying from
	 * @param string $fromTerrID Territory convoyed unit convoyed from
	 * @param string $toTerrID Territory convoyed unit convoyed to
	 * @param bool $success Convoy successful or not
	 */
	public function drawConvoy($terrID, $fromTerrID, $toTerrID, $success)
	{
		if ( $this->smallmap and !$success ) return;

		// Our toX and toY are 1/3 of the way between the two territories
		list($fromX, $fromY) = $this->territoryPositions[$fromTerrID];
		list($toX, $toY) = $this->territoryPositions[$toTerrID];

		$toX -= ( $toX - $fromX ) / 3;
		$toY -= ( $toY - $fromY ) / 3;

		list($fromX, $fromY) = $this->territoryPositions[$terrID];

		$this->drawOrderArrow(array($fromX, $fromY), array($toX, $toY), 'Convoy');

		if ( !$success ) $this->drawFailure(array($fromX, $fromY), array($toX, $toY));
	}

	/**
	 * Draw a move arrow
	 * @param string $fromTerrID Territory moving unit moved from
	 * @param string $toTerrID Territory moving unit moved to
	 * @param bool $success Move successful or not
	 */
	public function drawMove($fromTerrID, $toTerrID, $success)
	{
		list($fromX, $fromY) = $this->territoryPositions[$fromTerrID];
		list($toX, $toY) = $this->territoryPositions[$toTerrID];

		// Rotate the arrow slightly, so that head-to-heads are more clear
		//list($fromX, $fromY, $toX, $toY) = self::rotate(array($fromX, $fromY, $toX, $toY),
		//	array($fromX-($fromX-$toX)/2, $fromY-($fromY-$toY)/2), M_PI/15);

		$this->drawOrderArrow(array($fromX, $fromY), array($toX, $toY), 'Move');

		if ( !$success ) $this->drawFailure(array($fromX, $fromY), array($toX, $toY));
	}

	/**
	 * Draw a move arrow (but only in grey)
	 * Usefull for the Preview-function to draw the corresponding move-arrow for convoy and support commands
	 * @param string $fromTerrID Territory moving unit moved from
	 * @param string $toTerrID Territory moving unit moved to
	 * @param bool $success Move successful or not
	 */	
	public function drawMoveGrey($fromTerrID, $toTerrID, $success)
	{
		list($fromX, $fromY) = $this->territoryPositions[$fromTerrID];
		list($toX, $toY) = $this->territoryPositions[$toTerrID];

		// Rotate the arrow slightly, so that head-to-heads are more clear
		//list($fromX, $fromY, $toX, $toY) = self::rotate(array($fromX, $fromY, $toX, $toY),
		//	array($fromX-($fromX-$toX)/2, $fromY-($fromY-$toY)/2), M_PI/15);

		$this->drawOrderArrow(array($fromX, $fromY), array($toX, $toY), 'MoveGrey');

		if ( !$success ) $this->drawFailure(array($fromX, $fromY), array($toX, $toY));
	}
	
	/**
	 * Draw a red alert boy around the image...
	 * Usefull for the Preview-function 
	 */	
	public function drawRedBox()
	{
		$red=$this->color(array(240,20,20),$this->map['image']);
		self::imagelinethick($this->map['image'],0, 0, 0, $this->map['height'], $red, 8);
		self::imagelinethick($this->map['image'],0, $this->map['height'], $this->map['width'], $this->map['height'], $red, 8);
		self::imagelinethick($this->map['image'],$this->map['width'], $this->map['height'], $this->map['width'], 0, $red, 8);
		self::imagelinethick($this->map['image'], $this->map['width'], 0, 0, 0, $red, 8);
	}

	/**
	 * Draw a support hold arrow
	 * @param string $fromTerrID Territory supporting unit supporting from
	 * @param string $toTerrID Territory supporting unit supported to
	 * @param bool $success Support successful or not
	 */
	public function drawSupportHold($fromTerrID, $toTerrID, $success)
	{
		if ( $this->smallmap and !$success ) return;

		list($fromX, $fromY) = $this->territoryPositions[$fromTerrID];
		list($toX, $toY) = $this->territoryPositions[$toTerrID];

		$this->drawOrderArrow(array($fromX, $fromY), array($toX, $toY), 'Support hold');

		if ( !$success ) $this->drawFailure(array($fromX, $fromY), array($toX, $toY));
	}

	/**
	 * Draw an arrow marker indicating that an order failed
	 * @param string $fromTerrID Failed order origin territory X,Y coordinates
	 * @param string $toTerrID Failed order target territory X,Y coordinates
	 */
	protected function drawFailure(array $from, array $to)
	{
		$height = $this->army['height']/2;
		$width = $this->army['width']/2;

		$coords = array($height, $width, -1*$height, -1*$width,
						$height, -1*$width, -1*$height, $width);

		$rad = $this->lineAngle($from, $to);

		/*
		 * The marker is currently pointing upwards, so PI must be added to
		 * $rad to cancel this out
		 */
		$coords = $this->rotate($coords, array(0,0), $rad+M_PI);

		/*
		 * The marker is rotated correctly, and now must be moved into place
		 * before getting drawn
		 */
		$x = $from[0] - ( $from[0] - $to[0] )/3;
		$y = $from[1] - ( $from[1] - $to[1] )/3;

		for($i=0; $i<8; $i+=2)
		{
			$coords[$i] += $x;
			$coords[$i+1] += $y;
		}

		if ( $this->smallmap )
			$thickness = 2;
		else
			$thickness = 6;

		$this->drawCross($coords, $this->colors['standoff'], $thickness);
	}

	/**
	 * Convert two X,Y coordinates representing an arrow into an angle in radians, to
	 * allow use of the rotate function which requires radians are measured based on
	 * facing right being 0 Rad. Clockwise is positive
	 *
	 * To use the "rotate" function polygons/matrices must be built facing in the "right"
	 * direction, and they can then simply be rotated to point where required
	 *
	 * @param string $fromTerrID Failed order origin territory X,Y coordinates
	 * @param string $toTerrID Failed order target territory X,Y coordinates
	 *
	 * @return The angle the arrow represents in radians
	 */
	protected static function lineAngle(array $from, array $to)
	{
		$xdiff = $from[0] - $to[0];
		// xdiff > 0 = from > to = to on the left of from = rad between +/- PI and +/-1/2*PI

		$ydiff = $from[1] - $to[1];
		// ydiff > 0 = from > to = to above from = rad between -PI and 0

		if ( $ydiff > 0 ) // The radians angle is negative. The arrow points up
		{
			if ( $xdiff == 0 ) // Don't let this cause a div by zero error
			{
				return (-1/2)*M_PI;
			}
			elseif ( $xdiff > 0 )
			{
				/*
				 * The arrow points left; the radians angle is between -PI and -(1/2)*PI
				 */

				return ( M_PI + abs(atan(abs($ydiff/$xdiff))) );
			}
			else
			{
				// The radians angle is between 0 and -PI
				return -1*abs(atan(abs($ydiff/$xdiff)));
			}
		}
		else
		{
			// The value is positive
			if ( $xdiff == 0 )
			{
				return M_PI/2;
			}
			elseif ( $xdiff > 0 )
			{
				// The arrow points left; the radians angle is between PI and 2*PI
				return ( M_PI - abs(atan(abs($ydiff/$xdiff))) );
			}
			else
			{
				// The radians angle is between 0 and PI
				return abs(atan(abs($ydiff/$xdiff)));
			}
		}
	}

	/**
	 * Create a color resource using an RGB array
	 *
	 * @param array $color array(1,2,3) of RGB calues
	 * @param array[optional] $image The image to allocate for (map is default)
	 *
	 * @return GD color resource
	 */
	protected function color(array $color, $image=false)
	{
		if ( ! is_array($image) )
		{
			$image = $this->map;
		}

		list($r, $g, $b) = $color;
		
		// Try to allocate the color from the palette .. 
		$colorRes = imagecolorexact($image['image'], $r, $g, $b);
		if ($colorRes == -1)
		{
			// .. if the color doesn't exist within the palette add it to the palette (which can hit a limit if the palette gets full) .. 
			$colorRes = imageColorAllocate($image['image'], $r, $g, $b);
			
			if (!$colorRes)
			{
				// .. and failing that get the best available thing.
				$colorRes = imageColorClosest($image['image'], $r, $g, $b);
			}
		}
		
		return $colorRes;
	}

	/**
	 * Write the finished image
	 *
	 * @param string $filename The path to the file to write the image to
	 */
	public function write($filename)
	{
		imagepng($this->map['image'], $filename);
	}

	/**
	 * An array of different arrow parameters; colors, thicknesses etc, for arrows representing different orders
	 * @var array $orderArrows
	 */
	protected $orderArrows = array(
		//array(0, 153, 2)
		'Move' => array('color'=>array(196,32,0),  //0, 153, 2),//
						'thickness'=>array(2,4),
						'headAngle'=>7,
						'headStart'=>.1,
						'headLength'=>array(12,30),
						'border'=>array(0,0)
					),
		'MoveGrey' => array('color'=>array(100,100,100),  // Same as move, but arrowcolor=grey
						'thickness'=>array(2,4),
						'headAngle'=>7,
						'headStart'=>.1,
						'headLength'=>array(12,30),
						'border'=>array(0,0)
					),
		'Support hold' => array('color'=>array(67,206,16),
						'thickness'=>array(2,4),
						'headAngle'=>2,
						'headStart'=>0.2,
						'headLength'=>array(8,24),
						'border'=>array(0,0)
					),
		'Support move' => array('color'=>array(249,249,47),
						'thickness'=>array(2,4),
						'headAngle'=>7,
						'headStart'=>0.4,
						'headLength'=>array(12,30),
						'border'=>array(0,0)
					),
		'Convoy' => array('color'=>array(4,113,160),
						'thickness'=>array(2,4),
						'headAngle'=>7,
						'headStart'=>.1,
						'headLength'=>array(0,0),
						'border'=>array(0,0)
					),
		'Retreat' => array('color'=>array(198,39,159),
						'thickness'=>array(2,4),
						'headAngle'=>7,
						'headStart'=>.1,
						'headLength'=>array(12,30),
						'border'=>array(0,0)
					)
		);

	/**
	 * Draw an order's arrow, given the stand and end coordinates and the type of order
	 *
	 * @param array $start Starting X,Y coordinates
	 * @param array $end Ending X,Y coordinates
	 * @param string $moveType The type of move being drawn
	 */
	protected function drawOrderArrow(array $start, array $end, $moveType )
	{
		list($startX, $startY) = $start;
		list($endX, $endY) = $end;

		$this->updateBoundaries($startX, $startY);
		$this->updateBoundaries($endX, $endY);

		$params = &$this->orderArrows[$moveType];

		$borderColor = $this->colors['border'];

		// Some orders want to start the head further up the arrow ( support move)
		$headStartX = $endX-($endX-$startX)*$params['headStart'];
		$headStartY = $endY-($endY-$startY)*$params['headStart'];

		// Get the angle that the order arrow is pointing in, so that the head can be added that way
		$rad = $this->lineAngle($start, $end);

		/*
		 * Now we construct the head facing to the right, so that it can be
		 * aligned afterwards
		 */
		$head = array(
				0,0,
				-1*$params['headLength'] * cos($params['headAngle']), -1*$params['headLength'] * sin($params['headAngle']),
				-1*$params['headLength'] * cos($params['headAngle']), $params['headLength'] * sin($params['headAngle']),
			);
		$head = self::rotate($head, array(0,0), $rad);

		// Rotation done, now move it into place
		for($i=0; $i<6; $i+=2)
		{
			$head[$i] += $headStartX;
			$head[$i+1] += $headStartY;
		}

		if ( $params['border'] != 0 )
		{
			// Borders are drawn first, so they don't overlap, and are made a little longer
			self::imagelinethick($this->map['image'], $startX, $startY, $endX, $endY, $borderColor, $params['border']);
			self::imagelinethick($this->map['image'], $head[2], $head[3], $headStartX, $headStartY, $borderColor, $params['border']);
			self::imagelinethick($this->map['image'], $head[4], $head[5], $headStartX, $headStartY, $borderColor, $params['border']);
		}

		self::imagelinethick($this->map['image'], $startX, $startY, $endX, $endY, $params['color'], $params['thickness']);
		self::imagelinethick($this->map['image'], $head[2], $head[3], $headStartX, $headStartY, $params['color'], $params['thickness']);
		self::imagelinethick($this->map['image'], $head[4], $head[5], $headStartX, $headStartY, $params['color'], $params['thickness']);
	}

	/**
	 * Return an array of coordinates for an n sided symmetrical polygon, which has 0,0 at its center.
	 *
	 * @param int $corners The number of corners in the polygon
	 * @param int $outerRadius The distance to draw the outer-most points
	 * @param int[optional] $innerRadius The option to draw half the points at a the given radius
	 *
	 * @return array x,y,x,y matrix of polygon coordinates
	 */
	protected static function polygonMap($corners, $outerRadius, $innerRadius=false)
	{
		$coords = array();

		for($i=0;$i<$corners;$i++)
		{
			$coords[]=0; // x coord is 0

			if ( $i%2 != 0 and $innerRadius != false )
				$coords[]=$innerRadius;
			else
				$coords[]=$outerRadius;

			$coords = self::rotate($coords, array(0,0), (2*M_PI/$corners));
		}

		return $coords;
	}

	/**
	 * Draws an orange explosion where a unit has been destroyed, intended to be drawn on-top of the unit
	 *
	 * @param string $terrID The territory to draw the destruction at
	 */
	public function drawDestroyedUnit($terrID)
	{
		$size = $this->army['width']/2;

		if( !isset($this->destroyedPolygon) )
		{
			$blackInner = self::polygonMap(14, $size, $size/2);
			$blackOuter = self::polygonMap(14, ($size-1), ($size-1)/2);

			$inner = self::polygonMap(14, ($size-2), ($size-2)/2);
			$outer = self::polygonMap(14, ($size-3), ($size-3)/2);

			$outer = self::rotate($outer, array(0,0), M_PI/7);
			$blackOuter = self::rotate($blackOuter, array(0,0), M_PI/7);

			$this->destroyedPolygon=array(
				'blackInner'=>$blackInner,
				'blackOuter'=>$blackOuter,
				'inner'=>$inner,
				'outer'=>$outer,
			);
		}

		$position = $this->territoryPositions[$terrID];

		$this->drawPolygon($position, array(0,0,0), $this->destroyedPolygon['blackInner']);
		$this->drawPolygon($position, array(0,0,0), $this->destroyedPolygon['blackOuter']);
		$this->drawPolygon($position, array(255,100,0), $this->destroyedPolygon['inner']);
		$this->drawPolygon($position, array(255,0,0), $this->destroyedPolygon['outer']);
	}
	private $destroyedPolygon;


	/**
	 * Draws a small orange explosion where a unit has been dislodged, drawn to the upper right of the unit
	 * which took the place of the dislodged unit
	 *
	 * @param string $terrID The territory to draw the dislodgement marker at
	 */
	public function drawDislodgedUnit($terrID)
	{
		if ( $this->smallmap ) return;

		if( !isset($this->dislodgedPolygon) )
		{
			$size = $this->army['height']/2;

			$blackInner = self::polygonMap(10, $size, $size/2);
			$blackOuter = self::polygonMap(10, ($size-2), ($size-2)/2);

			$inner = self::polygonMap(10, ($size-3), ($size-3)/2);
			$outer = self::polygonMap(10, ($size-4), ($size-4)/2);

			$outer = self::rotate($outer, array(0,0), M_PI/5);
			$blackOuter = self::rotate($blackOuter, array(0,0), M_PI/5);

			$this->dislodgedPolygon=array(
				'blackInner'=>$blackInner,
				'blackOuter'=>$blackOuter,
				'inner'=>$inner,
				'outer'=>$outer,
			);
		}

		$position = $this->territoryPositions[$terrID];

		$this->drawPolygon($position, array(0,0,0), $this->dislodgedPolygon['blackInner'], true);
		$this->drawPolygon($position, array(0,0,0), $this->dislodgedPolygon['blackOuter'], true);
		$this->drawPolygon($position, array(255,150,0), $this->dislodgedPolygon['inner'], true);
		$this->drawPolygon($position, array(255,80,0), $this->dislodgedPolygon['outer'], true);
	}
	private $dislodgedPolygon;

	/**
	 * Draw a polygon to a certain color
	 * @param array $position x,y Coordinate of the center of the polygon
	 * @param array $color The color to use for the polygon
	 * @param array $polygon The x,y,x,y coordinate matrix for the polygon
	 * @param bool[optional] $small True if drawing onto small map
	 */
	protected function drawPolygon(array $position, array $color, array $polygon, $small=false)
	{
		list($x,$y) = $position;

		$corners = count($polygon) / 2;

		if ( $small )
		{
			$x += $this->army['width']/2;
			$y -= $this->army['height']/2;
		}

		for($i=0; $i<$corners*2; $i+=2)
		{
			$polygon[$i]+=$x;
			$polygon[$i+1]+=$y;
		}

		$color = $this->color($color);
		imagefilledpolygon($this->map['image'], $polygon, $corners, $color);
	}

	/**
	 * Draws a small star where a unit has been created, drawn to the upper right of the new unit
	 *
	 * @param string $terrID The territory to draw the creation marker at
	 */
	public function drawCreatedUnit($terrID, $unitType)
	{
		$position = $this->territoryPositions[$terrID];

		$size = $this->army['height']/2;

		$blackStar = self::polygonMap(10, $size, $size/2);
		$blackStar = self::rotate($blackStar, array(0,0), M_PI);

		// Flip them the right way up
		$whiteStar = self::polygonMap(10, ($size-2), $size/2-1);
		$whiteStar = self::rotate($whiteStar, array(0,0), M_PI);


		// The outer black border star
		$this->drawPolygon($position, array(0,0,0), $blackStar, true);
		// The inner white star
		$this->drawPolygon($position, array(255,255,0), $whiteStar, true);

		$this->addUnit($terrID, $unitType);
	}

	/**
	 * Draw a line
	 * @param resource $image GD image resource
	 * @param int $x1 Start-X coord
	 * @param int $y1 Start-Y coord
	 * @param int $x2 End-X coord
	 * @param int $y2 End-Y coord
	 * @param resource $color GD color resource
	 * @param int[optional] $thick Thickness of line
	 *
	 * @return bool Success/failure
	 */
	protected static function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
	{
		imagesetthickness($image, $thick);
		imageline($image, $x1, $y1, $x2, $y2, $color);
		imagesetthickness($image, 1);
	}

	/**
	 * Draw a line
	 * @param resource $image GD image resource
	 * @param int $x1 Start-X coord
	 * @param int $y1 Start-Y coord
	 * @param int $x2 End-X coord
	 * @param int $y2 End-Y coord
	 * @param resource $color GD color resource
	 * @param int[optional] $thick Thickness of line
	 *
	 * @return bool Success/failure
	 */
	protected static function imagesquareline($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
	{
		$v=sqrt(pow($x2-$x1,2)+pow($y2-$y1,2));
		$tv=$thick/2-0.5;
		$rectangle=array($x1-$tv,$y1-$tv,$x1-$tv,$y1+$tv,$x1+$v+$tv,$y1+$tv,$x1+$v+$tv,$y1-$tv);
		$rad=self::lineAngle(array($x1,$y1),array($x2,$y2));
		$rectangle=self::rotate($rectangle,array($x1,$y1),$rad);
		return imagefilledpolygon($image, $rectangle, 4, $color);
	}

	/**
	 * Write a caption; a large piece of text centered in the map
	 *
	 * @param string $text
	 */
	public function caption($text)
	{
		$this->drawText($text, 0, 0, $this->font['largeSize'], true);
	}

	/**
	 * Add the territory names, either with GD FreeType or with the small-map overlay
	 */
 	public function addTerritoryNames() {
 		if ( count($this->mapNames) )
		{
			$this->mapNames = $this->loadImage($this->mapNames);
			$this->setTransparancy($this->mapNames);
			$this->putImage($this->mapNames, 0, 0);
			imagedestroy($this->mapNames['image']);
		}
		else
		{
 			foreach($this->territoryPositions as $id=>$position)
			{
				$name=$this->territoryNames[$id];

				// Don't draw coast names
				if ( strstr($name, 'Coast)') ) continue;

				list($x, $y) = $position;
				$this->drawText($name, $x, $y+intval($this->fleet['height']/2));
 			}
		}
 	}
}

?>
