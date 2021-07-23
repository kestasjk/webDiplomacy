<?php

$mapID = 1;

print "Database\n";
$db=mysqli_connect('localhost', 'root', '');
mysqli_select_db($db,'webdiplomacy_batchfive');


print "Reading positions\n";
$sql = "SELECT id, name, smallMapX, smallMapY, mapX, mapY FROM wD_Territories WHERE mapID=".$mapID." AND NOT ( coast = 'Child' ) ORDER BY id";
$tabl = mysqli_query($db, $sql);
while($row = mysqli_fetch_assoc($tabl)) $territories[] = $row;
mysqli_free_result($tabl);
mysqli_close($db);


print "Loading images\n";
$smallIm = imagecreatefrompng('img/smallmap.png');
$largeIm = imagecreatefrompng('img/map.png');


print "Reindexing colors\n";
foreach($territories as $rec)
{
	$territoryColor = imagecolorat($smallIm, $rec['smallMapX'], $rec['smallMapY']);
	imagecolorset($smallIm, $territoryColor, $rec['id'], $rec['id'], $rec['id']);
	$territoryColor = imagecolorat($largeIm, $rec['mapX'], $rec['mapY']);
	imagecolorset($largeIm, $territoryColor, $rec['id'], $rec['id'], $rec['id']);
}

print "Saving reindexed\n";
imagepng($smallIm, 'reindexed_smallmap.png');
imagepng($largeIm, 'reindexed_map.png');

print "Done.\n";