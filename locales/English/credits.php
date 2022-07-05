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

global $DB;

$tabl = $DB->sql_tabl("SELECT CONCAT('<a href=\"userprofile.php?userID=',id,'\">',username,'</a>') FROM wD_Users WHERE type LIKE '%Moderator%'");
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
		'Managing the <a href="https://discord.gg/f2ram4w">webDiplomacy Discord Server</a>',
		'<a href="userprofile.php?userID=100862">Aurelin</a><br />
		<a href="userprofile.php?userID=41379">captainmeme</a><br />'
	),
	array(
		'Co-owners of webDiplomacy',
		'<a href="http://kestas.kuliukas.com/">Kestas Kuliukas</a>, original creator of webDiplomacy<br />
		<a href="userprofile.php?userID=33599">Zultar</a>'
	),	
	array(
		'The webDiplomacy Moderators, contact at '.Config::$modEMail, $moderators
	),
	array(
		'The webDiplomacy Developers',
		'<a href="userprofile.php?userID=10">kestasjk</a><br />
		<a href="userprofile.php?userID=15658">jmo1121109</a><br />
		<a href="userprofile.php?userID=59641">Squigs44</a><br />
		<a href="userprofile.php?userID=38739">bo_sox48</a>'
	),
	array(
		'Point and click board development',
		'<a href="https://www.codazen.com">Codazen</a><br />
		<a href="https://ai.facebook.com/">Meta AI</a>'
	),
	array(
		'Lifetime Site Contributors',
		'<a href="userprofile.php?userID=51170">Peregrine Falcon</a>, for his time as a moderator, code contributor, and esteemed member<br />
		<a href="userprofile.php?userID=4946">abgemacht</a>, for his time as a moderator, administrator, ombudsman, and overseer of the webDip Player Map<br />
		<a href="userprofile.php?userID=54909">A_Tin_Can</a>, for his time as a moderator and as a leader of site development'
	),
	array(
		'Past Developers',
		'<a href="userprofile.php?userID=54909">A_Tin_Can</a><br />
		<a href="userprofile.php?userID=382">figlesquidge</a><br />
		<a href="userprofile.php?userID=3013">thewonderllama</a><br />'
	),
	array(
		'Overseer of the <a id="credits-player-map-a" href="https://www.google.com/maps/d/u/0/viewer?mid=zkz1OHicklqk.ky67Va8gNVi0">webDiplomacy Player Map (external link)</a>',
		'<a href="userprofile.php?userID=74492">Claesar</a>'
	),
	array(
		'Creation of the Original Ghost Ratings',
		'<a href="userprofile.php?userID=2188">TheGhostMaker</a><br />
		<a href="userprofile.php?userID=13677">Alderian</a>'
	),
	array(
		'Integration of Ghost Ratings into the codebase',
		'<a href="userprofile.php?userID=59641">Squigs44</a><br />
		<a href="userprofile.php?userID=38739">bo_sox48</a><br/>
		<a href="userprofile.php?userID=15658">jmo1121109</a>'
	),
	array(
		'webDiplomacy AI development',
		'<a href="https://github.com/ppaquette">Philip Paquette</a> - <a href="https://github.com/diplomacy/research/blob/master/neurips_paper_v1.pdf" class="light">Underlying AI/ML Collaborative Research Paper</a><br />
		Noam Brown et al - <a href="https://ai.facebook.com/">Meta AI</a>' 
	),
	array(
		'Past Contributors',
		'<strong><u>Algis Kuliukas</u></strong> - original database design and maintenance <br />
		<a href="http://www.xcelco.on.ca/~ravgames/dipmaps/">Rob Addison</a> - creator of small diplomacy map image <br />
		<strong><u>Lucas Kruijswijk</u></strong> - authored the DATC adjudicator tests <br />
		<strong><u>mrlachette, Magilla, arning</u></strong> - pre-0.72 debugging and testing <br />
		<strong><u>jayp</u></strong> - development of multi-variant code <br />
		<a href="http://sourceforge.net/users/fallingrock/">Chris Hughes</a> - webDiplomacy Facebook development, variable phase lengths, game listings pagination <br />
		<strong><u>Carey Jensen</u></strong> - variant developer, goondip.com developer <br />
		<strong><u>Alex Lebedev</u></strong> - sponsored the localization support <br />
		<a href="https://sourceforge.net/sendmessage.php?touser=1295433">paranoidjpn</a> - Japanese translation, testing, UTF-8 support, developing the small PNG map <br />
		<strong><u><a href="userprofile.php?userID=18263">Oliver Auth</a></u></strong> - variant creator, owner of <a href="https://vdiplomacy.net" class="light">vDiplomacy</a>' 
	),
	array(
		'Miscellaneous',
		'<strong><u>Tank and Battleship Icons</u></strong> - released under the <a href="http://www.opensource.org/licenses/gpl-license.php" class="light">GNU Public License</a><br />
		<strong><u>Font used in the fullscreen map</u></strong> - released under the <a href="contrib/BVFL.txt" class="light">Bitsream Vera Fonts License</a><br />
		<strong><u>Our JavaScript utility library</u></strong> - the <a href="http://www.prototypejs.org/" class="light">Prototype</a> JavaScript framework team'
	));

	print '<div class="credits">';

	foreach($credits as $credit)
	{
		print '<div class="credits-title">'.$credit[0].'</div><div class="credits-info">'.$credit[1].'</div>';
	}

	print '</div>';
?>
</div>
