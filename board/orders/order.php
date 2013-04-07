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

require_once(l_r('board/orders/base/order.php'));

/**
 * @package Board
 * @subpackage Orders
 *
 * construct->loadDB->loadInput->
 * validate->{
 * 		updaterequirements->checkComplete->
 * 		wipeUnrequiredParams->wipeInvalidatedParams
 * 		{
 * 			paramIsset->
 * 			paramIsValid->{ paramNeedsValidation }->[paramWipe if not valid]
 * 		}
 * 	}
 * 	->commit
 */
abstract class userOrder extends order
{
	/**
	 * An array of requirements; Type is chosen, and this selects the sub-array of further
	 * requirements which need to be filled to complete the order.
	 *
	 * @var array
	 */
	protected $requirements=array();

	public $error;
	// The status, invalid until proven valid
	public $status='Loading'; // Loading, Loaded, Validating, Incomplete, Invalid, Complete

	/**
	 * Load different orders depending on the current phase
	 *
	 * @param array $row An order record
	 * @return userOrder A phase-specific userOrder-child
	 */
	public static function load($phase, $orderID, $gameID, $countryID)
	{
		global $Game;

		switch ( $phase )
		{
			case 'Diplomacy':
				$Order = libVariant::$Variant->userOrderDiplomacy($orderID, $gameID, $countryID);
			break;

			case 'Retreats':
				$Order = libVariant::$Variant->userOrderRetreats($orderID, $gameID, $countryID);
			break;

			case 'Builds':
				$Order = libVariant::$Variant->userOrderBuilds($orderID, $gameID, $countryID);
			break;
		}

		return $Order;
	}

	/**
	 * Initialize this userOrder, this is called from the userOrder children
	 *
	 * @param array $row An order record
	 */
	public function __construct($orderID, $gameID, $countryID)
	{
		// Load fixed order data from row (either from database or signed input)
		parent::__construct($orderID, $gameID, $countryID);
	}

	protected static $paramsValidLoadDB = array('type','unitID','toTerrID','fromTerrID','viaConvoy');
	protected static $paramsValidLoadInput = array('type','toTerrID','fromTerrID','viaConvoy');

	protected $loaded=array();
	protected $changed=array();
	protected $unchanged=array();
	protected $wiped=array();

	/**
	 * Load stuff from the database
	 *
	 * @param array $inputs
	 */
	public function loadFromDB(array $inputs) {
		$data=array();

		foreach(self::$paramsValidLoadDB as $paramName)
		{
			if( isset($inputs[$paramName]) && $inputs[$paramName] )
			{
				$data[$paramName] = $inputs[$paramName];

				if( in_array($paramName, self::$paramsValidLoadInput) )
					$this->loaded[] = $paramName;
			}
		}

		parent::loadData($data);
	}

	/**
	 * Load user inputted stuff. Will filter out invalid inputs, escape everything inputted,
	 * categorize whether params have been changed, not been changed, need to be wiped,
	 * will update requirements based on the new data, and wipe anything that was loaded from the DB
	 * but is no longer required.
	 *
	 * @param array $inputs
	 */
	public function loadFromInput(array $inputs) {
		global $DB; // To escape inputs

		$data = array();

		/*
		 * Load valid params, check which have changed and which haven't,
		 * check which were loaded but are not present in the input to be wiped
		 */

		// Load valid params
		foreach(self::$paramsValidLoadInput as $paramName)
		{
			if( isset($inputs[$paramName]) && $inputs[$paramName] )
				$data[$paramName] = $DB->escape($inputs[$paramName]);
		}

		if( isset($inputs['convoyPath']) && is_array($inputs['convoyPath']) )
		{
			foreach($inputs['convoyPath'] as $cpID)
				$this->convoyPath[] = (int)$cpID;
		}

		unset($inputs);

		// Check which have changed and which haven't
		foreach($data as $name=>$val)
		{
			if( $this->paramIsset($name) )
			{
				if( $val != $this->{$name} )
					$this->changed[]=$name;
				else
					$this->unchanged[]=$name;
			}
			else
			{
				$this->changed[]=$name;
			}
		}

		// Check which were loaded but have not been input, these are wiped
		foreach($this->loaded as $loadedName)
			if( !isset($data[$loadedName]) )
			{
				$data[$loadedName]=false;
				$this->paramWipe($loadedName);
			}

		// Load data
		parent::loadData($data);

		$this->setStatus('Loaded');

		$this->updaterequirements();
		$this->wipeUnrequiredParams();
	}

	/**
	 * This sets up $this->requirements, after all data has been loaded from the DB
	 * and the user.
	 */
	protected abstract function updaterequirements();

	/**
	 * This wipes values which were loaded, but aren't fixed and aren't required.
	 * (e.g. A complete support move order is changed to a hold, toTerrID and fromTerrID
	 * have to be wiped.)
	 * This is done after $this->requirements has been set up.
	 */
	protected function wipeUnrequiredParams() {
		foreach($this->loaded as $paramName)
			if( !in_array($paramName, $this->requirements) )
				$this->paramWipe($paramName);
	}

	/**
	 * Wipe all params starting from the first unset param. So if
	 * type = Hold, toTerrID isn't set, but fromTerrID is set, fromTerrID will be
	 * wiped here.
	 * Done after wipeUnrequired(). Once complete the only stuff that is set
	 * is stuff that is part of the order.
	 *
	 * If isComplete() is true (the order is complete) then this won't
	 * wipe anything and doesn't need to be called.
	 */
	protected function wipeInvalidatedParams()
	{
		$startedWiping=false;

		foreach($this->requirements as $reqName)
		{
			if( $startedWiping )
			{
				$this->paramWipe($reqName);
			}
			elseif ( !$this->paramIsset($reqName) )
			{
				$startedWiping=true;
			}
		}
	}

