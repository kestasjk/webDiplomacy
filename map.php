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
 * @package Map
 */

if( !isset($_REQUEST['variantID']) && ( !isset($_REQUEST['gameID']) || !isset($_REQUEST['turn']) ) )
	die('gameID or turn not provided; cannot draw map');

define('IN_CODE', 1);

if( isset($_REQUEST['DATC'])||isset($_REQUEST['nocache'])||isset($_REQUEST['uncache'])||isset($_REQUEST['profile']) )
	define('IGNORECACHE',1);
else
	define('IGNORECACHE',0);

if( isset($_REQUEST['uncache'])||isset($_REQUEST['profile']) )
	define('DELETECACHE',1);
else
	define('DELETECACHE',0);
    
// Check if we should hide the move arrows. (Preview do not need the old move-arrows too...)
if( isset($_REQUEST['hideMoves']) || isset($_REQUEST['preview']))
	define('HIDEMOVES',1);
else
	define('HIDEMOVES',0);

// Check if we need to color enhance the map
if( isset($_REQUEST['colorCorrect']))
{
	switch($_REQUEST['colorCorrect']) {
		case 'Protanope':   define('COLORCORRECT','Protanope');   break;
		case 'Deuteranope': define('COLORCORRECT','Deuteranope'); break;
		case 'Tritanope':   define('COLORCORRECT','Tritanope');   break;
		default: define('COLORCORRECT',0);
	}
}
else
	define('COLORCORRECT',0);

// Check if we should hide the move arrows.
if( isset($_REQUEST['preview']))
	define('PREVIEW',1);
else
	define('PREVIEW',0);

// Check if we need to show CountryNames
if( isset($_REQUEST['countryNames']))
	define('COUNTRYNAMES',1);
else
	define('COUNTRYNAMES',0);

if( !IGNORECACHE && !PREVIEW)
{
	// We might be able to fetch the map from the cache
	
	require_once('locales/layer.php'); // Load the localization layer; by itself it will do no localization
	require_once('objects/game.php');
	require_once('lib/html.php');
	require_once('lib/cache.php');

	$filename = Game::mapFilename((int)$_REQUEST['gameID'], (int)$_REQUEST['turn']);
    
    // Map without arrows 
    if (HIDEMOVES)
        $filename = str_replace(".map","-hideMoves.map",$filename);

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
}

// Cache isn't an option; set things up to draw the map
require_once('header.php');

if( DELETECACHE && !$User->type['Admin'] )
	die(l_t('Disable-cacheing flags set, but you are not an admin.'));

