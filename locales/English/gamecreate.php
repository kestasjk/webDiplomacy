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
 * @subpackage Forms
 */
?>
<div class="content-bare content-board-header content-title-header">
<div class="pageTitle barAlt1">
	Create a new game
</div>
<div class="pageDescription barAlt2">
Start a new game; you decide the name, how long it runs, and how much it's worth.
</div>
</div>
<div class="content content-follow-on">
<form method="post">
<ul class="formlist">

	<li class="formlisttitle">
		Name:
	</li>
	<li class="formlistfield">
		<input type="text" name="newGame[name]" value="" size="30">
	</li>
	<li class="formlistdesc">
		The name of your game
	</li>

	<li class="formlisttitle">
		Phase length: (5 minutes - 10 days)
	</li>
	<li class="formlistfield">
		<select name="newGame[phaseMinutes]" onChange="document.getElementById('wait').selectedIndex = this.selectedIndex">
		<?php
			$phaseList = array(5,7, 10, 15, 20, 30,
				60, 120, 240, 360, 480, 600, 720, 840, 960, 1080, 1200, 1320,
				1440, 2160, 2880, 4320, 5760, 7200, 8640, 10080, 14400, 1440+60, 2880+60*2);

			foreach ($phaseList as $i) {
				$opt = libTime::timeLengthText($i*60);

				print '<option value="'.$i.'"'.($i==1440 ? ' selected' : '').'>'.$opt.'</option>';
			}
		?>
		</select>
	</li>
	<li class="formlistdesc">
		The maximum number of hours allowed for players to discuss and enter orders each phase.<br />
		Longer phase hours means more time to make careful decisions and negotiations, but makes a game take longer. Shorter
		phase hours results in a faster game, but requires that players in the game are available to check the game frequently.<br /><br />

		<strong>Default:</strong> 24 hours/1 day
	</li>

	<li class="formlisttitle">
		Bet size: (5<?php print libHTML::points(); ?>-
			<?php print $User->points.libHTML::points(); ?>)
	</li>
	<li class="formlistfield">
		<input type="text" name="newGame[bet]" size="7" value="<?php print $formPoints ?>" />
	</li>
	<li class="formlistdesc">
		The bet required to join this game. This is the amount of points that all players, including you,
		must put into the game's "pot" (<a href="points.php" class="light">read more</a>).<br /><br />

		<strong>Default:</strong> <?php print $defaultPoints.libHTML::points(); ?>
	</li>

	<li class="formlisttitle">
		<img src="images/icons/lock.png" alt="Private" /> Add Invite Code (optional):
	</li>
	<li class="formlistfield">
		<ul>
			<li>Invite Code: <input type="password" name="newGame[password]" value="" size="30" /></li>
			<li>Confirm: <input type="password" name="newGame[passwordcheck]" value="" size="30" /></li>
		</ul>
	</li>
	<li class="formlistdesc">
		<strong>This is optional.</strong> If you set this only people who know the invite code will be able to join.<br /><br />

		<strong>Default:</strong> No invite code set
	</li>
</ul>

<div class="hr"></div>

<div id="AdvancedSettingsButton">
<ul class="formlist">
	<li class="formlisttitle">
		<a href="#" onclick="$('AdvancedSettings').show(); $('AdvancedSettingsButton').hide(); return false;">
		Open Advanced Settings
		</a>
	</li>
	<li class="formlistdesc">
		Advanced settings allowing extra customization of games for seasoned players, allowing
		different map choices, alternate rules, and non-standard timing options.<br /><br />

		The default settings are fine for <strong>new players</strong>.
	</li>
</ul>
</div>

<div id="AdvancedSettings" style="<?php print libHTML::$hideStyle; ?>">

<h3>Advanced settings</h3>

