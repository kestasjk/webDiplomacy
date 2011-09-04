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
 * @subpackage Options
 */

class searchOption
{
	public $label;
	public $htmlName;
	public $value;
	
	public $locked=false;
	public $checked=false;
	
	function __construct($htmlName, $label, $value)
	{
		$this->htmlName=$htmlName;
		$this->label=$label;
		$this->value=$value;
	}
}

class searchOptionSelect extends searchOption
{
	function formHTML()
	{
		return '<option value="'.$this->value.'" '.($this->checked?'selected ':'').'>'.$this->label.'</option>';
	}
}
class searchOptionCheckbox extends searchOption
{
	function __construct($htmlName, $label, $value)
	{
		parent::__construct($htmlName,$label,$value);
		$this->htmlName .= '[]';
	}
	
	function formHTML()
	{
		// Disabled stops the input being sent via the form
		// Readonly isn't actually read-only for checkboxes and radios
		// So if the checkbox is locked we have to set it to disabled and enter the data via a hidden field instead..
		$output = "";
		
		$output .= '<input type="checkbox" 
			value="'.$this->value.'" 
			'.($this->locked?'':'name="'.$this->htmlName.'"').' 
			'.($this->checked?'checked ':'').'
			'.($this->locked?'disabled ':'').'/> 
			'.$this->label;
		
		if($this->locked)
			$output .= ' <input type="hidden" name="'.$this->htmlName.'" value="'.$this->value.'" />';
		
		return $output;
	}
}
class searchOptionRadio extends searchOption
{
	function formHTML()
	{
		$output = "";
		$output .= '<input type="radio" 
			value="'.$this->value.'" 
			'.($this->locked?'':'name="'.$this->htmlName.'"').' 
			'.($this->checked?'checked ':'').'
			'.($this->locked?'disabled ':'').'/> 
			'.$this->label;
		
		if($this->locked)
			$output .= ' <input type="hidden" name="'.$this->htmlName.'" value="'.$this->value.'" />';
		
		return $output;
	}
}
?>