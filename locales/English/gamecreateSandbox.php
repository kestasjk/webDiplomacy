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
				<input class = "gameCreate" type="text" name="newGame[name]" value="<?php print md5(time()); ?>" size="30">
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
			<select id="variant" class="gameCreate" name="newGame[variantID]" onchange="variantSelectionChanged()">
			<?php
				$defaultVariantID = ( isset($_REQUEST['newGame']) && isset($_REQUEST['newGame']['variantID']) ) ? (int)$_REQUEST['newGame']['variantID'] : 1;
				foreach(Config::$variants as $variantID=>$variantName)
				{
					if($variantID != 57)
					{
						$Variant = libVariant::loadFromVariantName($variantName);
						if($variantID === $defaultVariantID ) { print '<option name="newGame[variantID]" selected value="'.$variantID.'">'.$variantName.'</option>'; }
						else { print '<option name="newGame[variantID]" value="'.$variantID.'">'.$variantName.'</option>'; }
					}
				}
				print '</select>';
			?>
			</br></br>

			<div class="hr"></div>
			
			<input type="hidden" name="newGame[armyAssignments]" id="armyAssignments" value=""  />
			<input type="hidden" name="newGame[fleetAssignments]" id="fleetAssignments" value="" />
			<input type="hidden" name="newGame[scAssignments]" id="scAssignments" value="" />
			<input type="hidden" name="savedOptions" id="savedOptions" value="<?php print isset($_REQUEST['savedOptions']) ? htmlentities($_REQUEST['savedOptions']) : '' ?>" />
			
			<p class="notice">
				<input class = "green-Submit" type="submit"  value="Create">
			</p>

			<div class="hr"></div>
			<strong>Custom unit assignments</strong>
			<img id = "modBtnUnitAssignments" height="16" width="16" src="images/icons/help.png" alt="Help" title="Help" class="modalButtonList" />
			<div id="modBtnUnitAssignmentsModal" class="modal">
				<!-- Modal content -->
				<div class="modal-content">
					<span id="modBtnUnitAssignmentsClose">&times;</span>
					<p>
						By default the game will start with units in their default positions, but you can change this by entering custom unit placements below.
						<br /><br />
						If custom units are placed suppy centers will be re-assigned to countries automatically based on the custom unit placements, and to try
						and make the unit and supply center count match for each country.
					</p>
				</div>
			</div>
			<canvas id="boardCanvasBase" style="display:none"></canvas>
			<canvas id="boardCanvasOptions" style="display:none"></canvas>
			<div style="text-align:center">
				<canvas id="boardCanvas"></canvas>
			</div>
			<strong>Set country:</strong><br />
			<div id="customUnitCountrySelect"></div>
			<strong>Set mode:</strong><br />
			<div id="customUnitAssignSelect">
				<table style='text-align:center'>
					<tr>
						<td id='assignUnitAndSC' class='selectedMode' style='border: 1px solid black; background-color:#ddd'>Unit and Supply center</td>
						<td id='assignUnit' style='border: 1px solid black; background-color:#ddd'>Unit only</td>
						<td id='assignSC' style='border: 1px solid black; background-color:#ddd'>Supply center only</td>
					<tr>
				</table>
			</div>
			<style>
				.selectedMode {
					border: 3px solid black !important;
					font-weight:bold;
				}
			</style>
			<strong>Assignments:</strong><br />
			<div id="customUnitAssignments">
			</div>
			<?php
			print libAuth::formTokenHTML();
			?>
		</form>
	</div>
	
<script>
	// Set up the modal help buttons that display help info for the form
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
</script>

<script>
	// Contains default assignments, code to generate a summary table, variant specific data
	let canvasBoardConfigJS = {};
<?php

foreach(Config::$variants as $variantID=>$variantName)
{
	// Exclude incompatible variants (TODO: put this in the config)
	if($variantID != 57 && $variantID != 70)
	{
		$Variant = libVariant::loadFromVariantName($variantName);
		print 'canvasBoardConfigJS['.$Variant->id.'] = '.$Variant->canvasBoardConfigJS().';';
	}
}
?>
</script>

