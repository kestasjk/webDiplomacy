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

/**
 * @package DATC
 */


/*
 * Some kludge to turn the DATC page HTML to test case SQL:
 * Watch out for differences in the spelling of territory names
 *
 * cat index.html | perl -e 'while(<>){m/<a name="(6\.[A-H]\.[0-9]+)">/ and $a=$1; m/<\/pre>/ and do { $a=""; $b=0;}; if ( $a and $b ) {print $a.$_;}; m/<pre>/ and $b=1;}'
 * | perl -ne 'm/6\.[A-H]\.[0-9]+.{3,}/ and print;'
 * | perl -e 'while(<>){m/6\.[A-H]\.[0-9]+((?:England)|(?:France)|(?:Germany)|(?:Italy)|(?:Russia)|(?:Austria)|(?:Turkey))/ and $a=$1 or print $a.",".$_;}'
 * | perl -pe 's/^(.*?),(6\.[A-H]\.[0-9]+)(A|F) (.*?) ((?:\-)|(?:Supports)|(?:Convoys)|(?:Hold))/$2,$1,$3,$4,$5/;s/, ((A|F) )?/,/;s/((?:A|F),[^,]+,)\- /$1Move,/;s/Supports (A|F) /Support,/;s/Convoys (A|F) /Convoy,/;s/ \- /,/g;s/via Convoy/via convoy/;s/,/","/g;s/^/"/;s/$/"/;s/\(nc\)/ (North Coast)/;s/\(sc\)/ (South Coast)/'
 * | perl -ne 's/(..)$//;print;'
 * | sed -e 's/$/"/'
 * | perl -pe 's/"Support",(".*?"),(".*?")/"Support move",$2,$1/;s/"Support"/"Support hold"/;s/"Convoy","(.*?)","(.*?)"/"Convoy","$2","$1"/;s/"Hold"/"Hold",NULL,NULL,"No"/;s/"Move","(.*?)"/"Move","$1",NULL,/;s/"Support hold","(.*?)"/"Support hold","$1",NULL,"No"/; m/Convoy|Support move/ and s/$/,"No"/;s/"F"/"Fleet"/;s/"A"/"Army"/; m/via convoy/ and s/ via convoy// and s/$/"Yes"/; s/,$/,"No"/;'
 *
 * cat moves2.txt | perl -pe 's/,"Illegal","Dislodglodged/ or m/Success/ or s/"((?:No)|(?:Yes))"/"$1","Hold"/; m/Illegal/ or s/$/,"Legal"/;s/^/(/;s/$/),/;' > datc.sql
 */

defined('IN_CODE') or die('This script can not be run by itself.');

ini_set('memory_limit',"30M");
ini_set('max_execution_time','30');


define('DATC',1);

require_once('gamemaster/game.php');

require_once('gamepanel/member.php'); // userMember extends panelMember

require_once('board/member.php');
require_once('board/orders/orderinterface.php');

require_once('gamemaster/orders/order.php');
require_once('gamemaster/orders/diplomacy.php');
//require_once('gamemaster/orders/retreats.php');
//require_once('gamemaster/orders/builds.php');
//require_once('gamemaster/adjudicator/pregame.php');
require_once('gamemaster/adjudicator/diplomacy.php');
//require_once('gamemaster/adjudicator/retreats.php');
//require_once('gamemaster/adjudicator/builds.php');

require_once('datc/datcGame.php');

$DB->sql_put("BEGIN");

if ( isset($_REQUEST['reset']) )
{
	if( $files = glob('datc/maps/*.*') )
		foreach($files as $file)
			unlink($file);

	$DB->sql_put("UPDATE wD_DATC SET status = 'NotPassed' WHERE status = 'Passed'");
}

unset($testID); // Determine the test ID to test

list($testID) = $DB->sql_row(
					"SELECT testID FROM wD_DATC
					WHERE status = 'NotPassed'
					ORDER BY testID ASC LIMIT 1"
				);

if ( isset($_REQUEST['testID']) )
{
	$testID = (int) $_REQUEST['testID'];
}
elseif ( !isset($testID) or !$testID )
{
	$DB->sql_put("BEGIN");
	list($gameID) = $DB->sql_row("SELECT id FROM wD_Games WHERE name='DATC-Adjudicator-Test'");
	if($gameID)
		processGame::eraseGame($gameID);
	$DB->sql_put("COMMIT");

	print '<p class="notice">'.l_t('There appear to be no more tests left! All tests have passed!').' <a href="datc.php?reset=on">'.l_t('Reset tests').'</a></p>';
	print '</div>';
	print '<div class="content">';
	return;
}

print '<div class="gamelistings-tabs">
		<a href="datc.php?next=on">Next</a>
		<a href="datc.php?testID='.$testID.'&verySlowStep=on&rand='.rand(0,99999).'">'.l_t('Run first third').'</a>
		<a href="datc.php?testID='.$testID.'&slowStep=on&rand='.rand(0,99999).'">'.l_t('Run first two thirds').'</a>
		<a href="datc.php?testID='.$testID.'&rand='.rand(0,99999).'">'.l_t('Run full').'</a>
		<a href="datc.php?testID='.$testID.'&batchTest='.rand(0,99999).'#map">'.l_t('Batch-test').'</a>
		<a href="datc.php?reset=on">'.l_t('Reset all').'</a>
		</div>';


libHTML::pagebreak();

list($testName, $variantID, $testDesc) = $DB->sql_row(
			"SELECT testName, variantID, testDesc FROM wD_DATC
			WHERE testID = ".$testID
		);

