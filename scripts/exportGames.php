<?php
/*
 * The monthly data export: every finished game, for anyone who wants to analyse them, as one gzipped JSON Lines file
 * with one game per line. sse-server/server.js runs this once a month and uploads the result to Hugging Face (see
 * "The monthly data export" there); the format is described in scripts/dataset/README.md, which goes with it.
 *
 * php scripts/exportGames.php --dir=PATH [--month=YYYY-MM] [--limit=N]
 *
 *   --dir=PATH       Where to write: games-YYYY-MM.jsonl.gz, variants.json, and games-YYYY-MM.json, the manifest,
 *                    which is written last and so says the export is complete
 *   --month=YYYY-MM  The export's month (default this month, UTC). It holds the games finished before the month began.
 *   --limit=N        Stop after N games, for testing
 *
 * What goes in is what a logged-out visitor can see of a finished game on board.php - the files lib/gamefiles.php
 * builds - less who played it. Players are a hash of their user ID, the same in every game and every month's export,
 * keyed from Config::$secret so that it can't be reversed by hashing every user ID. Usernames, points and when players
 * were last seen are left out. Messages are only those sent to everyone; sandbox games aren't included.
 *
 * It reads one game at a time and commits after every batch, so that it never holds a long transaction open on the
 * live database, and it is meant to be run under nice. Only one can run at once, per output directory.
 */

if( php_sapi_name() != 'cli' ) die();

chdir(dirname(__DIR__));
define('AJAX', true); // header.php then doesn't look for a logged-on user
require_once('header.php');
ob_end_clean();

ini_set('memory_limit', '1024M');
set_time_limit(0);
// Otherwise every query is timed and the slow ones kept, which over a whole export adds up
Config::$debug = false;

$options = getopt('', array('dir:', 'month:', 'limit:', 'help'));
if( isset($options['help']) || !isset($options['dir']) )
{
	// The usage comment at the top of this file
	preg_match('#/\*(.*?)\*/#s', file_get_contents(__FILE__), $usage);
	print trim(preg_replace('/^\s*\* ?/m', '', $usage[1]))."\n";
	exit(isset($options['help']) ? 0 : 1);
}

$dir = rtrim($options['dir'], '/');
$month = isset($options['month']) ? $options['month'] : gmdate('Y-m');
$limit = isset($options['limit']) ? max(1, (int)$options['limit']) : null;

if( !preg_match('/^\d{4}-\d{2}$/', $month) )
{
	fwrite(STDERR, "--month must be YYYY-MM\n");
	exit(1);
}
$monthStart = strtotime($month.'-01 00:00:00 UTC');

if( !is_dir($dir) && !mkdir($dir, 0775, true) )
{
	fwrite(STDERR, "Can't create $dir\n");
	exit(1);
}

$lock = fopen($dir.'/export.lock', 'c');
if( !$lock || !flock($lock, LOCK_EX | LOCK_NB) )
{
	fwrite(STDERR, "An export is already running in $dir\n");
	exit(2);
}

// The site's error handler ends the script on any warning; one bad game should only be skipped
set_error_handler(function($level, $message, $file, $line) {
	if( !( error_reporting() & $level ) || $level == E_DEPRECATED || $level == E_USER_DEPRECATED )
		return true;
	throw new ErrorException($message, 0, $level, $file, $line);
});

$hashKey = hash_hmac('sha256', 'dataexport-userid', Config::$secret);
function playerID($userID)
{
	global $hashKey;

	return is_null($userID) ? null : substr(hash_hmac('sha256', (string)$userID, $hashKey), 0, 16);
}

/**
 * One line of the export, from the game's public files
 */
