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
 * @subpackage AbstractItems
 */

/**
 * While the searchItem/searchObject setup seems needlessly complex if 
 * you need something simple and/or unchanging, it makes things much 
 * more managable and flexible as things get more complex.
 * As it is it's easy to add new search criteria, or add more qualifiers
 * to a certain subset of searches, or to add more standard searches, 
 * and it can be searched by a human or used by the system as a way to
 * find games meeting certain criteria. (Or even used for users or 
 * multi-account detection, etc)
 * 
 * 
 * A search item represents something that gives you some choice/some way of
 * defining a search. It can display this choice as an HTML form widget, 
 * can recognize a submitted choice via the form, and can convert this 
 * submitted choice into SQL which affects the resulting search.
 * 
 * In addition it also has to recognize default settings. For a given 
 * template a search item must be able to default to a certain choice, 
 * or even enforce that choice and not allow alternatives.
 * 
 * Finally an item may have sub-items, which are affected by form inputs 
 * and can alter the search SQL only if a certain choice is made in the 
 * parent item. 
 * 
 * 
 * The whole thing goes like this:
 * setOptions - Convert the array of option names to searchOption objects
 * setSubItems - Convert the array of sub-item names to searchItem objects
 * 		(This repeats this whole process for each sub-item)
 * setDefaults - Set the options and value to their defaults for the given search-template
 * setLocked - Set the options to locked depending on the given search-template
 * 
 * Then the item is set up and ready.
 * 
 * == This code is run if dealing with a user search from a form, otherwise it's skipped
 * == filterInput - Check any form input data to see if there's a valid option that is being set
 * == 		(This returns all the sub-items which may also need to take form input data)
 * == 
 * == Once this is done for all items the HTML form is printed off 
 * == formHTML - Print the HTML
 * == 		(This returns a string which will also contain the formHTML for all sub-items)
 * == 
 * == Once the HTML is all out the SQL is generated
 * 
 * 
 * sql - Get the SQL for this item, adding it to what's given
 * 		(This returns all the sub-items which may also need to alter the search SQL)
 * 
 * Then the SQL is finished and the query can be executed.
 */
abstract class searchItem
{
	/**
	 * The HTML form name of this item (e.g. "amMember")
	 * @var string
	 */
	public $name;
	
	/**
	 * The friendly label for this item (e.g. 'Game membership filters')
	 * @var string
	 */
	protected $label;
	
	/**
	 * The list of options, indexed by HTML form value, the array value is the label for that option.
	 * The first value is the default if no other is specified. All strings are replaced with searchOption
	 * objects in setOptions()
	 * (e.g. array('-'=>'All','Yes'=>'Joined games','No'=>'Non-joined games') )
	 * @var array
	 */
	protected $options;
	
	/**
	 * Is this item unchangeable?
	 * @var boolean
	 */
	protected $locked=false;
	
	/**
	 * The selected option value 
	 * @var string
	 */
	protected $value;
	
	/**
	 * The list of search templates in which this item cannot change its value
	 * (e.g. array('My games','Profile') )
	 * @var array
	 */
	protected $locks=array();
	
	/**
	 * An array of default values indexed by search-template. 
	 * (e.g. array( 'Notifications'=>'Yes', 'Profile'=>'Yes', 'My games'=>'Yes', 'New'=>'No', 'Open'=>'No', 'Active'=>'No' ) )
	 * @var unknown_type
	 */
	protected $defaults=array();
	
	/**
	 * The array of sub-item object postfix names this item may refer to depending on its selection
	 * (e.g. array('MemberStatus','ActivityTypes','IsJoinable') ). Replaced with their respective searchItem
	 * objects in setSubItems
	 * @var array
	 */
	protected $subItems=array();
	
	
	abstract function sql(&$TABLES,&$WHERE,&$ORDER);
	
