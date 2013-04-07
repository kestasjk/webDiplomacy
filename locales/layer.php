<?php
/*
    Copyright (C) 2004-2012 Kestas J. Kuliukas

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


/*
 * This is the main include for localization support. It is intended to take care of everything, so that 
 * localizable content can be passed through the functions below, and everything else will be taken care 
 * of from here.
 * 
 * Although webDiplomacy could support user-based localization since the game is so oriented around 
 * communication it is taken that localization is on a server-by-server basis, not user-by-user.
 * 
 * 
 * This file should be able to function without any loaded locale; this way a light page (e.g. map.php
 * loading a cached map) should be able to run a text through the localization layer without it being
 * localized, if necessary.
 */

// Text request
function l_t($text) {
	global $Locale;
	
	$args = func_get_args();
	array_shift($args);
	
	if( !isset($Locale) || $Locale == null )
		return vsprintf($text, $args);
	
	return $Locale->text($text, $args);
}

// PHP request / include requests
function l_r($include) {
	global $Locale;
	
	if( !isset($Locale) || $Locale == null )
		return $include;
	
	return $Locale->includePHP($include);
}

// Static resource requests
function l_s($resource) {
	global $Locale;
	
	if( !isset($Locale) || $Locale == null )
		return $resource;
	
	return $Locale->staticFile($resource);
}

// Javascript include requests
function l_j($javascriptInclude) {
	global $Locale;
	
	if( !isset($Locale) || $Locale == null )
		return $javascriptInclude;
	
	return $Locale->includeJS($javascriptInclude);
}

// Javascript function calls
function l_jf($javascriptFunction) {
	global $Locale;
	
	if( !isset($Locale) || $Locale == null )
		return $javascriptFunction;
	
	return $Locale->functionJS($javascriptFunction);
}

// Variant class requests
function l_vc($variantClassName) {
	global $Locale;
	
	if( !isset($Locale) || $Locale == null )
		return $variantClassName;
	
	return $Locale->variantClass($variantClassName);
}


abstract class Locale_Abstract { // Locale is a reserved class on Windows
	
	// Called in header.php, immidiately after config loaded
	public function initialize() {
	}
	
	// Called in libHTML::footerScripts() , before JavaScripts are output.
	public function onFinish() {
		
	}
	
	// The text lookup array, a hash table of strings with English keys
	protected $textLookup = array();
	
	// Lookup text in the lookup array, registering and returning untranslated if failed
	protected function textLookup($text) {
		if( isset($this->textLookup[$text]) )
			return $this->textLookup[$text];
		else 
		{
			$this->failedLookup($text);
			return $text;
		}
	}
	
	// An array of failed text lookups
	public $failedLookups = array();
	
	// A failed lookup has occurred. Log it if in debug mode
	protected function failedLookup($text) {
		
		if( Config::$debug )
			$this->failedLookups[$text] = $text;
	}
	
	// Text substitute
	public function text($text, array $args=array()) {
		
		$text = $this->textLookup($text);
		
		if( count($args) == 0 )
			return $text;
		else
			return vsprintf($text, $args);
	}
	// PHP include substitute
	public function includePHP($include) {
		return $include;
	}
	// JavaScript file substitute
	public function includeJS($javascriptInclude) {
		return $javascriptInclude;
	}
	// Variant class name substitute
	public function variantClass($variantClassName) {
		return $variantClassName;
	}
	// Static file (CSS/image) substitute
	public function staticFile($resource) {
		return $resource;
	}
	// JavaScript function substitute
	public function functionJS($javascriptFunction) {
		return $javascriptFunction;
	}
	
	/*
	 * A function to save all failed lookups to a database table, which can be cleared/output from the admin CP.
	 * Useful for clearing up the last few translations which are needed, best run from onFinished() .
	 */
	protected function logFailedLookups() {
		global $DB;
		
		// Save all failed lookups to the database.
		if( isset($DB) && is_object($DB) && isset($this->failedLookups) && is_array($this->failedLookups) && count($this->failedLookups)>0)
		{
			// If the table to do this doesn't exist, create it (it's a niche feature
			$DB->sql_put(
						"CREATE TABLE IF NOT EXISTS wD_FailedLookups (
						  id int unsigned NOT NULL AUTO_INCREMENT,
						  lookupString varchar(2000) NOT NULL,
						  count int(11) NOT NULL,
						  aggregateFlag tinyint(4) NOT NULL DEFAULT '0',
						  PRIMARY KEY (id),
						  KEY lookupString (lookupString(1024))
						) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
				
			// Base 64 encode all failed lookups, so the exact strings to be translated can be faithfully output for translation, and to prevent DB escaping issues
			$arr=array();
			foreach($this->failedLookups as $l)
			$arr[]=base64_encode($l);
				
			$DB->sql_put("INSERT INTO wD_FailedLookups ( lookupString, count ) VALUES ('"
			.implode("',1),('", $arr)
			."',1)");
				
			if( rand(0, 100) < 4 )
			{
				// 4% of the time run a query to compress the failed lookups table by aggregating duplicate failed lookups
				$DB->sql_put("INSERT INTO wD_FailedLookups ( lookupString, count, aggregateFlag ) ".
							"SELECT lookupString, SUM(count) as count, 1 FROM wD_FailedLookups WHERE aggregateFlag = 0");
				$DB->sql_put("DELETE FROM wD_FailedLookups WHERE aggregateFlag = 0");
				$DB->sql_put("UPDATE wD_FailedLookups SET aggregateFlag = 0");
			}
		}
	}
}
