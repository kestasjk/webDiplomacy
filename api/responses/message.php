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
 * A message, along with the sender and receiver country ID.
 * @package webdiplomacy_api
 */
class Message {
	public $message;
	public $fromCountryID;
	public $toCountryID;
	public $phaseMarker;

	function toJson() { return json_encode($this); }

	/**
	 * Initialize a unit of a power in civil disorder
	 * @param string $message - message
	 * @param int $fromCountryID - sender country ID
	 * @param int $toCountryID - receiver country ID
	 * @param string $phaseMarker - Diplomacy/Retreat/Builds/etc

	 */
	function __construct($message, $fromCountryID, $toCountryID, $phaseMarker = null)
	{
		$this->message = $message;
		$this->fromCountryID = intval($fromCountryID);
		$this->toCountryID = intval($toCountryID);
		$this->phaseMarker = $phaseMarker;
	}
}
