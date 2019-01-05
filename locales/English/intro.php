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

print libHTML::pageTitle('Intro to webDiplomacy','A quick &amp; easy guide to get newcomers to webDiplomacy playing the game.');

print '
<p>
Diplomacy is a game which is easy to learn but impossible to master. The rules are all very intuitive,
lots of people pick them up just by playing, but this document will familiarize you more quickly.
</p>

<div class="hr"></div>';

?>
<p style="text-align:center"><a href="#RR">RR</a> - <a href="#Points">Points</a> - <a href="#Objective">Objective</a> - <a href="#Units">Units</a> -
	<a href="#Moves">Moves</a> - <a href="#Rules">Rules</a> - <a href="#Play">Play</a></p>

<div class="hr"></div>

<p>webDiplomacy, as a community, is a competitive and fun place to play. Currently we have two site supported ranking systems. </p>

<a name="RR"></a>
<h3>RR</h3>
<p>
The first is our “Reliability Rating” (RR); this ranking is determined based on how often you fail to enter
 moves in a turn (NMR/No Moves Received) or leave a game before it’s conclusion (CD/Civil Disorder). Most tournaments, special rules games, 
 or high stakes games have RR restrictions; meaning if you leave games often or irresponsibly forget to enter 
 moves frequently you may find it hard to join competitive games. 
<br /><br />

<div class="hr"></div>

<a name="Points"></a>
<h3>Points</h3>
<p>
The second system is our point system (<img src="images/icons/points.png" alt="D" title="webDiplomacy points">), which is used to determine how many 
games you can join and what stakes the game will be. You start with 100 <img src="images/icons/points.png" alt="D" title="webDiplomacy points"> and are 
reimbursed whenever a loss of points results in you having less than 100 <img src="images/icons/points.png" alt="D" title="webDiplomacy points">. 
Points are fun and provide a tangible reward for winning but are far less important to your 
ability to play fun and enthralling games on webDiplomacy. Many of our tournaments are unranked, 
and those that aren’t are usually willing to play at lower <img src="images/icons/points.png" alt="D" title="webDiplomacy points"> levels for players with very high Reliability Ratings.
<br /><br />

<div class="hr"></div>

<a name="Objective"></a>
<h3>Objective</h3>
<p>
The objective of Diplomacy is to be the first to get 18 supply centers. For each supply center
you occupy you get a new unit, and you lose a unit whenever a supply center you own gets
occupied by someone else.<br /><br />
You can recognize the supply centers with the markers which are placed on them.</p>
<p style="text-align:center;">
	<img src="images/intro/supply.png" alt=" " title="Supply centers are marked (large map)" />
	<img src="images/intro/supply2.png" alt=" " title="Supply centers are marked (small map)" />
</p>

<div class="hr"></div>

<a name="Units"></a>
<h3>Units</h3>
<ul class="formlist">
	<li class="formlisttitle">Army <img src="<?php print STATICSRV; ?>contrib/army.png"
		alt=" "  title="An army unit icon" /></li>
	<li class="formlistdesc">
		This unit can only move on land.
	</li>

	<li class="formlisttitle">Fleet <img src="<?php print STATICSRV; ?>contrib/fleet.png"
		alt=" " title="A fleet unit icon" /></li>
	<li class="formlistdesc">
		This unit can only move in the sea, and in coastal territories. It
		can also convoy armies across sea territories using the convoy move.
	</li>
</ul>

<div class="hr"></div>
<a name="Moves"></a>
<h3>Moves</h3>
<ul class="formlist">
	<li class="formlisttitle">Hold</li>
	<li class="formlistdesc">
		The unit will defend if its territory is attacked, but otherwise do nothing.
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/801-large.map-thumb" alt=" " title="An army holds in Naples" />
		</p>
	</li>


	<li class="formlisttitle">Move</li>
	<li class="formlistdesc">
		The unit tries to move into(/attack) an adjacent territory.
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/802-large.map-thumb" alt=" " title="An army in Naples moves to Rome" />
		</p>
	</li>


	<li class="formlisttitle">Support hold, support move</li>
	<li class="formlistdesc">
		Support is what Diplomacy is all about. As no one unit is stronger than another you need to
		combine the strength of multiple units to attack other territories.<br />
		<em>(Try hovering your mouse over the more complex battles to get more explanation.)</em>
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/803-large.map-thumb" alt=" "
				title="A yellow support-move lets the army in Venice overpower the army holding in Rome" />
			<img src="<?php print STATICSRV; ?>datc/maps/804-large.map-thumb" alt=" "
				title="A green support-hold from the fleet in Tyrrhenian Sea lets Rome hold against an equally well-supported Venice" />
		</p>
	</li>


	<li class="formlisttitle">Convoy</li>
	<li class="formlistdesc">
		You can use fleets to carry army units across sea territories, this is called a convoy. You
		can also string multiple convoys together to move an army unit large distances overseas in a single
		turn.

		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/805-large.map-thumb" alt=" "
				title="An army in Venice moves to Tunis, convoyed by the fleets in Adriatic Sea and Ionian Sea" />
		</p>
	</li>
