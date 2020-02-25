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
	<div class="pageTitle barAlt1">Create a new game</div>
	<div class="pageDescription">Start a new customized game of Diplomacy.</div>
</div>
<div class="content content-follow-on">
	<p><a href="botgamecreate.php">Play A Game Against Bots</a></p>

	<div class = "gameCreateShow">
		<form method="post">
			<p>
				<strong>Game Name:</strong></br>
				<input class = "gameCreate" type="text" name="newGame[name]" value="" size="30">
			</p>

			<strong>Bet size: (5-<?php print $User->points.libHTML::points(); ?>)</strong>
			<img id = "modBtnBet" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" />
			<div id="betModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span class="close5">&times;</span>
					<p><strong>Bet:</strong> </br>
						The bet required to join this game. This is the amount of points that all players, including you,
						must put into the game's "pot" (<a href="points.php" class="light">read more</a>).<br /><br />
					</p>
				</div>
			</div>
			<input class = "gameCreate" type="text" name="newGame[bet]" size="7" value="<?php print $formPoints ?>" />
			
			</br></br>
			<strong>Phase length: (5 min - 10 days)</strong>
			<img id = "modBtnPhaseLength" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" />
			<div id="phaseLengthModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span class="close4">&times;</span>
					<p><strong>Phase Length: </strong></br>
						How long each phase of the game will last in hours. Longer phase hours means a slow game with more time to talk. 
						Shorter phases require players be available to check the game frequently.
					</p>
				</div>
			</div>
			<select class = "gameCreate" name="newGame[phaseMinutes]" id="selectPhaseMinutes">
			<?php
				$phaseList = array(5,7, 10, 15, 20, 30, 60, 120, 240, 360, 480, 600, 720, 840, 960, 1080, 1200, 1320,
					1440, 1440+60, 2160, 2880, 2880+60*2, 4320, 5760, 7200, 8640, 10080, 14400);

				foreach ($phaseList as $i) { print '<option value="'.$i.'"'.($i==1440 ? ' selected' : '').'>'.libTime::timeLengthText($i*60).'</option>'; }
			?>
			</select>
			
			<p id="phaseSwitchPeriodPara">
				<strong>Time Until Phase Swap</strong></br>
				<select class = "gameCreate" id="selectPhaseSwitchPeriod" name="newGame[phaseSwitchPeriod]">
				<?php
				$phaseList = array(-1, 10, 15, 20, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330, 360);
					foreach ($phaseList as $i) 
					{
						if ($i != -1){
							$opt = libTime::timeLengthText($i*60);

							print '<option value="'.$i.'"'.($i==-1 ? ' selected' : '').'>'.$opt.'</option>';
						}
						else {
							$opt = "No phase switch";
							print '<option value="'.$i.'"'.($i==-1 ? ' selected' : '').'>'.$opt.'</option>';
						}
					}
				?>
				</select>
			</p>
			
			<p id="nextPhaseMinutesPara">
				<strong>Phase Length After Swap</strong></br>
				<select class = "gameCreate" id="selectNextPhaseMinutes" name="newGame[nextPhaseMinutes]">
				<?php
				$phaseList = array(1440, 1440+60, 2160, 2880, 2880+60*2, 4320, 5760, 7200, 8640, 10080, 14400);
					foreach ($phaseList as $i) 
					{
						$opt = libTime::timeLengthText($i*60);

						print '<option value="'.$i.'"'.($i==1440 ? ' selected' : '').'>'.$opt.'</option>';
					}
				?>
				</select>
			</p>
			
			<p>
				<strong>Time to Fill Game: (5 min - 14 days)</strong></br>
				<select class = "gameCreate" id="wait" name="newGame[joinPeriod]">
				<?php
				$phaseList = array(5,7, 10, 15, 20, 30, 60, 120, 240, 360, 480, 600, 720, 840, 960, 1080, 1200, 1320,
				1440, 1440+60, 2160, 2880, 2880+60*2, 4320, 5760, 7200, 8640, 10080, 14400, 20160);
					foreach ($phaseList as $i) 
					{
						$opt = libTime::timeLengthText($i*60);

						print '<option value="'.$i.'"'.($i==10080 ? ' selected' : '').'>'.$opt.'</option>';
					}
				?>
				</select>
			</p>
			
			<strong>Game Messaging:</strong>
			<img id = "modBtnMessaging" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" />
			<div id="messagingModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span class="close7">&times;</span>
					<p><strong>Game Messaging:</strong> </br>
						The type of messaging allowed in a game.</br></br>
						All: Global and Private Messaging allowed. </br></br>
						Global Only: Only Global Messaging allowed.</br></br>
						None: No messaging allowed.</br></br>
						Rulebook: No messaging allowed during build and retreat phases.</br>
					</p>
				</div>
			</div>
			<select class = "gameCreate" id="pressType" name="newGame[pressType]" onchange="setBotFill()">
				<option name="newGame[pressType]" value="Regular" selected>All </option>
				<option name="newGame[pressType]" value="PublicPressOnly">Global only</option>
				<option name="newGame[pressType]" value="NoPress">None (No messaging)</option>
				<option name="newGame[pressType]" value="RulebookPress">Per rulebook</option>
			</select>

			</br></br>
			<strong>Variant type (map choices):</strong>
			<img id = "modBtnVariant" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" />
			<div id="variantModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span class="close3">&times;</span>
					<p><strong>Variant:</strong> </br>
						Type of Diplomacy game from a selection of maps and alternate rule settings available. Click any of the variant names to view the details on the variants page.
						<br /><br />
						<strong>Available variants:</strong> </br>
						<?php
						foreach(Config::$variants as $variantID=>$variantName)
						{
							if($variantID != 57)
							{
								$Variant = libVariant::loadFromVariantName($variantName);
								print $Variant->link().'</br>';
							}
						}
						?>
						<br/>
						*Please note that 1 vs 1 games will default to a 5 point bet as an unranked game no matter what bet/game type are selected.
					</p>
				</div>
			</div>
			<select id="variant" class = "gameCreate" name="newGame[variantID]" onchange="setBotFill()">
			<?php
			$first=true;
			foreach(Config::$variants as $variantID=>$variantName)
			{
				if($variantID != 57)
				{
					$Variant = libVariant::loadFromVariantName($variantName);
					if($first) { print '<option name="newGame[variantID]" selected value="'.$variantID.'">'.$variantName.'</option>'; }
					else { print '<option name="newGame[variantID]" value="'.$variantID.'">'.$variantName.'</option>'; }			
					$first=false;
				}
			}
			print '</select>';
			?>
			</br></br>
			<div id="botFill" style="display:none">
			<strong>Fill Empty Spots with Bots: </strong>
			<img id = "modBtnBot" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" />
			<div id="botModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span class="close8">&times;</span>
					<p><strong>Fill with Bots:</strong> </br>
						If the game has at least 2 human players it will 
						fill with bots if there are empty spaces at the designated start time instead of being cancelled. This type 
						of game will default to a 5 point bet, unranked, and anonymous regardless of what settings you select. If the game
						fills with 7 human players it will run just like any normal game and will be included in classic stats. 
					</p>
				</div>
			</div>
			<input type="checkbox" id="botBox" class="gameCreate" name="newGame[botFill]" value="Yes">
			</br></br>
			</div>
			
			<strong>Scoring:(<a href="points.php#DSS">See scoring types here</a>)</strong>
			<img id = "modBtnScoring" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" />
			<div id="scoringModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span class="close2">&times;</span>
					<p><strong>Scoring:</strong> </br>
						This setting determines how points are split up if/when the game draws. <br/><br/>
						In Draw-Size Scoring, the pot is split equally between the remaining players when the game draws (this setting used to be called WTA). 
						<br/><br/>
						In Sum-of-Squares scoring, the pot is divided depending on how many centers you control when the game draws.
						<br/><br/>
						In both Draw-Size Scoring and Sum-of-Squares, any solo winner receieves the whole pot.
						<br/><br/>
						Unranked games have no effect on your points at the end of the game; your bet is refunded whether you won, drew or lost.
					</p>
				</div>
			</div>
			<select class = "gameCreate" name="newGame[potType]">
				<option name="newGame[potType]" value="Winner-takes-all" selected>DSS (Equal split for draws)</option>
				<option name="newGame[potType]" value="Sum-of-squares">SoS (Weighted split on draw)</option>
				<option name="newGame[potType]" value="Unranked">Unranked</option>
			</select></br></br>

			<strong>Anonymous players: </strong>
			<img id = "modBtnAnon" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" />
			<div id="anonModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span class="close6">&times;</span>
					<p><strong>Anonymous players: </strong></br>
						Decide if player names should be shown or hidden.</br></br> *Please note that games with no messaging are always anonymous regardless of what is set here to prevent cheating.
					</p>
				</div>
			</div>
			<select class = "gameCreate" name="newGame[anon]">
				<option name="newGame[anon]" value="No" selected>No</option>
				<option name="newGame[anon]" value="Yes">Yes</option>
			</select>

			<p>
				<strong>Draw votes:</strong></br>
				<select class = "gameCreate" name="newGame[drawType]">
					<option name="newGame[drawType]" value="draw-votes-public" checked>Show draw votes</option>
					<option name="newGame[drawType]" value="draw-votes-hidden">Hide draw votes</option>
				</select>
			</p>

			<p>
				<strong>Required reliability rating:</strong></br>
				<input id="minRating" class = "gameCreate" type="text" name="newGame[minimumReliabilityRating]" size="2" value="<?php print $defaultRR ?>"
					onkeypress="if (event.keyCode==13) this.blur(); return event.keyCode!=13"
					onChange="
						this.value = parseInt(this.value);
						if (this.value == 'NaN' ) this.value = 0;
						if (this.value < 0 ) this.value = 0;
						if (this.value > <?php print $maxRR ?> ) this.value = <?php print $User->reliabilityRating ?>;"/>
			</p>

			<strong>Excused delays per player:</strong>
			<img id = "modBtnDelays" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" />
			<div id="delayModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span class="close1">&times;</span>
					<p><strong>Excused delays per player:</strong></br>
						The number of excused delays before a player is removed from the game and can be replaced. 
						If a player is missing orders at a deadline, the deadline will reset and the player will be 
						charged 1 excused delay. If they are out of excuses they will go into Civil Disorder.
						The game will only progress with missing orders if no replacement is found within one phase of a player being forced into Civil Disorder. 
						Set this value low to prevent delays to your game, set it higher to be more forgiving to people who might need occasional delays.
					</p>
				</div>
			</div>
			<select class = "gameCreate" id="NMR" name="newGame[excusedMissedTurns]">
			<?php
				for ($i=0; $i<=4; $i++) { print '<option value="'.$i.'"'.($i==1 ? ' selected' : '').'>'.$i.(($i==0)?' (strict)':'').'</option>'; }
			?>
			</select>

			<p>
				<img src="images/icons/lock.png" alt="Private" /> <strong>Add Invite Code (optional):</strong></br>
				<input class = "gameCreate" type="password"autocomplete="new-password" name="newGame[password]" value="" size="20" /></br>
				Confirm: <input class = "gameCreate" autocomplete="new-password" type="password" name="newGame[passwordcheck]" value="" size="20" /></br>
			</p>

			<p class="notice">
				<input class = "green-Submit" type="submit"  value="Create">
			</p>
			</br>
		</form>
	</div>

