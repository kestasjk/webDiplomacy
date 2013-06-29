<?php
/*
    Copyright (C) 2004-2013 Kestas J. Kuliukas

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
		
		$DB->sql_put("UPDATE wD_VariantData SET val_".$col." = ".number_format(round($val,3),3)." WHERE ".$this->where($offset));
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