</ul>

<div class="hr"></div>
<a name="Rules"></a>
<h3>Rules</h3>
<ul class="formlist">
<li class="formlistdesc">
	In diplomacy no army or fleet is stronger than another, and a <strong>holding</strong>
	unit will always beat a <strong>moving</strong> unit of equal support.
	<p style="text-align:center;">
		<img src="<?php print STATICSRV; ?>datc/maps/806-large.map-thumb" alt=" "
			title="An army in Naples attempts to move to Rome, but has no support, so the defending army is not dislodged" />
		<img src="<?php print STATICSRV; ?>datc/maps/807-large.map-thumb" alt=" "
			title="The fleet and army are both equally matched in their attempt to move into Apulia, so neither succeeds" />
	</p>
</li>


<li class="formlistdesc">
	The only way to win a battle is by supporting a <strong>moving</strong> unit with another unit, using a yellow
	<strong>support move</strong> order.
	<p style="text-align:center;">
		<img src="<?php print STATICSRV; ?>datc/maps/803-large.map-thumb" alt=" "
				title="A yellow support-move lets the army in Venice overpower the army holding in Rome" />
	</p>
</li>


<li class="formlistdesc">
	And support can be given to <strong>holding</strong> units, with a green <strong>support hold</strong> order.
	<p style="text-align:center;">
		<img src="<?php print STATICSRV; ?>datc/maps/804-large.map-thumb" alt=" "
				title="A green support-hold from the fleet in Tyrrhenian Sea lets Rome hold against an equally well-supported Venice" />
	</p>
</li>


<li class="formlistdesc">
	If the number of <strong>support moves</strong> are greater than the number of <strong>support holds</strong>
	the move will succeed, otherwise it will fail.
	<p style="text-align:center;">
		<img src="<?php print STATICSRV; ?>datc/maps/808-large.map-thumb" alt=" "
				title="The fleet moving from Trieste to Venice has two support-moves, and the fleet in Venice has only one support-hold, so Trieste succeeds" />
		<img src="<?php print STATICSRV; ?>datc/maps/809-large.map-thumb" alt=" "
				title="The fleet moving from Trieste to Venice has two support-moves, but the fleet in Venice has two support-holds, so Trieste and Venice are equally matched and the attacker is blocked" />
	</p>
</li>

<li class="formlistdesc">
	Also; if a unit is being attacked it has to defend itself by <strong>holding</strong>, and can't support another unit.
	<p style="text-align:center;">
		<img src="<?php print STATICSRV; ?>datc/maps/808-large.map-thumb" alt=" "
				title="No supporting units are being attacked, all of them count: Trieste 2 - Venice 1; Trieste moves" />
		<img src="<?php print STATICSRV; ?>datc/maps/810-large.map-thumb" alt=" "
				title="An army from Munich attacks Tyrolia, preventing it from supporting Trieste: Trieste 1 - Venice 1; Trieste stays" />
		<img src="<?php print STATICSRV; ?>datc/maps/811-large.map-thumb" alt=" "
				title="A fleet in the Tyrrhenian Sea attacks Rome, preventing it from supporting Venice: Trieste 1 - Venice 0; Trieste moves" />
	</p>
</li>

</ul>
<div class="hr"></div>
<ul class="formlist">
<li class="formlistdesc">
	<a name="Play"></a>
	With these rules you know everything you need to start playing Diplomacy online! After you
	<a href="register.php" class="light">register</a> a user account you can
	<a href="gamecreate.php" class="light">create a game</a>
	and <a href="gamelistings.php" class="light">join existing games</a>.
	<p style="text-align:center;">
		<img src="<?php print STATICSRV; ?>datc/maps/812-large.map-thumb" alt=" "
				title="Because Prussia is support-holding the fleet in the Baltic Sea the equally supported move to the Baltic Sea from Livonia fails: This allows the fleet in the Baltic Sea to successfully convoy an army from Berlin to Sweden"  />
	</p>
	</li>
</ul>