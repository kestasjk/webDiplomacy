<?php
/*
    Copyright (C) 2004-2013 Kestas J. Kuliukas

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
 * A page to allow administration of locales. Facilitates the upload
 * of new lookup lists to the locale directory via file upload, and allows 
 * the dumping and clearing of the (optional) failed lookups table.
 * 
 * @package Admin
 */

if( !$User->type['Admin'] ) 
{
	print l_t("This page is intended only for administrators to do work on locale lookup lists.");
	libHTML::footer();
}

?>
<style type="text/css">
	textarea {
		font-size:10pt ! important;
		font-family:monospace, courier !important
	}
</style>
<h4><?php print l_t('Failed lookup database'); ?></h4>
<p>
<?php print l_t('A table of failed lookups, which can be output as text for translation, or cleared. (Careful; these are not confirmed!)'); ?><br />
<?php print l_t('Note that these do not include JavaScript failures, which are not captured.'); ?><br />
<?php print '<a href="?failedLookupsDump=on">'.l_t('Dump').'</a> - <a href="?failedLookupsWipe=on">'.l_t('Wipe').'</a>'; ?>
</p>

<?php 
if( isset($_REQUEST['failedLookupsDump']) )
{
	$tabl = $DB->sql_tabl("SELECT * FROM ( SELECT lookupString, SUM(count) count FROM wD_FailedLookups GROUP BY lookupString ) a ORDER BY count DESC");
	print '<p class="notice">'.l_t('Dumping failed lookups: (Most frequent to least frequent)').'</p>';
	print '<textarea ROWS="20" style="width:100%">';
	while($row = $DB->tabl_hash($tabl))
	{
		print "'".str_replace("'", "\\'", base64_decode($row['lookupString']))."' => '',\n";
	}
	print '</textarea>';
}
elseif( isset($_REQUEST['failedLookupsWipe']) )
{
	$DB->sql_put("DELETE FROM wD_FailedLookups");
	print '<p class="notice">'.l_t('Failed lookups table cleared.').'</p>';
}
?>

<h4><?php print l_t('Uploading translations.'); ?></h4>
<p><?php print l_t('Copy the translations at the bottom into a text file, <b>take a backup</b>, make any necessary modifications, then reupload the file.'); ?><br /><br />

<?php print l_t('If you get errors check that the translations are well formed. You should be able to paste them into a PHP script that looks like this:'); ?>
<Br /><textarea ROWS="6" style="width:50%"><?php print "<?php \$translations=array(
' from %s' => 'da %s',
' to %s' => 'a %s',
' via convoy' => 'con trasporto'
);
?>";
?>
</textarea><br />
<?php print l_t('And it should run without any syntax errors.'); ?></p>
<div class="hr"></div>
<?php 

error_reporting(E_STRICT | E_ALL | E_NOTICE);