<script>

	// Set up the assignment mode buttons, which select whether a click assigns a unit, SC or both
	document.getElementById('assignUnitAndSC').onclick = function() {
		assignmentMode = 0;
		document.getElementById('assignUnitAndSC').className = 'selectedMode';
		document.getElementById('assignUnit').className = '';
		document.getElementById('assignSC').className = '';
	}
	document.getElementById('assignUnit').onclick = function() {
		assignmentMode = 1;
		document.getElementById('assignUnitAndSC').className = '';
		document.getElementById('assignUnit').className = 'selectedMode';
		document.getElementById('assignSC').className = '';
	}
	document.getElementById('assignSC').onclick = function() {
		assignmentMode = 2;
		document.getElementById('assignUnitAndSC').className = '';
		document.getElementById('assignUnit').className = '';
		document.getElementById('assignSC').className = 'selectedMode';
	}

	function setupCountryButtons() {
		let countryNamesByID = canvasBoardConfigJS[variantID].getCountryNamesByID();
		
		let emptyHtml = canvasBoardConfigJS[variantID].getEmptyFormHTML();
		document.getElementById('customUnitAssignments').innerHTML = emptyHtml;
		
		let countrySelectTable = '<table><tr>';
		let countryID = 0;
		for (const color of countryColors)
		{
			const countryName = countryID == 0 ? 'None' : countryNamesByID[countryID-1];
			countrySelectTable += '<td '+(countryID == 0 ? 'class="selectedMode"':'')+' style="background-color:rgb('+color[0]+','+color[1]+','+color[2]+'); width: 20px; height: 20px; border: 1px solid black;" id="countrySelection'+countryID+'">'+countryName+'</td>';
			countryID++;
		}
		countrySelectTable += '</tr></table>';
		document.getElementById('customUnitCountrySelect').innerHTML = countrySelectTable;
		countryID = 0;
		for (const color of countryColors)
		{
			const localCountryID = countryID;
			const countryElement = document.getElementById('countrySelection'+countryID);
			countryElement.onclick = function() {
				countryElement.classList.add('selectedMode');
				
				const previousCountryElement = document.getElementById('countrySelection'+assigningCountryID);
				previousCountryElement.classList.remove('selectedMode');
			
				assigningCountryID = localCountryID;
			}
			countryID++;
		}
	}

	// Saves the currentUnitSCState to the form so that it will be submitted
	function savecurrentUnitSCState() {
		// The list of army/fleet/SC territories by country ID, in a format 2,3,4;5,6,7;8,9,10 , where countryID 1 has terr IDs 2,3,4, countryID 2 has terr IDs 5,6,7, etc:
		const getUnitListByCountryID = (unitType) => {
			let listByCountry = [];
			for(let i = 0; i < countryColors.length; i++)
			{
				listByCountry[i] = [];
			}
			for(let optionIndex in currentUnitSCState)
			{
				let option = currentUnitSCState[optionIndex];
				if( option.unitType == unitType && option.unitPositionTerrID >= -1 && option.countryID > 0 )
				{
					listByCountry[option.countryID-1].push(option.unitPositionTerrID);
				}
			}
			return listByCountry;
		};
	
		const getSCListByCountryID = () => {
			let listByCountry = [];
			for(let i = 0; i < countryColors.length; i++)
			{
				listByCountry[i] = [];
			}
			for(let optionIndex in currentUnitSCState)
			{
				let option = currentUnitSCState[optionIndex];
				if( option.unitSCTerrID >= -1 && option.countryID > 0 )
				{
					listByCountry[option.countryID-1].push(option.unitSCTerrID);
				}
			}
			return listByCountry;
		};
	
		let armyTerrIDsByCountry = getUnitListByCountryID('Army').map(innerArray => innerArray.join(',')).join(':');
		let fleetTerrIDs = getUnitListByCountryID('Fleet').map(innerArray => innerArray.join(',')).join(':');
		let scTerrIDs = getSCListByCountryID().map(innerArray => innerArray.join(',')).join(':');
		document.getElementById('armyAssignments').value = armyTerrIDsByCountry;
		document.getElementById('fleetAssignments').value = fleetTerrIDs;
		document.getElementById('scAssignments').value = scTerrIDs;
		document.getElementById('savedOptions').value = JSON.stringify(currentUnitSCState);
	}

	function applyVariantToForm()
	{
		setupCountryButtons();
		canvasBoardConfigJS[variantID].applyOptionsToTable(currentUnitSCState);
		savecurrentUnitSCState();
	}

	// Triggers when a different variant is selected from the dropdown, triggering the variant map and options to be loaded
	function variantSelectionChanged() {
		let selection = document.getElementById('variant');
		variantID = selection.options[selection.selectedIndex].value;
		currentUnitSCState = {};
		loadVariant(applyVariantToForm);
	}
	
	function initializeSandboxSetupBoard() {
		// Load the default variant
		variantID = document.getElementById('variant').value;

		// If there was an error reload the previously saved options
		if( document.getElementById('savedOptions').value != '' )
		{
			currentUnitSCState = JSON.parse(document.getElementById('savedOptions').value);
		}

		// When the map is clicked apply an assignment, redraw the map, and save the new options
		canvasElement.addEventListener('click', (event) => {
			applyAssignment();
			drawMap();
			canvasBoardConfigJS[variantID].applyOptionsToTable(currentUnitSCState);
			savecurrentUnitSCState();
		});

		loadVariant(applyVariantToForm);
	}
</script>

<?php
libHTML::$footerIncludes[] = l_j('help.js'); 
libHTML::$footerIncludes[] = l_j('canvasBoard.js');
libHTML::$footerScript[] = 'initializeSandboxSetupBoard();';
?>
