<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

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

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * Output the chat logs
 *
 * @package Board
 */

$pagenum = 1;
$resultsPerPage = 20;
$maxPage = 0;
$totalResults = 0;
$msgFilter = -1;

if ( isset($_REQUEST['pagenum'])) { $pagenum=(int)$_REQUEST['pagenum']; }
if ( isset($_REQUEST['msgFilter'])) { $msgFilter=(int)$_REQUEST['msgFilter']; }

$SQLCounter = "SELECT COUNT(*) FROM wD_GameMessages WHERE gameID = ".$Game->id." AND ";

if( !isset($Member) ) { $msgFilter = 0; }

if ($msgFilter == -1)
{
	$SQLCounter .= "(toCountryID = 0".(isset($Member)?" OR fromCountryID = ".$Member->countryID." OR toCountryID = ".$Member->countryID:'').")";
}
elseif ($msgFilter == 0)
{
	$SQLCounter .= "toCountryID = 0";
}
else
{
	$SQLCounter .= "(( toCountryID = ".$Member->countryID." AND fromCountryID = ".$msgFilter." ) OR ( fromCountryID = ".$Member->countryID." AND toCountryID = ".$msgFilter." ))";
}


list($totalResults) = $DB->sql_row($SQLCounter);

$maxPage = ceil($totalResults / $resultsPerPage);
$remainder = ($maxPage * $resultsPerPage) - $totalResults;

if ($pagenum == $maxPage)
{
	$SQLLimit = ($resultsPerPage * ($maxPage - $pagenum)) . "," . ($resultsPerPage - $remainder) .";";
}
else
{
	$SQLLimit = ($resultsPerPage * ($maxPage - $pagenum) - $remainder) . "," . $resultsPerPage .";";
}

print "<a name='results'></a>";
print '<h4>'.l_t('Chat archive').'</h4>';

print '<div class="variant'.$Game->Variant->name.'">';

if ($totalResults == 0)
{
	if ($msgFilter == -1)
	{
		print '<br/><strong>There are no messages for this game</strong>';
	}
	else
	{
		print '<br/><strong>There are no results for this country.</strong>';
		printPageBar($pagenum, $maxPage, $msgFilter, $sortBar = True);
		print '<br/> <br/>';
	}
}
else
{
	printPageBar($pagenum, $maxPage, $msgFilter, $sortBar = True);
	print '<br/> <br/>';

	$CB = $Game->Variant->Chatbox();
	print '<table class="archive-messages-table">'.$CB->getMessages( $msgFilter, $SQLLimit).'</table>';

	print '</br>';
	printPageBar($pagenum, $maxPage, $msgFilter);
}

print '</div>';

function printPageBar($pagenum, $maxPage, $msgFilter, $sortBar = False)
{
	global $Game, $Member;
	if ($pagenum > 3)
	{
		printPageButton(1,False);
	}
	if ($pagenum > 4)
	{
		print "...";
	}
	if ($pagenum > 2)
	{
		printPageButton($pagenum-2, False);
	}
	if ($pagenum > 1)
	{
		printPageButton($pagenum-1, False);
	}
	if ($maxPage > 1)
	{
		printPageButton($pagenum, True);
	}
	if ($pagenum < $maxPage)
	{
		printPageButton($pagenum+1, False);
	}
	if ($pagenum < $maxPage-1)
	{
		printPageButton($pagenum+2, False);
	}
	if ($pagenum < $maxPage-3)
	{
		print "...";
	}
	if ($pagenum < $maxPage-2)
	{
		printPageButton($maxPage, False);
	}
	if ($sortBar)
	{
		print '<span style="float:right;">
			<FORM class="advancedSearch" method="get" action="board.php#results">
			<b>Country:</b>
			<select  class = "advancedSearch" name="msgFilter">
				<option'.(($msgFilter==-1) ? ' selected="selected"' : '').' value=-1>All</option>';
				for( $countryID=0; $countryID<=count($Game->Variant->countries); $countryID++)
				{
					if( isset($Member) )
					{
						if ( $countryID == $Member->countryID )
						{
							print '<option'.(($msgFilter == $countryID) ? ' selected="selected"' : '').' value='.$countryID.'>Notes</option>';
						}
						elseif(isset($Game->Members->ByCountryID[$countryID]))
						{
							print '<option'.(($msgFilter == $countryID) ? ' selected="selected"' : '').' value='.$countryID.'>'.$Game->Members->ByCountryID[$countryID]->memberCountryName().'</option>';
						}
						else
						{
							print '<option'.(($msgFilter==0) ? ' selected="selected"' : '').' value=0>Global</option>';
						}
					}
				}
			print '</select>';
			foreach(libHTML::sanitizeREQUESTForHiddenFormVariables($_REQUEST) as $key => $value)
			{
				if(strpos('x'.$key,'wD') == false && strpos('x'.$key,'phpbb3') == false && strpos('x'.$key,'__utm')== false && $key!="pagenum" && $key!="msgFilter")
				{
					print '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				}
			}
			print ' ';
			print '<input type="submit" class="form-submit" name="Submit" value="Refresh" /></form>
			</span>';
		}
}

function printPageButton($pagenum, $currPage)
{
	if ($currPage)
	{
		print '<div class="curr-page">'.$pagenum.'</div>';
	}
	else
	{
		print '<div style="display:inline-block; margin:3px;">';
		print '<FORM method="get" action=board.php#results>';
		foreach(libHTML::sanitizeREQUESTForHiddenFormVariables($_REQUEST) as $key => $value)
		{
			if(strpos('x'.$key,'wD') == false && strpos('x'.$key,'phpbb3')== false && strpos('x'.$key,'__utm')== false && $key!="pagenum")
			{
				print '<input type="hidden" name="'.$key.'" value="'.$value.'">';
			}
		}
		print '<input type="submit" name="pagenum" class="form-submit" value='.$pagenum.' /></form></div>';
	}
}

?>
