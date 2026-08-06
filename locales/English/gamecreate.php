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
	<div class="pageDescription">Start a new game of Diplomacy that other players can join, or <a href="botgamecreate.php">play against bots here</a>.</div>
</div>
<div class="content content-follow-on">
	<?php
	
		// game creation tutorial
		if (isset($_COOKIE['wD-Tutorial-GameCreate'])) 
		{
			$tutorialMessage = l_t('
				This is the game creation page. In order to create a new game, you just need to fill out
				the following form. These slides will give you a quick overview of what you need to think
				about in order to make your game and make sure that you know what each setting you select
				means.
				<br>
				You should first give your game an appropriate title, determine how much you want each player 
				to have to bet to join, and how long each phase would last. If you want to play a game that lasts
				a few hours in the evening but takes your full attention, your phase length should be 5 or 10 minutes, 
				or a "live" game. If you want the game to take place over time instead of requiring your full attention
				one night, pick a longer phase length, or a "non-live" game. 
				<br>
				By default, your game has 7 days to fill. You can change that the time to fill game if you like, but the
				important thing to know is that if you are creating a non-live game, your game will start when it is
				filled, not when the time to fill expires. So, for example, if your non-live game has 7 days to fill
				but is full within 3 days, it will start in 3 days, not 7. A live game will be "scheduled," meaning that
				it will not start until the time to fill has expired, even if it fills early.
				<br>
				You also get to choose whether players can send messages or not, whether players are anonymized or 
				displayed, what map you want to play on, how the game should be scored, and more. For more information on
				these settings, just click the "?" icon next to them on the form after you close this tutorial. Good luck!
			');

			libHTML::help('Create New Game', $tutorialMessage);

			unset($_COOKIE['wD-Tutorial-GameCreate']);
			setcookie('wD-Tutorial-GameCreate', '', ['expires'=>time()-3600,'samesite'=>'Lax']);
		}
	?>
	<div class = "gameCreateShow">
		<form method="post">
			<h3>Basic settings</h3>
			<p>
				<strong>Game Name:</strong></br>
				<input class = "gameCreate" type="text" name="newGame[name]" value="" size="30">
			</p>

			<strong>Bet size: (5-<?php print $User->points.libHTML::points(); ?>)</strong>
			<img id = "modBtnBet" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" class="modalButtonList" />
			<div id="modBtnBetModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span id="modBtnBetClose">&times;</span>
					<p><strong>Bet:</strong> </br>
						The bet required to join this game. This is the amount of points that all players, including you,
						must put into the game's "pot" (<a href="points.php" class="light">read more</a>).<br /><br />
					</p>
				</div>
			</div>
			<input class = "gameCreate" type="text" name="newGame[bet]" size="7" value="<?php print $formPoints ?>" />
			
			</br></br>
			<strong>Phase length: (5 min - 10 days)</strong>
			<img id = "modBtnPhaseLength" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" class="modalButtonList" />
			<div id="modBtnPhaseLengthModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span id="modBtnPhaseLengthClose">&times;</span>
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

			<div class="hr"></div>

			<h3>Advanced settings</h3>
			<strong>Phase length (Retreats, Builds & Missed Turns)</strong>
			<img id = "modBtnPhaseLengthRB" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" class="modalButtonList" />
			<div id="modBtnPhaseLengthRBModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span id="modBtnPhaseLengthRBClose">&times;</span>
					<p><strong>Phase length (Retreats, Builds & Missed Turns): </strong></br>
						How long retreat and build phases, and grace periods for missed turns will last.
					</p>
				</div>
			</div>
			<select class = "gameCreate" name="newGame[phaseMinutesRB]" id="selectPhaseMinutesRB">
			<?php
				$phaseList = array(-1,1, 2, 3, 5, 7, 10, 15, 20, 30, 60, 120, 240, 360, 480, 600, 720, 840, 960, 1080, 1200, 1320,
					1440, 1440+60, 2160, 2880, 2880+60*2, 4320, 5760, 7200, 8640, 10080, 14400);

				foreach ($phaseList as $i) { 
					if ($i != -1)
					{
						print '<option value="'.$i.'"'.($i==-1 ? ' selected' : '').'>'.libTime::timeLengthText($i*60).'</option>'; 
					}
					else
					{
						print '<option value="'.$i.'"'.($i==-1 ? ' selected' : '').'> Same as Movement phases</option>';
					}
				}
			?>
			</select>


			<p id="phaseSwitchPeriodPara">
				<strong>Time Until Phase Swap</strong></br>
				<select class = "gameCreate" id="selectPhaseSwitchPeriod" name="newGame[phaseSwitchPeriod]">
				<?php
					$phaseList = array(-1, 10, 15, 20, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330, 360);
					foreach ($phaseList as $i) 
					{
						if ($i != -1)
						{
							print '<option value="'.$i.'"'.($i==-1 ? ' selected' : '').'>'.libTime::timeLengthText($i*60).'</option>';
						}
						else 
						{
							print '<option value="'.$i.'"'.($i==-1 ? ' selected' : '').'> No phase switch</option>';
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
						print '<option value="'.$i.'"'.($i==1440 ? ' selected' : '').'>'.libTime::timeLengthText($i*60).'</option>';
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
						print '<option value="'.$i.'"'.($i==10080 ? ' selected' : '').'>'.libTime::timeLengthText($i*60).'</option>';
					}
				?>
				</select>
			</p>
			
			<strong>Game Messaging:</strong>
			<img id = "modBtnMessaging" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" class="modalButtonList" />
			<div id="modBtnMessagingModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span id="modBtnMessagingClose">&times;</span>
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
			<strong>Variant/Map type (map choices):</strong>
			<img id = "modBtnVariant" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" class="modalButtonList" />
			<div id="modBtnVariantModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span id="modBtnVariantClose">&times;</span>
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
						if($first) { print '<option name="newGame[variantID]" selected value="'.$variantID.'">'.$Variant->fullName.'</option>'; }
						else { print '<option name="newGame[variantID]" value="'.$variantID.'">'.$Variant->fullName.'</option>'; }			
						$first=false;
					}
				}
				print '</select>';
			?>
			</br></br>
			<div id="botFill" style="display:none">
			<strong>Fill Empty Spots with Bots: </strong>
			<img id = "modBtnBot" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" class="modalButtonList" />
			<div id="modBtnBotModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span id="modBtnBotClose">&times;</span>
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
			<img id = "modBtnScoring" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" class="modalButtonList" />
			<div id="modBtnScoringModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span id="modBtnScoringClose">&times;</span>
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
			<img id = "modBtnAnon" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" class="modalButtonList" />
			<div id="modBtnAnonModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span id="modBtnAnonClose">&times;</span>
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

			<strong>Excused delays per player:</strong>
			<img id = "modBtnDelays" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" class="modalButtonList" />
			<div id="modBtnDelaysModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span id="modBtnDelaysClose">&times;</span>
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
				for ($i=0; $i<=4; $i++) { print '<option value="'.$i.'"'.($i==3 ? ' selected' : '').'>'.$i.(($i==0)?' (strict)':'').'</option>'; }
			?>
			</select>

			<div class="hr"></div>

			<p>
				<strong>Required reliability rating:</strong>
				<img id = "modBtnReliability" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" class="modalButtonList" /></br>
				<div id="modBtnReliabilityModal" class="modal">
					<!-- Modal content -->
					<div class="modal-content">
						<span id="modBtnReliabilityClose">&times;</span>
						<p><strong>Required reliability rating:</strong></br>
							A player's reliability rating varies from 0% to 100% depending on how many times they have 
							failed to submit orders when they are due. By setting this to higher values you will exclude
							less reliable players, but you may have more trouble finding players to join.
						</p>
					</div>
				</div>
				<input id="minRating" class = "gameCreate" type="text" name="newGame[minimumReliabilityRating]" size="2" value="<?php print $defaultRR ?>"
					onkeypress="if (event.keyCode==13) this.blur(); return event.keyCode!=13"
					onChange="
						this.value = parseInt(this.value);
						if (this.value == 'NaN' ) this.value = 0;
						if (this.value < 0 ) this.value = 0;
						if (this.value > <?php print $maxRR ?> ) this.value = <?php print $User->reliabilityRating ?>;"/>
			</p>

			<p>
				<img src="images/icons/lock.png" alt="Private" /> <strong>Add Invite Code / Password:</strong>
				<img id = "modBtnPassword" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" class="modalButtonList" />
				<div id="modBtnPasswordModal" class="modal">
					<!-- Modal content -->
					<div class="modal-content">
						<span id="modBtnPasswordClose">&times;</span>
						<p><strong>Invite Code / Password: </strong></br>
							Optionally add a code / password so that only people you tell the code / password to can join. 
							Leave blank if you do not want to add a code / password.
						</p>
					</div>
				</div>
				<input class = "gameCreate" type="password"autocomplete="new-password" name="newGame[password]" value="" size="20" />
			</p>
			<p>
				<img src="images/icons/lock.png" alt="Private" /> <strong>Confirm Invite Code / Password:</strong></br> <input class = "gameCreate" autocomplete="new-password" type="password" name="newGame[passwordcheck]" value="" size="20" /></br>
			</p>

			<div class="hr"></div>

			<p class="notice">
				<input class = "green-Submit" type="submit"  value="Create">
			</p>
			</br>
			<?php
			print libAuth::formTokenHTML();
			?>
		</form>
	</div>

