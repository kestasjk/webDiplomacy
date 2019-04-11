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

<p style="text-align: center; margin-bottom: 5px">
	<a href="#points">Points System</a>
	-
	<a href="#DSS">DSS</a>
	-
	<a href="#SoS">SoS</a>
	-
	<a href="#unranked">Unranked</a>
	-
	<a href="#ppsc">PPSC</a>
</p>

<div id="points-scoring">

	<h2 class="points-title" name="points">Points on webDiplomacy</h2>

	<p class="scoring-text">
		In order to play games on webDiplomacy, you must bet a certain amount of points determined by the game creator. The lowest bet to enter a game is 5 points. Depending on the scoring system of the game, you may receive more points than you bet at the end of the game if you win or survive in a draw. If you do not win or survive in a draw, you will lose the points that you bet. In unranked games, you must still make a bet, but your points will be returned to you at the end of the game.
		<br><br>
		On your profile, you will see your available points and total points. Your available points is the number of points that you have to spend. Your available points are shown in the toolbar at the top of your screen. Your total points is how many points you have to spend plus the amount of points you have already bet on games.
		<br><br>
		Players cannot drop below 100 total points. If you lose a game and drop below 100 total points, you will be automatically topped up to 100 points again so that you will always have the ability to join new games. Your points are not refreshed until a game concludes, so if you are defeated but the other players continue to play you will have to wait until the game is finished.
	</p>

	<h2 class="points-title">webDiplomacy's Scoring Systems</h2>

	<p class="scoring-text">
		Every game of Diplomacy is created with a scoring system. Each scoring system has its own unique traits. webDiplomacy has three supported scoring systems, which are explained in detail below.
		<br><br>
		In order to win, or solo, a Diplomacy game, you must acquire the required number of supply centers. On the classic map, 18 supply centers is a solo. If nobody is able to win the game, then the surviving players may vote to draw. This can be done at any time. Once every living player has voted to draw, the game will end, and the points will be distributed according to the scoring system.
	</p>

	<h3 class="scoring-h" name="DSS">Draw-Size Scoring (DSS)</h3>
	<p class="scoring-text"><strong>Previously known as Winner-Takes-All (WTA).</strong></p>			
	<p class="scoring-text">
		Draw-Size Scoring is the default scoring system on webDiplomacy and is a draw-based scoring system. In games created with Draw-Size Scoring, you win the entire pot if you solo. In a draw, the points are split equally between every surviving player, regardless of how many supply centers they own or what position they are in the game. For example, if the pot is 210 points (7 players betting 30 points) and 3 people are alive when the game is drawn, each of those players receives 70 points. They gain 40 points more than they put into the pot.
		<br><br>
		Draw-Size Scoring encourages players to play for a solo, but if they cannot achieve a solo, it encourages players to "narrow" down a draw by eliminating smaller powers in order to get a better result. With fewer players remaining in the draw, each remaining player receives more points of the pot.
	</p>

	<h3 class="scoring-h" name="SoS">Sum-of-Squares Scoring (SoS)</h3>
	<p class="scoring-text">
		Sum-of-Squares is a supply-center-based scoring system. In games created using Sum-of-Squares, you win the entire pot if you solo, just like DSS. In a draw, each surviving player gets a proportion of the pot scaled by their supply center count at the time of the draw. The equation for determining their share of the pot is as follows:
		<br><br>
		(SC_count^2) / (sum of all players (SC_count^2))
		<br><br>
		In a game with a pot of 210 points (7 players betting 30 points) and 4 people alive when the game is drawn, each player receives a portion of the pot based on the number of supply centers they have. If you have 12 supply centers and the other three players have 10, 8, and 4 respectively, then you will receive 93 points. We know this because 12^2 / (12^2 + 10^2 + 8^2 + 4^2) * 210 = 93.
		<br><br>
		Sum-of-Squares Scoring encourages players to play for a solo, but if they cannot achieve a solo, it does not drag on games longer and longer just to get that last elimination because the number of players in the draw is less critical than the number of supply centers each player in the draw owns. Sum-of-Squares is often used in face-to-face Diplomacy tournaments where games may be time sensitive.
	</p>

	<h3 class="scoring-h" name="unranked">Unranked Games</h3>
	<p class="scoring-text">
		Unranked games require a bet to join, but instead of distributing the pot between players based on a scoring system, you are simply refunded your bet at the end of the game, so long as you have not been removed from the game due to entering Civil Disorder. These games are not included in any off-site ranking system like the Ghost Ratings.
		<br><br>
		Unranked games are often used for special rules games organized on our forum, tournament games, practice or school games, or any other instance where point distribution is not a priority. All 1v1 games are also unranked to prevent point farming.
	</p>

	<h3 class="scoring-h" name="ppsc">Points-Per-Supply-Center</h3>
	<p class="scoring-text">
		webDiplomacy used to support a scoring system called Points-Per-Supply-Center, which was a supply-center-based scoring system that distributed the pot based on a ratio of the number of supply centers each player owned. This scoring system was discontinued because of the prevalence of "strong seconds," which is when one player solos because another player is promised more supply centers, and thus more points, if they help the other player solo. While webDiplomacy does not judge the individual strategies of players in their games, we do not condone this strategy and removed the scoring system that encouraged it.
	</p>
</div>