<script>
// Get the modal
var modal1 = document.getElementById('delayModal');
var modal2 = document.getElementById('scoringModal');
var modal3 = document.getElementById('variantModal');
var modal4 = document.getElementById('phaseLengthModal');
var modal5 = document.getElementById('betModal');
var modal6 = document.getElementById('anonModal');
var modal7 = document.getElementById('messagingModal');
var modal8 = document.getElementById('botModal');

// Get the button that opens the modal
var btn1 = document.getElementById("modBtnDelays");
var btn2 = document.getElementById("modBtnScoring");
var btn3 = document.getElementById("modBtnVariant");
var btn4 = document.getElementById("modBtnPhaseLength");
var btn5 = document.getElementById("modBtnBet");
var btn6 = document.getElementById("modBtnAnon");
var btn7 = document.getElementById("modBtnMessaging");
var btn8 = document.getElementById("modBtnBot");

// Get the <span> element that closes the modal
var span1 = document.getElementsByClassName("close1")[0];
var span2 = document.getElementsByClassName("close2")[0];
var span3 = document.getElementsByClassName("close3")[0];
var span4 = document.getElementsByClassName("close4")[0];
var span5 = document.getElementsByClassName("close5")[0];
var span6 = document.getElementsByClassName("close6")[0];
var span7 = document.getElementsByClassName("close7")[0];
var span8 = document.getElementsByClassName("close8")[0];

