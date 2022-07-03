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

require_once(l_r('lib/home.php'));

print '<div class = "introToDiplomacy"><div class="content-notice" style="text-align:center">'.libHome::globalInfo().'</div></div>';
print libHTML::pageTitle(l_t('Play webDiplomacy now!'),l_t('A multiplayer web implementation of the popular turn-based strategy game Diplomacy.'));

?>
<div style="text-align:center">
	<form action='botgamecreate.php'>
		<input class='green-Submit' style="font-size:100%; font-weight:bold; background-color:#0e8805 !important;" type='submit' name='submit' value='Click here to start a new game of webDiplomacy against AI opponents!'></input>
	</form>
</div>

<?php

//print '<div class="content">';
require_once(l_r('locales/English/welcome.php'));
?>
</div>
</div>
<?php
print libHTML::pageTitle(l_t('How to play'),l_t('A quick introduction to get you started playing Diplomacy.'));
?>
<div style="text-align:center">
	<a href="#Basics">The Basics</a> - <a href="#Tactics">Advanced Tactics</a> - <a href="#More">Further Learning</a>
</div>

<div class="hr"></div>

<a name="Basics"></a>
<h2>The Basics of Diplomacy</h2>
<p>
	The objective of Diplomacy is to be the first nation to own half of the supply centers in the game. To accomplish this,
	you can move your units around the board, fighting to claim other players' supply centers as your own. For each 
	supply center you occupy, you get to build a new unit at the end of the year that you can use to fight for more supply 
	centers and help you win the game. If you lose a supply center to another player, you have to disband a unit instead, 
	and are left with fewer units to fight with for more supply centers.
	<br><br>
	You can recognize supply centers by the markers placed on them that you see here.
</p>
<p style="text-align:center;">
	<img src="images/intro/supply.png" alt=" " title="Supply centers are marked (large map)" />
</p>
<p>
	In this instance, the owner of each of these three supply centers is France. France is blue on our classic Diplomacy map,
	and the other six players on the classic Diplomacy map are represented with a different color.
</p>

<h3>Units</h3>
<p>
	When you begin a game, and when you claim new supply centers, you will gain new pieces, or units. Units can be fleets or armies. 
	An army can only move and attack other units on land. A fleet can move and attack other units on the coast or in the open sea, and 
	it can also transport armies across sea territories by convoying.
</p>

<h3>Moves</h3>
<p>
	In order to travel across the map and claim supply centers, or to defend your own supply centers, you can move your units. You can 
	also hold, meaning your unit will not move, or support other units, meaning you are either providing defensive reinforcements if they 
	are attacked or you are reinforcing their attack against another unit. We will get into supports a little bit more as part of our 
	introduction to <a href="#Tactics" class="light">advanced tactics</a>. For now, here are some examples of the basic moves your units can make.
</p>

<ul class="formlist">
	<li class="formlisttitle">Hold</li>
	<li class="formlistdesc">
		This Italian army is holding in Naples, and will not move. If attacked by only one other unit, it will be able to defend 
		itself and Naples, but otherwise, it will do nothing. If it is attacked by two units, it will be forced to retreat to another 
		adjacent territory, or, if there are none unoccupied, it will be forced to disband.
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/801-large.map-thumb" alt="An army holds in Naples"/>
		</p>
	</li>

	<li class="formlisttitle">Move</li>
	<li class="formlistdesc">
		This time, the army in Naples is moving to Rome. There is no unit occupying Rome, so it can move there freely. If there were 
		a unit occupying Rome, this unit could only move there if it were supported by another unit to do so.
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/802-large.map-thumb" alt="An army in Naples moves to Rome"/>
		</p>
	</li>

	<li class="formlisttitle">Convoy</li>
	<li class="formlistdesc">
		Fleet units can transport armies across the ocean. This is called a convoy. A string of fleets can also transport another unit 
		across multiple sea territories in one move. Below, the army in Venice moves all the way to Tunis on the North African coast because 
		the fleets in the Adriatic Sea and Ionian Sea work together to convoy it across the ocean.
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/805-large.map-thumb"
				alt="An army in Venice moves to Tunis, convoyed by the fleets in Adriatic Sea and Ionian Sea" />
		</p>
	</li>
</ul>

<div class="hr"></div>

<a name="Tactics"></a>
<h2>Advanced Tactics</h2>
<p>
	As you learned above, armies can hold and move across land territories, and fleets can hold and move on coastal and sea territories, as 
	well as convoy armies across the sea. However, the key to successfully conquering other supply centers is support. One unit cannot always 
	conquer a supply center on its own because there are enemy units on the board that occupy those units and defend them. Below, we'll get into 
	some more complex scenarios, including support, and how you can use it to conquer the board.
</p>

