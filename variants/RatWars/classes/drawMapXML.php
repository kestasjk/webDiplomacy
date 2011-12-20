<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Fog_drawMapXML extends drawMapXML
{
	// Hide the output if it's not called from our custom map-code...
	public function __construct($smallmap,$fogmap=false)
	{
		if ($fogmap == false) exit;
		parent::__construct($smallmap);
	}
}

class RatWarsVariant_drawMapXML extends Fog_drawMapXML {}