// When the user clicks the button, open the modal 
btn1.onclick = function() { modal1.style.display = "block"; }
btn2.onclick = function() { modal2.style.display = "block"; }
btn3.onclick = function() { modal3.style.display = "block"; }
btn4.onclick = function() { modal4.style.display = "block"; }
btn5.onclick = function() { modal5.style.display = "block"; }
btn6.onclick = function() { modal6.style.display = "block"; }
btn7.onclick = function() { modal7.style.display = "block"; }
btn8.onclick = function() { modal8.style.display = "block"; }

// When the user clicks on <span> (x), close the modal
span1.onclick = function() { modal1.style.display = "none"; }
span2.onclick = function() { modal2.style.display = "none"; }
span3.onclick = function() { modal3.style.display = "none"; }
span4.onclick = function() { modal4.style.display = "none"; }
span5.onclick = function() { modal5.style.display = "none"; }
span6.onclick = function() { modal6.style.display = "none"; }
span7.onclick = function() { modal7.style.display = "none"; }
span8.onclick = function() { modal8.style.display = "none"; }

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
	if (event.target == modal1) { modal1.style.display = "none"; }
	if (event.target == modal2) { modal2.style.display = "none"; }
	if (event.target == modal3) { modal3.style.display = "none"; }
	if (event.target == modal4) { modal4.style.display = "none"; }
	if (event.target == modal5) { modal5.style.display = "none"; }
	if (event.target == modal6) { modal6.style.display = "none"; }
	if (event.target == modal7) { modal7.style.display = "none"; }
	if (event.target == modal8) { modal8.style.display = "none"; }
}

