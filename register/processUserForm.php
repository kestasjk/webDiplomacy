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
 * @package Base
 * @subpackage Forms
 */

$formOutput = '';

try
{
	$error = array();

	$SQLVars = User::processForm($_REQUEST['userForm'], $errors);

	$set = '';

	$required = array('Username' => 'username', 'E-mail' => 'email',
					'E-mail hiding' => 'hideEmail');

	$allowed = array('Homepage'=>'homepage','Comment'=>'comment');

	foreach( $required as $name=>$SQLName )
	{
		if ( ! isset($SQLVars[$SQLName]) )
		{
			$errors[] = $name.' required, but not given';
		}

		if ( $set != '' ) $set .= ', ';

		// This will insert the data back into the form so it doesn't have to be re-entered
		$User->{$SQLName} = $SQLVars[$SQLName];

		$set .= $SQLName." = '".$SQLVars[$SQLName]."'";
	}

	if ( isset($SQLVars['password']) )
	{
		$set .= ', password = '.$SQLVars['password'];
	}
	else
	{
		$errors[] = 'Password required, but not given';
	}

	foreach( $allowed as $name=>$SQLName )
	{
		if ( ! isset($SQLVars[$SQLName]) )
			continue;

		$set .= ', ';

		$User->{$SQLName} = $SQLVars[$SQLName];

		$set .= $SQLName." = '".$SQLVars[$SQLName]."'";
	}

	if ( count($errors) ) throw new Exception(implode('. ',$errors));

	$set .= ', timeJoined = '.time().', timeLastSessionEnded = '.time();

	if( User::findUsername($SQLVars['username']) )
		throw new Exception("The username '".$SQLVars['username']."', or the e-mail address '".
							$SQLVars['email']."', is already in use. Please choose another.");
	elseif( User::findEmail($SQLVars['email']) )
		throw new Exception("The email '".$SQLVars['email']."', or the e-mail address '".
							$SQLVars['email']."', is already in use. Please choose another.");

	$DB->sql_put("INSERT INTO wD_Users SET ".$set);
	$DB->sql_put("COMMIT");

	// Re-authenticate with the new password, to create a new session ID
	$key = libAuth::userPass_Key($SQLVars['username'], $_REQUEST['userForm']['password']);
	$NewUser = libAuth::key_User($key);
	$NewUser->sendNotice('No','No',"Welcome! This area displays your notices, which let you catch "
		."up with what has happened since you were last here");

	// libHTML does not like letting registered users access the registration page
	$User = new User(GUESTID);

	print libHTML::pageTitle('Register a webDiplomacy account','Validate your e-mail address -&gt; Enter your account settings -&gt; <strong>Play webDiplomacy!</strong>');

	print "<h3>Welcome to webDiplomacy!</h3>
			<p>Welcome, ".$SQLVars['username']."!<br /><br />

				You can now post in the <a href='forum.php' class='light'>forum</a>,
				look for <a href='gamelistings.php' class='light'>a game to join</a>,
				create a <a href='gamecreate.php' class='light'>new game</a>,
				or get some <a href='help.php' class='light'>help/info</a>.<br />
				Be sure to bookmark the <a href='index.php' class='light'>home page</a>,
				which displays a summary of your games and forum activity.<br /><br />

				If you don't know what Diplomacy is about yet check out the quick
				<a href='intro.php' light='class'>graphical intro to webDiplomacy</a>,
				so you can get going faster.
			</p>";
	print '</div>';

	libHTML::footer();

}
catch(Exception $e)
{
	$formOutput .= $e->getMessage();
}

print '<div class="content"><p class="notice">'.$formOutput.'</p></div>';

?>