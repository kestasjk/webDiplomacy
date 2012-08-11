<?php
/*
    Copyright (C) 2004-2011 Oliver Auth

	This file is part of vDiplomacy.

    vDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    vDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 *  The vDiplomacy has a few more options you can use in your config file.
 *  You need to add the changes in your original config.php to use them...
 */
class Config
{
	/**
	 * Some more tools and custom webdip-files you might want to use.
	 * You need to create an entry for every non-webdip-file you want to use in your installation.
	 * inmenu=>TRUE creates a top-menu-bar entry, false hides the entry.
	 */	
	public static $top_menue=array(
		'admin'=> array(
			'edit.php'     => array('name'=>'Edit map', 'inmenu'=>TRUE,  'title'=>"Edit your maps"),
			'help.php'     => array('name'=>'Help',     'inmenu'=>FALSE, 'title'=>"Help")
		),
		'user' => array(
			'variants.php' => array('name'=>'Variants', 'inmenu'=>TRUE,  'title'=>"Variants")
		),
		'all'  => array(
			'impresum.php' => array('name'=>'Impresum', 'inmenu'=>FALSE, 'title'=>"Impresum"),
			'download.php' => array('name'=>'Download', 'inmenu'=>FALSE, 'title'=>"Download"),
			'stats.php'    => array('name'=>'Stats',    'inmenu'=>TRUE,  'title'=>"Statistics")
		)
	);
	
	/**
	 * If you use the piwik-webanalyser define his path here. If not comment this out.
	 */		
	// public static $piwik='piwik/';

	/**
	 * If set to any value > 0 the game will not progress for the given turns if a country misses it's orders.
	 * It will send the country in CD and extend the game for another phase.
	 *
	 * @var int.
	 */
	public static $specialCD=1;

	/**
	 * If set to any value it wil display this mail adress in the rules section instead of the admin-mail.
	 */
	//public static $modEMail = 'admin@vDiplomacy.com';

	/*
	 * Make CD-takeovers cheaper
	 */
	public static function adjustCD($bet)
	{
		return ceil($bet / 2);
	}
	
}

?>