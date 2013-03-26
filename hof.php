<?php
/*
    Copyright (C) 2013 Oliver Auth

	This file is part of vDiplomacy.

    vDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    vDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with vDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once('header.php');
require_once("lib/rating.php");

libHTML::starthtml();

print '<script type="text/javascript" src="contrib/tablekit/tablekit.js"></script>';
print libHTML::pageTitle('Hall of fame','The webDiplomacy hall of fame; the 100 highest ranking players on this server.');

print '<p align="center"><img src="images/points/vstack.png" alt=" "
			title="webDiplomacy ranking points; who are the most skilled at gathering them from their foes?" /></p>';

print '<style type="text/css">
		.sortcol {cursor: pointer; padding-right: 20px; background-repeat: no-repeat; background-position: right center; }
		.sortasc {background-color: #DDFFAC; background-image: url(contrib/tablekit/up.gif); }
		.sortdesc {background-color: #B9DDFF; background-image: url(contrib/tablekit/down.gif); }
		.nosort { cursor: default;} 
	</style>';

print '<style type="text/css">
		.cellg { border:1px solid #777; }
		.cellb { border:1px solid #000; }
		.points { width:16% ! important; border-right: solid #aaa 1px; }
	</style>';
	
if(isset($_REQUEST['userID']))
{
	$userID = (int)$_REQUEST['userID'];
	$UserProfile = new User($userID);
	print '<b>Stats for </b>'.$UserProfile->profile_link().':<br><br>';
		
	print '<TABLE class="sortable">
				<THEAD>
					<TH class="cellb">Game</TH>
					<TH class="cellb">Name</TH>
					<TH class="cellb">Variant</TH>
					<TH class="cellb">Status</TH>
					<TH class="cellb">Change</TH>
					<TH class="cellb">Total</TH>
				</THEAD>';
			
	$USER_TABL = $DB->sql_tabl("
		SELECT r.rating, r.gameID, g.name, g.variantID, m.status FROM wD_Ratings r
			LEFT JOIN wD_Games g ON (g.id = r.gameID)
			LEFT JOIN wD_Members m ON (m.userID = r.userID && g.id=m.gameID)
		WHERE r.ratingType='vDip'
			&& r.userID=".$userID."
			&& g.phase = 'Finished'
		ORDER BY g.processTime ASC");
	
	$rating_old = 1000;
	while ( list($rating, $gameID, $gameName, $variantID, $status) = $DB->tabl_row($USER_TABL) )
	{
		print '
			<TR>
				<TD class="cellg"><a href="hof.php?gameID='.$gameID.'">'.$gameID.'</a></TD>
				<TD class="cellg"><a href="board.php?gameID='.$gameID.'">'.$gameName.'</TD>
				<TD class="cellg"><a href="variants.php?variantID='.$variantID.'">'.Config::$variants[$variantID].'</TD>
				<TD class="cellg">'.$status.'</TD>
				<TD class="cellg">'.($rating - $rating_old).'</TD>
				<TD class="cellg">'.$rating.'</TD>
			</TR>';
		$rating_old = $rating;
	}

	print '</TABLE>';

} 
elseif(isset($_REQUEST['gameID']))
{
	include_once ('gamepanel/game.php');
	$gameID = (int)$_REQUEST['gameID'];
	$Variant=libVariant::loadFromGameID($gameID);
	$Game = $Variant->panelGame($gameID);
	
	$Members = libRating::loadVDipMembers($Game);
	$Members = libRating::calcVDipRating($Game, $Members);
	
	print $Game->titleBar();
	
	print '<br><TABLE class="sortable">
				<THEAD>
					<TH class="cellb">Player</TH>
					<TH class="cellb">Rating</TH>
					<TH class="cellb">Status</TH>
					<TH class="cellb" align="right">Re &Oslash</TH>
					<TH class="cellb" align="right">Rr &Oslash</TH>
					<TH class="cellb" align="right">Ch</TH>						
				</THEAD>';
				
	foreach ($Members as $userID => $Member)
	{
		$Re = $Rr = $gV = $mV = $Di = $Ch = 0 ;
		foreach ($Member['matches'] as $results)
		{
			$Re += $results['Re']; 
			$Rr += $results['Rr'];
			$Ch += $results['Ch'];		
		}
		$Members[$userID]['Re'] = round ($Re / (count($Members) -1),2) * 100;
		$Members[$userID]['Rr'] = round ($Rr / (count($Members) -1),2) * 100;
		$Members[$userID]['Ch'] = round ($Ch);
	} 
	 
	foreach ($Members as $userID => $Member)
	{
		if     ( $Member['Ch'] < 0) $col = '990002'; 
		elseif ( $Member['Ch'] > 0) $col = '009902';
		else                        $col = '000000';
		
		print '
			<TR>
				<TD class="cellg">'.$Member['name'].'</TD>
				<TD class="cellg">'.$Member['rating'].' -> '.($Member['rating'] + $Member['Ch']).'</TD>
				<TD class="cellg">'.$Member['status'].(($Member['SCr'] > 0 && $Member['status'] != 'Resigned') ? ' ('.$Member['SCr'].' SC)' : '').'</TD>
				<TD class="cellg" align="right">'.$Member['Re'].'%</TD>
				<TD class="cellg" align="right">'.$Member['Rr'].'%</TD>
				<TD class="cellg" align="right"><font color="#'.$col.'"><B>'.$Member['Ch'].'</B></font></TD>
			</TR>';
	}
	
	print '</TABLE><BR>';
	
	foreach ($Members as $Member)
	{
		print "<b>".$Member['name']." (".$Member['status']." / ".$Member['SCr']."SCs / ".$Member['rating']."->".round($Member['rating'] + $Member['change'])."):</b>";
		print '<TABLE class="sortable">
					<THEAD>
						<TH class="cellb">Vs</TH>
						<TH class="cellb" align="right">Re</TH>
						<TH class="cellb" align="right">Rr</TH>
						<TH class="cellb" align="right">Dif</TH>
						<TH class="cellb" align="right">mV</TH>
						<TH class="cellb" align="right">gV</TH>
						<TH class="cellb" align="right">Ch</TH>						
					</THEAD>';
		 
		foreach ($Member['matches'] as $userID => $results)
		{
			if     ( $results['Ch'] < 0) $col = '990002'; 
			elseif ( $results['Ch'] > 0) $col = '009902';
			else                        $col = '000000';
			
			print '
				<TR>
					<TD class="cellg">'.$Members[$userID]['name'].' ('.
						$Members[$userID]['status'].
						(($Members[$userID]['SCr'] > 0 && $Members[$userID]['status'] != 'Resigned') ? " / ".$Members[$userID]['SCr']. " SC " : "").
						') vs</TD>
					<TD class="cellg" align="right">'.(round($results['Re'],2)*100).'%</TD>
					<TD class="cellg" align="right">'.(round($results['Rr'],2)*100).'%</TD>
					<TD class="cellg" align="right"><font color="#'.$col.'">'.(round($results['Rr'] - $results['Re'],2)*100).'%</font></TD>
					<TD class="cellg" align="right">'.(round($results['mV'],2)*100).'%</TD>
					<TD class="cellg" align="right">'.round($results['gV'],2).'</TD>
					<TD class="cellg" align="right"><font color="#'.$col.'">'.round($results['Ch'],2).'</font></TD>
				</TR>';
		}
		if     ( round($Member['change']) < 0) $col = '990002'; 
		elseif ( round($Member['change']) > 0) $col = '009902';
		else                                   $col = '000000';
		print '	<TFOOT>
					<TR>
						<TD colspan=6></TD>
						<TD class="cellg" align="right"><font color="#'.$col.'"><b>'.round($Member['change']).'</b></font></TD>
					</TR>
				</TFOOT></TABLE><BR>';
	}

}
else
{

	print '<table class="credits">';

	$alternate = false;

	$i=1;
	$VPOINTS_TABL = $DB->sql_tabl("
		SELECT r.userID, u.username, r.rating FROM wD_Ratings r
			LEFT JOIN wD_Users u ON (u.id = r.userID)
			LEFT JOIN wD_Games g ON (g.id = r.gameID)
			JOIN (SELECT MAX(g2.processTime) AS last, r2.userID AS uid FROM wD_Ratings r2
				LEFT JOIN wD_Games g2 ON (g2.id = r2.gameID ) GROUP BY r2.userID) AS tab2 ON 
				(uid = r.userID && last = g.processTime)			
		WHERE r.ratingType='vDip'
			&& u.type <> 'Banned'
		ORDER BY r.rating DESC LIMIT 100");
		
	while ( list($id, $username, $vPoints) = $DB->tabl_row($VPOINTS_TABL) )
	{
		$users[$id]['id']   = $id;
		$users[$id]['name'] = $username;
		$users[$id]['Vr']   = $vPoints;
		$users[$id]['Vr#']  = $i;
		
		$alternate = !$alternate;
		print '
		<tr class="replyalternate'.($alternate ? '1' : '2' ).'">
			<td class="left points">
				'.$vPoints.' '.libHTML::vpoints().' - #'.$i.'
			</td>

			<td class="right message"><a href="hof.php?userID='.$id.'">'.$username.'</a></td>
		</tr>';
		$i++;
	}

	print '</table>';

}
		
print '</div>';
libHTML::footer();

?>
