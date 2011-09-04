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
require_once('pager/pager.php');
class PagerGames extends Pager
{
	private $approxPageCount;
	public $type='games';
	function __construct($URL, $approxItemCount=null)
	{
		if(isset($approxItemCount))
			$this->approxPageCount = ceil($approxItemCount / 10);
			
		parent::__construct($URL,null,10);
	}
	function currentPageNumberOfTotal()
	{
		if( $this->currentPage != 1 )
			return parent::currentPageNumber();
		else
			return '';
	}
	function currentPageNumber()
	{
		if(!isset($this->approxPageCount))
			return '';
		
		$this->pageCount = '~'.$this->approxPageCount;
		$buf = parent::currentPageNumber();
		unset($this->pageCount);
		
		return $buf;
	}
}