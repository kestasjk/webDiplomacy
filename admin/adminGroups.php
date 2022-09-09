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
 * Group management for moderators. Initially just a place to display a queue of groups that may
 * need attention. In future would be good to enable groups to be tied in to admin actions so that
 * a mod can e.g. require a response from a user by a certain length of time or there will be 
 * an automatic admin action just as unpausing, replacing a user with a bot, etc.
 *
 * @package Admin
 */

 require_once("objects/group.php");
 require_once("objects/groupUser.php");
 require_once("lib/group.php");

 print '<h4>Group Management</h4>';
?><p>Group management for moderators. Initially just a place to display a queue of groups that may
 need attention. In future would be good to enable groups to be tied in to admin actions so that
 a mod can e.g. require a response from a user by a certain length of time or there will be 
 an automatic admin action just as unpausing, replacing a user with a bot, etc.
</p><?php

$groupUsers = Group::getUsers('gr.isActive = 1');

usort($groupUsers, fn($a, $b) => $b->Group->timeChanged - $a->Group->timeChanged);

 print '<div>';
 print Group::outputUserTable_static($groupUsers, null, null);
 print '</div>';

 