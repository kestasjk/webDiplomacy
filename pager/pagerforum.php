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
 * @package Base
 * @subpackage Pager
 */
 
require_once(l_r('pager/pagerthread.php'));
class PagerForum extends Pager
{
	public static $defaultPostsPerPage=30;
	public $type='forum';
	
	function __construct($itemsTotal)
	{
		parent::__construct('forum.php',$itemsTotal,self::$defaultPostsPerPage);
	}
	function getCurrentPage($currentPage=1)
	{
		parent::getCurrentPage($this->pageCount);
		if ( $this->currentPage>$this->pageCount )
			$this->currentPage = $this->pageCount;
	}
	function currentPageNumber()
	{
		if( $this->currentPage != $this->pageCount )
			return parent::currentPageNumber();
		else
			return '';
	}
	
	function SQLLimit()
	{
		return ' LIMIT '.($this->pageCount-$this->currentPage)*$this->itemsPerPage.', '.$this->itemsPerPage;
	}
}