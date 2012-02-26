<?php
/*
Copyright (C) 2004-2011 Oliver Auth

This file is part of vDiplomacy.

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

/**
* @package Base
* @subpackage Static
*/

require_once('header.php');

libHTML::starthtml();

if(!(isset($_REQUEST['variantID'])))
{
	print libHTML::pageTitle('webDiplomacy variants','A list of the variants available on this server, with credits and information on variant-specific rules.');
	$variantsOn=array();
	$variantsOff=array();

	foreach(glob('variants/*') as $variantDir)
	{
		if( file_exists($variantDir.'/variant.php') )
		{
			$variantDir=substr($variantDir,9);
			if( in_array($variantDir, Config::$variants) )
				$variantsOn[] = $variantDir;
			else
				$variantsOff[] = $variantDir;
		}
	}

	if( count($variantsOff) )
		print '<a name="top"></a><h4>Active variants</h4>';
		
	print '<ul>';
	foreach( $variantsOn as $variantName )
	{
		$Variant = libVariant::loadFromVariantName($variantName);
		print '<li>'.$Variant->link().' (' . count($Variant->countries) . ' Players)';
		$sql = 'SELECT COUNT(*) FROM wD_Games WHERE variantID=' .  $Variant->id . ' AND phase != "Pre-game"';
		list($num) = $DB->sql_row($sql);
		print ' - '.$num.' game'.($num!=1?'s':'').' played on this server</li>';
	}
	print '</ul>';

	if( count($variantsOff) )
	{
		print '<h4>Disabled variants</h4>';
		print '<p>Variants which are present but not activated.</p>';
		print '<ul>';
		foreach( $variantsOff as $variantName )
		{
			$Variant = libVariant::loadFromVariantName($variantName);
			print '<li>' . $Variant->name . '</a> (' . count($Variant->countries) . ' Players)</li>';
		}
		print '</ul>';
	}

	print '<div class="hr"></div>';
}
else
{
	$id=intval($_REQUEST['variantID']);
	if (!(isset(Config::$variants[$id])))
		foreach (array_reverse(Config::$variants,true) as $id => $name);
	$Variant = libVariant::loadFromVariantID($id);
	print libHTML::pageTitle($Variant->fullName . ' (' . count($Variant->countries) . ' players)',$Variant->description);
	print '<div style="text-align:center"><img id="Image_'. $Variant->name . '" src="';
	if (file_exists(libVariant::cacheDir($Variant->name).'/sampleMap.png'))
		print libVariant::cacheDir($Variant->name).'/sampleMap.png';
	else
		print 'map.php?variantID=' . $Variant->id;
	print '" alt=" " title="The map for the '. $Variant->name .' Variant" /></div><br />';

	print '<table>
		<td style="text-align:left">Search for games: 		
			<form style="display: inline" action="gamelistings.php" method="POST">
				<input type="hidden" name="gamelistType" value="New" />
				<input type="hidden" name="searchOff" value="true" />
				<input type="hidden" name="search[chooseVariant]" value="'.$Variant->id.'" />
				<input type="submit" value="New" /></form>							
			<form style="display: inline" action="gamelistings.php" method="POST">
				<input type="hidden" name="gamelistType" value="Open" />
				<input type="hidden" name="searchOff" value="true" />
				<input type="hidden" name="search[chooseVariant]" value="'.$Variant->id.'" />
				<input type="submit" value="Open"/></form>				
			<form style="display: inline" action="gamelistings.php" method="POST">
				<input type="hidden" name="gamelistType" value="Active" />
				<input type="hidden" name="searchOff" value="true" />
				<input type="hidden" name="search[chooseVariant]" value="'.$Variant->id.'" />
				<input type="submit" value="Active" /></form>
			<form style="display: inline" action="gamelistings.php" method="POST">
				<input type="hidden" name="gamelistType" value="Finished" />
				<input type="hidden" name="searchOff" value="true" />
				<input type="hidden" name="search[chooseVariant]" value="'.$Variant->id.'" />
				<input type="submit" value="Finished" /></form>
		</td> <td style="text-align:right">
			<form style="display: inline" action="stats.php" method="GET">
				<input type="hidden" name="variantID" value="'.$Variant->id.'" />
				<input type="submit" value="View statistics" /></form>			
			<form style="display: inline" action="files.php" method="GET">
				<input type="hidden" name="variantID" value="'.$Variant->id.'" />
				<input type="submit" value="View/Download code" /></form>
		</td>
	</table>';
			
	print '<br><div><strong>Variant Parameters';
	if ((isset($Variant->version)) || (isset($Variant->CodeVersion)))
	{
		print ' (';
		if (isset($Variant->version))
			print 'Version: '. $Variant->version.(isset($Variant->codeVersion)?' / ':'');
		if (isset($Variant->codeVersion))
			print 'Code: ' . $Variant->codeVersion;
		print ')';
	}
	print ':</strong>';
	
	print '<ul>';
	if (isset($Variant->homepage))
		print '<li><a href="'. $Variant->homepage .'">Variant homepage</a></li>';
	if (isset($Variant->author))
		print '<li> Created by: '. $Variant->author .'</li>';
	if (isset($Variant->adapter))
		print '<li> Adapted for webDiplomacy by: '. $Variant->adapter .'</li>';
	print '<li> SCs required for solo win: ' . $Variant->supplyCenterTarget . ' (of '.$Variant->supplyCenterCount.')</li>';
	if (!file_exists('variants/'. $Variant->name .'/rules.html'))
		print '<li>Standard Diplomacy Rules Apply</li>';
	print '</ul>';

	if (file_exists('variants/'. $Variant->name .'/rules.html'))
	{
		print '<p><strong>Special rules/information:</strong></p>';
		print '<div>'.file_get_contents('variants/'. $Variant->name .'/rules.html').'</div>';
	}
}

print '</div>';
libHTML::footer();

?>