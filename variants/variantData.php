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
	/**
	 * The variant ID; the only mandatory field
	 * @var int
	 */
	public $variantID;
	/**
	 * Game ID, or 0 if game ID not relevant (e.g. user specific or global variant data)
	 * @var int
	 */
	public $gameID = 0;
	/**
	 * An extra token to prevent conflict with any other unknown code using the same system for data storage. Should be a random number from 1 to 2^31-1
	 * @var int
	 */
	public $systemToken = 0;
	/**
	 * User ID, or 0 if not user specific
	 * @var int
	 */
	public $userID = 0;
	/**
	 * Data type ID, basically another way to distinguish between the sorts of data stored. Default is 0, which is fine for most use.
	 * @var int
	 */
	public $typeID = 0;
	
	public function __construct($variantID)
	{
		$this->variantID = $variantID;
	}
	
	/**
	 * Create where clause to select this variant data
	 * @param int $offset
	 * @return string
	 */
	public function where($offset=0)
	{
		$params = array(
				'variantID' => $this->variantID,
				'gameID' => $this->gameID,
				'systemToken' => $this->systemToken,
				'typeID' => $this->typeID,
				'userID' => $this->userID,
				'`offset`' => $offset);
		
		$arr = array();
		foreach($params as $k=>$v)
			$arr[] = $k.'='.$v;
		
		return implode(' AND ', $arr);
	}
	
	/**
	 * Create a comma separated string of values up to and including offset
	 * @param int $offset
	 * @return string
	 */
	public function commaSeparatedValueString($offset=0)
	{
		$vals = array(
				$this->variantID,
				$this->gameID,
				$this->systemToken,
				$this->typeID,
				$this->userID,
				$offset);
				
		return implode(', ', $vals);
	}
	
	/**
	 * Generic get data column
	 * @param string $col Name of the column to extract
	 * @param int $offset The variable offset to extract
	 * @return int/float The data in that record
	 */
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
	
	private function updateCol($updateInt, $val, $offset=0)
	{
		global $DB;
		
		if($updateInt)
		{
			$intAndFloatParamsString = ",".$val.", 0";
		}
		else
		{
			$intAndFloatParamsString = ", 0, ".$val;
		}
			
		
		return $DB->sql_put("REPLACE INTO wD_VariantData VALUES(".$this->commaSeparatedValueString($offset).$intAndFloatParamsString.");");
	}
	
	public function updateFloat($val, $offset=0)
	{
		return $this->updateCol(false,$val,$offset);
	}
	
	public function updateInt($val, $offset=0)
	{
		return $this->updateCol(true,$val,$offset);
	}
}