<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Claccic-Fog-of-War variant for webDiplomacy

	The Claccic-Fog-of-War variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General Public
	License as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Claccic-Fog-of-War variant for webDiplomacy is distributed in the hope that 
	it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

if( !isset($_REQUEST['variantID']) && ( !isset($_REQUEST['gameID']) || !isset($_REQUEST['turn']) ) )
	die('gameID or turn not provided; cannot draw map');

define('IN_CODE', 1);

if( isset($_REQUEST['country']))
	$mcountry=$_REQUEST['country'];

if( isset($_REQUEST['DATC'])||isset($_REQUEST['nocache'])||isset($_REQUEST['uncache'])||isset($_REQUEST['profile']) )
	define('IGNORECACHE',1);
else
	define('IGNORECACHE',0);

if( isset($_REQUEST['uncache'])||isset($_REQUEST['profile']) )
	define('DELETECACHE',1);
else
	define('DELETECACHE',0);

chdir ('../../../');

// Cache isn't an option; set things up to draw the map
require_once('header.php');

if( DELETECACHE && !$User->type['Admin'] )
	die('Disable-cacheing flags set, but you are not an admin.');

if ( isset($_REQUEST['DATC']) )
{
	if( $Misc->Maintenance )
		define('DATC', 1);
	else
		die('Cannot render DATC maps outside of maintenance mode.');
}

/*
 * Map drawing:
 * - What turn are we viewing?
 * - Is the map we want in the cache?
 * - Draw countryID colors, standoffs, from TerrStatusArchive
 * - Draw units, order arrows, from MovesArchive
 * - Save map
 * - Recreate map cache files
 * - Output map
 */

ini_set('memory_limit',"14M");
ini_set('max_execution_time','12');

