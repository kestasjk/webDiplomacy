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

require_once(l_r('gamepanel/member.php'));
/**
 * This class displays the members subsection of a game panel.
 *
 * @package GamePanel
 */
class panelMembers extends Members
{
	/**
	 * Load a panelMember instead of a Member
	 */
	protected function loadMember(array $row)
	{
		return $this->Game->Variant->panelMember($row);
	}

	/*
	private function sortBy($sortList, $by)
	{
		$byIndex=array();
		$byList=array();
		foreach($sortList as $obj)
		{
			$objVal = (int)$obj->{$by};

			if(!isset($byIndex[$objVal]))
				$byIndex[$objVal] = array();

			$byList[$objVal]=$objVal;
			$byIndex[$objVal][$obj->id] = $obj;
		}
		sort($byList);
		$byList=array_reverse($byList);
		$sorted=array();
		foreach($byList as $objVal)
			foreach($byIndex[$objVal] as $obj)
				$sorted[] = $obj;

		return $sorted;
	}
	*/

	/**
	 * The order in which to display the various statuses of members. Which come last etc.
	 * @var array
	 */
	private static $statusOrder=array('Won','Survived','Drawn','Playing','Left','Resigned','Defeated');

	/**
	 * The list of members; just names if pregame, otherwise a full detailed table, ordered by
	 * status then relative success in-game.
	 *
	 * @return string
	 */
	function membersList()
	{
		if( $this->Game->phase == 'Pre-game')
		{
			$membersNames = array();
			foreach($this->ByUserID as $Member)
				$membersNames[] = '<span class="memberName">'.$Member->memberName().'</span>';

			return '<table><tr class="member memberAlternate1 memberPreGameList"><td>'.
				implode(', ',$membersNames).'</td></tr></table>';
		}

		libHTML::$alternate=2;
		$membersList = array();
		foreach(self::$statusOrder as $status)
		{
			foreach($this->ByStatus[$status] as $Member)
			{
				$membersList[] = '<tr class="member memberAlternate'.libHTML::alternate().'">'.
					$Member->memberBar().'</tr>';
			}
		}

		$extras ='';
		// Show members CD users after a game is finished. 	
		if (($this->Game->hasModeratorPowers() || $this->Game->phase == 'Finished') && count($this->Game->civilDisorderInfo) != 0) 
		{ 
				$extras = '<div class="bar titleBar modEyes">Civil Disorders</div><table><tbody>';
				foreach ($this->Game->civilDisorderInfo as $userID => $CD) 
				{
					$cdUser = new User($userID);
					$extras .= '<tr class="member memberAlternate1"><td class="memberLeftSide" style="white-space: nowrap;"><span><a href="profile.php?userID='.$userID .'">'.$cdUser->username.'</a>'.
							' <span class="points">('.$cdUser->points.libHTML::points().User::typeIcon($cdUser->type).')'
					.(defined('AdminUserSwitch') ? ' (<a href="board.php?gameID='.$this->Game->id.'&auid='.$cdUser->id.'" class="light">+</a>)':'') .
							'</span></span></td><td class="memberRightSide">';
					$extras .= '<span class="country' .$CD['countryID']. '">';
					if( $CD['countryID']==0 )
							$extras .= 'Unassigned';
					else
							$extras .= $this->Game->Variant->countries[$CD['countryID']-1];
					$extras .= '</span> (' .$this->Game->datetxt($CD['turn']). ') with ' .$CD['SCCount']. ' centres.';

					$extras .= '</td></tr>';
				}
				$extras .= "</tbody></table>";
		}

		return '<table>'.implode('',$membersList).'</table>'.$extras;
	}

	/**
	 * A form showing a selection of civil-disorder countries which can be taken over from
	 * @return string
	 */
	public function selectCivilDisorder()
	{
		global $User;

		$buf = "";
		if( 1==count($this->ByStatus['Left']) )
		{
			foreach($this->ByStatus['Left'] as $Member);

			$buf .= '<input type="hidden" name="countryID" value="'.$Member->countryID.'" />
				'.l_t('<label>Take over:</label> %s, for %s.',$Member->countryColored(),'<em>'.$Member->pointsValueInTakeover().libHTML::points().'</em>');
		}
		else
		{
			$buf .= '<label>'.l_t('Take over:').'</label> <select name="countryID">';
			foreach($this->ByStatus['Left'] as $Member)
			{
				$pointsValue = $Member->pointsValueInTakeover();

				if ( $User->points >= $pointsValue )
				{
					$buf .= '<option value="'.$Member->countryID.'" />
						'.l_t('%s, for %s',$Member->country,$pointsValue).'
						</option>';
				}
			}
			$buf .= '</select>';
		}
		return $buf;
	}

	/**
	 * The occupation bar HTML; only generate it once then store it here, as it is usually used at least twice for one game
	 * @var unknown_type
	 */
	private $occupationBarCache;

	/**
	 * The occupation bar; a bar representing each of the countries current progress as measured by the number of SCs.
	 * If called pre-game it goes from red to green as 1 to 7 players join the game.
	 *
	 * @return string
	 */
	function occupationBar()
	{
		if ( isset($this->occupationBarCache)) return $this->occupationBarCache;

		libHTML::$first=true;
		if( $this->Game->phase != 'Pre-game' )
		{
			$SCPercents = $this->SCPercents();
			$buf = '';
			foreach($SCPercents as $countryID=>$width)
				if ( $width > 0 )
					$buf .= '<td class="occupationBar'.$countryID.' '.libHTML::first().'" style="width:'.$width.'%"></td>';
		}
		else
		{
			$joinedPercent = ceil((count($this->ByID)*100.0/count($this->Game->Variant->countries)));
			$buf = '<td class="occupationBarJoined '.libHTML::first().'" style="width:'.$joinedPercent.'%"></td>';
			if ( $joinedPercent < 99.0 )
				$buf .= '<td class="occupationBarNotJoined" style="width:'.(100-$joinedPercent).'%"></td>';
		}

		$this->occupationBarCache = '<table class="occupationBarTable"><tr>
					'.$buf.'
				</tr></table>';

		return $this->occupationBarCache;
	}
}
?>
