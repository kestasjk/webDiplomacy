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

/**
 * @package Base
 */

require_once('header.php');

global $DB;

$userID= 0;

if( isset($_REQUEST['searchUser']) )
{
    // Limits the searches to 1 per second to keep scraping from being to process intensive.
    libAuth::resourceLimiter('user search',1);

    $searchUser = $_REQUEST['searchUser'];

    if ( isset($searchUser['id']) && $searchUser['id'] && strlen($searchUser['id']) )
    {
        list($foundUserID) = $DB->sql_row("SELECT id FROM wD_Users WHERE id = ".(int)$searchUser['id']." LIMIT 1");
    }

    else if ( isset($searchUser['username']) && $searchUser['username'] && strlen($searchUser['username']) )
    {
        list($foundUserID) = $DB->sql_row("SELECT id FROM wD_Users WHERE username = '".$DB->escape($searchUser['username'])."' LIMIT 1");
    }

    else if ($User->type['Moderator'] && isset($searchUser['email']) && $searchUser['email'] && strlen($searchUser['email']) )
    {
        list($foundUserID) = $DB->sql_row("SELECT id FROM wD_Users WHERE email = '".$DB->escape($searchUser['email'])."' LIMIT 1");
    }

    if( !isset($foundUserID) || !$foundUserID )
        $searchReturn = l_t('No users found matching the given search parameters.');
    else
        $userID=$foundUserID;

    // Redirect to user's profile
    if ($userID > 0)
        header('refresh: 0; url=userprofile.php?userID='.$userID);
}

libHTML::starthtml();

print libHTML::pageTitle(l_t('User Search'),l_t('Find a user!'));

if( isset($searchReturn) ) 
    print '<p class="notice">No users found matching the given search parameters.</p>';

print	'<div class = "userSearch_show">
		<form action="search.php" method="post">
		<ul class="formlist">
			<p>
				<strong>User ID:</strong> </br>
				<input class="gameCreate" type="text" name="searchUser[id]" value="" size="10">
			</p>
			<p>
				<strong>Username</strong> </br>
				<input class="gameCreate" type="text" name="searchUser[username]" value="" size="40">
				</br>
				(Not case sensitive, but otherwise must match exactly.)
            </p>';
if ( $User->type['Moderator'] )
{
    print	'<p>
                <strong>Email</strong> </br>
                <input class="gameCreate" type="text" name="searchUser[email]" value="" size="40">
                </br>
                (Not case sensitive, but otherwise must match exactly.)
            </p>';
}
            
print		'<p>
				<input type="submit" class="green-Submit" value="Search">
			</p>
		</ul>

		</form>
	</div>
	</div>';

libHTML::footer();

?>