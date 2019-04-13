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

require_once(l_r('objects/mailer.php'));
global $Mailer;
$Mailer = new Mailer();

if ( $Misc->Panic )
{
	libHTML::notice(l_t('Registration disabled'),
		l_t("Registration has been temporarily disabled while we take care of an ".
		"unexpected problem. Please try again later, sorry for the inconvenience."));
}

// The user must be guest to register a new account
if( $User->type['User'] )
{
	libHTML::error(l_t("You're attempting to create a ".
		"new user account when you already have one. Please use ".
		"your existing user account."));
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
			throw new Exception(l_t("A bad anti-script code was given, please try again"));


		list($Hash, $Time) = $imageToken;

		if ( md5(Config::$secret.$_REQUEST['imageText'].$_SERVER['REMOTE_ADDR'].$Time) != $Hash )
		{
			throw new Exception(l_t("An invalid anti-script code was given, please try again"));
		}
		elseif( (time() - 3*60) > $Time)
		{
			throw new Exception(l_t("This anti-script code has expired, please submit it within 3 minutes"));
		}


		// The user's imageText is validated; he's not a robot. But does he have a real e-mail address?
		$email = trim($DB->escape($_REQUEST['emailValidate']));

		if( User::findEmail($email) )
			throw new Exception(
				l_t("The e-mail address '%s', is already in use. If this is your e-mail, please use the Forgot your username and password features to recover your account or contact the moderators at %s for assistance. Making a second account for any reason is against the site rules.",$email, Config::$modEMail));

		if ( !libAuth::validate_email($email) )
			throw new Exception(l_t("A first check of this e-mail is finding it invalid. Remember you need one to ".
				"play, and it will not be spammed or released."));

		// Prelim checks look okay, lets send the e-mail
		$Mailer->Send(array($email=>$email), l_t('Your new webDiplomacy account'),
l_t("Hello and welcome!")."<br><br>

".l_t("Thanks for validating your e-mail address; just use this link to create your new webDiplomacy account:")."<br>
".libAuth::email_validateURL($email)."<br><br>

".l_t("There are two main rules that we want you to be aware of:")."<br>

".l_t("1. No Multi-Accounting")."<br><br>

".l_t("You may only have one account, second accounts are not allowed under any circumstances, and will be banned. This may also lead to your first account also being banned.  If you forget your password, use the lost password finder here: http://www.webdiplomacy.net/logon.php?forgotPassword=1. If you are still unable to log in, contact the mods.")."<br><br>

".l_t("2. No Meta-gaming")."<br><br>

".l_t("You cannot play a public game with players that you know outside of the site. In doing so, you create an unfair environment for other players by giving yourself the opportunity to form alliances for reasons outside the game. This includes playing public games with family, friends, relatives, coworkers, or even joining a game with any player of a previous game with a predetermined intent to ally with or attack certain players.")."<br><br>

".l_t("Because Diplomacy is a social game, we always encourage playing with friends. However, you should always do so in a private, password-protected game and make sure that every player knows about any real life connections before the game begins.")."<br><br>

".l_t("The rest of the rules can be found here: http://www.webdiplomacy.net/rules.php")."<br><br>
".l_t("If you have any further problems contact the server's admin at %s.",Config::$adminEMail)."<br><br>

".l_t("Enjoy your new account!")."<br>
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
			throw new Exception(l_t("A bad e-mail token was given, please try again"));

		$email = trim($DB->escape($email));

		$page = 'userForm';

		// The user's e-mail is authenticated; he's not a robot and he has a real e-mail address
		// Let him through to the form, or process his form if he has one
		if ( isset($_REQUEST['userForm']) )
		{
			$_REQUEST['userForm']['email'] = $email;

			// If the form is accepted the script will end within here.
			// If it isn't accepted they will be shown back to the userForm page
			require_once(l_r('register/processUserForm.php'));
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
		print libHTML::pageTitle(l_t('Register a webDiplomacy account'),l_t('<strong>Validate your e-mail address</strong> -&gt; Enter your account settings -&gt; Play webDiplomacy!'));
		break;

	case 'emailSent':
	case 'emailTokenFailed':
	case 'firstUserForm':
	case 'userForm':
		print libHTML::pageTitle(l_t('Register a webDiplomacy account'),l_t('Validate your e-mail address -&gt; <strong>Enter your account settings</strong> -&gt; Play webDiplomacy!'));
}

switch($page)
{
	case 'firstValidationForm':

		print '<h2>'.l_t('Welcome to webDiplomacy!').'</h2>';
		print '<p>'.l_t('We are a competitive community looking for fair and fun games; to ensure you are a human with a working email address please fill out the registration form below. Help us keep the server free of spam and cheaters!').'</p>';

		print '<h2>'.l_t('Site User Agreement (We aren’t Apple™, so please read this.)').'</h2>';
		print '<p>'.l_t('I agree not to create more than one account.<br /> '.
		'I agree not to work around game communication rules.<br /> '.
		'I agree not to make alliances based on out of game relationships.<br /> '.
		'I agree not to play public games with family, or friends.<br /> '.
		'I agree to treat all members with respect regardless of race, religion, gender, or creed.<br /><br /> '.

		'If you can agree to these values and adhere to our site rules, you are welcome here!').'</p>';
		
	case 'validationForm':

		require_once(l_r('locales/English/validationForm.php'));

		break;

	case 'emailSent':

		print '<h3>'.l_t('Anti-bot Validation - Confirmed!').'</h3>';
		print "<p>".l_t("Okay, now that we know you're a human we need to check that you have a real e-mail address.")."</p>";

		print '<div class="hr"></div>';
		print '<h3>'.l_t('E-mail Validation').'</h3>';
		print l_t("An e-mail has been sent to the address you provided (<strong>%s</strong>) ".
			"with a link that you can click on to confirm that it's your real e-mail address, and then you're ".
			"ready to go!",htmlentities($_REQUEST['emailValidate']))."</p>";

		print "<p>".l_t("The e-mail may take a couple of minutes to arrive; if it doesn't appear check your spam folder.")."</p>";

		print '<p>'.l_t('If you have problems e-mail this server\'s admin at %s',Config::$adminEMail).'</p>';

		break;

	case 'emailTokenFailed':
		print '<p>'.l_t('The e-mail token you provided was not accepted; please go back to the e-mail you were sent and '.
			'check that you visited the exact URL given.').'</p>';
		print '<p>'.l_t('If the e-mail did not arrive check your spam box. If you are sure you haven\'t received it and that '.
			'you have waited long enough for it try going through the registration process from the start.').'<br /><br />

			'.l_t('If it still fails e-mail this server\'s admin at %s',Config::$adminEMail).'</p>';
		break;

	case 'firstUserForm':

		print '<h3 style = "margin-left: 0px;">'.l_t('E-mail address confirmed!').'</h3>';

		print "<p>".l_t("Thank you for verifying your e-mail address!</p>
			<p>Enter the username, password, and any of the optional settings you want into the screen below to
			complete the registration process.")." </br></br><font color='red'>Your username is visible to other members so please keep it appropiate and 
			and avoid using your full name if you are concerned about privacy.</font></p>";

	case 'userForm':
		print '<form method="post" class = "settings_show" autocomplete="off"><ul class="formlist">';
		print '<input type="hidden" name="emailToken" value="'.$_REQUEST['emailToken'].'" />';

		require_once(l_r('locales/English/userRegister.php'));
		require_once(l_r('locales/English/user.php'));

		print '</div>';

		break;
}

print '</div>';
libHTML::footer();

?>
