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

/**
 * @package Base
 * @subpackage Static
 */
?>
<div>
<?php

// Get moderator list
global $DB;
$query = "SELECT CONCAT('<a href=\"profile.php?userID=',id,'\">',username,'</a>,')
FROM wD_Users WHERE type LIKE '%Moderator%'";
$tabl = $DB->sql_tabl($query);
$moderators = '';
while( list($row) = $DB->tabl_row($tabl) )
{
	$moderators .= $row . ' ';
}

$credits = array(
	array(
		'The Diplomacy Board Game',
		'If you like webDiplomacy, you can purchase the board game! <br />
		<a href="http://www.wizards.com/default.asp?x=ah/prod/diplomacy">Available for Purchase from Avalon Hill</a>'
	),
	array(
		'Co-owners of webDiplomacy',
		'<a href="http://kestas.kuliukas.com/">Kestas Kuliukas</a>, original creator of webDiplomacy<br />
		<a href="http://www.webdiplomacy.net/profile.php?userID=33599">Zultar</a>'
	),	
	array(
		'The webDiplomacy Moderators, reachable at ' . Config::$modEMail,
		$moderators
	),
	array(
		'The webDiplomacy Developers',
		'<a href="http://www.webdiplomacy.net/profile.php?userID=15658">jmo1121109</a>, current lead developer<br />
		<a href="https://www.webdiplomacy.net/profile.php?userID=10">kestasjk</a>'
	),
	array(
		'Lifetime Site Contributors',
		'<a href="https://www.webdiplomacy.net/profile.php?userID=51170">Peregrine Falcon</a>, for his time as a moderator, code contributor, and esteemed member<br />
		<a href="https://www.webdiplomacy.net/profile.php?userID=4946">abgemacht</a>, for his time as a moderator, administrator, ombudsman, and overseer of the webDip Player Map<br />
		<a href="https://www.webdiplomacy.net/profile.php?userID=54909">A_Tin_Can</a>, for his time as a moderator and as a leader of site development'
	),
	array(
		'Past Developers',
		'<a href="https://www.webdiplomacy.net/profile.php?userID=54909">A_Tin_Can</a><br />
		<a href="https://www.webdiplomacy.net/profile.php?userID=382">figlesquidge</a><br />
		<a href="http://www.webdiplomacy.net/profile.php?userID=3013">thewonderllama</a><br />'
	),
	array(
		'Overseer of the <a id="credits-player-map-a" href="https://www.google.com/maps/d/u/0/viewer?mid=zkz1OHicklqk.ky67Va8gNVi0">webDiplomacy Player Map (external link)</a>',
		'<a href="http://www.webdiplomacy.net/profile.php?userID=74492">Claesar</a>'
	),
	array(
		'Creation of the Original Ghost Ratings',
		'<a href="https://www.webdiplomacy.net/profile.php?userID=2188">TheGhostMaker</a><br />
		<a href="https://www.webdiplomacy.net/profile.php?userID=13677">Alderian</a>'
	),
	array(
		'Maintenance of the Ghost Ratings List',
		'<a href="https://www.webdiplomacy.net/profile.php?userID=2188">TheGhostMaker</a><br />
		<a href="https://www.webdiplomacy.net/profile.php?userID=13677">Alderian</a><br />
		<a href="https://www.webdiplomacy.net/profile.php?userID=23172">Hellenic Riot</a><br />
		<a href="https://www.webdiplomacy.net/profile.php?userID=37168">ghug</a>'
	),
	array(
		'Past Contributors',
		'Algis Kuliukas - original database design and maintenance <br />
		<a href="http://www.xcelco.on.ca/~ravgames/dipmaps/">Rob Addison</a> - creator of small diplomacy map image <br />
		Lucas Kruijswijk - authored the DATC adjudicator tests <br />
		mrlachette, Magilla, arning - pre-0.72 debugging and testing <br />
		jayp - development of multi-variant code <br />
		<a href="http://sourceforge.net/users/fallingrock/">Chris Hughes</a> - webDiplomacy Facebook development, variable phase lengths, game listings pagination <br />
		Carey Jensen - variant developer, goondip.com developer <br />
		Alex Lebedev - sponsored the localization support <br />
		<a href="https://sourceforge.net/sendmessage.php?touser=1295433">paranoidjpn</a> - Japanese translation, testing, UTF-8 support, developing the small PNG map <br />
		Oliver Auth - variant creator, owner of vDiplomacy' 
	),
	array(
		'Miscellaneous',
		'Tank and Battleship Icons - released under the <a href="http://www.opensource.org/licenses/gpl-license.php" class="light">GNU Public License</a><br />
		Font used in the fullscreen map - released under the <a href="contrib/BVFL.txt" class="light">Bitsream Vera Fonts License</a><br />
		Our JavaScript utility library - the <a href="http://www.prototypejs.org/">Prototype</a> JavaScript framework team'
	));

	// $leftColumn=array();
	// $rightColumn=array();

	// $half=ceil(count($credits)/2);
	// for($i=0;$i<$half;$i++)
	// {
	// 	$leftColumn[]=$credits[$i];
	// 	if ( isset($credits[$i+$half]) )
	// 		$rightColumn[]=$credits[$i+$half];
	// }

	// print '<div class="rightHalf"><ul class="formlist">';
	// foreach($rightColumn as $credit)
	// 	print '<li class="formlisttitle">'.$credit[0].'</li><li class="formlistdesc">'.$credit[1].'</li>';
	// print '</ul></div>';

	print '<div class="credits">';
	foreach($credits as $credit)
		print '<div class="credits-title">'.$credit[0].'</div><div class="credits-info">'.$credit[1].'</div>';
	print '</div>';

?>

</div>