<?php

$user1 = $user2 = array();
$gameID=0;

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
	
if ( isset($_REQUEST['User1']) )
	foreach ($_REQUEST['User1'] as $user)
		$user1[]=(int)$user;

if ( isset($_REQUEST['User2']) )
	foreach ($_REQUEST['User2'] as $user)
		$user2[]=(int)$user;

// Output as Txt or Csv:
if ((($gameID != 0) && (count($user1) > 0) && (count($user2) > 0)) && ($asTxt OR $asCsv))
{
	$Variant=libVariant::loadFromGameID($gameID);
	
	$sql = 'SELECT message, toCountryID, fromCountryID, turn, timeSent
				FROM wD_GameMessages WHERE
					gameID = '.$gameID.' AND
					(
						( toCountryID IN ('.implode(', ',$user1).') AND fromCountryID IN ('.implode(', ',$user2).') )
							OR
						( toCountryID IN ('.implode(', ',$user2).') AND fromCountryID IN ('.implode(', ',$user1).') )
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
print '<P><STRONG>Game ID: </STRONG><INPUT type="text" name="gameID" value="'.($gameID!=0?$gameID:'').'" length="10" /><BR>';

if ($gameID != 0)
{
	require_once('gamepanel/gameboard.php');
	
	$Variant=libVariant::loadFromGameID($gameID);
	libVariant::setGlobals($Variant);
	$Game = $Variant->panelGameBoard($gameID);
	
	if ($Game->Members->isJoined())
	{
		if (in_array($Game->Members->ByUserID[$User->id]->countryID,$user1) && !(in_array($Game->Members->ByUserID[$User->id]->countryID,$user2)))
			$user1=array($Game->Members->ByUserID[$User->id]->countryID);
		elseif (in_array($Game->Members->ByUserID[$User->id]->countryID,$user2) && !(in_array($Game->Members->ByUserID[$User->id]->countryID,$user1)))
			$user2=array($Game->Members->ByUserID[$User->id]->countryID);
		elseif (!(in_array($Game->Members->ByUserID[$User->id]->countryID,$user2)) && !(in_array($Game->Members->ByUserID[$User->id]->countryID,$user1)))
			$user1=$user2=array();
		else
			$user1=$user2=array($Game->Members->ByUserID[$User->id]->countryID);
	}
	
	print $Game->titleBar().'<br>';
	
	print '<DIV class="variant'.$Variant->name.'">
		<TABLE>
			<THEAD>
				<TH style="border: 1px solid #000">Country</TH>
				<TH style="border: 1px solid #000">Player</TH>
				<TH style="border: 1px solid #000" align="center">User 1</TH>
				<TH style="border: 1px solid #000" align="center">User 2</TH>
			</THEAD>
			<TR>
				<TD style="border: 1px solid #666">Global</TD>
				<TD style="border: 1px solid #666"></TD>
				<TD style="border: 1px solid #666" align="center"><input type="checkbox" name="User1[]" value="0" '.(in_array('0',$user1)?'checked':'').'></TD>
				<TD style="border: 1px solid #666" align="center"><input type="checkbox" name="User2[]" value="0" '.(in_array('0',$user2)?'checked':'').'></TD>
			</TR>';

	foreach($Game->Members->ByCountryID as $Member)
		print '
			<TR>
				<TD style="border: 1px solid #666"><span class="memberCountryName">'.$Member->memberCountryName().'</TD>
				<TD style="border: 1px solid #666">'.$Member->memberUserDetail().'</TD>
				<TD style="border: 1px solid #666" align="center"><input type="checkbox" name="User1[]" value="'.$Member->countryID.'" '.(in_array($Member->countryID,$user1)?'checked':'').'></TD>
				<TD style="border: 1px solid #666" align="center"><input type="checkbox" name="User2[]" value="'.$Member->countryID.'" '.(in_array($Member->countryID,$user2)?'checked':'').'></TD>
			</TR>';
				
	print '</TABLE></DIV>';
}

print '<br><input type="submit" name="Submit" class="form-submit" value="Check" /></form>';

if (($gameID != 0) && (count($user1) > 0) && (count($user2) > 0))
{
	$sql = 'SELECT message, toCountryID, fromCountryID, turn, timeSent
				FROM wD_GameMessages WHERE
					gameID = '.$gameID.' AND
					(
						( toCountryID IN ('.implode(', ',$user1).') AND fromCountryID IN ('.implode(', ',$user2).') )
							OR
						( toCountryID IN ('.implode(', ',$user2).') AND fromCountryID IN ('.implode(', ',$user1).') )
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
		
		foreach ($user1 as $user)
			print '<input type="hidden" name="User1[]" value="'.$user.'">';
		foreach ($user2 as $user)
			print '<input type="hidden" name="User2[]" value="'.$user.'">';
			
		print  '<input type="hidden" name="gameID" value="'.$gameID.'">
				<input type="submit" name="CSV" value="Download as .csv (Excel)">
				<input type="submit" name="TXT" value="Download as .txt">
				</FORM>';
	}
	else
		print '<br>No sent messages to display.';
}




?>