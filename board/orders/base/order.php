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

require_once(l_r('board/orders/base/unit.php'));

/**
 * The root order function; mostly just holds information and loads objects for
 * userOrder and processOrder
 *
 * @package Base
 * @subpackage Game
 */
abstract class order
{
	/**
	 * The order ID
	 * @var int
	 */
	public $id;

	/**
	 * The type of order e.g. 'Hold', 'Support move', 'Destroy', 'Build Army'
	 * @var string
	 */
	public $type;

	/**
	 * The game ID corresponding to this order
	 * @var int
	 */
	protected $gameID;

	/**
	 * The countryID corresponding to this order
	 * @var int
	 */
	public $countryID;

	/**
	 * The ID of the unit in the order; may or may not be fixed
	 * @var int
	 */
	public $unitID;
	/**
	 * The Unit object corresponding to the order's unitID field
	 * @var Unit
	 */
	protected $Unit;

	/**
	 * A territory name from the toTerrID field
	 * @var string
	 */
	public $toTerrID;
	/**
	 * The Territory object corresponding to the toTerrID field
	 * @var Territory
	 */
	protected $toTerritory;

	/**
	 * A territory name from the fromTerrID field
	 * @var string
	 */
	public $fromTerrID;
	/**
	 * The Territory object corresponding to the fromTerrID field
	 * @var Territory
	 */
	protected $fromTerritory;

	/**
	 * Whether or not this unit is moving via convoy (if applicable)
	 *
	 * @var string 'Yes'/'No'
	 */
	public $viaConvoy;

	// If true countryIDs are ignored and a user can enter orders for any country
	public $isSandboxMode = false;

	/**
	 * Create the order and initialize objects from a $row
	 *
	 * @param array $row
	 */
	protected function __construct($orderID, $gameID, $countryID)
	{
		$this->id = $orderID;
		$this->gameID = $gameID;
		$this->countryID = $countryID;
	}

	protected function loadData(array $data) {
		foreach($data as $name=>$val)
		{
			if( $val )
			{
				$this->{$name} = $val;
				$this->loadObject($name); // Load the object if applicable
			}
		}
	}

	/**
	 * Load an object corresponding to an entered class field. Used when inputting new
	 * data and when initializing the object (->set() and ->__construct()). If the input
	 * doesn't correspond to an object it is ignored.
	 *
	 * @param string $name
	 * @param string|int $value
	 */
	protected function loadObject($name)
	{
		switch($name)
		{
			case 'unitID':
				if( !isset($this->unitID) || !$this->unitID )
				{
					unset($this->Unit);
					return true;
				}
				else
				{
					$this->Unit = libVariant::$Variant->Unit($this->unitID);
					$this->Unit->Territory = libVariant::$Variant->Territory($this->Unit->terrID);
					if( $this->Unit->gameID != $this->gameID )
						throw new Exception(l_t("Invalid unitID given; does not belong in this game."));
					if( $this->Unit->countryID != $this->countryID && !$this->isSandboxMode )
						throw new Exception(l_t("Invalid unitID given; does not belong to this country."));
					return true;
				}
			case 'toTerrID':
				if( !isset($this->toTerrID) || !$this->toTerrID )
					unset($this->toTerritory);
				else
					$this->toTerritory = libVariant::$Variant->Territory($this->toTerrID);
				return true;
			case 'fromTerrID':
				if( !isset($this->fromTerrID) || !$this->fromTerrID )
					unset($this->fromTerritory);
				else
					$this->fromTerritory = libVariant::$Variant->Territory($this->fromTerrID);
				return true;
		}
		return false;
	}
}

?>