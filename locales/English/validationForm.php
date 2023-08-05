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
		if( !(isset(Config::$recaptchaSiteKey) && Config::$recaptchaSiteKey != null) )
		{
			?>
			<li class="formlisttitle">Anti-bot code</li>
			<li class="formlistfield">
					<img alt="EasyCaptcha image" src="<?php print STATICSRV; ?>contrib/easycaptcha.php" /><br />
					<input type="text" name="imageText" />
			</li>
			<li class="formlistdesc">
				By entering the above code you protect our forum from spam-bots and other scripts
			</li>
			<?php 
		}
		else if (isset($_REQUEST['antiBotTest']))
		{
			?>
			
			<li class="formlisttitle">Anti-bot challenge</li>
			<li class="formlistdesc">
				To keep the site free of bots please select Munich, Berlin, and Kiel on the map below.<br />
				If you are having trouble with this anti-bot challenge please contact <a href="mailto:admin@webdiplomacy.net">admin@webdiplomacy.net</a>.
			</li>
			<li class="formlistfield">
				<canvas id="boardCanvasBase" style="display:none"></canvas>
				<canvas id="boardCanvasOptions" style="display:none"></canvas>
				<div style="text-align:center">
					<canvas id="boardCanvas"></canvas>
				</div>
			</li>
<script>
	// Contains default assignments, code to generate a summary table, variant specific data
	let canvasBoardConfigJS = {};
<?php
	$Variant = libVariant::loadFromVariantID(1);
	print 'canvasBoardConfigJS['.$Variant->id.'] = '.$Variant->canvasBoardConfigJS().';';
?>
	function initializeAntiBotBoard() {
		// Load the default variant
		variantID = "1";

		// When the map is clicked apply an assignment, redraw the map, and save the new options
		canvasElement.addEventListener('click', (event) => {
			applyAssignment();
			drawMap();
		});

		loadVariant(() => {
			currentUnitSCState = canvasBoardConfigJS[variantID].getEmptyOptions()
			assigningCountryID = 4;
			drawMap();
		});
	}
</script>

<?php
libHTML::$footerIncludes[] = l_j('canvasBoard.js');
libHTML::$footerScript[] = 'initializeAntiBotBoard();';
		}
		?>
		<li class="formlisttitle">E-mail address</li>
		<li class="formlistfield"><input type="text" name="emailValidate" value="<?php
		        if ( isset($_REQUEST['emailValidate'] ) )
					print $_REQUEST['emailValidate'];
		        ?>"></li>
		<li class="formlistdesc">
			By making sure every user has a real e-mail address we stop cheaters from creating many users for themselves. 
			This will not be spammed, shared, or released.
		</li>
</ul>

<div class="hr"></div>

<p class="notice">
	<?php 
	if( isset(Config::$recaptchaSiteKey) && Config::$recaptchaSiteKey != null )
	{
	?>
		<input type="hidden" name="recaptchaToken" id="recaptchaToken" value="" />
		<script>
		function onSubmit(e) {
			//e.preventDefault();
			grecaptcha.enterprise.ready(async () => {
				const token = await grecaptcha.enterprise.execute('<?php print Config::$recaptchaSiteKey; ?>', {action: 'LOGIN'});
				document.getElementById("recaptchaToken").value = token;
				document.getElementById("wd-register-form").submit();
			});
		}
		</script>
		<button type="button" 
			class="green-Submit" 
			onClick="onSubmit()"
			value="Validate me">Submit</button>

	<?php
	}
	else
	{
	?>
		<input type="submit" class="green-Submit" value="Validate me">
	<?php
	}
	?>
</p>
</form>