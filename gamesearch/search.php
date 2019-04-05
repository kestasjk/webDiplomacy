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
 */

require_once(l_r('searchItems.php'));
require_once(l_r('searchItemSettings.php'));
require_once(l_r('searchOptions.php'));

class search
{
	protected $searchItems = array('AmMember','IsPublic','PotType','DrawType','ChooseVariant','PhaseHours','Phase','IsAnonymous','PressType','OrderBy');


	protected $searchTypes = array('My games','New','Joinable','Active','Finished','Search','Profile');

	public function __construct($searchType)
	{
		global $User;

		// If we're a guest (and not browsing a profile) we can't filter games from the guest acconut
		if(!$User->type['User'] && $searchType!='Profile')
			unset($this->searchItems[array_search('AmMember', $this->searchItems)]);

		if ( !in_array($searchType, $this->searchTypes))
			throw new Exception(l_t('Invalid game list type:').' '.$searchType);

		$searchItems=array();
		foreach($this->searchItems as $searchItem)
		{
			$itemName = 'search'.$searchItem;
			$itemObj = new $itemName($searchType);
			$searchItems[$itemObj->name] = $itemObj;
		}
		$this->searchItems = $searchItems;
	}

	public function filterInput($formInput, $items = false)
	{
		if ( $items === false ) $items = $this->searchItems;

		foreach($items as $itemName=>$item)
		{
			if ( !isset($formInput[$itemName]) ) continue;//$formInput[$itemName]=null;

			$subItems = $item->filterInput($formInput[$itemName]);

			if($subItems)
				$this->filterInput($formInput, $subItems);
		}
	}

	public function formHTML()
	{
		print '<form method="post" name="search">';
		foreach($this->searchItems as $item)
		{
			print $item->formHTML();
		}
		print '<br /><input type="submit" class="form-submit" value="'.l_t('Search').'" />';
		print '</form>';
	}

	protected $SELECT="g.*";

	protected function sql($items = false,&$TABLES=false,&$WHERE=false,&$ORDER=false)
	{
		if ( $items === false )
		{
			$items = $this->searchItems;

			$TABLES="wD_Games g";
			$WHERE=array('1=1');
			$ORDER="";
		}

		foreach($items as $item)
		{
			$subItems = $item->sql($TABLES,$WHERE,$ORDER);

			if($subItems)
				$this->sql($subItems,$TABLES,$WHERE,$ORDER);
		}

		return 'SELECT '.$this->SELECT.' FROM '.$TABLES.' WHERE '.implode(' AND ',$WHERE).' '.$ORDER;
	}

	private function devQueryData($SQL)
	{
		global $DB;
		print '<p class="notice">'.$SQL.'</p>';

		$tabl = $DB->sql_tabl("EXPLAIN ".$SQL);
		print '<table>';
		while($hash = $DB->tabl_hash($tabl))
		{
			foreach($hash as $name=>$val)
				print '<tr><td style="width:25%; text-align:right"><strong>'.$name.':</strong></td>
					<td style="width:75%">'.$val.'</td></tr>';
		}
		print '</table>';
	}

	public function printGamesList($Pager=null)
	{
		global $DB;

		$SQL = $this->sql();
		//$this->devQueryData($SQL);

		if($Pager instanceof Pager)
			$SQL .= $Pager->SQLLimit();

		$tabl = $DB->sql_tabl($SQL);

		$count=0;
		print '<div class="gamesList">';
		while( $row = $DB->tabl_hash($tabl) )
		{
			$count++;
			$Variant = libVariant::loadFromVariantID($row['variantID']);
			$G = $Variant->panelGame($row);
			print $G->summary(false);
		}
		print '</div>';

		return $count;
	}
}

?>