	protected function setStatus($newStatus) {
		if( $this->status == 'Invalid' ) return;
		elseif ( $this->status == 'Incomplete' && $newStatus != 'Invalid' ) return;
		else $this->status = $newStatus;
	}

	/**
	 * Return true if all requirements are set, false otherwise.
	 *
	 * @return boolean
	 */
	protected function checkComplete() {
		foreach($this->requirements as $reqName)
			if( ! $this->paramIsset($reqName) )
				return false;

		return true;
	}

	protected function paramIsset($paramName)
	{
		return ( isset($this->{$paramName}) && $this->{$paramName} );
	}

	protected function paramWipe($paramName)
	{
		if( !in_array($paramName, $this->wiped) )
			$this->wiped[] = $paramName;

		unset($this->{$paramName});
	}

	/**
	 * If true all paramNeedsValidation() will return true. Once one param needs
	 * validation all following need validation.
	 *
	 * @var bool
	 */
	protected $followingNeedValidation=false;

	/**
	 * Does this param need to be checked? True if it has changed, if a preceding
	 * param has changed, or if a preceding param was found to be invalid.
	 *
	 * @param string $paramName
	 * @return bool
	 */
	protected function paramNeedsValidation($paramName)
	{
		if( $this->followingNeedValidation ) return true;

		/*
		 * If a param has changed but isn't in $this->changed it doesn't have to be
		 * put in, since $this->changed is only used to determine which params to save
		 * to the database. If it isn't already in $this->changed the meaning of the
		 * param may have changed, but it still doesn't need to be updated in the database.
		 */

		if( in_array($paramName, $this->changed) )
		{
			$this->followingNeedValidation=true;
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * If true all paramIsValid() will return false. An invalid param cannot
	 * be followed by valid params.
	 *
	 * @var bool
	 */
	protected $followingAreInvalid=false;
	/**
	 * Is a param valid?
	 * If a preceding param was found to be invalid it returns false,
	 * else if the param doesn't need to be validated it returns true,
	 * otherwise it checks if the param is valid or not.
	 *
	 * @param string $paramName
	 * @return bool
	 */
	protected function paramIsValid($paramName)
	{
		if( $this->followingAreInvalid ) return false;
		elseif( !$this->paramNeedsValidation($paramName) ) return true;

		try
		{
			if( $this->{$paramName.'Check'}() )
				return true;
			else
			{
				$this->error = l_t("Parameter '%s' set to invalid value '%s'",$paramName,$this->{$paramName});
				$this->setStatus('Invalid');
				$this->followingAreInvalid=true;

				return false;
			}
		}
		catch(Exception $e)
		{
			$this->error = l_t("Parameter '%s' set to invalid value '%s': %s",$paramName,$this->{$paramName},$e->getMessage());
			$this->setStatus('Invalid');
			$this->followingAreInvalid=true;
			return false;
		}
	}

	/**
	 * Run a SQL query and use it to add to/create an array of options. If
	 * an array of options is provided it is added to, otherwise a new array
	 * is created and returned.
	 *
	 * @param string $sql A SQL query returning a row if valid, and nothing if invalid
	 *
	 * @return array An array of options
	 */
	protected function sqlCheck($sql)
	{
		global $DB;

		$tabl=$DB->sql_tabl($sql);

		while($row=$DB->tabl_row($tabl)) return true; // The selection was found

		return false;
	}

	protected $hasChanged=true;
	public function validate()
	{
		$this->setStatus('Validating');

		if( $this->checkComplete() )
		{
			$this->setStatus('Complete');
		}
		else
		{
			$this->wipeInvalidatedParams();
			$this->setStatus('Incomplete');
		}

		// The params which are left only have to be validated
		foreach($this->requirements as $reqName)
		{
			// If this is an incomplete order and we have reached the end then stop
			if( !$this->paramIsset($reqName) )
				break;

			if( !$this->paramIsValid($reqName) )
				$this->paramWipe($reqName);
		}

		if( !count($this->changed) && !count($this->wiped) )
			$this->hasChanged=false;

		/*
		 * Done validating. Status will now reflect the true status, all params will be set to
		 * valid values which can be saved to the database, anything that needs to be wiped will
		 * be in $this->wiped[].
		 * (Something may be in both $this->wiped[] and $this->changed[], in which case
		 * $this->wiped[] will take precedence.)
		 */
	}

	/**
	 * Save a validated order, if a save is needed
	 */
	public function commit()
	{
		global $DB;

		$setSQL=array();

		foreach( $this->wiped as $reqName )
			$setSQL[$reqName] = $reqName." = NULL";

		foreach( $this->changed as $reqName )
		{
			if( isset($setSQL[$reqName]) ) continue; // It is being wiped.

			$setSQL[$reqName] = $reqName." = '".$this->{$reqName}."'";
		}

		if( count($setSQL) )
		{
			$DB->sql_put("UPDATE wD_Orders SET ".implode(', ',$setSQL)." WHERE id = ".$this->id." AND gameID=".$this->gameID);
			if( $DB->affected() )
				return true;
		}

		return false;
	}

	public function results() {
		return array('status'=>$this->status,'notice'=>$this->error,'changed'=>($this->hasChanged?'Yes':'No'));
	}
}

?>