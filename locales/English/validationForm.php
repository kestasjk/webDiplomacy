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

?><h2>Anti-bot Validation</h2>

<form method="post" action="register.php" id="wd-register-form">

	<ul class="formlist">
		<?php 
		/*if( !(isset(Config::$recaptchaSiteKey) && Config::$recaptchaSiteKey != null) )
		{
			?>
			<li class="formlisttitle">Anti-bot code</li>
			<li class="formlistfield">
					<img alt="EasyCaptcha image" src="contrib/easycaptcha.php" /><br />
					<input type="text" name="imageText" />
			</li>
			<li class="formlistdesc">
				By entering the above code you protect our forum from spam-bots and other scripts
			</li>
			<?php 
		}
		else if (isset($_REQUEST['antiBotTest']))
		{*/
			$Variant = libVariant::loadFromVariantID(1);
			$countryIDChallenge = array_rand($Variant->countries) + 1;
			$countryIDChallengeName = $Variant->countries[$countryIDChallenge-1];
			?>
			
			<li class="formlisttitle">Anti-bot challenge</li>
			<li class="formlistdesc variantClassic">
				To prevent bots from joining please verify you are human by clicking 
				the supply centers for <strong><span class="country<?php print $countryIDChallenge;?>"><?php print $countryIDChallengeName; ?></span></strong>
				in the map below: 

				<strong><span id="antiBotRequest" class="country<?php print $countryIDChallenge;?>"></span></strong>.<br />
				<em>If you are having trouble with this anti-bot challenge please contact <a href="mailto:admin@webdiplomacy.net">admin@webdiplomacy.net</a></em>.
			</li>
			<li class="formlistfield">
				<input type="hidden" name="antiBotCountryID" value="<?php print $countryIDChallenge;?>"  />
				<input type="hidden" name="antiBotTerritoryIDs" id="antiBotTerritoryIDs" value=""  />
				<canvas id="boardCanvasBase" style="display:none"></canvas>
				<canvas id="boardCanvasOptions" style="display:none"></canvas>
				<div style="text-align:center">
					<canvas id="boardCanvas"></canvas>
					<div id="antiBotRequestStatus" class="variantClassic"></div>
				</div>
			</li>
<script>
	// Contains default assignments, code to generate a summary table, variant specific data
	let canvasBoardConfigJS = {};
<?php
	print 'canvasBoardConfigJS['.$Variant->id.'] = '.$Variant->canvasBoardConfigJS().';';
	// Select a random countryID:
?>

	let countryIDChallenge = <?php print $countryIDChallenge; ?>;

	function initializeAntiBotBoard() {
		// Load the default variant
		variantID = "1";

		let supplyCenters = [];

		function refreshAntiBotRequestText() {
			var tick = '<img src="/images/icons/tick.png" alt="(Selected)" />';
			var cross = '<img src="/images/icons/cross.png" alt="(Not selected)" />';
			var isAllSelected = true;
			var supplyCenterIDs = [];
			let supplyCenterNames = supplyCenters.sort((a, b) => a.id - b.id).map((supplyCenter) => {
				// Check if the currentUnitSCState has a record where unitPostitionTerrID = supplyCenter.id
				let isSelected = false;
				currentUnitSCState.find((unitPosition) => {
					if( unitPosition.unitPositionTerrID == supplyCenter.id )
					{
						isSelected = true;
					}
				});
				
				if( !isSelected )
					isAllSelected = false;
				else
					supplyCenterIDs.push(supplyCenter.id);

				return supplyCenter.name + ' ' + ( isSelected ? tick : cross );
			});
			// Combine into a comma seperated string:
			var supplyCenterList = supplyCenterNames.join(', ');
			document.getElementById('antiBotRequest').innerHTML = supplyCenterList;

			var challengeText = 'Supply centers to select: <strong>' + supplyCenterList + '</strong>';
			if( isAllSelected )
				challengeText += ' <strong><em>Challenge passed!</em></strong>';
			else
				challengeText += ' <strong><em>Challenge not passed.</em></strong>';
			document.getElementById('antiBotRequestStatus').innerHTML = challengeText;
			
			document.getElementById('antiBotTerritoryIDs').value = supplyCenterIDs.join(',');
		}

		// When the map is clicked apply an assignment, redraw the map, and save the new options
		canvasElement.addEventListener('click', (event) => {
			applyAssignment();
			drawMap();
			refreshAntiBotRequestText();
		});

		loadVariant(() => {
			currentUnitSCState = canvasBoardConfigJS[variantID].getEmptyOptions()
			assigningCountryID = countryIDChallenge;
			drawMap();
			supplyCenters = Object.values(canvasBoardConfigJS[variantID].getSupplyCenters()).filter((supplyCenter) => {
				return supplyCenter.countryID == countryIDChallenge && supplyCenter.coastParentID == supplyCenter.id;
			});
			refreshAntiBotRequestText();
		});
	}
</script>

<?php
libHTML::$footerIncludes[] = l_j('canvasBoard.js');
libHTML::$footerScript[] = 'initializeAntiBotBoard();';
		//}
		?>
		<li class="formlisttitle">E-mail address</li>
		<li class="formlistfield"><input type="text" name="emailValidate" value="<?php
		        if ( isset($_REQUEST['emailValidate'] ) )
					print htmlspecialchars($_REQUEST['emailValidate'], ENT_QUOTES, 'UTF-8');
		        ?>"></li>
		<li class="formlistdesc">
			By making sure every user has a real e-mail address we stop cheaters from creating many users for themselves. 
			This will not be spammed, shared, or released.
		</li>
</ul>

<div class="hr"></div>

<p class="notice">
	<input type="submit" class="green-Submit" value="Validate me">
</p>
</form>