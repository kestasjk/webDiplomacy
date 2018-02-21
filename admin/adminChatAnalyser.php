<?php

$countryID1 = $countryID2 = array();
$gameID=$userID=0;
$reason = 'Press was checked for gameID: ';

if (!defined('IN_CODE'))
{
	chdir ('..');
	require_once('header.php');
}

if (!$User->type['Moderator'])
	die ('Only admins or mods can run this script');

$asTxt = (isset($_REQUEST['TXT'])?true:false);
$asCsv = (isset($_REQUEST['CSV'])?true:false);

if ( isset($_REQUEST['gameID']) )
	$gameID=(int)$_REQUEST['gameID'];
	
if ( isset($_REQUEST['userID']) && $gameID == 0 )
	$userID=(int)$_REQUEST['userID'];
	
if ( isset($_REQUEST['countryID1']) )
	foreach ($_REQUEST['countryID1'] as $user)
		$countryID1[]=(int)$user;

if ( isset($_REQUEST['countryID2']) )
	foreach ($_REQUEST['countryID2'] as $user)
		$countryID2[]=(int)$user;

if ( isset($_REQUEST['reason']) && $gameID == 0 )
	$reason = htmlentities( $_REQUEST['reason'], ENT_NOQUOTES, 'UTF-8');

// Output as Txt or Csv:
if ((($gameID != 0) && (count($countryID1) > 0) && (count($countryID2) > 0)) && ($asTxt OR $asCsv))
{
	$Variant=libVariant::loadFromGameID($gameID);
	
	$sql = 'SELECT message, toCountryID, fromCountryID, turn, timeSent
				FROM wD_GameMessages WHERE
					gameID = '.$gameID.' AND
					(
						( toCountryID IN ('.implode(', ',$countryID1).') AND fromCountryID IN ('.implode(', ',$countryID2).') )
							OR
						( toCountryID IN ('.implode(', ',$countryID2).') AND fromCountryID IN ('.implode(', ',$countryID1).') )
					)
				ORDER BY id ASC';
	
	$tabl = $DB->sql_tabl($sql);
	
	
	if ($asCsv)
	{
		header("Content-type: csv/plain; charset=utf-8");
		header("Content-disposition: inline; filename=Chatlog.csv");
		print '"timeSent";"gameDate";"To";"From";"Message"'."\n";
	}
	else
	{
		header("Content-type: txt/plain; charset=utf-8");
		header("Content-disposition: inline; filename=Chatlog.txt");
	}
		
	while ( list($message, $to, $from, $turn, $timeSent) = $DB->tabl_row($tabl) )
	{
		if ($asCsv)
			print '"'.gmstrftime("%d %b %y %I:%M %p", $timeSent).'";'.
					'"'.$Variant->turnAsDate($turn).'";'.
					'"'.($to==0?'Global':$Variant->countries[$to-1]).'";'.
					'"'.($from==0?'Global':$Variant->countries[$from-1]).'";'.
					'"'.addslashes($message).'"'."\n";		
		else
			print gmstrftime("%d %b %y %I:%M %p", $timeSent).
					' ('.$Variant->turnAsDate($turn).')'.
					' / To '.($to==0?'Global':$Variant->countries[$to-1]).
					' from '.($from==0?'Global':$Variant->countries[$from-1]).': '.
					$message."\r\n";		
	}
	exit(0);
}

/**
 * Print a form for selecting which game to check, and which users to check against
 */
print '<FORM method="get" action="admincp.php">';
print '<P><STRONG>Game ID: </STRONG><INPUT type="text" name="gameID" value="'.($gameID!=0?$gameID:'').'" length="10" /> <br> ';

