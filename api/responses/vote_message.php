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
 * A vote, along with the sender country ID.
 * @package webdiplomacy_api
 */
class VoteMessage {
	public $vote;
	public $countryID;
	public $timeSent;
	public $phaseMarker;

	function toJson() { return json_encode($this); }

	/**
	 * @param string $vote - e.g. 'Draw','Pause','Cancel','Concede'
	 * @param int $countryID - sender country ID
	 * @param int $timeSent - time sent
	 * @param string $phaseMarker - Diplomacy/Retreat/Builds/etc

	 */
	function __construct($vote, $countryID, $timeSent, $phaseMarker = null)
	{
		$this->vote = $vote;
		$this->countryID = intval($countryID);
		$this->timeSent = intval($timeSent);
		$this->phaseMarker = $phaseMarker;
	}
}