/*
 * DATC tests are performed in two parts since orders were made to be submitted via JSON.
 * - First request for a given testID:
 * 		- Load the testID, create/find the DATC test game, create the units, terrstatus, blank orders for the game.
 * 		- For each set of orders for each countryID generate a JSON context token for that countryID, output the
 * 			skeleton orders table and the javascript to initialize it.
 * 			Aside from the usual stuff extra code is provided for each countryID in each test which enters each order
 * 			parameter one by one, via the same code path it would go through if it was being submitted by hand. As
 * 			this code executes the countryID's orders table fills up with the required parameters.
 * 	- For each countryID with orders: Once this is done a non-asynchronous request is made using that countryID's context
 * 		token to ajax.php, submitting the countryID's orders. The JSON per-order status returned from this query is saved
 * 		to a hash indexed by order ID.
 * 	- Once all countryIDs have submitted orders the results for each order set are JSON encoded and submitted to datc.php,
 * 		refreshing the page. The new datc.php loads up the same testID as before, finding the game without initializing it.
 * 		It then decodes the JSON DATCResults from ajax.php, and checks it against the order valid/legal requirements.
 * 		If an order wasn't supposed to be valid but in fact was valid, or visa versa, an exception is thrown.
 * 		If all the orders were submitted and handled as expected it then adjudicates the game using the given orders,
 * 		which ajax.php updated in wD_Orders. The orders are completed and adjudication proceeds as normal. (This will
 * 		mess up any other games being processed via gamemaster.php, which is why DATC testing can only be done in
 * 		maintenance mode.)
 * 		Once adjudication is done the outcomes are tested against the DATC test's required outcomes, differences result
 * 		in exceptions. Then orders and units are archived and the game is set to a phase where it'll be drawn by map.php.
 * 	- Two map.php requests, for large and small maps, are submitted. DATC tests are written to a different folder, and
 * 		will overwrite existing maps by default, but are otherwise handled as regular maps are.
 * 		If DATC testing is in batch mode, and no errors occurred, the page will move onto the next test after a 1 second wait.
 * 		This continues until all tests are complete or an error is found. Once all tests are complete the DATC test
 * 		game is wiped.
 *
 * The code is inefficient, messy, will corrupt games being processed normally at the same time, and may screw up badly if
 * 	run two at a time, but it works and provides an easy way to test changes to orders/adjudication code.
 *
 * Non-Diplomacy phase DATC tests are currently unsupported.
 */

print l_t('Loading test').' <strong><a href="http://web.inter.nl.net/users/L.B.Kruijswijk/#'.$testName.'">'.
		$testName.'</a></strong>: '.$testDesc.'<br /><br />';

global $Variant;
$Variant = libVariant::loadFromVariantID($variantID);
$testCase = new datcGame($testID);

$testCase->outputTest();

print l_t('Initialized test');
if( !isset($_REQUEST['DATCResults']) )
{
	// We're loading a new test: Set the game up
	$testCase->initialize();

	print ', '.l_t('generating orders').'<br /><br />';
	$testCase->submitOrders();

	print l_t('Orders being generated and sumbitted').'...<br /><br />';

	// submitOrders() has code that will submit the results of all countryID's ajax JSON orders updates once finished.
}
else // In this space a bunch of ajax.php requests update wD_Orders.
{
	// Since the code above all countryID's orders have been submitted, and the results are now given via a JSON encoded DATCResults parameter.
	// Order updates have been saved to wD_Orders; check the ajax.php results are correct, then adjudicate and check the adjudication results.

	$_REQUEST['DATCResults'] = (array)json_decode($_REQUEST['DATCResults']);

	try
	{
		$testCase->checkInvalidOrders(); // Check DATCResults.

		print l_t('New adjudication').'<br /><br />';

		$DB->sql_put("DELETE FROM wD_Moves WHERE gameID=".$GLOBALS['GAMEID']);

		// Prepare wD_Orders for adjudication
		$PO=$testCase->Variant->processOrderDiplomacy();
		$PO->completeAll();
		$PO->toMoves();

		$adj=$Game->Variant->adjudicatorDiplomacy();
		$standOffTerrs = $adj->adjudicate();

		$testCase->checkResults(); // Check the adjudication results, saved to wD_Moves

		print l_t('%s has passed!',$testName);
		$passed = true;

		$DB->sql_put("UPDATE wD_DATC SET status = 'Passed' WHERE testID = ".$testID);
	}
	catch ( Exception $e )
	{
		// Something messed up.
		print $e->getMessage();
		$passed = false;
	}

	// Save the adjudication results in wD_Moves to wD_Units and wD_TerrStatus, and update wD_Games so that map.php will correctly
	// draw the results.
	$testCase->mapPrepare( isset($standOffTerrs) ? $standOffTerrs : array() );

	if ( isset($_REQUEST['batchTest']) and $passed )
	{
		// We're batch testing, and we passed, move straight onto the next test.

		header('refresh: 0; url=datc.php?next=on&batchTest='.rand(0,999999));

		ob_clean();
		libHTML::starthtml('Batch testing');

		print '<div class="content datc">
			<p class="notice">'.l_t('Passed test %s. Rendering, saving maps, moving onto next test.',$testName).'</p>';
	}

	print '<div class="hr"></div>
		<p class="notice"><a href="map.php?gameID='.$testCase->id.'&turn='.$testID.'&DATC=1&nocache='.rand(0,99999).'"><img alt="'.l_t('Drawing map').'..." src="map.php?gameID='.$testCase->id.'&turn='.$testID.'&DATC=1&nocache='.rand(0,99999).'" /></a></p>';
}

print '</div>';

libHTML::footer();
