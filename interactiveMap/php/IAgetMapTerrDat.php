<?php
header("Content-Type: application/json");

if (!defined('IN_CODE'))
    define('IN_CODE', 1); // A flag to tell scripts they aren't being executed by themselves

chdir("../../");
require_once('header.php');
require_once('objects/database.php');
require_once('config.php');
require_once('lib/variant.php');

//$DB = new Database();

if (isset($_REQUEST['gameID'])) {
    $Variant = libVariant::loadFromGameID($_REQUEST['gameID']);
} elseif (isset($_REQUEST['variantID'])) {
    $Variant = libVariant::loadFromVariantID($_REQUEST['variantID']);
} else {
    die("No gameID/variantID");
}

createIAmapData();

echo file_get_contents('variants/' . $Variant->name . '/resources/IA_mapData.map');
    

//----------------------------------------
function createIAmapData() {
    global $Variant;

    if (!file_exists('variants/' . $Variant->name . '/resources/IA_mapData.map')) {

        $colors = array();

        $map = imagecreatefrompng('variants/' . $Variant->name . '/resources/IA_smallMap.png');

        $territoryPositions = getTerrPos();

        foreach ($territoryPositions as $terrID => $terrPos) {
            $colors[imagecolorat($map, $terrPos[0], $terrPos[1])]["ID"] = $terrID;
            $colors[imagecolorat($map, $terrPos[0], $terrPos[1])]["Positions"][] = $terrPos;
            imagefill($map, $terrPos[0], $terrPos[1], 0);
        }

        for ($y = 0; $y < imagesy($map); $y++) {
            for ($x = 0; $x < imagesx($map); $x++) {
                $color = imagecolorat($map, $x, $y);
                if ($color != 0) {
                    if (isset($colors[$color]["ID"])) {
                        $colors[$color]["Positions"][] = array($x, $y);
                        imagefill($map, $x, $y, 0);
                    }
                }
            }
        }
        imagedestroy($map);
        //var_dump($colors);

        $terrColorPos = array();

        foreach ($colors as $content) {
            $terrColorPos[$content["ID"]] = $content["Positions"];
        }
        //var_dump($terrColorPos);

        file_put_contents('variants/' . $Variant->name . '/resources/IA_mapData.map', json_encode($terrColorPos));
    }
}

function getTerrPos() {
    global $DB, $Variant;


    $territoryPositionsSQL = "SELECT id, coast, ";
//if ( $this->smallmap )
    $territoryPositionsSQL .= 'smallMapX, smallMapY';
    /* else
      $territoryPositionsSQL .= 'mapX, mapY';// */
    $territoryPositionsSQL .= " FROM wD_Territories WHERE mapID=" . $Variant->mapID;

    $territoryPositions = array();
    $tabl = $DB->sql_tabl($territoryPositionsSQL);
    while (list($terrID, $coast, $x, $y) = $DB->tabl_row($tabl)) {
        if ($coast != 'Child') {
            $territoryPositions[$terrID] = array(intval($x), intval($y));
        }
    }

    return $territoryPositions;
}

?>