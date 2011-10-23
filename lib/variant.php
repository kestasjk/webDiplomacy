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

require_once('variants/variant.php');

/**
 * This class performs variant related functions, most importantly loading the right Variant class
 * given a variety of inputs. Since these Variant classes are required to load anything game related
 * most game related code begins via a request to libVariant here.
 *
 * Also will serialize/cache newly loaded variants and load ones available via cache, preventing
 * unneeded database lookups.
 */
class libVariant {

	/**
	 * When a change in behavior is made to the variants system this is incremented to allow
	 * variants to react to changes in the variant system.
	 *
	 * 1: $WDVariant->codeVersion and ->cacheVersion added, allowing variant versioning and cache wipes.
	 *
	 * @var int
	 */
	public static $Version=1;

	public static $Variant;

	/**
	 * For everything in board/* (used by ajax.php and board.php) it can be assumed that only one variant
	 * will be loaded, so here that variant is defined where it will be globally accessible.
	 *
	 * @param Variant $Variant
	 * @return unknown_type
	 */
	public static function setGlobals(WDVariant $Variant) {
		if( isset(libVariant::$Variant) )
		{
			// In DATC tests this might get called twice
			if( libVariant::$Variant->id == $Variant->id )
				return;
			else
				trigger_error("Alternate variant being set as global");
		}
		else
		{
			libVariant::$Variant=$Variant;
			define('VARIANTID',$Variant->id);
			define('MAPID',$Variant->mapID);
		}
	}

	/**
	 * A variant's cache dir
	 * @param string $variantName
	 * @return string Location relative to root webDip folder
	 */
	public static function cacheDir($variantName) {
		return 'variants/'.$variantName.'/cache';
	}

	/**
	 * All loaded variants indexed by name
	 * @var array[$variantName]=$Variant
	 */
	private static $Variants=array();
	/**
	 * For looking up variants by gameID quickly
	 * @var array[$gameID]=$variantID
	 */
	private static $variantIDsByGameID=array();

	/**
	 * Return a Variant object given a variant ID
	 * @param int $variantID
	 * @return Variant
	 */
	public static function loadFromVariantID($variantID) {
		return self::loadFromVariantName( Config::$variants[$variantID] );
	}

	public static function installLock() {
		global $DB;

		static $locked;

		if( !isset($locked) )
			$DB->get_lock('VariantInstall');

		$locked=true;
	}

	public static function wipe($variantName) {
		self::installLock();

		if( file_exists(self::cacheDir($variantName).'/data.php') )
			unlink(self::cacheDir($variantName).'/data.php');
		
		// Delete the javascript-cache too
		foreach (glob(self::cacheDir($variantName).'/*.js') as $jsfilename)
			unlink($jsfilename);			
	}

	/**
	 * Return a Variant object given its short name (the preferred/quickest way)
	 * @param string $variantName
	 * @return Variant
	 */
	public static function loadFromVariantName($variantName) {
		global $DB, $Misc;

		if( !isset(self::$Variants[$variantName]) )
		{
			$variantCache=self::cacheDir($variantName).'/data.php';

			if( !file_exists($variantCache) )
			{
				self::installLock();

				if( file_exists($variantCache) )
					libHTML::notice("Installed variant", "Variant '".$variantName." installed, please refresh.");

				$classname = $variantName.'Variant';
				$Variant = new $classname(); // variants/variant.php __autoload() will find the class for this

				// The object will have loaded all the cacheable data and be ready to be saved for next time
				file_put_contents($variantCache, serialize($Variant));
			}
			else
			{
				// This variant is saved, and doesn't need to waste database queries retreiving this data again
				$variantData = file_get_contents($variantCache);
				$Variant = unserialize($variantData);


				if( isset($Variant->codeVersion)
					&& $Variant->codeVersion !=null && $Variant->codeVersion != 0 )
				{
					// Cache version checking is enabled

					if( !isset($Variant->cacheVersion) || $Variant->cacheVersion==null
					|| $Variant->cacheVersion < $Variant->codeVersion || !$Variant->cacheVersion )
					{
						// An old cache version has been loaded; wipe this variant's cache and try again.
						self::wipe($variantName);
						$Variant = self::loadFromVariantName($variantName);
					}
				}
			}

			self::$Variants[$variantName]=$Variant;
		}

		return self::$Variants[$variantName];
	}

	/**
	 * Return a Variant object corresponding to a game ID. This has to
	 * @param unknown_type $gameID
	 * @return unknown_type
	 */
	public static function loadFromGameID($gameID) {
		global $DB;

		if( !isset(self::$variantIDsByGameID[$gameID]) )
		{
			$gameID=(int)$gameID;

			list($variantID) = $DB->sql_row("SELECT variantID FROM wD_Games WHERE id=".$gameID);

			if( !isset($variantID) || !$variantID )
			{
				libHTML::error("Game not found, or has an invalid variant set; ensure a valid game ID has been given. Check that this game hasn't been canceled, you may have received a message about it on your <a href='index.php' class='light'>home page</a>.");
			}

			self::$variantIDsByGameID[$gameID]=$variantID;;
		}

		return self::loadFromVariantID(self::$variantIDsByGameID[$gameID]);
	}
}

?>