function exportRecord(array $files)
{
	$game = $files['game'];

	$members = array();
	foreach($game['members'] as $member)
		$members[] = array(
			'countryID' => $member['countryID'],
			'country' => $member['country'],
			'playerID' => is_null($member['user']) ? null : playerID($member['user']['userID']),
			'isBot' => !is_null($member['user']) && in_array('Bot', $member['user']['type']),
			'status' => $member['status'],
			'supplyCenterNo' => $member['supplyCenterNo'],
			'unitNo' => $member['unitNo'],
			'bet' => $member['bet'],
			'pointsWon' => $member['pointsWon'],
			'missedPhases' => $member['missedPhases'],
			'excusedMissedTurns' => $member['excusedMissedTurns'],
		);

	$civilDisorders = array();
	foreach(( is_null($game['civilDisorders']) ? array() : $game['civilDisorders'] ) as $cd)
		$civilDisorders[] = array(
			'playerID' => playerID($cd['userID']),
			'countryID' => $cd['countryID'],
			'turn' => $cd['turn'],
			'supplyCenterNo' => $cd['supplyCenterNo'],
		);

	$messages = array();
	foreach($files['messages']['messages'] as $message)
		$messages[] = array(
			'turn' => $message['turn'],
			'phase' => $message['phase'],
			'timeSent' => $message['timeSent'],
			'fromCountryID' => $message['fromCountryID'],
			'message' => $message['message'],
		);

	$voteLog = array();
	foreach($files['messages']['voteLog'] as $vote)
		$voteLog[] = array(
			'turn' => $vote['turn'],
			'phase' => $vote['phase'],
			'timeSent' => $vote['timeSent'],
			'countryID' => $vote['countryID'],
			'vote' => $vote['vote'],
			'on' => $vote['on'],
		);

	return array(
		'gameID' => $game['gameID'],
		'name' => $game['name'],
		'variantID' => $game['variant']['id'],
		'variant' => $game['variant']['name'],
		'gameOver' => $game['gameOver'],
		'turn' => $game['turn'],
		'turnText' => $game['turnText'],
		'startTime' => $game['startTime'],
		'finishTime' => $game['finishTime'],
		'phaseMinutes' => $game['phaseMinutes'],
		'phaseMinutesRB' => $game['phaseMinutesRB'],
		'nextPhaseMinutes' => $game['nextPhaseMinutes'],
		'phaseSwitchPeriod' => $game['phaseSwitchPeriod'],
		'pot' => $game['pot'],
		'potType' => $game['potType'],
		'minimumBet' => $game['minimumBet'],
		'pressType' => $game['pressType'],
		'anon' => $game['anon'],
		'drawType' => $game['drawType'],
		'missingPlayerPolicy' => $game['missingPlayerPolicy'],
		'playerTypes' => $game['playerTypes'],
		'excusedMissedTurns' => $game['excusedMissedTurns'],
		'minimumReliabilityRating' => $game['minimumReliabilityRating'],
		'isPrivate' => $game['isPrivate'],
		'members' => $members,
		'civilDisorders' => $civilDisorders,
		'phases' => $files['history']['phases'],
		'messages' => $messages,
		'voteLog' => $voteLog,
	);
}

$gamesFile = 'games-'.$month.'.jsonl.gz';
$tmp = $dir.'/'.$gamesFile.'.part';
$gz = gzopen($tmp, 'wb6');
if( !$gz )
{
	fwrite(STDERR, "Can't write $tmp\n");
	exit(1);
}

$started = time();
$exported = $skipped = 0;
$variantIDs = array();
$lastID = 0;

fwrite(STDERR, "Exporting the games finished before $month-01 to $dir/$gamesFile\n");

for(;;)
{
	$gameRows = array();
	$tabl = $DB->sql_tabl("SELECT ".libGameFiles::GAME_COLUMNS." FROM wD_Games
		WHERE id > ".$lastID." AND phase = 'Finished' AND sandboxCreatedByUserID IS NULL
			AND ( finishTime IS NULL OR finishTime < ".$monthStart." )
		ORDER BY id LIMIT 200");
	while( $row = $DB->tabl_hash($tabl) )
		$gameRows[] = $row;

	if( !count($gameRows) ) break;

	foreach($gameRows as $gameRow)
	{
		$lastID = intval($gameRow['id']);
		if( !is_null($limit) && $exported >= $limit ) break 2;

		try
		{
			$files = libGameFiles::buildForExport($gameRow, libGameFiles::loadMemberRows($gameRow['id']));
			if( $files === false ) throw new Exception("its history couldn't be built");

			$files['game']['gameID'] = intval($gameRow['id']);
			$line = json_encode(exportRecord($files), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
			if( $line === false ) throw new Exception(json_last_error_msg());

			gzwrite($gz, $line."\n");
			$variantIDs[intval($gameRow['variantID'])] = true;
			$exported++;
		}
		catch(Throwable $e)
		{
			fwrite(STDERR, 'Skipped game '.$gameRow['id'].': '.$e->getMessage()."\n");
			$skipped++;
		}
	}

	// A long-running read would otherwise keep one snapshot of the database open for the whole export
	$DB->sql_put("COMMIT");

	fwrite(STDERR, date('Y-m-d H:i:s').': '.$exported.' games exported, '.$skipped.' skipped, up to game '.$lastID."\n");
}

gzclose($gz);

// The maps the games were played on, for turning territory IDs into names and borders
$variants = array();
ksort($variantIDs);
foreach(array_keys($variantIDs) as $variantID)
{
	$variantURL = libGameFiles::variantURL($variantID);
	if( is_null($variantURL['url']) || !file_exists($variantURL['url']) ) continue;

	$variant = json_decode(file_get_contents($variantURL['url']), true);
	// The map images are the site's, at paths relative to it
	unset($variant['map'], $variant['version']);
	$variants[] = $variant;
}
file_put_contents($dir.'/variants.json', json_encode($variants, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

rename($tmp, $dir.'/'.$gamesFile);

// Written last: the server takes it to mean the export is complete
file_put_contents($dir.'/games-'.$month.'.json', json_encode(array(
	'month' => $month,
	'finishedBefore' => $monthStart,
	'file' => $gamesFile,
	'games' => $exported,
	'skipped' => $skipped,
	'bytes' => filesize($dir.'/'.$gamesFile),
	'started' => $started,
	'generated' => time(),
), JSON_PRETTY_PRINT)."\n");

fwrite(STDERR, "Done: $exported games exported, $skipped skipped, in ".round((time() - $started)/60)." minutes\n");
