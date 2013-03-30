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
		if(isset($this->pageCount) && $this->pageCount<=1) return '';

		if($this->extraArgs)
			$args = $this->extraArgs.'&amp;';
		else
			$args = '';

		$buf = '
		<div style="float:right; text-align:right">'.
			// The start, to the left, is page 1
			'<a href="'.$this->URL.'?'.$args.'page-'.$this->type.'=1#'.$anchor.'">'.
				'<img src="'.l_s('images/historyicons/Start'.( $this->currentPage == 1 ?'_disabled':'').'.png').'"
					alt="'.l_t('First').'" title="'.l_t('First page').'" />'.
			'</a> '.
			// If we're at the start the next page along is the same page, otherwise the previousone
			'<a href="'.$this->URL.'?'.$args.'page-'.$this->type.'='.($this->currentPage>1?$this->currentPage-1:1).'#'.$anchor.'">'
				.'<img src="'.l_s('images/historyicons/Backward'.( $this->currentPage == 1 ?'_disabled':'').'.png').'"
					alt="'.l_t('Previous').'" title="'.l_t('Previous page').'" />'.
			'</a>
			';

		// If we don't know the page-count the next pageis always active, and there is no last page
		if ( !isset($this->pageCount) )
			$buf .= '<a href="'.$this->URL.'?'.$args.'page-'.$this->type.'='.($this->currentPage+1).'#'.$anchor.'">'.
						'<img src="'.l_s('images/historyicons/Forward.png').'"
							alt="'.l_t('Next').'" title="'.l_t('Next page').'" />'.
					'</a>
					<img src="'.l_s('images/historyicons/End_disabled.png').'"
					alt="'.l_t('Last').'" title="'.l_t('Last page (disabled when last page number unknown)').'" />';
		else
			$buf .= '<a href="'.$this->URL.'?'.$args.'page-'.$this->type.'='.( !$this->currentPage == $this->pageCount? $this->currentPage:($this->currentPage+1)).'#'.$anchor.'">'.
				'<img src="'.l_s('images/historyicons/Forward'.( $this->currentPage == $this->pageCount ?'_disabled':'').'.png').'"
					alt="'.l_t('Next').'" title="'.l_t('Next page').'" />'.
			'</a>
			<a href="'.$this->URL.'?'.$args.'page-'.$this->type.'='.$this->pageCount.'#'.$anchor.'">'.
				'<img src="'.l_s('images/historyicons/End'.( $this->currentPage == $this->pageCount ?'_disabled':'').'.png').'"
					alt="'.l_t('Last').'" title="'.l_t('Last page').'" />'.
			'</a>';


		$buf .= $this->currentPageNumber();

		$buf .= '</div>';

		return $buf;
	}

	function currentPageNumber()
	{
		return '<div style="padding:3px; padding-bottom:0; margin-top:5px; border-top: solid 1px #aaa;">
					<em>'.(isset($this->pageCount)?
					l_t('Page <strong>%s</strong> of <strong>%s</strong>',$this->currentPage,$this->pageCount):
					l_t('Page <strong>%s</strong>',$this->currentPage)
					).'</em>
				</div>';
	}
}

?>