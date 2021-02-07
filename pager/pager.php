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
 * The abstract pager object; the child objects of this allow screens with lots of
 * elements to be divided into pages and navigated, with a common paging back/forward
 * toolbar displayed.
 *
 * @package Base
 * @subpackage Pager
 */

abstract class Pager
{
	protected $URL;
	public $pageCount;
	public $currentPage;

	protected $extraArgs;
	function addArgs($args)
	{
		if(isset($this->extraArgs))
			$this->extraArgs .= '&amp;'.$args;
		else
			$this->extraArgs = $args;
	}

	function getCurrentPage($currentPage=1)
	{
		$defaultPage=$currentPage;

		if ( isset($_REQUEST['page-'.$this->type]) )
		{
			$currentPage = ($_SESSION['page-'.$this->type.'-'.$this->URL] = (int)$_REQUEST['page-'.$this->type]);
		}
		elseif ( isset($_SESSION['page-'.$this->type.'-'.$this->URL]) )
			$currentPage = $_SESSION['page-'.$this->type.'-'.$this->URL];

		if ( $currentPage < 1 )
			$currentPage = $defaultPage;

		if ( $currentPage == $defaultPage && isset($_SESSION['page-'.$this->type.'-'.$this->URL]) )
			unset($_SESSION['page-'.$this->type.'-'.$this->URL]);

		$this->currentPage = $currentPage;
	}

	function SQLLimit()
	{
		return ' LIMIT '.($this->currentPage-1)*$this->itemsPerPage.', '.$this->itemsPerPage;
	}

	protected $itemsPerPage;
	function __construct($URL, $itemsTotal=null, $itemsPerPage=20)
	{
		$this->URL=$URL;
		$this->itemsPerPage = $itemsPerPage;
		if( isset($itemsTotal) )
			$this->pageCount = ceil($itemsTotal / $itemsPerPage);
		$this->getCurrentPage();
	}

	function pagerBar($anchor, $leftHandSide='&nbsp;')
	{
		print '<div>'.$this->html($anchor).'
				<div>
					<a name="'.$anchor.'"></a>
					'.$leftHandSide.'
				</div>
				<div style="clear:both;"> </div>
			</div>';
	}

	function html($anchor='')
	{
		// If only one page, then do not display the page or buttons.
		if(isset($this->pageCount) && $this->pageCount<=1) return '';

		// Check for any extra arguments to include in the links.
		if($this->extraArgs)
			$args = $this->extraArgs.'&amp;';
		else
			$args = '';

		// Determine what to do with start/prev page buttons based on if we are on the first page.
		$prevDisabled = '';
		$prevPage = $this->currentPage - 1;
		if ($this->currentPage == 1)
		{
			$prevDisabled = '_disabled';
			$prevPage = 1;
		}

		// Determine what to do with next/last page buttons based if there is not a last page.
		if (! isset($this->pageCount))
		{
			$lastPage = '';
			$extra = ' (disabled when last page number unknown)';
			$nextDisabled = '';
			$lastDisabled = '_disabled';
			$nextPage = $this->currentPage + 1;
		}
		else
		{
			$lastPage = $this->pageCount;
			$extra = '';
			$nextDisabled = '';
			$lastDisabled = '';
			$nextPage = $this->currentPage + 1;

			// Determine what to do with next/last page buttons based on if we are on the last page.
			if ($this->pageCount == $this->currentPage)
			{
				$nextDisabled = '_disabled';
				$lastDisabled = '_disabled';
				$nextPage = $this->currentPage;
			}
		}
 
		// Okay, let's put it all together now.
		$buf = '<div style="float:right; text-align:right; padding:5px">';

		$buf .= '<div style="float:left">';
		$buf .= $this->currentPageNumber();
		$buf .= '</div>';

		$buf .= '<div style="float:right; padding-left:10px">';
		$buf .= $this->button($args, $anchor, '1',       'Start'.   $prevDisabled.'.svg', 'First');
		$buf .= $this->button($args, $anchor, $prevPage, 'Backward'.$prevDisabled.'.svg', 'Previous');
		$buf .= $this->button($args, $anchor, $nextPage, 'Forward'. $nextDisabled.'.svg', 'Next');
		$buf .= $this->button($args, $anchor, $lastPage, 'End'.     $lastDisabled.'.svg', 'Last', $extra);
		$buf .= '</div>';

		$buf .= '</div>';

		return $buf;
	}

	function button($args, $anchor, $number, $icon, $which, $extra='')
	{
		$image = '<img src="'.l_s('images/historyicons/'.$icon).'" alt="'.l_t($which).'" title="'.l_t($which.' page'.$extra).'" />';
		if ($number == '')
		{
			return $image;
		}
		return '<a href="'.$this->URL.'?'.$args.'page-'.$this->type.'='.$number.'#'.$anchor.'">'.$image.'</a>';
	}

	function currentPageNumber()
	{
		return '<div style="padding-right:10px; border-right: solid 1px #aaa;">'.
					'<em>'.
						(isset($this->pageCount) ?
						 l_t('Page <strong>%s</strong> of <strong>%s</strong>',$this->currentPage,$this->pageCount) :
						 l_t('Page <strong>%s</strong>',$this->currentPage)).
					'</em>'.
				'</div>';
	}
}

?>