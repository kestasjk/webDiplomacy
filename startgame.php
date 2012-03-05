<?php

require_once('header.php');

libHTML::starthtml();
print '<div class="content">';

if ( !($User->type['Admin'] )) {
	print "Admins only";
	print '</div>';
	libHTML::footer();
}	
		
if ($_SERVER['REQUEST_METHOD'] != "POST") {
	print 'Fill the remaining places with dummy players to start the game: ';
			
	print '<form style="display: inline" method="POST" name="start_game">';
	print '<select name=gameID>';
	$tabl = $DB->sql_tabl('SELECT id, variantID FROM wD_Games WHERE phase="Pre-Game"');
	while(list($id, $variantID)=$DB->tabl_row($tabl))
	{
		$Variant=libVariant::loadFromVariantID($variantID);
		$Game = $Variant->Game($id);
		print '<option value="'.$Game->id.'"> ID:'.$Game->id.' - '.$Game->name.' ('.$Variant->name.')</option>';
	}
	print '</select>';
	print '<input type="submit" class="form-submit" value="Fill game" />';
	print '</form>';
} else {

	$gameID=(int)$_REQUEST['gameID'];
	require_once('gamemaster/game.php');

	$Variant=libVariant::loadFromGameID($gameID);
	libVariant::setGlobals($Variant);
	$Game = $Variant->processGame($gameID);
	
	global $DB;
	$dummy_add=0;
	$Game->phaseMinutes = 60;

	while (!($Game->needsProcess()))
	{
		$dummy_add++;
		list($id)=$DB->sql_row("SELECT id FROM wD_Users WHERE username = 'dummy_".$dummy_add."'");
		if (!($id))
		{
			$DB->sql_put("INSERT INTO wD_Users (username, email, points) VALUES ('dummy_".$dummy_add."', 'dummy_".$dummy_add."', 20000)");
			list($id)=$DB->sql_row("SELECT id FROM wD_Users WHERE username = 'dummy_".$dummy_add."'");
		}
		processMember::create($id, $Game->minimumBet);
	}
	print "Filled the game with ".($dummy_add)." users. ";
	print "Click <u><a href='board.php?gameID=".$Game->id."'>here</a></u> to open the game.";

}

print '</div>';
libHTML::footer();

?>
