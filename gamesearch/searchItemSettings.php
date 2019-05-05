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
 * @package Search
 * @subpackage ItemSettings
 */

class searchAmMember extends searchItemRadio
{
	public $name='amMember';
	protected $label='Game membership filters';
	protected $options=array('-'=>'All','Yes'=>'Joined games','No'=>'Non-joined games');
	protected $subItems=array('MemberStatus','ActivityTypes','IsJoinable');

	protected $locks=array('My games','Profile');

	protected $defaults=array(
			'Notifications'=>'Yes',
			'Profile'=>'Yes',
			'My games'=>'Yes',
			'New'=>'No',
			'Joinable'=>'No',
			'Active'=>'No'
		);

	private $User;

	function __construct($searchType)
	{
		global $User, $UserProfile;

		parent::__construct($searchType);

		if ( $searchType == 'Profile' )
			$this->User = $UserProfile;
		else
			$this->User = $User;
	}

	function filterInput($input)
	{
		parent::filterInput($input);

		return $this->activeSubItems();
	}

	function formHTML()
	{
		print '<li>
			<strong>'.l_t($this->label).'</strong>:
			<ul>
				<li>'.$this->options['-']->formHTML().'</li>
				<li>'.$this->options['Yes']->formHTML().'
					<ul>'.$this->subItems['memberStatus']->formHTML().
						$this->subItems['activityTypes']->formHTML().
					'</ul>
				</li>
				<li>'.$this->options['No']->formHTML().'
					<ul>'.$this->subItems['isJoinable']->formHTML().
					'</ul>
				</li>
			</ul>
			</li>';
	}
	/**/

	private function activeSubItems()
	{
		switch($this->value)
		{
			case 'Yes':
				return array('memberStatus'=>$this->subItems['memberStatus'],'activityTypes'=>$this->subItems['activityTypes']);
			case 'No':
				return array('isJoinable'=>$this->subItems['isJoinable']);
		}
	}

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		$expr = 'm.userID IS NULL';

		switch($this->value)
		{
			case 'Yes':
				$WHERE[] = 'NOT '.$expr;
				break;

			case 'No':
				$WHERE[] = $expr;
				break;
		}

		if( $this->value == 'Yes'||$this->value == 'No')
			$TABLES.=" LEFT JOIN wD_Members m ON (
				m.gameID = g.id AND m.userID = ".$this->User->id." )";

		return $this->activeSubItems();
	}
}
class searchMemberStatus extends searchItemCheckbox
{
	public $name='memberStatus';
	protected $label='Status';
	protected $options=array('Playing'=>'Playing','Left'=>'Left','Defeated'=>'Defeated','Survived'=>'Survived','Drawn'=>'Drawn','Won'=>'Won');

	protected $locks=array('New');

	protected $defaults=array(
			'My games'=>array('Playing','Left'),
			'Notifications'=>array('Playing','Left')
		);

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		$unchecked = $this->invertedChecks();
		foreach($unchecked as $uncheck)
			$WHERE[] = "NOT m.status='".$uncheck."'";
	}
}
class searchActivityTypes extends searchItemRadio
{
	public $name='activityTypes';
	protected $label='Notifications';
	protected $options=array('-'=>'All','Notification'=>'Notification games','Inactive'=>'Inactive games');

	protected $locks=array('Profile');

	protected $defaults=array(
			'Notifications'=>'Notification'
		);

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		$expr = "( ( m.orderStatus LIKE '%Ready%' OR m.orderStatus LIKE '%None%' ) AND ( (m.newMessagesFrom+0) = 0 ) )";
		switch($this->value)
		{
			case 'Notification':
				$WHERE[] = 'NOT '.$expr;
				return;
			case 'Inactive':
				$WHERE[] = $expr;
				return;
		}
	}
}
class searchIsJoinable extends searchItemRadio
{
	public $name='isJoinable';
	protected $label='Joinable';
	protected $options=array('-'=>'All','Yes'=>'Joinable','No'=>'Not joinable');

	protected $locks=array();

	protected $defaults=array(
			'Joinable'=>'Yes'
		);

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		global $User;

		if ( !$User->type['User'] ) return;

		$expr = $User->points." >= g.minimumBet";
		switch($this->value)
		{
			case 'Yes':
				$WHERE[] = $expr;
				return;
			case 'No':
				$WHERE[] = 'NOT '.$expr;
				return;
		}
	}
}

class searchIsPublic extends searchItemRadio
{
	public $name='isPublic';
	protected $label='Access type';
	protected $options=array('-'=>'All','Yes'=>'Public','No'=>'Private');
	
	protected $defaults=array('Joinable'=>'Yes');

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		$expr = "password IS NULL";

