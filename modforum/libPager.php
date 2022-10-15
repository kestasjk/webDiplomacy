<?php

require_once(l_r('pager/pager.php'));

class PagerThread extends Pager
{
	public static $defaultPostsPerPage=30;
	public $type='thread';
	function __construct($itemsTotal, $threadID)
	{
		parent::__construct('modforum.php',$itemsTotal,self::$defaultPostsPerPage);
		$this->addArgs('threadID='.$threadID);
	}
	function getCurrentPage($currentPage=1)
	{
		parent::getCurrentPage($this->pageCount);
		if ( $this->currentPage>$this->pageCount )
			$this->currentPage = $this->pageCount;
	}
	function currentPageNumber()
	{
		return parent::currentPageNumber();
		if( $this->currentPage != $this->pageCount )
			return parent::currentPageNumber();
		else
			return '';
	}
}

class PagerForum extends Pager
{
	public static $defaultPostsPerPage=30;
	public $type='forum';
	
	function __construct($itemsTotal)
	{
		parent::__construct('modforum.php',$itemsTotal,self::$defaultPostsPerPage);
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

?>