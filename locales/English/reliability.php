<?php

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * @package Base
 * @subpackage Static
 */
?>

<p class="intro">

Because Diplomacy is a game of communication, trust (and distrust), 
and because games usually take a long time to finish it's very important for 
players that you play the best you can and don't screw the game halfway.</p>

<p class="intro">

The reliable rating is an easy calculation that represents how reliable 
you enter your commands and how reliable you play your games till the end.
</p>

<div class="hr" ></div>

<p class="intro">
Your rating is dependent on 2 important factors. How many phases you missed 
to enter orders in comparison to your total phases played, and how many games 
you left before the end, because you didn't check the game page for 2 turns in a row.<br>
<b>Example</b>: If a user misses 5% of their games, rating would be 90, 15% would be 70, etc.
</p>

<p class="intro">
From this rating we subtract 10% for each game you left before the end.
The penalty for the "Left" games seems a bit harsh, but many games get totally 
screwed if a player does not play the game till the end. Most of the time some 
countries gain really big unearned advantages.
<br>But you can even out your lost reliability by taking <b>an open spot from a game</b> another player left.
</p>

<p class="intro">
<style>
div.fraction-inline { display: inline-block; position: relative; vertical-align: middle; }
.fraction-inline > span { display: block; padding: 0; }
.fraction-inline span.divider{ position: absolute; top: 0em; display: none;	letter-spacing: -0.1em;	 }
.fraction-inline span.denominator{ border-top: thin solid black; text-align:center;}
</style>
The exact calculation is: 
<div>
	100 &minus; (100 *
	<div class="fraction-inline">
		<span class="numerator">2 * MissedPhases</span>
		<span class="divider">________________</span>
		<span class="denominator">TotalPhases</span>
	</div>
	) &minus; 10 * UnbalancedCDs
</div>
<br>The calculation for your rating is:
<span>
	100 &minus; (100 *
	<div class="fraction-inline">
		<span class="numerator">2 * <b><?php print $User->missedMoves;?></b></span>
		<span class="divider">________________</span>
		<span class="denominator"><b><?php print $User->phasesPlayed;?></b></span>
	</div>
	) &minus; 10 * <b><?php print ($User->gamesLeft - $User->leftBalanced);?></b> = 
	100 &minus; <?php print ($User->phasesPlayed == 0 ? '0' : (200 * $User->missedMoves / $User->phasesPlayed))?> &minus; <?php print (10 * ($User->gamesLeft - $User->leftBalanced))?> =
	<b><?php print abs($User->getReliability());?></b>
</span>

</p>

<p class="intro">
When someone creates a game they can select a minimum rating for the people able to enter their games, 
and if you rating is too low you might not be able to join all the games as you like.<br>
Also for each 10% of reliability you can join 1 game. If your reliability is <b>91% or better</b> you can join as many games as you want.</p>

<div class="hr" ></div>
<p class="intro">
On the games-pages your rating is displayed as a grade after your name.
The current grades are:<br>
<?php
	require_once ("lib/reliability.php");
	foreach (libReliability::$grades as $limit=>$grade)
		print $grade." = ".$limit." or better.<br>";
?>
This grading-system might change in the future.
<div class="hr" ></div>

<p class="intro">
<b>Why should I continue a game if my country can't win?</b><br>
If you can't win a game or are on a losing position you might choose to hurt the country that sealed 
your failure as much as possible by making your defeat as hard as possible. Talk to stronger players 
on the board, they might help you, just because you have a common enemy.
</p>