if( isset($_FILES["file"]) ) 
{
	print '<h2>'.l_t('Processing upload').'</h2>';
	print '<p>'.l_t('Loading data..').'<br />';
	
	if ($_FILES["file"]["error"] > 0)
		throw new Exception(l_t("Error:")." " . $_FILES["file"]["error"]);
	
	$translations = file_get_contents($_FILES["file"]["tmp_name"]);
	
	$length = strlen($translations);
	
	$mode = 'whitespace_before';
	$quote_mode = 'single';
	$string = array();
	$string_from = "";
	$parsed = array();
	
	function error_context($translations, $i) 
	{
		$start = $i - 20;
		$length = 40;
		if( $start < 0 ) $start = 0;
		return substr($translations, $start, $length);
	}
	
	function process_string($str) 
	{
		return mb_convert_encoding(stripslashes($str),"UTF-8","US-ASCII");
	}
	
	// whitespace_before -> quote
	// string_from -> quote			<== translate from
	// whitespace_between_first -> =>
	// whitespace_between_last -> quote
	// string_to -> quote 			<== translate to
	// whitespace_after -> ,
	
	print l_t('Parsing..').'<br />';
	
	for( $i = 0; $i < $length; $i++)
	{
		//print "Character ".$i.". Mode: ".$mode.". Character: ".$translations[$i].".<br />";
		
		switch($mode) 
		{
			case 'whitespace_before':
				if( $translations[$i] == ' ' || $translations[$i] == "\r" || $translations[$i] == "\n"  || $translations[$i] == "\t" )
					continue;
				else if( $translations[$i] == "'" )
				{
					$quote_mode = 'single';
					$mode = "string_from";
				}
				else if( $translations[$i] == '"' )
				{
					$quote_mode = 'double';
					$mode = "string_from";
				}
				else
				{
					throw new Exception(l_t("Parse error at character %s. Mode: %s. Character: %s. Context: %s",$i,$mode,$translations[$i],$error_context($translations, $i)));
				}
				break;
				
			case 'string_from':
				if( ( $quote_mode == 'single' && ($translations[$i] == "'" && ( $i == 0 || $translations[$i-1] != '\\' ) ) )  ||
					( $quote_mode == 'double' && ($translations[$i] == '"' && ( $i == 0 || $translations[$i-1] != '\\' ) ) ) ) 
				{
					$mode = 'whitespace_between_first';
					$string_from = process_string(implode('',$string));
					$parsed[$string_from] = "";
					$string = array();
				}
				else
				{
					$string[] = $translations[$i];
				}
				break;
				
			case 'whitespace_between_first':
				if( $translations[$i] == ' ' || $translations[$i] == "\n"  || $translations[$i] == "\t" )
					continue;
				else if( $translations[$i] == '=' && $translations[$i+1] == '>' )
				{
					$i++;
					$mode = 'whitespace_between_last';
				}
				else
				{
					throw new Exception(l_t("Parse error at character %s. Mode: %s. Character: %s. Context: %s",$i,$mode,$translations[$i],$error_context($translations, $i)));
				}
				break;
				
			case 'whitespace_between_last':
				if( $translations[$i] == ' ' || $translations[$i] == "\n"  || $translations[$i] == "\t" )
					continue;
				else if( $translations[$i] == "'" )
				{
					$quote_mode = 'single';
					$mode = "string_to";
				}
				else if( $translations[$i] == '"' )
				{
					$quote_mode = 'double';
					$mode = "string_to";
				}
				else
				{
					throw new Exception(l_t("Parse error at character %s. Mode: %s. Character: %s. Context: %s",$i,$mode,$translations[$i],$error_context($translations, $i)));
				}
				break;
				
			case 'string_to':
				if( ( $quote_mode == 'single' && ($translations[$i] == "'" && $translations[$i-1] != '\\' ) ) ||
					( $quote_mode == 'double' && ($translations[$i] == '"' && $translations[$i-1] != '\\' ) ) ) 
				{
					$mode = 'whitespace_after';
					$parsed[$string_from] = process_string(implode('',$string));
					$string = array();
				}
				else
				{
					$string[] = $translations[$i];
				}
				break;
				
			case 'whitespace_after':
				if( $translations[$i] == ' ' || $translations[$i] == "\n"  || $translations[$i] == "\t" )
					continue;
				else if( $translations[$i] == ',' )
				{
					$mode = "whitespace_before";
				}
				else
				{
					throw new Exception(l_t("Parse error at character %s. Mode: %s. Character: %s. Context: %s",$i,$mode,$translations[$i],$error_context($translations, $i)));
				}
				break;
				
			default:
				throw new Exception(l_t("Unexpected parse mode: %s",$mode));
		}
	}
	
	print l_t('Taking a backup of PHP lookups..').'<br />';
	if( file_exists('locales/'.Config::$locale.'/lookup.php.txt') )
	{
		if( !copy('locales/'.Config::$locale.'/lookup.php.txt', 'locales/'.Config::$locale.'/lookup.php.txt-'.time().'.bak'))
			throw new Exception(l_t("Couldn't back up lookup.php.txt"));
	}
	
	print l_t('Saving to PHP..').'<br />';
	
	if( false === file_put_contents('locales/'.Config::$locale.'/lookup.php.txt',serialize($parsed)) ) 
	{
		throw new Exception(l_t("Couldn't write results to lookup.php.txt"));
	}
	
	print l_t('Taking a backup of JS lookups..').'<br />';
	if( file_exists('locales/'.Config::$locale.'/lookup.js') )
	{
		if( !copy('locales/'.Config::$locale.'/lookup.js', 'locales/'.Config::$locale.'/lookup.js-'.time().'.bak'))
			throw new Exception(l_t("Couldn't back up lookup.js"));
	}
	
	print l_t('Saving to JavaScript..').'<br />';
	
	$js = "Locale.textLookup = \$H({\n";
	
	foreach($parsed as $k=>$v) 
	{
		$js .= "\t'".
			str_replace("'", "\\'", str_replace("\n", "\\\n", str_replace("\r\n", "\n", $k)))
			."': '".
			str_replace("'", "\\'", str_replace("\n", "\\\n", str_replace("\r\n", "\n", $v)))
			."',\n";
		
	}
	$js .= "});\n";
	
	if( false === file_put_contents('locales/'.Config::$locale.'/lookup.js',$js) ) 
	{
		throw new Exception(l_t("Couldn't write results to lookup.js"));
	}
	
	print l_t('Done').'.<br /><br />'.l_t('Check below that the translations have been applied successfully.').'</p>';
}
?>

<h2><?php print l_t('Translations upload:'); ?></h2>
<form method="post" enctype="multipart/form-data">
<input type="file" name="file" id="file"><br>
<input type="Submit" value="Submit" />
</form>
<div class="hr"></div>
<h2><?php print l_t('Current translations:'); ?></h2>
<textarea ROWS="20" style="width:100%">
<?php 

if( file_exists('locales/'.Config::$locale.'/lookup.php.txt')) 
{
	$current = unserialize(file_get_contents('locales/'.Config::$locale.'/lookup.php.txt'));
	foreach( $current as $k=>$v)
		print "'".str_replace('&', '&amp;', addslashes($k))."' => '".str_replace('&', '&amp;', addslashes($v))."',\n";
}

?></textarea>