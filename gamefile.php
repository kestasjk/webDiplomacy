<?php
/*
    Copyright (C) 2004-2026 Kestas J. Kuliukas

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
 * Where a client goes when one of a game's public JSON files (lib/gamefiles.php, doc/gamedata/02-spec.md) isn't in
 * the cache folder: a game from before the files existed, or a cache which has been cleared. It writes the files and
 * redirects to the one asked for. Like map.php it needs no log-on, as the files are public; clients which are logged
 * on get the files' URLs from the game/playercontext API route, which also writes missing files, and never come here.
 *
 * gamefile.php?gameID=<id>&file=<game|status|history|messages>[&sbToken=<token>]
 * gamefile.php?variantID=<id>&file=variant
 *
 * @package Board
 */

$file = isset($_REQUEST['file']) ? (string)$_REQUEST['file'] : '';
if( !in_array($file, array('variant', 'game', 'status', 'history', 'messages'), true) )
{
	http_response_code(400);
	die('Unknown file.');
}

define('IN_CODE', 1);

// If the file is there nothing else needs loading. (Not for sandbox games, whose files have names which can't be
// worked out from the game ID; they are dealt with below.)
$gameID = isset($_REQUEST['gameID']) ? (int)$_REQUEST['gameID'] : 0;
if( $file != 'variant' && $gameID > 0 )
{
	$path = 'cache/games/'.floor($gameID/100).'/'.$gameID.'/'.$file.'.json';
	if( file_exists($path) )
	{
		header('Location: '.$path.'?v='.filemtime($path), true, 302);
		die();
	}
}

require_once('header.php');

if( $file == 'variant' )
{
	$variantID = isset($_REQUEST['variantID']) ? (int)$_REQUEST['variantID'] : 0;
	$url = libGameFiles::variantURL($variantID);
	if( is_null($url['url']) || is_null($url['version']) )
	{
		http_response_code(404);
		die('Unknown variant.');
	}

	header('Location: '.$url['url'].'?v='.$url['version'], true, 302);
	die();
}

$gameRow = ( $gameID > 0 ? libGameFiles::loadGameRow($gameID) : false );
if( $gameRow === false )
{
	http_response_code(404);
	die('Unknown game.');
}

// The rule board.php applies to sandbox games
if( libGameFiles::isSandbox($gameRow) && $gameRow['sandboxCreatedByUserID'] != $User->id && !$User->type['Moderator']
	&& !( isset($_REQUEST['sbToken']) && is_string($_REQUEST['sbToken']) && hash_equals(libAuth::sandboxToken_Key($gameID), $_REQUEST['sbToken']) ) )
{
	http_response_code(403);
	die("You can't view this game, it is a sandbox game which you didn't create.");
}

$stale = libGameFiles::staleFiles($gameRow, libGameFiles::loadMemberRows($gameID));
$state = count($stale) ? libGameFiles::refresh($gameID, $stale) : libGameFiles::state($gameID);
$urls = libGameFiles::urls($gameRow, $state);

if( is_null($urls[$file]['version']) || !file_exists($urls[$file]['url']) )
{
	http_response_code(503);
	header('Retry-After: 5');
	die('The file could not be written just now.');
}

header('Location: '.$urls[$file]['url'].'?v='.$urls[$file]['version'], true, 302);

?>