if ( isset($_REQUEST['DATC']) )
{
	if( $Misc->Maintenance )
		define('DATC', 1);
	else
		die(l_t('Cannot render DATC maps outside of maintenance mode.'));
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
	global $Game;
	$Variant=libVariant::loadFromGameID($_REQUEST['gameID']);
	libVariant::setGlobals($Variant);
	// The game is locked for update so the map isn't drawn twice at the same time
	$Game=$Variant->Game($_REQUEST['gameID'],UPDATE);

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


// Load the drawMap object for the given map type
if ( $mapType == 'xml' )
{
	require_once(l_r('map/drawMapXML.php'));
	$drawMap = $Variant->drawMapXML();
}
elseif ( $mapType == 'json' )
{
	require_once(l_r('board/orders/jsonBoardData.php'));
	$filename=Game::mapFilename($Game->id, $turn, 'json');
	file_put_contents($filename, jsonBoardData::getBoardTurnData($Game->id) );
	libHTML::serveImage($filename, 'text/plain');
}
else
{
	require_once(l_r('map/drawMap.php'));
	$drawMap = $Variant->drawMap($mapType=='small');
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
	if ( $terrType == 'Sea' )
	{
		// Set owner to false so that units will draw their countryID flag
		$owners[$terrID] = 0;
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

		if (!HIDEMOVES) $drawMap->drawMove($terrID, $toTerrID, $success);
	}
	elseif ( $moveType == 'Retreat' )
	{
		if (!HIDEMOVES) $drawMap->drawRetreat($terrID, $toTerrID, $success);

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
		if (!HIDEMOVES) $drawMap->drawSupportHold($terrID,
			isset($deCoastMap['SupportHoldToTerrID'][$toTerrID]) ? $deCoastMap['SupportHoldToTerrID'][$toTerrID] : $toTerrID,
			$success);
	}
	elseif ( $moveType == 'Support move' )
	{
		if (!HIDEMOVES) $drawMap->drawSupportMove($terrID,
			isset($deCoastMap['SupportMoveFromTerrID'][$fromTerrID]) ? $deCoastMap['SupportMoveFromTerrID'][$fromTerrID] : $fromTerrID,
			isset($deCoastMap['SupportMoveToTerrID'][$fromTerrID.'-'.$toTerrID]) ? $deCoastMap['SupportMoveToTerrID'][$fromTerrID.'-'.$toTerrID] : $toTerrID,
			$success);
	}
	elseif ( $moveType == 'Convoy' )
	{
		if (!HIDEMOVES) $drawMap->drawConvoy($terrID, $fromTerrID, $toTerrID, $success);
	}

	/*
	 * If we have already drawn an "occupying thing" in this territory don't do it again,
	 * although drawing moves to the occupied territory is okay
	 */
	if ( !$dislodged
		and !isset($fullTerrID[$terrID])
		and ( isset($drawToTerrID) and ! isset($fullTerrID[$drawToTerrID]) ) )
	{
		// Do not display destroyed units in previews 
		if (PREVIEW && in_array($terrID,$destroyedTerrs)) continue;

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

foreach( $destroyedTerrs as $terrID ) 
    if (!HIDEMOVES) $drawMap->drawDestroyedUnit(isset($deCoastMap['DestroyToTerrID'][$terrID]) ? $deCoastMap['DestroyToTerrID'][$terrID] : $terrID );
foreach( $dislodgedTerrs as $terrID ) 
    if (!HIDEMOVES) $drawMap->drawDislodgedUnit($terrID);
foreach( $builtTerrs as $terrID=>$unitType ) 
    if (!HIDEMOVES) $drawMap->drawCreatedUnit($terrID, $unitType);

// support hold to, support move from, support move to, build/destroy fleet

// Map is drawn, now add a preview of the server-side orders...
if (PREVIEW && $Game->Members->isJoined())
{
	$sql = "SELECT u.type, u.terrID, o.type, o.toTerrID, o.fromTerrID, o.viaConvoy	
				FROM wD_Orders o
			LEFT JOIN wD_Units u ON (u.id = o.unitID)
				WHERE o.gameID = ".$Game->id." AND o.countryID = ".$Game->Members->ByUserID[$User->id]->countryID."
				ORDER BY FIELD(o.type, 'Move')";

	$tabl = $DB->sql_tabl($sql);

	while(list($unitType, $terrID, $orderType, $toTerrID, $fromTerrID, $viaConvoy) = $DB->tabl_row($tabl))
	{
		if ($orderType == 'Move' && (int)$terrID != 0 && (int)$toTerrID != 0)
		{
			$drawMap->drawMove($terrID, $toTerrID, true);
		}
		elseif ( $orderType == 'Support hold' && (int)$terrID != 0 && (int)$toTerrID != 0)
		{
			$drawMap->drawSupportHold($terrID,
				isset($deCoastMap['SupportHoldToTerrID'][$toTerrID]) ? $deCoastMap['SupportHoldToTerrID'][$toTerrID] : $toTerrID,
				true);
		}
		elseif ( $orderType == 'Support move' && (int)$terrID != 0 && (int)$toTerrID != 0 && (int)$fromTerrID != 0 )
		{
			$drawMap->drawMoveGrey(isset($deCoastMap['SupportMoveFromTerrID'][$fromTerrID]) ? $deCoastMap['SupportMoveFromTerrID'][$fromTerrID] : $fromTerrID,
							isset($deCoastMap['SupportMoveToTerrID'][$fromTerrID.'-'.$toTerrID]) ? $deCoastMap['SupportMoveToTerrID'][$fromTerrID.'-'.$toTerrID] : $toTerrID,
							true);			
			$drawMap->drawSupportMove($terrID,
				isset($deCoastMap['SupportMoveFromTerrID'][$fromTerrID]) ? $deCoastMap['SupportMoveFromTerrID'][$fromTerrID] : $fromTerrID,
				isset($deCoastMap['SupportMoveToTerrID'][$fromTerrID.'-'.$toTerrID]) ? $deCoastMap['SupportMoveToTerrID'][$fromTerrID.'-'.$toTerrID] : $toTerrID,
				true);
		}
		elseif ( $orderType == 'Convoy' && (int)$terrID != 0 && (int)$toTerrID != 0 && (int)$fromTerrID != 0  )
		{
			$drawMap->drawMoveGrey(isset($deCoastMap['SupportMoveFromTerrID'][$fromTerrID]) ? $deCoastMap['SupportMoveFromTerrID'][$fromTerrID] : $fromTerrID,
							isset($deCoastMap['SupportMoveToTerrID'][$fromTerrID.'-'.$toTerrID]) ? $deCoastMap['SupportMoveToTerrID'][$fromTerrID.'-'.$toTerrID] : $toTerrID,
							true);					
			$drawMap->drawConvoy($terrID, $fromTerrID, $toTerrID, true);
		}
		if ($orderType == 'Build Army' && (int)$toTerrID != 0)
		{
			$drawMap->drawCreatedUnit($toTerrID,'Army');
		}
		if ($orderType == 'Build Fleet' && (int)$toTerrID != 0)
		{
			$drawMap->drawCreatedUnit($toTerrID,'Fleet');
		}
		if ($orderType == 'Retreat' && (int)$terrID != 0 && (int)$toTerrID != 0)
		{
			$drawMap->countryFlag($terrID, $Game->Members->ByUserID[$User->id]->countryID);
			$drawMap->addUnit($terrID, $unitType);			
			$drawMap->drawRetreat($terrID, $toTerrID, true);
		}
		if ($orderType == 'Destroy' && (int)$toTerrID != 0)
		{
			$drawMap->drawDestroyedUnit(isset($deCoastMap['DestroyToTerrID'][$toTerrID]) ? $deCoastMap['DestroyToTerrID'][$toTerrID] : $toTerrID );
		}
		
		$drawMap->caption('Preview');
		$drawMap->drawRedBox();
		
	}
	
}

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

if (HIDEMOVES)
    $filename = str_replace(".map","-hideMoves.map",$filename);

if( defined('DATC') && $mapType!='small')
	$drawMap->saveThumbnail($filename.'-thumb');

	
// colorCorrect Patch
if (COLORCORRECT)
{
	$filename = str_replace(".map","-".COLORCORRECT.".map",$filename);
	$drawMap->colorEnhance(COLORCORRECT);
}
// End colorCorrect Patch

// Add countrynames for colorblind:
if (COUNTRYNAMES)
	$filename = str_replace(".map","-names.map",$filename);

if (PREVIEW)
{
	$drawMap->writeToBrowser();
}
else 
{
	$drawMap->write($filename);
	libHTML::serveImage($filename);
}
unset($drawMap); // $drawMap is memory intensive and should be freed as soon as no longer needed

?>