<ul class="formlist">
	<li class="formlisttitle">Bouncing Moving Units</li>
	<li class="formlistdesc">
		In Diplomacy no army or fleet is stronger than another. Without support from another unit, two units trying to move into 
		the same territory will both be unsuccessful. We call this a bounce.
		<br><br>
		Here an Italian army in Venice and an Austrian fleet in the Ionian Sea both attempt to move to Apulia at the same time 
		without any support and bounce.
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/807-large.map-thumb" 
			alt="The fleet and army are both equally matched in their attempt to move into Apulia, so neither succeeds"/>
		</p>
	</li>

	<li class="formlisttitle">Attacking Occupied Centers</li>
		While no unit is stronger than another, a unit that is holding will always repel a unit that is attacking, so long as 
		the attacking unit is not supported to attack.
		<br><br>
		Below, the Austrian army in Rome is unaffected by the Italian army in Naples attempting to displace it and take Rome for itself.
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/806-large.map-thumb" 
			alt="An army in Naples attempts to move to Rome, but has no support, so the defending army is not dislodged"/>
		</p>
	</li>

	<li class="formlisttitle">Support Move</li>
	<li class="formlistdesc">
		While supporting a move, a unit does not itself move to another territory, but instead reinforces the move another unit is making. Thus, 
		the unit being supported has more strength, and can overcome a single unit that occupies a territory. This is how you gain supply 
		centers even when they are occupied by an enemy.
		<br><br>
		This example shows an army moving from Venice to Rome. The army in Tuscany supports the move, and as a result it moves in successfully 
		even if the territory had been occupied by another unit. 
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/803-large.map-thumb" 
			alt="A yellow support-move lets the army in Venice overpower the army holding in Rome"/>
		</p>
	</li>

	<li class="formlisttitle">Support Hold</li>
	<li class="formlistdesc">
		While supporting holding, a unit does not move, but instead of simply holding it will support an adjacent unit. So long as the other unit 
		also does not move, it will be reinforced, and it is more difficult to attack. Because the other unit is supported, it 
		could not be forced to retreat to another territory unless an attacking unit is supported by more than one other unit. This 
		is very useful for defending your supply centers.
		<br><br>
		In this case, the red army in Rome, belonging to Austria, is being attacked by Italy from Venice. The attack from Venice is being 
		supported by the Italian army in Tuscany. However, because the Austrian fleet in the Tyrrhenian Sea is support holding Rome, 
		the attack is unsuccessful, and Rome is still owned by Austria.
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/804-large.map-thumb" 
			alt="A green support-hold from the fleet in Tyrrhenian Sea lets Rome hold against an attack from Venice, supported by Tuscany"/>
		</p>
	</li>

	<li class="formlisttitle">Three Units vs Two Units</li>
	<li class="formlistdesc">
		Just as a unit with support can overcome a unit without support, a unit with two supporting units can overcome a unit with only one unit 
		supporting it. Having more supporting units than your enemy has is the key to conquering supply centers. 
		<br><br>
		In this example, the Austrian fleet in Trieste is supported to Venice by the Austrian army in Tyrolia and the Austrian army in Piedmont. 
		With two units supporting its move, the fleet to Trieste is able to overcome the Italian unit supported to hold by only one unit in Venice. 
		Italy loses Venice, and Austria claims its new supply center.
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/808-large.map-thumb"
				alt="The fleet moving from Trieste to Venice has two support-moves, and the fleet in Venice has only one support-hold, so Trieste succeeds" />
		</p>
	</li>

	<li class="formlisttitle">Three Units vs Three Units</li>
	<li class="formlistdesc">
		You already learned that no single unit is stronger than another. You also learned that a holding unit will overcome a unit attacking it unsupported. 
		The same is true when the unit holding is supported equally to hold as the unit attacking it is supported to move into the territory. Since no one 
		unit is stronger than another, these supports equal out, and the attack is unsuccessful.
		<br><br>
		This time, the Italian army in Venice is well protected with support from an army in Rome and a fleet in Apulia. Even though Austria 
		has two units supporting its attack on Venice, the attack is repelled, and Venice remains in Italy's possession.
		<p style="text-align:center;">
			<img src="<?php print STATICSRV; ?>datc/maps/809-large.map-thumb" 
			alt="A green support-hold from the fleet in Tyrrhenian Sea lets Rome hold against an equally well-supported Venice"/>
		</p>
	</li>

	<li class="formlisttitle">Cutting Support Moves</li>
	<li class="formlistdesc">
		A unit can only support another unit's attack if it is not attacked itself. If it is attacked, it has to prioritize its own safety, and thus 
		is unable to offer any assistance. We call this cutting support, and it is a very valuable tactic when you need to overcome another player's strong attack. 
		<br><br>
		Below, the Italian fleet is Venice is outnumbered. Austria has two supporting units against it, and Italy can only defend its supply center with one. 
		However, Germany and Italy are allies working together. Germany knows that its ally needs help, so Germany cuts the support from Tyrolia, thus rendering 
		that unit unable to provide aid to the attack on Venice. Now, only one unit is supporting the attack, and one unit is supporting Venice to hold. The 
		unit holding is able to repel the attacker.
		<p style="text-align:center;">
			<img src="datc/maps/810-large.map-thumb" 
			alt="An army from Munich attacks Tyrolia, preventing it from supporting Trieste: Trieste 1 - Venice 1; Trieste stays"/>
		</p>
	</li>

	<li class="formlisttitle">Cutting Support Holds</li>
	<li class="formlistdesc">
		Support holds work the exact same way as support moves. If a unit is ordered to support hold another unit but it is attacked, it must prioritize its own 
		safety, and thus cannot support hold another unit.
		<br><br>
		As before, the Italian fleet is Venice is outnumbered. Austria has two supporting units against it, and Italy can only defend its supply center with one. 
		Germany and Italy are allies working together, so Germany cuts Austria's support. But unbeknownst to Italy and Germany, Austria also has a friend in Turkey. 
		Turkey uses its fleet in the Tyrrhenian Sea to cut the support hold the unit in Rome offers, and thus, the Austrian fleet in Trieste, supported by one unit, 
		is able to overcome the unsupported unit holding in Venice. Austria takes Venice.
		<p style="text-align:center;">
			<img src="datc/maps/811-large.map-thumb" 
			alt="A fleet in the Tyrrhenian Sea attacks Rome, preventing it from supporting Venice: Trieste 1 - Venice 0; Trieste moves in"/>
		</p>
	</li>

	<li class="formlisttitle">Attacking Convoys</li>
	<li class="formlistdesc">
		While convoying an army from one landmass to another, a fleet is not moving, so it is vulnerable to attack. If attacked by an unsupported unit, a 
		fleet is able to repel the attack and can still complete its role in the convoy. However, if a fleet is attacked by a supported unit, it cannot repel 
		the attack. The convoy is broken, and the army is unable to move across the sea.
		<br><br>
		However, because a fleet is not moving, it can be supported with a support hold. An army cannot support hold a unit in the sea, so a fleet 
		in the sea can only be supported like this by another fleet. In this example, the German fleet in the Baltic Sea is convoying the German army in Berlin to 
		Sweden. The Russian fleets in Livonia and the Gulf of Bothnia do not want this to happen, so they team up to attack the convoying fleet. However, Germany also 
		has a fleet in Prussia, which supports the fleet in the Baltic Sea to hold. Thus, the attack is repelled, and the convoy is successful.
		<p style="text-align:center;">
			<img src="datc/maps/812-large.map-thumb" 
			alt="Because Prussia is support-holding the fleet in the Baltic Sea the equally supported move to the Baltic Sea from Livonia fails: 
			This allows the fleet in the Baltic Sea to successfully convoy an army from Berlin to Sweden"/>
		</p>
	</li>
