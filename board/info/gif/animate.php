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

/*
	This file is a stripped version of the AnimGif class released under GNU Public License 
		@ https://github.com/lunakid/AnimGif.
	The AnimGif class is a fork of Clément Guillemain's GifCreator class released under GNU Public License
		@ https://github.com/Sybio/GifCreator.
		
	@ authors Sybio (Clément Guillemain / @Sybio01) and lunakid (@GitHub, @Gmail, @SO etc.)
	@ license http://opensource.org/licenses/gpl-license.php GNU Public License
	@ copyright Clément Guillemain and Szabolcs Szász

	The class has been stripped to only include the code necessary for the GIF feature on webDiplomacy.
 */
 
defined('IN_CODE') or die('This script can not be run by itself.');

class AnimGif
{

	/**
	* @var string: The generated (binary) image
	*/
	private $gif;

	/**
	* @var boolean: Has an image (frame) been added already?
	*/
	private $imgBuilt;

	/**
	* @var array or string: Frame sources like filenames, URLs, bin. data, or a folder name
	*/
	private $frameSources;

	/**
	* @var integer: Gif loop count
	*/
	private $loop;

	/**
	* @var integer: Gif frame disposal method
	*/
	private $dis;

	/**
	* @var integer: Gif transparent color index
	*/
	private $transparent_color;
 
	// Methods
	// ===================================================================================
    
	public function __construct()
	{
		$this->reset();
	}

	/**
	 * Create animated GIF from source images
	 * 
	 * @param array $frames The source iamges: can be a local dir path, or an array  
	 *                      of file paths, resource image variables, binary data or image URLs.
	 * @param array|number $durations The duration (in 1/100s) of the individual frames, 
	 *                      or a single integer for each one.
	 * @return string The resulting GIF binary data.
	 */
	public function create($frames, $durations)
	{
		// Set standard values
		$this->loop = 0;	// loop indefinitely
		$this->dis  = 2;	// "reset to bgnd." (http://www.matthewflickinger.com/lab/whatsinagif/animation_and_transparency.asp)

		assert(is_array($frames));

		$i = 0;
		foreach ($frames as $frame) {

			$bin = file_get_contents($frame); 
			$resourceImg = imagecreatefromstring($bin);

			ob_start();
			imagegif($resourceImg);
			$this->frameSources[] = ob_get_contents();
			ob_end_clean();

			if ($i == 0)
				$this->transparent_color = imagecolortransparent($resourceImg);

			for ($j = (13 + 3 * (2 << (ord($this->frameSources[$i] { 10 }) & 0x07))), $k = true; $k; $j++) 
			{
				switch ($this->frameSources[$i] { $j }) 
				{			
					case ';':
		    				$k = false;
						break;
				}
			}

			unset($resourceImg);
			++$i;
		}//foreach

		$this->gifAddHeader();

		for ($i = 0; $i < count($this->frameSources); $i++)
			$this->addGifFrames($i, $durations[$i]);

		$this->gifAddFooter();
		
		return $this;
	}
    
	/**
	 * Save the resulting GIF to a file.
	 * 
	 * @param $filename String Target file path
	 * 
	 * @return that of file_put_contents($filename)
	 */
	public function save($filename)
	{
		return file_put_contents($filename, $this->gif);
	}
    
	/**
	 * Clean-up the current object (also used by the ctor.)
	 */
	public function reset()
	{
		$this->frameSources = null;
		$this->gif = 'GIF89a'; // the GIF header
		$this->imgBuilt = false;
		$this->loop = 0;
		$this->dis = 2;
		$this->transparent_color = -1;
	}
	    
	// Internals
	// ===================================================================================

	/**
	 * Assemble the GIF header
	 */
	protected function gifAddHeader()
	{
		$cmap = 0;

		if (ord($this->frameSources[0] { 10 }) & 0x80) {
		  
			$cmap = 3 * (2 << (ord($this->frameSources[0] { 10 }) & 0x07));

			$this->gif .= substr($this->frameSources[0], 6, 7);
			$this->gif .= substr($this->frameSources[0], 13, $cmap);
			if ($this->loop !== 1) // Only add the looping extension if really looping
				$this->gif .= "!\xFF\x0BNETSCAPE2.0\x03\x01".word2bin($this->loop==0?0:$this->loop-1)."\x0";
		}
	}
    