	function __construct($searchType)
	{
		$this->setOptions();
		$this->setSubItems($searchType);
		$this->setDefaults($searchType);
		$this->setLocked($searchType);
	}
	protected abstract function setOptions();
	protected function setSubItems($searchType)
	{
		$subItems=array();
		foreach($this->subItems as $subItem)
		{
			$subItem = 'search'.$subItem;
			$subItemObj = new $subItem($searchType);
			$subItems[$subItemObj->name] = $subItemObj;
		}
		$this->subItems=$subItems;
	}
	protected function setDefaults($searchType)
	{
		if ( isset($this->defaults[$searchType]) )
			$this->setValue($this->defaults[$searchType]);
		else
		{
			foreach($this->options as $firstOption)
			{
				$firstOption->checked=true;
				$this->setValue($firstOption->value);
				break;
			}
		}
	}
	protected function setLocked($searchType)
	{
		if( in_array($searchType,$this->locks) )
		{
			foreach($this->options as $option)
				$option->locked=true;
			
			$this->locked=true;
		}
	}
	
	protected function setValue($value)
	{
		if(isset($this->value))
			foreach($this->options as $option)
				$option->checked = false;
		
		$this->value = $value;
		$this->options[$this->value]->checked = true;
	}
	
	function filterInput($input)
	{
		if ( $this->locked ) return;
		
		foreach($this->options as $value=>$option)
			if( $input === $value )
			{
				$this->setValue($value);
				break;
			}
	}
	
	function formHTML()
	{
		$formHTML='<li>';
		
		if($this->label)
			$formHTML .= '<strong>'.$this->label.':</strong>';
		
		foreach($this->options as $option)
			$formHTML .= $option->formHTML().' ';
		
		return $formHTML.'</li>';
	}
}

abstract class searchItemSelect extends searchItem
{
	protected function setOptions()
	{
		foreach($this->options as $value=>$label)
		{
			$optionObj = new searchOptionSelect('search['.$this->name.']', $label, $value);
			$options[$optionObj->value] = $optionObj;
		}
			
		$this->options = $options;
	}
	
	function formHTML()
	{
		$formHTML='';
		foreach($this->options as $option)
			$formHTML .= $option->formHTML().' ';
			
		return '<li><strong>'.
			$this->label.':</strong> 
			<select name="search['.$this->name.']">'.
			$formHTML.
			'</select>
			</li>';
	}
}
abstract class searchItemCheckbox extends searchItem
{
	protected function invertedChecks()
	{
		$invertedChecks = array();
		foreach($this->options as $value=>$option)
		{
			if ( !$option->checked )
				$invertedChecks[] = $value;
		}
		return $invertedChecks;
	}
	
	protected function setOptions()
	{
		foreach($this->options as $value=>$label)
		{
			$optionObj = new searchOptionCheckbox('search['.$this->name.']', $label, $value);
			$options[$optionObj->value] = $optionObj;
		}
			
		$this->options = $options;
	}
	
	protected function setValue($values)
	{
		assert('is_array($values)');
		foreach($this->options as $value=>$option)
			if(in_array($value,$values))
				$option->checked=true;
			else
				$option->checked=false;
		
		$this->value=$values;
	}
	
	protected function setDefaults($searchType)
	{
		if ( isset($this->defaults[$searchType]) )
			$this->setValue($this->defaults[$searchType]);
		else
		{
			$values=array();
			
			foreach($this->options as $option)
				$values[]=$option->value;
			
			$this->setValue($values);
		}
	}
	
	function filterInput($input)
	{
		assert('is_array($input)');
		if ( $this->locked ) return;
		
		$values=array();
		
		foreach($input as $selectedOption)
		{
			if( isset($this->options[$selectedOption]) )
			{
				$this->options[$selectedOption]->checked=true;
				$values[]=$this->options[$selectedOption]->value;
			}
		}
		
		$this->setValue($values);
	}
}
abstract class searchItemRadio extends searchItem
{
	protected function setOptions()
	{
		foreach($this->options as $value=>$label)
		{
			$optionObj = new searchOptionRadio('search['.$this->name.']', $label, $value);
			$options[$optionObj->value] = $optionObj;
		}
			
		$this->options = $options;
	}
}
?>