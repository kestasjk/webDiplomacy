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

if ( $Misc->Panic )
{
	libHTML::notice('Registration disabled',
		"Registration has been temporarily disabled while we take care of an
		unexpected problem. Please try again later, sorry for the inconvenience.");
}

// The user must be guest to register a new account
if( $User->type['User'] )
{
	libHTML::error("You're attempting to create a
		new user account when you already have one. Please use
		your existing user account.");
}

libHTML::starthtml();

$page = 'firstValidationForm';

if ( isset($_COOKIE['imageToken']) && isset($_REQUEST['imageText']) && isset($_REQUEST['emailValidate']) )
{
	try
	{
		// Validate and send e-mail
		$imageToken = explode('|', $_COOKIE['imageToken'], 2);

		if ( count($imageToken) != 2 )
			throw new Exception("A bad anti-script code was given, please try again");


		list($Hash, $Time) = $imageToken;

		if ( md5(Config::$secret.$_REQUEST['imageText'].$_SERVER['REMOTE_ADDR'].$Time) != $Hash )
		{
			throw new Exception("An invalid anti-script code was given, please try again");
		}
		elseif( (time() - 3*60) > $Time)
		{
			throw new Exception("This anti-script code has expired, please submit it within 3 minutes");
		}


		// The user's imageText is validated; he's not a robot. But does he have a real e-mail address?
		$email = $DB->escape($_REQUEST['emailValidate']);

		if( User::findEmail($email) )
			throw new Exception(
				"The e-mail address '".$email."', is already in use. Please choose another.");

		if ( !libAuth::validate_email($email) )
			throw new Exception("A first check of this e-mail is finding it invalid. Remember you need one to
				play, and it will not be spammed or released.");

		// Prelim checks look okay, lets send the e-mail
		$Mailer->Send(array($email=>$email), 'Your new webDiplomacy account',
"Hello and welcome!<br><br>

Thanks for validating your e-mail address; just use this link to create your new webDiplomacy account:<br>
".libAuth::email_validateURL($email)."<br><br>

If you have any further problems contact the server's admin at ".Config::$adminEMail.".<br><br>

Enjoy your new account!<br>
The webDiplomacy Gamemaster<br>
"
			);

		$page = 'emailSent';
	}
	catch(Exception $e)
	{
		print '<div class="content">';
		print '<p class="notice">'.$e->getMessage().'</p>';
		print '</div>';

		$page = 'validationForm';
	}
}
elseif ( isset($_REQUEST['emailToken']) )
{
	try
	{
		if( !($email = libAuth::emailToken_email($_REQUEST['emailToken'])) )
			throw new Exception("A bad e-mail token was given, please try again");

		$email = $DB->escape($email);

		$page = 'userForm';

		// The user's e-mail is authenticated; he's not a robot and he has a real e-mail address
		// Let him through to the form, or process his form if he has one
		if ( isset($_REQUEST['userForm']) )
		{
			$_REQUEST['userForm']['email'] = $email;

			// If the form is accepted the script will end within here.
			// If it isn't accepted they will be shown back to the userForm page
			require_once('register/processUserForm.php');
		}
		else
		{
			$_REQUEST['userForm']=array('email' => $email);

			$page = 'firstUserForm';
		}
	}
	catch( Exception $e)
	{
		print '<div class="content">';
		print '<p class="notice">'.$e->getMessage().'</p>';
		print '</div>';

		$page = 'emailTokenFailed';
	}
}

switch($page)
{
	case 'firstValidationForm':
	case 'validationForm':
		print libHTML::pageTitle('Register a webDiplomacy account','<strong>Validate your e-mail address</strong> -&gt; Enter your account settings -&gt; Play webDiplomacy!');
		break;

	case 'emailSent':
	case 'emailTokenFailed':
	case 'firstUserForm':
	case 'userForm':
		print libHTML::pageTitle('Register a webDiplomacy account','Validate your e-mail address -&gt; <strong>Enter your account settings</strong> -&gt; Play webDiplomacy!');
}

switch($page)
{
	case 'firstValidationForm':

		print '<h2>Welcome to webDiplomacy!</h2>';
		print '<p>So that we can all enjoy fun, fair games we need to quickly double check that
				you\'re a human and that you have an e-mail address. It only takes a moment
				and it keeps the server free of spam and cheaters! :-)</p>';

	case 'validationForm':

		require_once('locales/'.$User->locale.'/validationForm.php');

		break;

	case 'emailSent':

		print '<h3>Anti-bot Validation - Confirmed!</h3>';
		print "<p>Okay, now that we know you're a human we need to check that you have a real
			e-mail address.</p>";

		print '<div class="hr"></div>';
		print '<h3>E-mail Validation</h3>';
		print "An e-mail has been sent to the address you provided (<strong>".htmlentities($_REQUEST['emailValidate'])."</strong>)
			with a link that you can click on to confirm that it's your real e-mail address, and then you're
			ready to go!</p>";

		print "<p>The e-mail may take a couple of minutes to arrive; if it doesn't appear check your spam inbox.</p>";

		print '<p>If you have problems e-mail this server\'s admin at '.Config::$adminEMail.'</p>';

		break;

	case 'emailTokenFailed':
		print '<p>The e-mail token you provided was not accepted; please go back to the e-mail you were sent and
			check that you visited the exact URL given.</p>';
		print '<p>If the e-mail did not arrive check your spam box. If you are sure you haven\'t received it and that
			you have waited long enough for it try going through the registration process from the start.<br /><br />

			If it still fails e-mail this server\'s admin at '.Config::$adminEMail.'</p>';
		break;

	case 'firstUserForm':

		print '<h3>E-mail address confirmed!</h3>';

		print "<p>Alright; you're a human with an e-mail address!</p>
			<p>Enter the username and password you want, and any of the optional details/settings, into the screen below to
			complete the registration process.</p>";

	case 'userForm':
		print '<form method="post"><ul class="formlist">';
		print '<input type="hidden" name="emailToken" value="'.$_REQUEST['emailToken'].'" />';

		require_once('locales/'.$User->locale.'/userRegister.php');
		require_once('locales/'.$User->locale.'/user.php');

		break;
}

print '</div>';
libHTML::footer();

?>