	/**
	 * Add frame to the GIF data
	 * 
	 * @param integer $i: index of frame source
	 * @param integer $d: delay time (frame display duration)
	 */
	protected function addGifFrames($i, $d)
	{
		$Locals_str = 13 + 3 * (2 << (ord($this->frameSources[ $i ] { 10 }) & 0x07));

		$Locals_end = strlen($this->frameSources[$i]) - $Locals_str - 1;
		$Locals_tmp = substr($this->frameSources[$i], $Locals_str, $Locals_end);

		$Global_len = 2 << (ord($this->frameSources[0 ] { 10 }) & 0x07);
		$Locals_len = 2 << (ord($this->frameSources[$i] { 10 }) & 0x07);

		$Global_rgb = substr($this->frameSources[ 0], 13, 3 * (2 << (ord($this->frameSources[ 0] { 10 }) & 0x07)));
		$Locals_rgb = substr($this->frameSources[$i], 13, 3 * (2 << (ord($this->frameSources[$i] { 10 }) & 0x07)));

		$Locals_ext = "!\xF9\x04" . chr(($this->dis << 2) + 0) . word2bin($d) . "\x0\x0";
        
		switch ($Locals_tmp { 0 }) {
		  
			case '!':
            
				$Locals_img = substr($Locals_tmp, 8, 10);
				$Locals_tmp = substr($Locals_tmp, 18, strlen($Locals_tmp) - 18);
                                
				break;
                
			case ',':
            
				$Locals_img = substr($Locals_tmp, 0, 10);
				$Locals_tmp = substr($Locals_tmp, 10, strlen($Locals_tmp) - 10);
                                
				break;
		}
        
		if (ord($this->frameSources[$i] { 10 }) & 0x80 && $this->imgBuilt) {
		  
			if ($Global_len == $Locals_len) {
			 
				if ($this->gifBlockCompare($Global_rgb, $Locals_rgb, $Global_len)) {
				    
					$this->gif .= $Locals_ext.$Locals_img.$Locals_tmp;
                    
				} else {
				    
					$byte = ord($Locals_img { 9 });
					$byte |= 0x80;
					$byte &= 0xF8;
					$byte |= (ord($this->frameSources[0] { 10 }) & 0x07);
					$Locals_img { 9 } = chr($byte);
					$this->gif .= $Locals_ext.$Locals_img.$Locals_rgb.$Locals_tmp;
				}
                
			} else {
			 
				$byte = ord($Locals_img { 9 });
				$byte |= 0x80;
				$byte &= 0xF8;
				$byte |= (ord($this->frameSources[$i] { 10 }) & 0x07);
				$Locals_img { 9 } = chr($byte);
				$this->gif .= $Locals_ext.$Locals_img.$Locals_rgb.$Locals_tmp;
			}
            
		} else {
			$this->gif .= $Locals_ext.$Locals_img.$Locals_tmp;
		}
        
		$this->imgBuilt = true;
	}
    
	/**
	 * Add the gif string footer char
	 */
	protected function gifAddFooter()
	{
		$this->gif .= ';';
	}
    
	/**
	 * Compare two blocks and return 1 if they are equal, 0 if differ.
	 * 
	 * @param string $globalBlock
	 * @param string $localBlock
	 * @param integer $length
	 * 
	 * @return integer
	 */
	protected function gifBlockCompare($globalBlock, $localBlock, $length)
	{
		for ($i = 0; $i < $length; $i++) {
		  
			if ($globalBlock [ 3 * $i + 0 ] != $localBlock [ 3 * $i + 0 ] ||
			    $globalBlock [ 3 * $i + 1 ] != $localBlock [ 3 * $i + 1 ] ||
			    $globalBlock [ 3 * $i + 2 ] != $localBlock [ 3 * $i + 2 ]) {
				
				return 0;
			}
		}

		return 1;
	}
}

/**
 * Convert an integer to 2-byte little-endian binary data
 * 
 * @param integer $word Number to encode
 * 
 * @return string of 2 bytes representing @word as binary data
 */
function word2bin($word)
{
	return (chr($word & 0xFF).chr(($word >> 8) & 0xFF));
}