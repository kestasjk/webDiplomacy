<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class NorthSeaWarsVariant_panelGameBoard extends panelGameBoard {

	function mapHTML()
	{
		return str_replace('width:150px','display:none;', parent::mapHTML());
	}
}

?>