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

require_once('api/exceptions.php');

/**
 * Class to manage an API key and check associated permissions.
 */
class ApiKey {
    /**
     * API access key.
     * @var string
     */
    public $apiKey;

    /**
     * User ID associated to API key.
     * @var int
     */
    public $userID;

    /**
     * Permissions associated to this API key.
     * Associative array mapping permission name to a boolean.
     * @var array
     */
    public $permissions;

    /**
     * List of current permissions names in database table `wD_ApiPermissions`.
     */
    static private $permissionFields = array(
        'canReplaceUsersInCD'
    );

    /**
     * Initialize API key object.
     * @param string $apiKey - API access key.
     * @throws ClientUnauthorizedException - If associated user cannot be found.
     */
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
        $this->userID = null;
        $this->permissions = array();
        foreach (ApiKey::$permissionFields as $permissionField) {
            $this->permissions[$permissionField] = false;
        }
        $this->load();
    }

    /**
     * Load API key.
     * @throws ClientUnauthorizedException - if associated user cannot be found.
     */
    private function load() {
        global $DB;

        // Loading user
        $rowUserId = $DB->sql_hash("SELECT userID from wD_ApiKeys WHERE apiKey = '" . $DB->escape($this->apiKey) . "'");
        if (!$rowUserId) { throw new ClientUnauthorizedException('No user associated to this API key.'); }
        $this->userID = intval($rowUserId['userID']);

        // Loading permissions
        $permissionRow = $DB->sql_hash("SELECT * FROM wD_ApiPermissions WHERE userID = " . $this->userID);
        if ($permissionRow) {
            foreach (ApiKey::$permissionFields as $permissionField) {
                if ($permissionRow[$permissionField] == 'Yes') {
                    $this->permissions[$permissionField] = true;
                }
            }
        }
    }
}