<ul class="formlist">
<?php
if( count(Config::$variants)==1 )
{
	foreach(Config::$variants as $variantID=>$variantName) ;

	$defaultVariantName=$variantName;

	print '<input type="hidden" name="newGame[variantID]" value="'.$variantID.'" />';
}
else
{
?>
	<li class="formlisttitle">Variant map/rules:</li>
	<li class="formlistfield">
	<?php
	$checkboxes=array();
	$first=true;
	foreach(Config::$variants as $variantID=>$variantName)
	{
		if($variantID != 57)
		{
			if( $first )
				$defaultVariantName=$variantName;
			$Variant = libVariant::loadFromVariantName($variantName);
			$checkboxes[] = '<input type="radio" '.($first?'checked="on" ':'').'name="newGame[variantID]" value="'.$variantID.'"> '.$Variant->link();
			$first=false;
		}
	}
	print '<p>'.implode('</p><p>', $checkboxes).'</p>';
	?>
	</li>
	<li class="formlistdesc">
		Select which type of Diplomacy game you would like to play from a selection of maps and alternate rule settings
		available on this server.<br /><br />

		Click any of the variant names to view the details on the variants page.<br /><br />
		
		<strong>*Please note that 1 vs 1 games will default to a 5 point bet as an unranked game no matter what bet/game type are selected</strong>
		<br /><br />

		<strong>Default:</strong> <?php print $defaultVariantName; ?>
	</li>
<?php
}
?>

	<li class="formlisttitle">Scoring:</li>
	<li class="formlistfield">
		<input type="radio" name="newGame[potType]" value="Winner-takes-all" checked > Draw-Size Scoring (previously called WTA)<br />
		<input type="radio" name="newGame[potType]" value="Sum-of-squares" > Sum-of-Squares Scoring (<a href="points.php#SoS">more information</a>)<br />
		<input type="radio" name="newGame[potType]" value="Unranked" > Unranked (your bet is refunded at the end of the game)
	</li>
	<li class="formlistdesc">
		This setting determines how points are split up if/when the game draws. <br/><br/>
		In Draw-Size Scoring, the pot is split equally between the remaining players when the game draws (this setting used to be called WTA). 
		<br/><br/>
		In Sum-of-Squares scoring, the pot is divided depending on how many centers you control when the game draws.
		<br/>
		<br/>
		In both Draw-Size Scoring and Sum-of-Squares, any solo winner receieves the whole pot.
		<br/>
		<br/>
		Unranked games have no effect on your points at the end of the game; your bet is refunded whether you won, drew or lost.
		<br /><br />

		<strong>Default:</strong> Draw-Size Scoring
	</li>

	<li class="formlisttitle">
		Anonymous players:
	</li>
	<li class="formlistfield">
		<input type="radio" name="newGame[anon]" value="No" checked>No
		<input type="radio" name="newGame[anon]" value="Yes">Yes
	</li>
	<li class="formlistdesc">
		If enabled players will not see each others names and user information, players are anonymous until the game ends.<br /><br />

		<strong>Default:</strong> No, players aren't anonymous
	</li>

	<li class="formlisttitle">
		Disable in-game messaging:
	</li>
	<li class="formlistfield">
		<input type="radio" name="newGame[pressType]" value="Regular" checked>Allow all
		<input type="radio" name="newGame[pressType]" value="PublicPressOnly">Global messages only, no private chat/press
		<input type="radio" name="newGame[pressType]" value="NoPress">No in-game messaging
		<input type="radio" name="newGame[pressType]" value="RulebookPress">Per rulebook
	</li>
	<li class="formlistdesc">
		Disable some types of messaging; allow only global in-game messages, or allow no in-game messages.

		<br/><br/> "Per rulebook" means no discussion during builds and retreats as per the original Diplomacy rulebook. In this mode, saved retreats and builds are automatically readied for the next turn.
		<br /><br /><strong>Default:</strong> Allow all
	</li>
	<li class="formlisttitle">
		Draw votes:
	</li>
	<li class="formlistfield">
		<input type="radio" name="newGame[drawType]" value="draw-votes-public" checked>Public draw votes
		<input type="radio" name="newGame[drawType]" value="draw-votes-hidden">Hidden draw votes
	</li>
	<li class="formlistdesc">
		Whether or not draw votes can be seen by the other players. In both modes, the game will be drawn when all players have voted draw. However, if draw votes are 
		hidden then you are the only one who knows whether you have voted to draw or not. 
		<br /><br /><strong>Default:</strong>Public draw votes
	</li>

	<li class="formlisttitle">
		Joining pre-game period length: (5 minutes - 10 days)
	</li>
	<li class="formlistfield">
		<select id="wait" name="newGame[joinPeriod]">
		<?php
			foreach ($phaseList as $i) {
				$opt = libTime::timeLengthText($i*60);

				print '<option value="'.$i.'"'.($i==1440 ? ' selected' : '').'>'.$opt.'</option>';
			}
		?>
		</select>
	</li>
	<li class="formlistdesc">
		The amount of time to wait for people to join. For 5 minute games you may want to give players longer than 5 minutes to join.

		<br /><br /><strong>Default:</strong> The same as phase length
	</li>
	<li class="formlisttitle">
		Reliability Requirements
	</li>
	<li class="formlistfield">
		Reliability rating: <input id="minRating" type="text" name="newGame[minimumReliabilityRating]" size="2" value="0"
			style="text-align:right;"
			onkeypress="if (event.keyCode==13) this.blur(); return event.keyCode!=13"
			onChange="
				this.value = parseInt(this.value);
				if (this.value == 'NaN' ) this.value = 0;
				if (this.value < 0 ) this.value = 0;
				if (this.value > 100 ) this.value = 100;
				"/>% or better.  
	</li>
	<li class="formlistdesc">
		The minimum reliability rating that a player must have before they can join your game. If players miss turns or go in to Civil Disorder (by not checking the game), then
		their reliability rating will go down. If you have a low reliability rating, then you can improve it by playing games and not missing turns.
		<br /><br /><strong>Default:</strong> 0 (No restrictions)
	</li>
	   
<!-- 
	<li class="formlisttitle">
		No moves received options:
	</li>
	<li class="formlistfield">
		<input type="radio" name="newGame[missingPlayerPolicy]" value="Normal" checked > Normal<br />
		<input type="radio" name="newGame[missingPlayerPolicy]" value="Wait"> Wait for all players
	</li>
	<li class="formlistdesc">
		What should happen if the end of the turn comes and a player has not submitted any orders?<br /><br />
		
		If set to <strong>Normal</strong> the game will proceed, and after 
		a couple of turns they will go into civil disorder and their country can be taken over by another player.<br /><br />
		
		If set to <strong>Wait for all players</strong> the game will not continue until all players have submitted their orders.<br />
		This avoids any issues caused by 
		someone not submitting their orders on time, but it means that if someone becomes unavailable the game will not continue until they either
		return, or a moderator manually sets them to civil disorder.<br /><br />

		<strong>Default:</strong> Normal
	</li>
	 -->
</ul>

</div>

<div class="hr"></div>

<p class="notice">
	<input type="submit" class="form-submit" value="Create">
</p>
</form>
