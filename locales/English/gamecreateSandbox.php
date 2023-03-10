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
	<div class="pageTitle barAlt1">Create a new sandbox game</div>
	<div class="pageDescription">Start a new sandbox game of Diplomacy where you can play as all powers.</div>
</div>
<div class="content content-follow-on">
	<div class = "gameCreateShow">
		<form method="post">
			<p>
				<strong>Sandbox Name:</strong></br>
				<input class = "gameCreate" type="text" name="newGame[name]" value="" size="30">
			</p>

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
			<select id="variant" class = "gameCreate" name="newGame[variantID]" onchange="getVariantTerritoryBuildChoices()">
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

			<div class="hr"></div>
			<p class="notice">
				<input class = "green-Submit" type="submit"  value="Create">
			</p>

			<!--
			<div class="hr"></div>
				<h3>Custom unit assignments</h3>
				TODO: Allow assignment of units at the start of the game, for example to test a specific scenario.
			</br>
			-->
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

function getVariantTerritoryBuildChoices() {
}


</script>

<?php libHTML::$footerIncludes[] = l_j('help.js'); ?>
