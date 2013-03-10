<?php
/*
    Copyright (C) 20013 Oliver Auth

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
print libHTML::pageTitle('Hall of fame','The webDiplomacy hall of fame; the 150 highest ranking players on this server.');


print '<p align="center"><img src="images/points/stack.png" alt=" "
			title="webDiplomacy ranking points; who are the most skilled at gathering them from their foes?" /></p>';

print '<style type="text/css">
		.sortcol {cursor: pointer; padding-right: 20px; background-repeat: no-repeat; background-position: right center; }
		.sortasc {background-color: #DDFFAC; background-image: url(contrib/tablekit/up.gif); }
		.sortdesc {background-color: #B9DDFF; background-image: url(contrib/tablekit/down.gif); }
		.nosort { cursor: default;} 
	</style>';

if(isset($_REQUEST['userID']))
{
	$userID = (int)$_REQUEST['userID'];
	$UserProfile = new User($userID);
	print '<b>Stats for </b>'.$UserProfile->profile_link().':<br><br>';
		
	print '<TABLE class="sortable">
				<THEAD>
					<TH style="border: 1px solid #000">Game</TH>
					<TH style="border: 1px solid #000">Name</TH>
					<TH style="border: 1px solid #000">Variant</TH>
					<TH style="border: 1px solid #000">Status</TH>
					<TH style="border: 1px solid #000">Change</TH>
					<TH style="border: 1px solid #000">Total</TH>
				</THEAD>
			<TR>
				<TD style="border: 1px solid #666"></TD>
				<TD style="border: 1px solid #666"></TD>
				<TD style="border: 1px solid #666"></TD>
				<TD style="border: 1px solid #666"></TD>
				<TD style="border: 1px solid #666"></TD>
				<TD style="border: 1px solid #666">1000</TD>
			</TR>';
			
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
				<TD style="border: 1px solid #666"><a href="hof.php?gameID='.$gameID.'">'.$gameID.'</a></TD>
				<TD style="border: 1px solid #666"><a href="board.php?gameID='.$gameID.'">'.$gameName.'</TD>
				<TD style="border: 1px solid #666"><a href="variants.php?variantID='.$variantID.'">'.Config::$variants[$variantID].'</TD>
				<TD style="border: 1px solid #666">'.$status.'</TD>
				<TD style="border: 1px solid #666">'.($rating - $rating_old).'</TD>
				<TD style="border: 1px solid #666">'.$rating.'</TD>
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
					<TH style="border: 1px solid #000">Player</TH>
					<TH style="border: 1px solid #000">Rating</TH>
					<TH style="border: 1px solid #000">Status</TH>
					<TH align="right" style="border: 1px solid #000">Re &Oslash</TH>
					<TH align="right" style="border: 1px solid #000">Rr &Oslash</TH>
					<TH align="right" style="border: 1px solid #000">V &Oslash</TH>
					<TH align="right" style="border: 1px solid #000">Ch</TH>						
				</THEAD>';
				
	foreach ($Members as $userID => $Member)
	{
		$Re = $Rr = $gV = $mV = $Di = $Ch = 0 ;
		foreach ($Member['matches'] as $results)
		{
			$Re += $results['Re']; 
			$Rr += $results['Rr'];
			$gV += $results['mV'] * $results['gV'];			
			$Ch += $results['Ch'];		
		}
		$Members[$userID]['Re'] = round ($Re / (count($Members) -1),2) * 100;
		$Members[$userID]['Rr'] = round ($Rr / (count($Members) -1),2) * 100;
		$Members[$userID]['gV'] = round ($gV / (count($Members) -1),2) * 100;
		$Members[$userID]['Ch'] = round ($Ch);
	} 
	 
	foreach ($Members as $userID => $Member)
	{
		if     ( $Member['Ch'] < 0) $col = '990002'; 
		elseif ( $Member['Ch'] > 0) $col = '009902';
		else                        $col = '000000';
		
		print '
			<TR>
				<TD style="border: 1px solid #000">'.$Member['name'].'</TD>
				<TD style="border: 1px solid #000">'.$Member['rating'].' -> '.($Member['rating'] + $Member['Ch']).'</TD>
				<TD style="border: 1px solid #000">'.$Member['status'].(($Member['SCc'] > 0 && $Member['status'] != 'Resigned') ? ' ('.$Member['SCc'].' SC)' : '').'</TD>
				<TD align="right" style="border: 1px solid #000">'.$Member['Re'].'%</TD>
				<TD align="right" style="border: 1px solid #000">'.$Member['Rr'].'%</TD>
				<TD align="right" style="border: 1px solid #000">'.$Member['gV'].'</TD>
				<TD align="right" style="border: 1px solid #000"><font color="#'.$col.'"><B>'.$Member['Ch'].'</B></font></TD>
			</TR>';
	}
	
	print '</TABLE><BR>';
	
	foreach ($Members as $Member)
	{
		print "<b>".$Member['name']." (".$Member['status']." / ".$Member['SCc']."SCs / ".$Member['rating']."->".round($Member['rating'] + $Member['change'])."):</b>";
		print '<TABLE class="sortable">
					<THEAD>
						<TH style="border: 1px solid #000">Vs</TH>
						<TH align="right" style="border: 1px solid #000">Re</TH>
						<TH align="right"style="border: 1px solid #000">Rr</TH>
						<TH align="right" style="border: 1px solid #000">Dif</TH>
						<TH align="right" style="border: 1px solid #000">mV</TH>
						<TH align="right" style="border: 1px solid #000">gV</TH>
						<TH align="right" style="border: 1px solid #000">Ch</TH>						
					</THEAD>';
		 
		foreach ($Member['matches'] as $userID => $results)
		{
			if     ( $results['Ch'] < 0) $col = '990002'; 
			elseif ( $results['Ch'] > 0) $col = '009902';
			else                        $col = '000000';
			
			print '
				<TR>
					<TD style="border: 1px solid #000">'.$Members[$userID]['name'].' ('.
						$Members[$userID]['status'].
						(($Members[$userID]['SCc'] > 0 && $Members[$userID]['status'] != 'Resigned') ? " / ".$Members[$userID]['SCc']. " SC " : "").
						')</TD>
					<TD align="right" style="border: 1px solid #000">'.(round($results['Re'],2)*100).'%</TD>
					<TD align="right" style="border: 1px solid #000">'.(round($results['Rr'],2)*100).'%</TD>
					<TD  align="right"style="border: 1px solid #000"><font color="#'.$col.'">'.(round($results['Rr'] - $results['Re'],2)*100).'%</font></TD>
					<TD align="right" style="border: 1px solid #000">'.(round($results['mV'],2)*100).'%</TD>
					<TD align="right" style="border: 1px solid #000">'.round($results['gV'],2).'</TD>
					<TD align="right" style="border: 1px solid #000"><font color="#'.$col.'">'.round($results['Ch'],2).'</font></TD>
				</TR>';
		}
		if     ( round($Member['change']) < 0) $col = '990002'; 
		elseif ( round($Member['change']) > 0) $col = '009902';
		else                                   $col = '000000';
		print '	<TFOOT>
					<TR>
						<TD colspan=6></TD>
						<TD align="right" style="border: 1px solid #000"><font color="#'.$col.'"><b>'.round($Member['change']).'</b></font></TD>
					</TR>
				</TFOOT></TABLE><BR>';
	}

}
else
{
	$users = array();
	
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
		ORDER BY r.rating DESC LIMIT 150");
		
	while ( list($id, $username, $vR) = $DB->tabl_row($VPOINTS_TABL) )
	{
		$users[$id]['id']   = $id;
		$users[$id]['name'] = $username;
		$users[$id]['Vr']   = $vR;
		$users[$id]['Vr#']  = $i++;
	}

	$i=1;
	$DPOINTS_TABL = $DB->sql_tabl("SELECT id, username, points FROM wD_Users
							order BY points DESC LIMIT 150 ");					
	while ( list($id, $username, $dpoints) = $DB->tabl_row($DPOINTS_TABL) )
	{
		$users[$id]['id'] = $id;
		$users[$id]['name'] = $username;
		$users[$id]['DPoints'] = $dpoints;
		$users[$id]['DPoints#'] = $i++;
	}

	print '<TABLE class="sortable">
				<THEAD>
					<TH style="border: 1px solid #000">Name</TH>
					<TH style="border: 1px solid #000">DPoints</TH>
					<TH style="border: 1px solid #000" class="sortfirstdesc">Won/Drawn/Lost</TH>
				</THEAD>';
				
	foreach ($users as $user)
		print '
			<TR>
				<TD style="border: 1px solid #666"><a href="hof.php?userID='.$user['id'].'">'.$user['name'].'</a></TD>
				<TD style="border: 1px solid #666">'.(isset($user['DPoints'])? $user['DPoints'].' '.libHTML::points().' (<b>#'.$user['DPoints#'].'</b>)' : '-').'</TD>
				<TD style="border: 1px solid #666">'.(isset($user['Vr'])? $user['Vr'].' (<b>#'.$user['Vr#'].'</b>)' : '-').'</TD>
			</TR>';

	print '</TABLE>';
}
		

print '</div>';
libHTML::footer();

?>