		if($this->value == 'No')
			$WHERE[] = "NOT ".$expr;
		elseif($this->value == 'Yes')
			$WHERE[] = $expr;
	}
}
class searchDrawType extends searchItemRadio
{
	public $name='drawType';
	protected $label='Draw votes';
	protected $options=array('-'=>'All','draw-votes-public'=>'Public draw votes','draw-votes-hidden'=>'Hidden draw votes');

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		if($this->value != '-')
		{
			$WHERE[] = "drawType = '".$this->value."'";
		}
	}
}

class searchPotType extends searchItemRadio
{
	public $name='potType';
	protected $label='Points distribution type';
	protected $options=array('-'=>'All','Winner-takes-all'=>'Draw-Size Scoring', 'Sum-of-squares'=>'Sum of Squares','Unranked'=>'Unranked');

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		if($this->value != '-')
		{
			$WHERE[] = "potType = '".$this->value."'";
		}
	}
}
class searchChooseVariant extends searchItemRadio
{
	public $name='chooseVariant';
	protected $label='Variant';
	protected $options=array('-'=>'All');

	function __construct($searchType) {

		foreach(Config::$variants as $variantID=>$variantName)
		{
			//$Variant = libVariant::loadFromVariantName($variantName);
			$this->options[$variantID]=l_t($variantName);//$Variant->fullName;
		}

		parent::__construct($searchType);
	}

	function filterInput($input)
	{
		if( $input!='-' ) $input=(int)$input;

		return parent::filterInput($input);
	}
	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		if($this->value != '-')
		{
			$WHERE[] = "variantID = ".$this->value;
		}
	}
}
class searchPhaseHours extends searchItemCheckbox
{
	public $name='phaseHours';
	protected $label='Phase length';
	protected $options=array('0-1'=>'&lt;1 hours','1-6'=>'1-6 hours','6-12'=>'6-12 hours','12-24'=>'12-24 hours','24-48'=>'1-2 days','48-120'=>'2-5 days','120-241'=>'5-10 days');

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		$excludedValues = $this->invertedChecks();

		$excludedHours = array();
		foreach($excludedValues as $timeSlot)
		{
			list($startHour,$endHour) = explode('-',$timeSlot);
			$excludedHours[$startHour] = $endHour;
		}

		$simplifiedExcludedHours = array();
		$maxHour = -1;
		foreach($excludedHours as $startHour => $endHour)
		{
			if ( $maxHour > $startHour ) continue;

			$maxHour = $endHour;

			while( isset($excludedHours[$maxHour]) )
				$maxHour = $excludedHours[$maxHour];

			$simplifiedExcludedHours[$startHour] = $maxHour;
		}

		$excludedHours = $simplifiedExcludedHours;

		foreach($excludedHours as $startHour => $endHour)
		{
			$startHour *= 60;
			$endHour *= 60;
			$WHERE[] = 'NOT ('.$startHour.' <= phaseMinutes AND phaseMinutes < '.$endHour.')';
		}
	}
}
class searchPhase extends searchItemRadio
{
	public $name='phase';
	protected $label='Game status';
	protected $options=array('-'=>'All','Active'=>'Active','Finished'=>'Finished');
	protected $subItems=array('ActivePhases','ProcessStatus','GameOver');

	protected $locks=array('New','Joinable','Active','Finished');

	protected $defaults=array('My games'=>'Active','New'=>'Active','Joinable'=>'Active','Active'=>'Active','Finished'=>'Finished');

	function filterInput($input)
	{
		parent::filterInput($input);

		return $this->activeSubItems();
	}

	function formHTML()
	{
		print '<li>
			<strong>'.l_t($this->label).'</strong>:
			<ul>
				<li>'.$this->options['-']->formHTML().'</li>
				<li>'.$this->options['Active']->formHTML().'
					<ul>'.$this->subItems['activePhases']->formHTML().
						$this->subItems['processStatus']->formHTML().
					'</ul>
				</li>
				<li>'.$this->options['Finished']->formHTML().'
					<ul>'.$this->subItems['gameOver']->formHTML().
					'</ul>
				</li>
			</ul>
			</li>';
	}

	private function activeSubItems()
	{
		switch($this->value)
		{
			case 'Active':
				return array('activePhases'=>$this->subItems['activePhases'],'processStatus'=>$this->subItems['processStatus']);

			case 'Finished':
				return array('gameOver'=>$this->subItems['gameOver']);
		}
	}

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		$expr="phase = 'Finished'";

		switch($this->value)
		{
			case 'Active':
				$WHERE[] = 'NOT '.$expr;
				break;

			case 'Finished':
				$WHERE[] = $expr;
				break;
		}

		return $this->activeSubItems();
	}
}
class searchActivePhases extends searchItemRadio
{
	public $name='activePhases';
	protected $label='Phase';
	protected $options=array('-'=>'All','Pre-game'=>'Pre-game','Diplomacy,Retreats,Builds'=>'Diplomacy,Retreats,Builds');

	protected $locks=array('New','Joinable','Active','Finished');

