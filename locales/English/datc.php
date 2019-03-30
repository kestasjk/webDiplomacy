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
<div class="datc">
<p>
This is the output from webDiplomacy's <a href="http://web.inter.nl.net/users/L.B.Kruijswijk/">DATC</a>
test script, which loads certain tricky scenarios and runs the adjudicator on them, checking to see that
the end result is correct. It checks the adjudicator and the client-side order generation code, as all
orders have to also be correctly generated in the client-side Javascript.<br />
At the moment only Diplomacy-phase tests are run, and the Retreats and Unit-placement
phase tests have not been attempted. (Though these are also expected to pass on the current
adjudicator)
</p>
<p>
'Passed' means that the test passed, matching the DATC rules, 'NotPassed' means the test hasn't been attempted
or doesn't pass when attempted (which you shouldn't see on a live server) and 'Invalid' means that the test does not
apply to webDiplomacy.<br />
'Invalid' may seem like a cop-out, but many of the DATC tests are designed to test the software's handling of
text-based rules. Asking what webDiplomacy does when the user enters a textual order in an odd way makes no sense
since webDiplomacy shows the valid orders to the user to be selected from.
</p>

<a name="sections"></a><h4>Sections</h4>
<p>
The tests are split into the following sections:
<?php
$sections=array(
	1=>array('6.A.','TEST CASES, BASIC CHECKS'),
	2=>array('6.B.','TEST CASES, COASTAL ISSUES'),
	3=>array('6.C.','TEST CASES, CIRCULAR MOVEMENT'),
	4=>array('6.D.','TEST CASES, SUPPORTS AND DISLODGES'),
	5=>array('6.E.','TEST CASES, HEAD TO HEAD BATTLES AND BELEAGUERED GARRISON'),
	6=>array('6.F.','TEST CASES, CONVOYS'),
	7=>array('6.G.','TEST CASES, CONVOYING TO ADJACENT PLACES'),
	8=>array('webDip intro',l_t('webDiplomacy introduction image generating tests')),
	9=>array('webDip tests',l_t('webDiplomacy specific test cases'))
);
print '<ul>';
foreach( $sections as $sectionID=>$section )
	print '<li><a href="#section'.$sectionID.'">'.$section[0].'</a> - '.$section[1].'</li>';
print '</ul>';
?>
</p>

<a name="choices"></a><h4>Choices</h4>
<div id="showchoices">
<p>Some DATC tests may have multiple correct outcomes depending on certain choices made. <a class="light" href="#" onclick="$('choices').show(); $('showchoices').hide(); return false;">Show DATC choice details</a></p>
</div>
<div id="choices" style="<?php print libHTML::$hideStyle; ?>">
<p>
Wherever there was an option the recommended one was taken, giving these results:<br />
<ul>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.1">4.A.1</a> - B - A convoy is disrupted when all routes are disrupted</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.2">4.A.2</a> - D - Szykman convoy paradox rule</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.3">4.A.3</a> - D - Via convoy can be specified (and is always explicit)</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.4">4.A.4</a> - A - Attacking a unit supporting a move against you will not cut the support, even when you are attacking the support via convoy</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.5">4.A.5</a> - B - If dislodged by an adjacent unit which attacked via convoy the dislodged unit can retreat to the territory which the attacking convoyed move came from</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.6">4.A.6</a> - A - Convoy path specifications cannot be made</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.A.7">4.A.7</a> - B - A dislodged unit can affect the territory which dislodged it, if it was dislodged by a unit moving via convoy</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.B">4.B</a> - Invalid</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.C">4.C</a> - Invalid</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.1">4.D.1</a> - Invalid</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.2">4.D.2</a> - Invalid</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.3">4.D.3</a> - Invalid</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.4">4.D.4</a> - Invalid</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.5">4.D.5</a> - B - If many build orders are specified for the same place the first order will be used, and the rest discarded.</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.6">4.D.6</a> - B - If many destroy orders are specified for the same place the first order will be used, and the rest discarded.</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.7">4.D.7</a> - A - You can wait and build units at a later turn</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.8">4.D.8</a> - D - In civil disorder units will be removed by distance from the nearest supply center, allowing armies to move
	over any territory, and fleets to only move over places where fleets may move. If two units are at equal distance alphabetical order of the territories is used.</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.D.9">4.D.9</a> - B - Players can support hold civil disorder players</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.E.1">4.E.1</a> - D - Only orders which are valid in the current situation are legal.</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.E.2">4.E.2</a> - Invalid</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.E.3">4.E.3</a> - B - Implicit orders are not allowed. Making certain orders does imply and set other orders, but
	those orders are not implicit, because they are displayed as normal, and are not fixed. This gives the convenience of implicit orders without the ambiguity and other issues.</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.E.4">4.E.4</a> - B - Perpetual orders are not allowed</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.E.5">4.E.5</a> - C - Proxy orders are not allowed</li>
<li><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#4.E.6">4.E.6</a> - Invalid</li>
</ul>
</p>
</div>

</div>

<?php

libHTML::pagebreak();

$tabl = $DB->sql_tabl("SELECT testID, testName, status, testDesc FROM wD_DATC ORDER BY testID");

print '
<div class="datc">
<a name="tests"></a><h4>Tests</h4>
<table>
	';
$lastSectionID=-1;
while ( list($id, $name, $status, $description) = $DB->tabl_row($tabl) )
{
	$sectionID = floor($id/100);
	if( $sectionID != $lastSectionID )
	{
		print '<tr class="datc">
<th>'.$sections[$sectionID][0].'</th>
<th><a name="section'.$sectionID.'"></a>'.$sections[$sectionID][1].'</th>
</tr>';
	}
	$lastSectionID = $sectionID;

	if( $status=='Invalid' )
		$image = '(Invalid test)';
	elseif( $status=='NotPassed' )
		$image = 'Test not passed!';
	else
		$image = '
<a href="#" onclick="$(\'testimage'.$id.'\').src=\'datc/maps/'.$id.'-large.map\'; return false;">'.
			'<img id="testimage'.$id.'" src="'.STATICSRV.'datc/maps/'.$id.'-large.map-thumb" alt="Test ID #'.$id.' map thumbnail" />'.
			'</a>
			';

	$details = '<a name="test'.$id.'" href="http://web.inter.nl.net/users/L.B.Kruijswijk/#'.$name.'">'.
			$name.'</a> - '.$status.'<br />'.$description;

	print '
<tr class="datc">
<td><p class="notice">'.$image.'</p></td>
<td><p>'.$details.'</p></td>
</tr>
		';
}
print '</table>';
print '</div>';
print '</div>';
libHTML::footer();
