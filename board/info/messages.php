<?php
/*
    Copyright (C) 2004-2009 Kestas J. Kuliukas

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
 * Output the chat logs
 *
 * @package Board
 */


require_once('pager/pagerarchive.php');

list($itemsTotal) = $DB->sql_row("SELECT COUNT(*) FROM wD_GameMessages WHERE gameID = ".$Game->id." AND ".
	"(toCountryID = 0".(isset($Member)?" OR fromCountryID = ".$Member->countryID." OR toCountryID = ".$Member->countryID:'').")");
$pager = new PagerArchive($itemsTotal, $Game->id, 'Messages');

print $pager->html();


print '<h4>'.l_t('Chat archive').'</h4>';

print '<div class="variant'.$Game->Variant->name.'">';

ini_set('memory_limit',"16M");
$CB = $Game->Variant->Chatbox();
print '<table>'.$CB->getMessages( -1, $pager->SQLLimit()).'</table>';

// Set the global messages as seen (usefull in Nopress games to remove the newmessage-icon after a Gamemaster post)
if( is_object($Member) )
	$Member->seen(0);

print '</div>';

?>