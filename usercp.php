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
 * @subpackage Forms
 */

require_once('header.php');

require_once('objects/mailer.php');
global $Mailer;
$Mailer = new Mailer();

if(!$User->type['User'])
{
	libHTML::error("You can't use the user control panel, you're using a guest account.");
}

libHTML::starthtml();

if ( isset($_REQUEST['optout']) )
{
	if ( $_REQUEST['optout'] == 'on' && !$User->type['Donator'] )
	{
		libHTML::notice("Opt-out", "Are you sure you want to opt-out of Plura? It helps keep this place running and on
			most modern computers is barely noticable.<br />
			<form><input type='submit' class='form-submit' name='optout' value='Opt-out' /></form>");
	}
	elseif( $_REQUEST['optout'] == 'Opt-out' && !$User->type['Donator'] )
	{
		$DB->sql_put("UPDATE wD_Users SET type = CONCAT_WS(',',type,'Donator') WHERE id = ".$User->id);

		$User->type['Donator'] = true;

		libHTML::notice("Opt-out", "You've opted-out of running the Plura applet. If you decide to re-enable it
			later the <a href='faq.php' class='light'>FAQ</a> has a link to do so.");
	}
	elseif( $_REQUEST['optout'] == 'off' && $User->type['Donator'] )
	{
		libHTML::notice("Opt-out", "Would you like to opt back into running the Plura Java applet?<br />
			<form><input type='submit' class='form-submit' name='optout' value='Opt-in' /></form>");
	}
	elseif( $_REQUEST['optout'] == 'Opt-in' && $User->type['Donator'] )
	{
		$types = array();
		foreach($User->type as $type=>$isMember)
		{
			if ( $isMember && $type != 'Donator' ) $types[] = $type;
		}
		$types = implode(',',$types);

		$DB->sql_put("UPDATE wD_Users SET type = '".$types."' WHERE id = ".$User->id);

		$User->type['Donator'] = false;

		libHTML::notice("Opt-out", "You've decided to re-add the Plura applet, thanks! By running the Plura applet you
			help keep this server running.");
	}
}

if ( isset($_REQUEST['emailToken']))
{
	if( !($email = libAuth::emailToken_email($_REQUEST['emailToken'])) )
		libHTML::notice("E-mail change validation",
			"A bad e-mail token was given, please check the validation link try again");

	$email = $DB->escape($email);

	if( User::findEmail($email) )
		libHTML::notice("E-mail change validation",
			"The given e-mail address is already in use, please use a unique e-mail address");

	$DB->sql_put("UPDATE wD_Users SET email='".$email."' WHERE id = ".$User->id);

	$User->email = $email;

	print '<div class="content"><p class="notice">Your e-mail address has been succesfully changed</p></div>';
}

if ( isset($_REQUEST['userForm']) )
{
	$formOutput = '';

	try
	{
		$errors = array();

		$SQLVars = User::processForm($_REQUEST['userForm'], $errors);
		if( count($errors) )
			throw new Exception(implode('. ',$errors));

		unset($errors);

		$allowed = array('E-mail'=>'email','E-mail hiding'=>'hideEmail',
				'Locale'=>'locale','Homepage'=>'homepage','Comment'=>'comment');

		$set = '';
		foreach( $allowed as $name=>$SQLName )
		{
			if ( ! isset($SQLVars[$SQLName]) or $User->{$SQLName} == $SQLVars[$SQLName] )
				continue;

			if ( $SQLName == 'email' )
			{
				if( User::findEmail($SQLVars['email']) )
					throw new Exception("The e-mail address '".$SQLVars['email'].
								"', is already in use. Please choose another.");

				$Mailer->Send(array($SQLVars['email']=>$User->username), 'Changing your e-mail address',
"Hello ".$User->username.",<br><br>

You can use this link to change your account's e-mail address to this one:<br>
".libAuth::email_validateURL($SQLVars['email'])."<br><br>

If you have any further problems contact the server's admin at ".Config::$adminEMail.".<br>
Regards,<br>
The webDiplomacy Gamemaster<br>
");

				$formOutput .= 'A validation e-mail was sent to the new address, containing a link which will confirm '.
					'the e-mail change. If you don\'t see it after a few minutes check your spam folder.';

				unset($SQLVars['email']);
				continue;
			}
			elseif( $SQLName == 'comment' )
			{
				if ( $User->{$SQLName} == $DB->msg_escape($SQLVars[$SQLName]) )
					continue;
			}

			if ( $set != '' ) $set .= ', ';

			$set .= $SQLName." = '".$SQLVars[$SQLName]."'";

			$formOutput .= $name.' updated successfully. ';
		}

		if ( $set != '' )
		{
			$DB->sql_put("UPDATE wD_Users SET ".$set." WHERE id = ".$User->id);
		}

		if ( isset($SQLVars['password']) )
		{
			$DB->sql_put("UPDATE wD_Users SET password = ".$SQLVars['password']." WHERE id = ".$User->id);

			libAuth::keyWipe();
			header('refresh: 3; url=logon.php');

			$formOutput .= 'Password updated successfully; you have been logged out and '.
							'will need to logon with the new password. ';
		}
	}
	catch(Exception $e)
	{
		$formOutput .= $e->getMessage();
	}

	// We may have received no new data
	if ( $formOutput )
	{
		$User->load(); // Reload in case of a change

		print '<div class="content"><p class="notice">'.$formOutput.'</p></div>';
	}
}


print libHTML::pageTitle('User account settings','Alter the settings for your webDiplomacy user account; e.g. change your password/e-mail.');

print '<form method="post">
<ul class="formlist">';

require_once('locales/'.$User->locale.'/user.php');

print '</div>';
libHTML::footer();

?>