</ul>

<div class="hr"></div>

<a name="More"></a>
<h2>Further Learning on Diplomacy</h2>
<p>
	The best way to get better at Diplomacy is to play! You now understand the tactics of Diplomacy, so you are ready to 
	<a href="gamecreate.php" class="light">create a game</a> or <a href="gamelistings.php?gamelistType=New" class="light">join existing games</a>.
	In addition to playing games, here are some helpful pages you should visit as you begin your experience on webDiplomacy.
	<br><br>
	While the original Diplomacy board is the <a href="variants.php#Classic" class="light">classic map</a> that you may be familiar with (and have 
	seen little bits of in this guide!), webDiplomacy features many variant Diplomacy boards. Check out all of our supported variants
	<a href="variants.php" class="light">here</a>.
	<br><br>
	On webDiplomacy, we pride ourselves on being reliable players. Missing deadlines causes delays and makes the game less fun for everyone. To help 
	facilitate reliable gameplay, we implemented our Reliability Rating, better known as RR. Players with a low RR can find themselves blocked out of 
	games with high RR requirements to join, and players who miss phases frequently may find themselves temporarily suspended from joining or creating 
	new games. You can see your RR on your <a href="userprofile.php" class="light">profile</a> by expanding the Reliability Rating section, and read 
	about how our reliability rating works in detail.
	<br><br>
	In order to rank players, webDiplomacy uses the <a href="ghostRatings.php" class="light">Ghost Ratings</a>. This is a true skill rating system 
	that not only takes into account how well you do in your games but also the quality of your competition. As you play more games and become more skilled, 
	you might be able to rise up the ranks and become known as one of the best Diplomacy players. 
	<br><br>
	To enter a game, you will need to bet points. The bets of all the players in the game will be combined into the pot. If you win a game, you will get 
	the entire pot! If you lose, you will lose your points. You can also draw before you win or lose, which means that you will split the pot with the other survivors. 
	More details on how points work and how you can win more can be found <a href="points.php" class="light">here</a>.
	<br><br>
	If you have more questions that this guide or those pages have not answered, check out our <a href="faq.php" class="light">FAQ</a>! 
	<br><br>
	If you're interested in learning a more comprehensive and detailed treatment of this guide, see 
	<a href="https://media.wizards.com/2015/downloads/ah/diplomacy_rules.pdf" class="light">Avalon Hill's Official Rulebook</a>, but this intro is all you 
	need to get playing! Have fun!
</p>