	protected $defaults=array(
			'My games'=>'Diplomacy,Retreats,Builds',
			'New'=>'Pre-game',
			'Joinable'=>'Diplomacy,Retreats,Builds',
			'Active'=>'Diplomacy,Retreats,Builds'
		);

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		$expr="phase = 'Pre-game'";

		switch($this->value)
		{
			case 'Pre-game':
				$WHERE[] = $expr;
				return;

			case 'Diplomacy,Retreats,Builds':
				$WHERE[] = 'NOT '.$expr;
				return;
		}
	}
}
class searchProcessStatus extends searchItemRadio
{
	public $name='processStatus';
	protected $label='Status';
	protected $options=array('-'=>'All','Running'=>'Running','Stopped'=>'Stopped');

	protected $locks=array('New','Finished');

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		$expr="processStatus = 'Not-processing'";

		switch($this->value)
		{
			case 'Stopped':
				$WHERE[] = 'NOT '.$expr;
				return;

			case 'Running':
				$WHERE[] = $expr;
				return;
		}
	}
}
class searchGameOver extends searchItemRadio
{
	public $name='gameOver';
	protected $label='Finish type';
	protected $options=array('-'=>'All','Won'=>'Won','Drawn'=>'Drawn');

	protected $locks=array('New','Joinable','Active');

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		if($this->value != '-')
			$WHERE[] = "gameOver = '".$this->value."'";
	}
}
class searchIsAnonymous extends searchItemRadio
{
	public $name='isAnonymous';
	protected $label='Anonymous games';
	protected $options=array('-'=>'All','Yes'=>'Anonymous games only','No'=>'No anonymous games');

	protected $defaults=array('Profile'=>'No');
	protected $locks=array('Profile');

	function __construct($searchType) 
	{
		global $User;

		if ( ($searchType == 'Profile') && ($User->type['Moderator']) )
		{
			$this->locks = array();
			$this->defaults = array();
		}

		parent::__construct($searchType);
	}

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		if($this->value == 'No')
			$WHERE[] = "(anon = 'No' OR g.phase='Finished')";
		elseif($this->value == 'Yes')
			$WHERE[] = "anon = 'Yes'";
	}
}
class searchPressType extends searchItemCheckbox
{
	public $name='pressType';
	protected $label='Messaging rules';
	protected $options=array('Regular'=>'Normal', 'PublicPressOnly'=>'Public messages only', 'NoPress'=>'No messages','RulebookPress'=>'Rulebook press');

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		$unchecked = $this->invertedChecks();
		foreach($unchecked as $uncheck)
			$WHERE[] = "NOT pressType='".$uncheck."'";
	}
}
class searchOrderBy extends searchItemSelect
{
	public $name='orderBy';
	protected $label='Order by';
	protected $options=array(
			'-'=>'None',
			'processTime-ASC'=>'Time until next process (Closest-&gt;furthest)',
			'phaseMinutes-DESC'=>'Time per phase (Longest-&gt;shortest)',
			'phaseMinutes-ASC'=>'Time per phase (Shortest-&gt;longest)',
			'pot-DESC'=>'Pot size (Largest-&gt;smallest)',
			'pot-ASC'=>'Pot size (Smallest-&gt;largest)',
			'minimumBet-DESC'=>'Bet size (Largest-&gt;smallest)',
			'minimumBet-ASC'=>'Bet size (Smallest-&gt;largest)',
			'name-ASC'=>'Alphabetical order (A-&gt;Z)',
			'name-DESC'=>'Alphabetical order (Z-&gt;A)',
			'turn-ASC'=>'Turn (in-game date) (Youngest-&gt;oldest)',
			'turn-DESC'=>'Turn (in-game date) (Oldest-&gt;youngest)',
			'id-DESC'=>'Game age (Youngest-&gt;oldest)',
			'id-ASC'=>'Game age (Oldest-&gt;youngest)'
		);

	protected $defaults=array(
			'My games'=>'processTime-ASC',
			'New'=>'processTime-ASC',
			'Joinable'=>'turn-ASC',
			'Active'=>'pot-DESC',
			'Finished'=>'id-DESC',
			'Profile'=>'id-DESC'
		);

	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		if($this->value != '-')
		{
			list($field, $order) = explode('-',$this->value);
			$ORDER = "ORDER BY ".$field." ".$order;
		}
	}
}
class searchExcusedNMRs extends searchItemCheckbox
{
	public $name='excusedNRMs';
	protected $label='Excused missing turns';
	protected $options=array('0'=>'no excuses','1'=>'1 excuse','2'=>'2 excuses','3'=>'3 excuses','4'=>'4 excuses');
 	function sql(&$TABLES,&$WHERE,&$ORDER)
	{
		$unchecked = $this->invertedChecks();
		foreach($unchecked as $uncheck)
			$WHERE[] = "NOT g.excusedMissedTurns='".$uncheck."'";
	}
}

?>