if ($gameID != 0)
{
	require_once('gamepanel/gameboard.php');
	
	$Variant=libVariant::loadFromGameID($gameID);
	libVariant::setGlobals($Variant);
	$Game = $Variant->panelGameBoard($gameID);
	
	$reason = $reason . $gameID;
	$DB->sql_put("INSERT INTO wD_AdminLog ( name, userID, time, details, params )
					VALUES ( 'CheckGamePress', ".$User->id.", ".time().", '".$reason."', '' )");
	
	if ($Game->Members->isJoined())
	{
		if (in_array($Game->Members->ByUserID[$User->id]->countryID,$countryID1) && !(in_array($Game->Members->ByUserID[$User->id]->countryID,$countryID2)))
			$countryID1=array($Game->Members->ByUserID[$User->id]->countryID);
		elseif (in_array($Game->Members->ByUserID[$User->id]->countryID,$countryID2) && !(in_array($Game->Members->ByUserID[$User->id]->countryID,$countryID1)))
			$countryID2=array($Game->Members->ByUserID[$User->id]->countryID);
		elseif (!(in_array($Game->Members->ByUserID[$User->id]->countryID,$countryID2)) && !(in_array($Game->Members->ByUserID[$User->id]->countryID,$countryID1)))
			$countryID1=$countryID2=array();
		else
			$countryID1=$countryID2=array($Game->Members->ByUserID[$User->id]->countryID);
	}
	
	print $Game->titleBar().'<br>';
	
	print '<DIV class="variant'.$Variant->name.'">
		<TABLE>
			<THEAD>
				<TH style="border: 1px solid #000">Country</TH>
				<TH style="border: 1px solid #000">Player</TH>
				<TH style="border: 1px solid #000" align="center">Country 1</TH>
				<TH style="border: 1px solid #000" align="center">Country 2</TH>
			</THEAD>
			<TR>
				<TD style="border: 1px solid #666">Global</TD>
				<TD style="border: 1px solid #666"></TD>
				<TD style="border: 1px solid #666" align="center"><input type="checkbox" name="countryID1[]" value="0" '.(in_array('0',$countryID1)?'checked':'').'></TD>
				<TD style="border: 1px solid #666" align="center"><input type="checkbox" name="countryID2[]" value="0" '.(in_array('0',$countryID2)?'checked':'').'></TD>
			</TR>';

	foreach($Game->Members->ByCountryID as $Member)
		print '
			<TR>
				<TD style="border: 1px solid #666"><span class="memberCountryName">'.$Member->memberCountryName().'</TD>
				<TD style="border: 1px solid #666">'.$Member->memberUserDetail().'</TD>
				<TD style="border: 1px solid #666" align="center"><input type="checkbox" name="countryID1[]" value="'.$Member->countryID.'" '.(in_array($Member->countryID,$countryID1)?'checked':'').'></TD>
				<TD style="border: 1px solid #666" align="center"><input type="checkbox" name="countryID2[]" value="'.$Member->countryID.'" '.(in_array($Member->countryID,$countryID2)?'checked':'').'></TD>
			</TR>';
				
	print '</TABLE></DIV>';
}

print '<br><input type="submit" name="Submit" class="form-submit" value="Check" /></form>';

if (($gameID != 0) && (count($countryID1) > 0) && (count($countryID2) > 0))
{
	$sql = 'SELECT message, toCountryID, fromCountryID, turn, timeSent
				FROM wD_GameMessages WHERE
					gameID = '.$gameID.' AND
					(
						( toCountryID IN ('.implode(', ',$countryID1).') AND fromCountryID IN ('.implode(', ',$countryID2).') )
							OR
						( toCountryID IN ('.implode(', ',$countryID2).') AND fromCountryID IN ('.implode(', ',$countryID1).') )
					)
				ORDER BY id ASC';
	
	$tabl = $DB->sql_tabl($sql);
	
	print '<BR><DIV class="variant'.$Variant->name.'">';
	print '<TABLE class="chatbox">';
	
	$alternate = $output = false;
	
	while ( list($message, $to, $from, $turn, $timeSent) = $DB->tabl_row($tabl) )
	{
		$alternate = ! $alternate;
		$output=true;
		
		print '<TR class="replyalternate'.($alternate ? '1' : '2' ).' gameID'.$gameID.'countryID'.$from.'">
					<TD class="left time">'.libTime::text($timeSent).'</TD>
					<TD class="right '.( $from != 0 ? 'country'.$from:'').'">
						To: <strong>'.($to==0?'Global':$Game->Variant->countries[$to-1]).'</strong> from <strong>'.($from==0?'Global':$Game->Variant->countries[$from-1]).'</strong>
						('.$Game->datetxt($turn).'): 
						<BR>'.$message.'
					</TD>
				</TR>';	
	}
	
	print '</TABLE></DIV>';
	
	if ($output)
	{
		print '<BR><FORM method="get" action="admin/adminChatAnalyser.php">';
		
		foreach ($countryID1 as $user)
			print '<input type="hidden" name="countryID1[]" value="'.$user.'">';
		foreach ($countryID2 as $user)
			print '<input type="hidden" name="countryID2[]" value="'.$user.'">';
			
		print  '<input type="hidden" name="gameID" value="'.$gameID.'">
				<input type="submit" name="CSV" value="Download as .csv (Excel)">
				<input type="submit" name="TXT" value="Download as .txt">
				</FORM>';
	}
	else
		print '<br>No sent messages to display.';
}



?>