if( !isset($_REQUEST['variantID']) )
{
	/*
	 * Get the two required parameters; game ID and turn
	 */
	require_once('objects/game.php');
	require_once('lib/html.php');
	require_once('lib/cache.php');
	
	global $Game;
	$Variant=libVariant::loadFromGameID($_REQUEST['gameID']);
	libVariant::setGlobals($Variant);
	
	// The game is locked for update so the map isn't drawn twice at the same time
	$Game=$Variant->Game($_REQUEST['gameID'],UPDATE);

	$verify=$_REQUEST['verify'];
	if (strlen($verify) == 6) {
		list($ccodes)=$DB->sql_row("SELECT text FROM wD_Notices WHERE toUserID=3 AND timeSent=0 AND fromID=".$Game->id);
		$pos=strpos($ccodes,$verify);
	} else {
		$pos=false;
	}
	if ($pos === false) 
		$verify="fog";
	else
		$mcountry=$pos/6;	

	// Prevent cheaters to open the map-directory
	$filename = Game::gameFolder((int)$_REQUEST['gameID']) . '/index.html';
	if (!(file_exists($filename))) {
		$handle = fopen ($filename, 'w');
		fwrite($handle, "Cheater don't do this...");
		fclose($handle);
	}
		
	// We might be able to fetch the map from the cache
	$filename = Game::mapFilename((int)$_REQUEST['gameID'], (int)$_REQUEST['turn']) ;
	$filename = str_replace(".map","-".$verify.".map",$filename);
	if( file_exists($filename) )
	{
		header("Last-Modified: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);

		if( Game::mapType()=='json' )
			libHTML::serveImage($filename, 'text/plain');
		else
			libHTML::serveImage($filename);
	}
	
	/*
	 * Determine which turn we are viewing. This is made a little trickier because
	 * in the Diplomacy phase the *previous* turn is drawn. $_REQUEST['turn'] is
	 * expected to have already been adjusted for this: If this game is in
	 * Diplomacy phase turn should already be one less than the Game's turn
	 */

	// Determine the turn number:
	if ( $Game->phase == 'Diplomacy' ) $latestTurn = $Game->turn-1;
	else $latestTurn = $Game->turn;

	$turn = $latestTurn;

	$givenTurn = (int) $_REQUEST['turn'];
	if ( $givenTurn >= -1 && $givenTurn <= $latestTurn )
		$turn = $givenTurn;
	unset($givenTurn);

	$mapType = Game::mapType();
}
else
{
	$Variant=libVariant::loadFromVariantID($_REQUEST['variantID']);
	libVariant::setGlobals($Variant);
	$mapType = 'small';
	$turn=-1;
}

// Initialize the NoFog array

$noFog = array();

if ($verify != "fog") {

	if( $turn == -1 )	{
		$sql="SELECT toTerrID,fromTerrID from wD_Borders WHERE mapID=".$Variant->mapID." AND fromTerrID IN
					(SELECT t.id FROM wD_Territories t WHERE t.supply='Yes' AND t.countryID=".$mcountry." AND t.mapID=".$Variant->mapID.")
				UNION (SELECT id,NULL from wD_Territories WHERE countryID=".$mcountry." AND mapID=".$Variant->mapID.")";								
	} elseif ( $turn == $latestTurn ) {
		$sql="SELECT toTerrID, fromTerrID from wD_Borders WHERE mapID=".$Variant->mapID." AND fromTerrID IN
					(SELECT t.coastParentID FROM wD_Territories t 
						LEFT JOIN wD_TerrStatus ts ON (t.id=ts.terrID AND ts.gameID=".$Game->id." )
					WHERE t.mapID=".$Variant->mapID." AND ((t.supply='Yes' AND ts.countryID=".$mcountry.") OR EXISTS (SELECT * from wD_Units u WHERE u.terrID=t.id AND u.countryID=".$mcountry." AND u.gameID=".$Game->id.")))
				UNION (SELECT terrID,NULL from wD_TerrStatus WHERE countryID=".$mcountry." AND gameID=".$Game->id.")";
	} else {
		$sql="SELECT toTerrID,fromTerrID from wD_Borders WHERE mapID=".$Variant->mapID." AND fromTerrID IN
					(SELECT t.coastParentID FROM wD_Territories t 
						LEFT JOIN wD_TerrStatusArchive ts ON (ts.turn=".($turn)." AND t.id=ts.terrID AND ts.gameID=".$Game->id." AND t.mapID=".$Variant->mapID.")
					WHERE t.mapID=".$Variant->mapID." AND ((t.supply='Yes' AND ts.countryID=".$mcountry.") OR EXISTS (SELECT * from wD_MovesArchive u WHERE u.turn=".($turn+1)." AND u.terrID=t.id AND u.countryID=".$mcountry." AND u.gameID=".$Game->id.")))
				UNION (SELECT terrID,NULL from wD_TerrStatusArchive WHERE turn=".($turn)." AND countryID=".$mcountry." AND gameID=".$Game->id.")";
	}

	$tabl = $DB->sql_tabl($sql);

	if ($mcountry > 0) {
		while(list($terrID1,$terrID2) = $DB->tabl_row($tabl))
		{
			$noFog[] = $Variant->deCoast($terrID1);
			$noFog[] = $Variant->deCoast($terrID2);
		}
	} else {
		for ($i=0; $i<500; $i++)
			$noFog[] = $i;
	}
}

// Load the drawMap object for the given map type
if ( $mapType == 'xml' )
{
	require_once('map/drawMapXML.php');
	$drawMap = $Variant->drawMapXML();
}
elseif ( $mapType == 'json' )
{
	require_once('variants/ClassicFog/resources/jsonBoardData.php');
	$filename=Game::mapFilename($Game->id, $turn, 'json');
	$filename = str_replace(".map","-".$verify.".map",$filename);
	file_put_contents($filename, jsonBoardData::getBoardTurnData($Game->id,$noFog));
	libHTML::serveImage($filename, 'text/plain');
}
else
{
	require_once('map/drawMap.php');
	$drawMap = $Variant->drawMap($mapType=='small',false);
}

/*
 * Draw TerrStatus
 */
 
if( $turn==-1 )
{
	// Pre-game; just draw country default terrstatus
	$sql = "SELECT t.id, t.name, t.type, t.countryID, 'No' as standoff
			FROM wD_Territories t
			WHERE (t.coast='No' OR t.coast='Parent') AND mapID=".$Variant->mapID;
}
else
{
	$sql = "SELECT t.id, t.name, t.type, ts.countryID, ts.standoff
			/* Territories are selected first, not TerrStatus, so that unoccupied territories can be drawn neutral */
			FROM wD_Territories t
			LEFT JOIN wD_TerrStatusArchive ts
				ON ( ts.gameID = ".$Game->id." AND ts.turn = ".$turn." AND ts.terrID = t.id )
			/* TerrStatus is non-coastal */
			WHERE (t.coast='No' OR t.coast='Parent') AND t.mapID=".$Variant->mapID;
}

$tabl = $DB->sql_tabl($sql);
$owners = array();
while(list($terrID, $terrName, $terrType, $countryID, $standoff) = $DB->tabl_row($tabl))
{
	if (in_array($terrID,$noFog)) { 
		if ( $terrType == 'Sea' )
		{
			// Set owner to false so that units will draw their countryID flag
			$owners[$terrID] = 0;
			$drawMap->colorTerritory($terrID, 9);
		}
		else
		{
			if ( ! $countryID ) $countryID = 0;
			$owners[$terrID] = $countryID;
			$drawMap->colorTerritory($terrID, $countryID);
		}

		if ( isset($Game) && $Game->phase == 'Retreats' or $mapType!='small' )
		{
			// Only draw standoffs if we're in the retreats phase, or we're viewing that large map
			if ( $standoff == 'Yes' ) $drawMap->drawStandoff($terrID);
		}
	} else {
		$drawMap->colorTerritory($terrID, 8);
	}	
}

if( isset($_REQUEST['variantID']) )
{
	$drawMap->addTerritoryNames();

	$drawMap->saveThumbnail(libVariant::cacheDir($Variant->name).'/sampleMap-thumbnail.png');
	$drawMap->write(libVariant::cacheDir($Variant->name).'/sampleMap.png');
	libHTML::serveImage(libVariant::cacheDir($Variant->name).'/sampleMap.png');

	die();
}

/*
 * Collect the de-coast mappings. There are 4 types of moves which store decoasted territory data, which now
 * needs to be reconstructed to determine the actual positions.
 */
$deCoastMap=array('SupportMoveFromTerrID'=>array(),'SupportMoveToTerrID'=>array(),'SupportHoldToTerrID'=>array(),'DestroyToTerrID'=>array());

$tabl=$DB->sql_tabl("SELECT type, terrID, toTerrID, dislodged, success FROM wD_MovesArchive
	WHERE gameID = ".$Game->id." AND turn = ".$turn." AND unitType='Fleet' AND
		( type='Hold' OR type='Move' OR type='Support hold' OR type='Support move' )");
while(list($moveType, $terrID, $toTerrID, $dislodged, $success) = $DB->tabl_row($tabl))
{
	$terrDeCoast = $Game->Variant->deCoast($terrID);
	$toTerrDeCoast = $Game->Variant->deCoast($toTerrID);

	if( $terrDeCoast != $terrID )
	{
		$deCoastMap['SupportMoveFromTerrID'][$terrDeCoast]=$terrID;
		$deCoastMap['SupportHoldToTerrID'][$terrDeCoast]=$terrID;

		if( ( $moveType!='Move' || $success=='No') && $dislodged=='No' )
			$deCoastMap['DestroyToTerrID'][$terrDeCoast]=$terrID;
	}
	elseif( $toTerrDeCoast != $toTerrID && $moveType=='Move' )
	{
		$deCoastMap['SupportMoveToTerrID'][$terrID.'-'.$toTerrDeCoast]=$toTerrID;

		if( $success=='Yes' )
			$deCoastMap['DestroyToTerrID'][$toTerrDeCoast]=$toTerrID;
	}
}

$tabl=$DB->sql_tabl("SELECT toTerrID FROM wD_MovesArchive
	WHERE gameID = ".$Game->id." AND turn = ".$turn." AND unitType='Fleet' AND
		type='Retreat' AND success='Yes'");
while(list($toTerrID) = $DB->tabl_row($tabl))
{
	$toTerrDeCoast = $Game->Variant->deCoast($toTerrID);
	if( $toTerrDeCoast != $toTerrID )
		$deCoastMap['DestroyToTerrID'][$toTerrDeCoast] = $toTerrID;
}


// Territories are colored, standoffs drawn, decoast mappings collected. Now the moves need to be drawn:
/*
 * Draw moves
 */
if( $turn==-1  )
{
	if( $Game->turn<=0 && $Game->phase=='Diplomacy' )
		$sql = "SELECT
					'Hold' as type, terrID,
					countryID, 0 as toTerrID, 0 as fromTerrID, 'No' as viaConvoy, /* Order */
					type as unitType, /* Unit */
					'Yes' as success, 'No' as dislodged /* Move */
				FROM wD_Units
				WHERE gameID = ".$Game->id;
	else
		$sql = "SELECT * FROM wD_Units WHERE 1=2";
}
else
{
	$sql = "SELECT
					type, terrID,
					countryID, toTerrID, fromTerrID, viaConvoy, /* Order */
					unitType, /* Unit */
					success, dislodged /* Move */
				FROM wD_MovesArchive
				WHERE gameID = ".$Game->id." AND turn = ".$turn." ORDER BY type DESC";
}

/* Start with unit placement moves, and go back. This lets us know that the place we're
 not drawing a unit to is about to have a unit destruction or another unit drawn on top of it. */
$tabl = $DB->sql_tabl($sql);

$destroyedTerrs = array();
$dislodgedTerrs = array();
$builtTerrs = array();
while(list($moveType, $terrID,
		$countryID, $toTerrID, $fromTerrID, $viaConvoy,
		$unitType,
		$success, $dislodged) = $DB->tabl_row($tabl))
{
	if (in_array($Variant->decoast($terrID),$noFog) || in_array($Variant->decoast($toTerrID),$noFog)) {

		$success = ( $success == 'Yes' );
		$dislodged = ( $dislodged == 'Yes' );

		if ( $moveType == 'Destroy' )
		{
			$destroyedTerrs[$terrID] = $terrID;
			continue;
		}
		elseif ( $dislodged )
		{
			$dislodgedTerrs[$terrID] = $terrID;
		}
		elseif( $moveType == 'Disband' )
		{
			continue;
		}

		unset($drawToTerrID);

		if ( $moveType == 'Move' )
		{
			if ( $success ) $drawToTerrID = $toTerrID;
			else $drawToTerrID = $terrID;

			$drawMap->drawMove($terrID, $toTerrID, $success);
		}
		elseif ( $moveType == 'Retreat' )
		{
			$drawMap->drawRetreat($terrID, $toTerrID, $success);

			if ( $success ) $drawToTerrID = $toTerrID;
			else continue;
		}
		elseif( ( $moveType == 'Build Army' or $moveType == 'Build Fleet' ) and $success )
		{
			if ( $moveType == 'Build Army' ) $unitType = 'Army';
			elseif ( $moveType == 'Build Fleet' ) $unitType = 'Fleet';

			$builtTerrs[$terrID] = $unitType;

			$drawToTerrID = $terrID;
		}
		elseif ( ! $dislodged )
		{
			$drawToTerrID = $terrID;
		}

		if ( $moveType == 'Support hold' )
		{
			$drawMap->drawSupportHold($terrID,
				isset($deCoastMap['SupportHoldToTerrID'][$toTerrID]) ? $deCoastMap['SupportHoldToTerrID'][$toTerrID] : $toTerrID,
				$success);
		}
		elseif ( $moveType == 'Support move' )
		{
			$drawMap->drawSupportMove($terrID,
				isset($deCoastMap['SupportMoveFromTerrID'][$fromTerrID]) ? $deCoastMap['SupportMoveFromTerrID'][$fromTerrID] : $fromTerrID,
				isset($deCoastMap['SupportMoveToTerrID'][$fromTerrID.'-'.$toTerrID]) ? $deCoastMap['SupportMoveToTerrID'][$fromTerrID.'-'.$toTerrID] : $toTerrID,
				$success);
		}
		elseif ( $moveType == 'Convoy' )
		{
			$drawMap->drawConvoy($terrID, $fromTerrID, $toTerrID, $success);
		}

		/*
		 * If we have already drawn an "occupying thing" in this territory don't do it again,
		 * although drawing moves to the occupied territory is okay
		 */
		if ( !$dislodged
			and !isset($fullTerrID[$terrID])
			and ( isset($drawToTerrID) and ! isset($fullTerrID[$drawToTerrID]) ) 
			and in_array($Variant->deCoast($drawToTerrID),$noFog) )
		{
			/*
			 * We're drawing a unit onto the board
			 */
			if ( $owners[$Game->Variant->deCoast($drawToTerrID)] != $countryID )
			{
				// We don't own the countryID which we're inside of, draw our flag
				$drawMap->countryFlag($drawToTerrID, $countryID);
			}
			
			$drawMap->addUnit($drawToTerrID, $unitType);
		}
	}
}

foreach( $destroyedTerrs as $terrID )
	if (in_array($Variant->decoast($terrID),$noFog))
		$drawMap->drawDestroyedUnit(isset($deCoastMap['DestroyToTerrID'][$terrID]) ? $deCoastMap['DestroyToTerrID'][$terrID] : $terrID );

foreach( $dislodgedTerrs as $terrID )
	if (in_array($Variant->decoast($terrID),$noFog))
		$drawMap->drawDislodgedUnit($terrID);
foreach( $builtTerrs as $terrID=>$unitType ) 
	if (in_array($Variant->decoast($terrID),$noFog))
		$drawMap->drawCreatedUnit($terrID, $unitType);

// support hold to, support move from, support move to, build/destroy fleet

/*
 * Territories colored, orders entered, units drawn on.
 * Now add territory names, and game-over caption if finished
 */

// Territory names
$drawMap->addTerritoryNames();


if( DELETECACHE )
{
	$drawMap->caption(
		round((microtime(true)-$GLOBALS['scriptStartTime']),2).'sec, DB-Out:'.$DB->getqueries.', DB-In:'.$DB->putqueries
			.(function_exists('memory_get_usage')?', '.round((memory_get_usage()/1024)/1024, 3).'MB':'')
	);
}
elseif( $Game->phase == 'Finished' and $turn == $latestTurn )
	$drawMap->caption($Game->gameovertxt(TRUE));


/*
 * All done; save map to disk, then generate a new JavaScript list
 * of available maps which includes the new map, and finally output
 * the map which was saved.
 */

$filename = Game::mapFilename($Game->id, $turn);
$filename = str_replace(".map","-".$verify.".map",$filename);

if( defined('DATC') && $mapType!='small')
	$drawMap->saveThumbnail($filename.'-thumb');

$drawMap->write($filename);
unset($drawMap); // $drawMap is memory intensive and should be freed as soon as no longer needed

libHTML::serveImage($filename);

?>