<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas / Timothy Jones

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
namespace webdiplomacy_api;

defined('IN_CODE') or die('This script can not be run by itself.');


/**
 * Order JSON response
 * @package webdiplomacy_api
 */
class Order {

    /**
     * Turn
     * @var int
     */
    public $turn;

    /**
     * Phase - (Finished, Pre-game, Diplomacy, Retreats, Builds)
     * @var string
     */
    public $phase;

    /**
     * The country ID issung the order
     * @var int
     */
    public $countryID;

    /**
     * The territory ID where the unit is located
     * @var int
     */
    public $terrID;

    /**
     * The unit type - (Army, Fleet)
     * @var string
     */
    public $unitType;

    /**
     * The order type
     * 'Hold', 'Support hold', 'Support move', 'Convoy', 'Retreat', 'Disband', 'Build Army', 'Build Fleet', 'Wait', 'Destroy'
     * @var string
     */
    public $type;

    /**
     * The destination territory (for move, support, and convoy)
     * @var int
     */
    public $toTerrID;

    /**
     * The source territory (for support move and convoy)
     * @var int
     */
    public $fromTerrID;

    /**
     * Indicates that we are moving via convoy - (Yes, No)
     * @var string
     */
    public $viaConvoy;

    /**
     * Indicates if the order was successful or not - (Yes, No)
     * @var string
     */
    public $success;

    /**
     * Indicates if the unit has been dislodged or not - (Yes, No)
     * @var string
     */
    public $dislodged;

    function toJson()
	{
		return json_encode($this);
	}

	/**
	 * Get unit ordered by this order, if available.
	 */
	function getOrderedUnit() {
		if ($this->type == 'Build Army' || $this->type == 'Build Fleet')
			return null;
		$retreating = ($this->type == 'Retreat' && $this->success == 'Yes');
		return new Unit($this->unitType, $this->terrID, $this->countryID, $retreating);
	}

	/**
	 * Initialize a order (move) object
     * @param int $turn - The turn where the order/move was issued.
     * @param string $phase - The phase within the turn
     * @param int $countryID - The country ID owning the unit
     * @param int $terrID - The territory where the unit is located
     * @param string $unitType - The unit type
     * @param string $type - The order type
     * @param int $toTerrID - The dest territory (for move, support, convoy)
     * @param int $fromTerrID - The src territory (for support move and convoy)
     * @param string $viaConvoy - Whether the units wants to move via convoy
     * @param string $success - Whether the move/order succeeded
     * @param string $dislodged - Whether the unit has been dislodged
	 */
	function __construct($turn, $phase, $countryID, $terrID, $unitType, $type, $toTerrID, $fromTerrID, $viaConvoy, $success, $dislodged)
	{
        $this->turn = intval($turn);
        $this->phase = strval($phase);
        $this->countryID = intval($countryID);
        $this->terrID = intval($terrID);
        $this->unitType = strval($unitType);
        $this->type = strval($type);
        $this->toTerrID = intval($toTerrID);
        $this->fromTerrID = intval($fromTerrID);
        $this->viaConvoy = strval($viaConvoy);
        $this->success = strval($success);
        $this->dislodged = strval($dislodged);
	}

}