function setBotFill(){
	content = document.getElementById("botFill");

	ePress = document.getElementById("pressType");
	pressType = ePress.options[ePress.selectedIndex].value;

	eVariant = document.getElementById("variant");
	variant = eVariant.options[eVariant.selectedIndex].value;

	if (pressType == "NoPress" && variant == 1){
		content.style.display = "block";
	}
	else{
		content.style.display = "none";
		document.getElementById("botBox").checked = false;
	}
}

// Display nextPhaseMinutes paragraph only if phaseSwitchPeriod has selected a period.
nextPhaseMinutesPara = document.getElementById("nextPhaseMinutesPara");

selectPhaseSwitchPeriod = document.getElementById("selectPhaseSwitchPeriod");
phaseSwitchPeriodPara = document.getElementById("phaseSwitchPeriodPara");

selectPhaseMinutes = document.getElementById("selectPhaseMinutes");

nextPhaseMinutesPara.style.display = "none";
phaseSwitchPeriodPara.style.display = "none";


function updatePhasePeriod(){
	if (selectPhaseMinutes.value > 60){
		phaseSwitchPeriodPara.style.display = "none";
		nextPhaseMinutesPara.style.display = "none";
	}
	else{
		phaseSwitchPeriodPara.style.display = "block";
		
		if (selectPhaseSwitchPeriod.value == -1){	
		nextPhaseMinutesPara.style.display = "none";
		}
		else{
		nextPhaseMinutesPara.style.display = "block";
		}
	}
}




selectPhaseSwitchPeriod.addEventListener("change", updatePhasePeriod)
selectPhaseMinutes.addEventListener("change", updatePhasePeriod)

</script>
