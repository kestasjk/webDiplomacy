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

/**
* @package Base
* @subpackage Static
*/

require_once('header.php');

libHTML::starthtml();

print libHTML::pageTitle('webDiplomacy variants','A list of the variants available on this server, with credits and information on variant-specific rules.');

$variantsOn=array();
$variantsOff=array();

$variants = glob('variants/*');
foreach($variants as $variantDir) {
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
   print '<li><a href="variants.php#' . $Variant->name . '">' . $Variant->fullName . '</a> (' . count($Variant->countries) . ' Players)';
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
	   print '<li><a href="variants.php#'   . $Variant->name . '">' . $Variant->name . '</a> (' . count($Variant->countries) . ' Players)</li>';
	}
	print '</ul>';
}

libHTML::pagebreak();


foreach( $variantsOn as $variantName )
{
   $Variant = libVariant::loadFromVariantName($variantName);
   print '<h2><a name="'. $Variant->name .'"></a>'. $Variant->fullName . ' (' . count($Variant->countries) . ' players)</h2>';
   if (isset($Variant->description))
      print $Variant->description."<br /><br />";

   print '<div style="text-align:center"><img id="Image_'. $Variant->name . '" src="';
   if (file_exists(libVariant::cacheDir($Variant->name).'/sampleMap.png'))
      print libVariant::cacheDir($Variant->name).'/sampleMap.png';
   else
      print 'map.php?variantID=' . $Variant->id;
   print '" alt=" " title="The map for the '. $Variant->name .' Variant" /></div><br />';
   print '<strong>Variant Parameters';
   if (isset($Variant->version))
      print ' (Version: '. $Variant->version .')';
   print ':</strong>';
   print '<ul>';

   if (isset($Variant->homepage))
      print '<li><a href="'. $Variant->homepage .'">Variant homepage</a></li>';
   if (isset($Variant->author))
      print '<li> Created by: '. $Variant->author .'</li>';
   if (isset($Variant->adapter))
      print '<li> Adapted for webDiplomacy by: '. $Variant->adapter .'</li>';
   print '<li> SCs required for solo win: ' . $Variant->supplyCenterTarget . '</li>';

   if (!file_exists('variants/'. $Variant->name .'/rules.html'))
      print '<li>Standard Diplomacy Rules Apply</li>';

   print '</ul>';

   if (file_exists('variants/'. $Variant->name .'/rules.html')) {
      print '<p><strong>Special rules/information:</strong></p>';
      print '<div>'.file_get_contents('variants/'. $Variant->name .'/rules.html').'</div>';
   }
   print '<div><a href="#top" class="light">Back to top</a></div>';

   print '<div class="hr"></div>';
}

print '</div>';
libHTML::footer();

?>