<script>
var buttons = document.getElementsByClassName("modalButtonList");
for(var i = 0; i < buttons.length; i++) {
	buttons[i].onclick = function() {
		var modal = document.getElementById(this.id + "Modal");
		modal.style.display = "block";
		var closeButton = document.getElementById(this.id + "Close");
		closeButton.onclick = function() {
			modal.style.display = "none"; 
		};
		closeButton.innerHTML = "<a href='#'>&times;</a>";
		closeButton.style = "float: right; color: #000; font-size: 28px; font-weight: bold; padding-right:10px;";
		window.onclick = function(event) {
			if (event.target == modal) { modal.style.display = "none"; }
		}
	}
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


	var phaseLength = parseInt(selectPhaseMinutes.value);


	for (i = 0; i < selectPhaseSwitchPeriod.length; i++){
		var optVal = parseInt(selectPhaseSwitchPeriod.options[i].value);
		if (optVal <= 0 || optVal > phaseLength){
			selectPhaseSwitchPeriod.options[i].hidden = false;
			selectPhaseSwitchPeriod.options[i].disabled = false;
		}
		else{
			selectPhaseSwitchPeriod.options[i].hidden = true;
			selectPhaseSwitchPeriod.options[i].disabled = true;
		}
	}

	selectPhaseMinutesRB = document.getElementById("selectPhaseMinutesRB");

	for (i = 0; i < selectPhaseMinutesRB.length; i++){
		var optVal = parseInt(selectPhaseMinutesRB.options[i].value);
		if (optVal < 0 || optVal >= phaseLength / 10 && optVal <= phaseLength){
			selectPhaseMinutesRB.options[i].hidden = false;
			selectPhaseMinutesRB.options[i].disabled = false;
		}
		else{
			selectPhaseMinutesRB.options[i].hidden = true;
			selectPhaseMinutesRB.options[i].disabled = true;
		}
	}
	selectPhaseMinutesRB.value = -1;
}




selectPhaseSwitchPeriod.addEventListener("change", updatePhasePeriod)
selectPhaseMinutes.addEventListener("change", updatePhasePeriod)
window.onload = updatePhasePeriod

</script>

<?php libHTML::$footerIncludes[] = l_j('help.js'); ?>
