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

<h4>What are Points?</h4>
<div align="center"><img src="images/points/stack.png" alt=" "
	title="A stack of webDiplomacy points. Points are a lot like casino chips in poker; tokens to bet with" /></div>

<p class="intro">
Points are what allow you to join games on webDiplomacy, measured in D. To join a game, you "bet" a number of points, and at the end of the game, you may receive more or less, depending on the outcome. Your points can be found on your profile, and two numbers of points are shown: 
available points and total points.
</p>


<h4>Available Points</h4> 
<p class="intro">
This is how many points you have that are not tied up in ongoing games; and 
thus you can use to join new games.
</p>
<h4>Total Points</h4> 
<p class="intro">
This is how many points you have including the points you have already bet to 
join games. You start with 100 points, and you will be automatically topped 
back up to 100 if you lose your games, so you will always have the ability to 
join new games. 
</p>
<div align="center"><img src="images/points/bet.png" alt=" "
	title="All players who want to join the game bet the same amount of points when the game begins" /></div>
<p class="intro">


<p class="intro">
It is recommended that you always keep a reasonable amount of points available, 
as in high-stakes games you can lose a lot of points very quickly.
</p>



<p class="intro">
When someone creates a game they can select how many of their points they want to "bet" on the game. Only people who can bet the same number of points can join the game.
More experienced players will usually have more points, and as a result, they will be able to join higher stakes games.
</p>


<p class="intro">
Once everyone who wants to join has joined the game has a large "pot" of points. If there are seven players, the pot size will be seven times the size that each player bet. 
</p>
<div align="center"><img src="images/points/play.png" alt=" "
	title="The game begins; all players are now fighting for the 'pot' of points which they have all bet" /></div>

<p class="intro">
Once the game is over the pot is paid back to the players depending on how well they did, as explained below.
</p>

<div class="hr" ></div>

<h4>Scoring Systems</h4>
<p class="intro">
Scoring a game of Diplomacy has been a big topic of discussion among Diplomacy players for many years. 
Most players agree that a winner of a game of Diplomacy (who takes more than half of the centers) is the solo winner of the game.
However, there are different opinions on how to score games where there is no sole winner.</p>

<p class="intro">WebDiplomacy has three scoring systems enabled: Draw-Size Scoring, Sum of Squares and Unranked.</p>

<p class="intro">
In the first two, systems, if you win this game by capturing the required supply centres (18, on the Classic map), you win the entire pot for the game, and receive all those points.
</p>
<div align="center">
	<img src="images/points/wta.png" alt=" "
		title="The winner takes all the points: Winner-takes-all" />
</div>

<p class="intro">
If nobody is able to win the game, then the surviving players may vote to draw. This can be done at any time. Once every living player has voted to draw, the game will end, and points will be distributed according to the scoring system.
</p>

<h4>Draw-Size Scoring (DSS)</h4>
<p class="intro">
<em>Previously called Winner-Takes-All (WTA).</em>
This is the default scoring system on webDiplomacy, and is a draw-based scoring system. This means that if there is a draw, then the points will be shared equally between every surviving player, regardless of how many supply centres they own. So if the pot is 210D, and 3 people are alive when the game draws, each of those living players will receive 70D.
</p>
<div align="center"><img src="images/points/draw.png" alt=" "
		title="When all surviving players vote for a draw an equal share of the points are given to each surviving player" /></div>

<p class="intro">
DSS encourages you to "narrow" a draw down by cutting small players out to get a better result.
</p>

<h4>Sum-Of-Squares (SoS)</h4>
<a name="SoS>"></a>
<p class="intro">
This is a centre-based scoring system. In a draw, each player gets a proportion 
of the pot scaled by their centre count:               
</p>

<p class="intro">
(SC_count^2) / (sum of all players (SC_count^2))
</p>

<p class="intro">
So with a pot of 210 points in SoS, then the points are divided up by the amount of centres you own. So if there are four people alive, with you having 12 SC's, and the others having 10, 8, and 4 SC's respectively, then you can work out how many points you will receive by following that formula, so 12^2 / (12^2 + 10^2 + 8^2 + 4^2) * 210 = 93 points.
</p>
<div align="center"><img src="images/points/win.png" alt=" "
	title="Centre-based scoring divides the pot depending on the players' success at taking centers in the game" /></div>

<p class="intro">
This kind of scoring is often used by face-to-face tournaments, because it 
allows for more distinction between players, encourages going for solos rather 
than cutting out surviving players, and doesn't drag games on just to get that 
last elimination.
</p>

<h4>Unranked Games</h4>
<p class="intro">
These games require a bet to join, but you are simply refunded your bet at the 
end of the game, as long as you don't enter Civil Disorder by not inputting 
moves. You don't get any more points than you put in, even if you win the game. 
These games can be used for special rules games organised on the forums, 
tournaments, crazy strategic practising, or anything else that tickles your 
fancy.
</p>
<p class="intro">These games will never be included in any off-site ranking system, such as the Ghost-Rating ranking system.</p>

<div class="hr" ></div>
