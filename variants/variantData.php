<?php

class VariantData
{
	public $variantID;
	public $gameID = 0;
	public $systemToken = 0;
	public $userID = 0;
	public $typeID = 0;
	
	public function VariantData($variantID)
	{
		$this->variantID = $variantID;
	}
	
	public function where($offset=0)
	{
		$params = array(
				'variantID' => $this->variantID,
				'gameID' => $this->gameID,
				'systemToken' => $this->systemToken,
				'typeID' => $this->typeID,
				'userID' => $this->userID,
				'offset' => $offset);
		
		$arr = array();
		foreach($params as $k=>$v)
			$arr[] = $k.'='.$v;
		
		return implode(' AND ', $arr);
	}
	private function getCol($col, $offset=0)
	{
		global $DB;
		
		list($val) = $DB->sql_row("SELECT val_".$col." FROM wD_VariantData WHERE ".$this->where($offset));

		return $val;
	}
	public function getInt($offset=0, $default=0)
	{
		$val = $this->getCol('int',$offset);
		if( is_null($val) || empty($val) ) return $default;
		else return $val;
	}
	public function getFloat($offset=0, $default=0)
	{
		$val = $this->getCol('float',$offset);
		if( is_null($val) || empty($val) ) return $default;
		else return $val;
	}
	private function setCol($col, $val, $offset=0)
	{
		global $DB;
		
		$DB->sql_put("UPDATE wD_VariantData SET val_".$col." = ".$val." WHERE ".$this->where($offset));
	}

	public function setFloat($val, $offset=0)
	{
		$this->setCol('float',$val,$offset);
	}

	public function setInt($val, $offset=0)
	{
		$this->setCol('int',$val,$offset);
	}
}