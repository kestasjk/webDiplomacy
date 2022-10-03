<?php

define('IN_CODE', 1);

/**
 * @package Base
 */

require_once('config.php');
if( !isset(Config::$fingerPrintWebHookUsername) || Config::$fingerPrintWebHookUsername == null ||
    !isset(Config::$fingerPrintWebHookPassword) || Config::$fingerPrintWebHookPassword == null )
{
    die('Fingerprinting web hook not configured');
}

require_once('header.php');
require_once('global/definitions.php');
require_once('objects/database.php');

if (!isset($_SERVER['PHP_AUTH_USER'])) 
{
    header('WWW-Authenticate: Basic realm="webDiplomacy"');
    header('HTTP/1.0 401 Unauthorized');
    die('Not authorized');
}

if( $_SERVER['PHP_AUTH_USER'] !== Config::$fingerPrintWebHookUsername || $_SERVER['PHP_AUTH_PW'] !== Config::$fingerPrintWebHookPassword ) 
{
    header('WWW-Authenticate: Basic realm="webDiplomacy"');
    header('HTTP/1.0 401 Unauthorized');
    die('Bad credentials');
}

$json_data = json_decode(file_get_contents("php://input"), true);

file_put_contents('fp.out', print_r($json_data,true));

$requestId = isset($json_data['requestId']) ? $json_data['requestId'] : '';
$visitorId = isset($json_data['visitorId']) ? $json_data['visitorId'] : '';
$visitorFound = isset($json_data['visitorFound']) ? $json_data['visitorFound'] : '';
$incognito = isset($json_data['incognito']) ? $json_data['incognito'] : '';
if( isset($json_data['ipLocation']) )
{
	$accuracyRadius = isset($json_data['ipLocation']['accuracyRadius']) ? $json_data['ipLocation']['accuracyRadius'] : 0;
	$latitude = isset($json_data['ipLocation']['latitude']) ? $json_data['ipLocation']['latitude'] : 0;
	$longitude = isset($json_data['ipLocation']['longitude']) ? $json_data['ipLocation']['longitude'] : 0;
}
$linkedId = isset($json_data['linkedId']) ? $json_data['linkedId'] : -1;
if( isset($json_data['confidence']) )
{
	$confidence = isset($json_data['confidence']['score']) ? $json_data['confidence']['score'] : -1;
}

$DB->sql_put("INSERT INTO wD_FingerprintProRequests (requestId, visitorId, linkedId, confidence, visitorFound, incognito, latitude, longitude, accuracyRadius) VALUES ".
	"('" . $requestId . "', '" . $visitorId . "', '" . $linkedId . "', '" . $confidence . "', '" . $visitorFound . "', '" . $incognito . "', '" . $latitude . "', '" . $longitude . "', '" . $accuracyRadius . "')");

$DB->sql_put("COMMIT");

