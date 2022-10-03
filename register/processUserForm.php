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
	$errors = array();

	$SQLVars = User::processForm($_REQUEST['userForm'], $errors);

	$set = '';

	$required = array('Username' => 'username', 'E-mail' => 'email',
					'E-mail hiding' => 'hideEmail');

	$allowed = array('Homepage'=>'homepage','Comment'=>'comment');

	foreach( $required as $name=>$SQLName )
	{
		if ( ! isset($SQLVars[$SQLName]) )
		{
			$errors[] = l_t('%s required, but not given',$name);
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
		$errors[] = l_t('Password required, but not given');
	}

	foreach( $allowed as $name=>$SQLName )
	{
		if ( ! isset($SQLVars[$SQLName]) )
			continue;

		$set .= ', ';

		$User->{$SQLName} = $SQLVars[$SQLName];

		$set .= $SQLName." = '".$SQLVars[$SQLName]."'";
	}

	if ( is_array($errors) && count($errors)>0 ) throw new Exception(implode('. ',$errors));

	$set .= ', timeJoined = '.time().', timeLastSessionEnded = '.time();

	if( User::findUsername($SQLVars['username']) )
		throw new Exception(l_t("The username '%s' is already in use. Please choose another.",$SQLVars['username']));
	elseif( User::findEmail($SQLVars['email']) )
		throw new Exception(l_t("The e-mail address '%s', is already in use. If this is your e-mail, please use the Forgot your username and password features to recover your account or contact the moderators at %s for assistance. Making a second account for any reason is against the site rules.",$SQLVars['email'], Config::$modEMail));

	$DB->sql_put("INSERT INTO wD_Users SET ".$set);
	$DB->sql_put("COMMIT");

	// Re-authenticate with the new password, to create a new session ID
	$key = libAuth::userPass_Key($SQLVars['username'], $_REQUEST['userForm']['password']);
	$NewUser = libAuth::key_User($key);
	$NewUser->sendNotice('No','No',l_t("Welcome! This area displays your notices, which let you catch "
		."up with what has happened since you were last here"));

	$NewUser->options->set($_REQUEST['userForm']);
	$NewUser->options->load();

	// Give user access to tutorial views for 365 days
	setcookie('wD-Tutorial', 'wD-Tutorial', ['expires'=>time()+60*60*24*365,'samesite'=>'Lax']);
	setcookie('wD-Tutorial-Index', 'wD-Tutorial-Index', ['expires'=>time()+60*60*24*365,'samesite'=>'Lax']);
	setcookie('wD-Tutorial-GameCreate', 'wD-Tutorial-GameCreate', ['expires'=>time()+60*60*24*365,'samesite'=>'Lax']);
	setcookie('wD-Tutorial-JoinNewGame', 'wD-Tutorial-JoinNewGame', ['expires'=>time()+60*60*24*365,'samesite'=>'Lax']);
	setcookie('wD-Tutorial-Settings', 'wD-Tutorial-Settings', ['expires'=>time()+60*60*24*365,'samesite'=>'Lax']);

	// libHTML does not like letting registered users access the registration page
	$User = new User(GUESTID);

	print libHTML::pageTitle(l_t('Register a webDiplomacy account'),l_t('Validate your e-mail address -&gt; Enter your account settings -&gt; <strong>Play webDiplomacy!</strong>'));

	print "<h3>".l_t("Welcome to webDiplomacy!")."</h3>
			<p>".l_t("Welcome, %s!",$SQLVars['username'])."<br /><br />

				".l_t("Before you start your conquest, please check out our <a href='intro.php' class='light'>Intro to Diplomacy</a> which explains the technical bits you’ll need to dominate every board.".
				"<br /><br /> Want to announce your arrival? Take a stop by our <a href='/contrib/phpBB3/' class='light'>forum</a>.".
				"<br /><br /> Looking for help? Check out the <a href='/contrib/phpBB3/viewtopic.php?f=6&t=40' class='light'>Mentor Program</a> for new players or the <a href='help.php' class='light'>Help/Info</a> page. ".
				"<br /><br /> Confident in yourself already? Well then, you can jump right into a <a href='gamelistings.php' class='light'>game</a> or <a href='gamecreate.php' class='light'>create your own</a>.")."<br /> ".
				l_t("<br /> Finally, don’t forget to bookmark the <a href='index.php' class='light'>Home Page</a>, ".
				"and give our <a href='rules.php' class='light'>Rules/info</a> a read through.")."<br /><br />

				".l_t("<font color='red'>If the moderator team needs to contact you they will use your listed email so please make sure to keep it up to date!</font>")."
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
