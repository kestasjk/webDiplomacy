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

require_once('objects/basic/set.php');

/**
 * An object representing a relationship between users. Many to many between users and groups,
 * intended for associating people who know each other in real life, to allow people who know
 * each other to play together in a transparent way. Also can be used to allow users to associate
 * with one another, moderators to group people 
 * 
 *
 * @package Base
 */
class GroupUserToUserLink
{
	public $fromUserID;
	public $toUserID;

	public $fromCountryID;
	public $toCountryID;

	public $peerAvgScore = 0; 
	public $peerCount = 0;
	public $modAvgScore = 0; 
	public $modCount = 0;
	public $selfAvgScore = 0; 
	public $selfCount = 0;

	public $fromStyle = "";
	public $toStyle = "";

	public $fromHTML = null;
	public $toHTML = null;

	public function __construct($row)
	{
		foreach($row as $key=>$val)
			$this->{$key} = $val;
	}

	private static function scoreToColor($avgScore, $count)
	{
		// Score -> hue : high = red, zero = white, low = green
		// Full green: hsla(115,100%,28%,70%)
		// Full gray: hsla(115,0%,28%,70%)
		// Full red: hsla(0,100%,28%,70%)

		// 0 rating: hsla(115,100%,28%,0%)
		// 1 rating: hsla(115,100%,28%,15%)
		// 10+ ratings: hsla(115,100%,28%,75%)

		$hue = $avgScore >= 0.0 ? 115 : 0;
		$sat = round(min(max(abs($avgScore) * 100.0,0),100));
		$opacity = round(max(min($count * 15,75),0));

		return 'hsla('.$hue.','.$sat.'%,28%,'.$opacity.'%)';
		// Count -> opacity
	}
	private static function scoreToText($avgScore, $count)
	{
		return round($avgScore*100).'%/'.round($count);
	}

	public function outputRow()
	{
		$buf = '<tr>';
		
		$buf .= '<td style="'.$this->fromStyle.'">';
		$buf .= $this->fromHTML == null ? $this->fromUserID : $this->fromHTML;
		$buf .= '</td>';

		$buf .= '<td>';
		$buf .= $this->toHTML == null ? $this->toUserID : $this->toHTML;
		$buf .= '</td>';

		$buf .= '<td style="background-color:'.self::scoreToColor($this->selfAvgScore, $this->selfCount).'">';
		$buf .= self::scoreToText($this->selfAvgScore, $this->selfCount);
		$buf .= '</td>';
		$buf .= '<td style="background-color:'.self::scoreToColor($this->peerAvgScore, $this->peerCount).'">';
		$buf .= self::scoreToText($this->peerAvgScore, $this->peerCount);
		$buf .= '</td>';
		$buf .= '<td style="background-color:'.self::scoreToColor($this->modAvgScore, $this->modCount).'">';
		$buf .= self::scoreToText($this->modAvgScore, $this->modCount);
		$buf .= '</td>';
		$buf .= '</tr>';

		return $buf;
	}

	public function applyGame($Game)
	{
		foreach($Game->Members->ByUserID as $Member)
		{
			if($this->fromUserID == $Member->userID)
			{
				$this->fromHTML = $Member->userID;//$Game->Variant->countries[$Member->countryID-1];
			}
			else if($this->toUserID == $Member->userID)
			{
				$this->toHTML = $Member->userID;//$Game->Variant->countries[$Member->countryID-1];
			}
		}
	}
	public function applyGroup($Group)
	{
		foreach($Group->GroupUsers as $groupUser)
		{
			if( $this->fromUserID == $groupUser->userID )
				$this->fromHTML = $groupUser->userLink();
			else if ( $this->toUserID == $groupUser->userID )
				$this->toHTML = $groupUser->userLink();
		}
	}
	public function applyUsers($UsersByID)
	{
		if( isset($UsersByID[$this->fromUserID]) )
			$this->fromHTML = $UsersByID[$this->fromUserID]->profile_link();

		if( isset($UsersByID[$this->toUserID]) )
			$this->toHTML = $UsersByID[$this->toUserID]->profile_link();
	}
}
