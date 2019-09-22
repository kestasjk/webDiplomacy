<?php
/*
    Copyright (C) 2004-2019 Kestas J. Kuliukas, Philip Paquette

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

namespace API;
defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * Message JSON response
 * @package API
 */
class Message {

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
     * Time Sent
     * @var int
     */
    public $timeSent;

    /**
     * The country ID sending the message
     * @var int
     */
    public $fromCountryID;

    /**
     * The country ID receiving the message
     * @var int
     */
    public $toCountryID;

    /**
     * The actual message
     * @var string
     */
    public $message;

    /**
     * Initialize a game message object
     * @param int $turn - The turn where the order/move was issued.
     * @param string $phase - The phase within the turn
     * @param int $timeSent - The timestamp when the message was sent
     * @param int $fromCountryID - The countryID sending the message
     * @param int $toCountryID - The countryID receiving the message
     * @param string $message - The actual message
     */
    function __construct($turn, $phase, $timeSent, $fromCountryID, $toCountryID, $message) {
        $this->turn = intval($turn);
        $this->phase = strval($phase);
        $this->timeSent = intval($timeSent);
        $this->fromCountryID = intval($fromCountryID);
        $this->toCountryID = intval($toCountryID);
        $this->message = strval($message);
    }

    /**
     * Encodes this object in JSON
     */
    function toJson() { return json_encode($this); }
}
