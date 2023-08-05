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
require_once('lib/sms.php');

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

$exception = null;

if ( ((isset($_COOKIE['imageToken']) && isset($_REQUEST['imageText'])) || isset($_REQUEST['recaptchaToken'])) && isset($_REQUEST['emailValidate']) )
{
	try
	{
		if(isset($_COOKIE['imageToken']) && isset($_REQUEST['imageText']))
		{
			// Validate using the built in easycaptcha, which is fairly easy to work around and has cookie issues with certain browser configs
			// Validate and send email
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
		}
		else if( isset($_REQUEST['recaptchaToken']) )
		{
			// Validate the given token using the CURL API
			//print 'Validating token: '.$_REQUEST['recaptchaToken'].'<br />';

			$ch = curl_init('https://recaptchaenterprise.googleapis.com/v1/projects/'.Config::$recaptchaProject.'/assessments?key='.Config::$recaptchaApiKey.'');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt($ch, CURLOPT_POST,           1 );
			curl_setopt($ch, CURLOPT_POSTFIELDS,     '{
	"event": {
		"token": "'.$_REQUEST['recaptchaToken'].'",
		"siteKey": "'.Config::$recaptchaSiteKey.'",
		"expectedAction": "LOGIN"
	}
	}' ); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json; charset=utf-8'
			));
			//print 'Executing request<br />';
			$res = curl_exec($ch);
			if ( ! ($res)) {
				$errno = curl_errno($ch);
				$errstr = curl_error($ch);
				curl_close($ch);
				throw new Exception("Error validating anti-bot token: [$errno] $errstr");
			}
			
			$info = curl_getinfo($ch);
			$http_code = $info['http_code'];
			if ($http_code != 200) {
				throw new Exception("Google responded with http code $http_code");
			}

			$responseData = json_decode($res, true);
			if( $responseData['tokenProperties']['valid'] === true )
			{
				
			}
			else
			{
				throw new Exception("Google responded with invalid token: " . $responseData['tokenProperties']['invalidReason']);
			}

			/*if( $responseData['riskAnalysis']['score'] )
			{
				//  $responseData['riskAnalysis']['reasons']
			}*/

			//print 'Got response: '.$res.'<br />';

			

			//print 'Got info: '.print_r($info, true).'<br />';

			curl_close($ch);

		}
		else
		{
			throw new Exception(l_t("No anti-bot token provided"));
		}

		if( !isset($_REQUEST['antiBotCountryID']) || !isset($_REQUEST['antiBotTerritoryIDs']) )
		{
			throw new Exception(l_t("No anti-bot country selection provided."));
		}
		$antiBotCountryID = (int)$_REQUEST['antiBotCountryID'];
		$antiBotTerritoryIDs = $_REQUEST['antiBotTerritoryIDs'];
		
		$tabl = $DB->sql_tabl("SELECT id FROM wD_Territories WHERE mapID = 1 AND countryID = ".$antiBotCountryID." AND supply='Yes' ORDER BY id");
		$validTerritoryIDs = array();
		while( $row = $DB->tabl_row($tabl) ) $validTerritoryIDs[] = $row[0];
		$validTerritoryIDs = implode(',', $validTerritoryIDs);
		if( $validTerritoryIDs !== $antiBotTerritoryIDs )
		{
			throw new Exception(l_t("The selected territories do not match the expected values. Please click on the supply center territories requested."));
		}

		// The user's imageText is validated; he's not a robot. But does he have a real email address?
		$email = trim($DB->escape($_REQUEST['emailValidate']));

		if( User::findEmail($email) )
			throw new Exception(
				l_t("The email address '%s', is already in use. If this is your email, please use the Forgot your username and password features to recover your account or contact the moderators at %s for assistance. Making a second account for any reason is against the site rules.",$email, Config::$modEMail));

		if ( !libAuth::validate_email($email) )
			throw new Exception(l_t("A first check of this email is finding it invalid. Remember you need one to ".
				"play, and it will not be spammed or released."));

		// Prelim checks look okay, lets send the email
		$Mailer->Send(array($email=>$email), l_t('Your new webDiplomacy account'),
			l_t("Hello and welcome!")."<br><br>

			".l_t("Thanks for validating your email! Use this link to create your webDiplomacy account: ").libAuth::email_validateURL($email)."<br><br>

			".l_t("There are two main rules to keep in mind:")."<br>
			".l_t("1. You may only have one account.")."<br>
			".l_t("2. You need to have an invitation code on any game you play with people you know from outside the site to keep games fair.")."<br>
			".l_t("The rest of the rules can be found here: http://www.webdiplomacy.net/rules.php")."<br><br>

			".l_t("Join the webDiplomacy community on Discord at https://discord.gg/dPm4QnY")."<br><br>

			".l_t("If you forgot your password, use the lost password finder here: http://www.webdiplomacy.net/logon.php?forgotPassword=1")."<br><br>
			".l_t("If you have any further problems contact the server's admin at %s.",Config::$adminEMail)."<br><br>

			".l_t("Enjoy your new account!")."<br>"
		);

		$page = 'emailSent';
	}
	catch(Exception $e)
	{
		$exception = $e;
		$page = 'validationForm';
	}
}
elseif ( isset($_REQUEST['emailToken']) )
{
	try
	{
		if( !($email = libAuth::emailToken_email($_REQUEST['emailToken'])) )
			throw new Exception(l_t("A bad email token was given, please try again"));

		$email = trim($DB->escape($email));

		$page = 'userForm';

		// The user's email is authenticated; he's not a robot and he has a real email address
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
		$exception = $e;
		$page = 'emailTokenFailed';
	}
}

switch($page)
{
	case 'firstValidationForm':
	case 'validationForm':
		print libHTML::pageTitle(l_t('Register a webDiplomacy account'),l_t('<strong>Validate your email address</strong> -&gt; Enter your account settings -&gt; Play webDiplomacy!'));
		break;

	case 'emailSent':
	case 'emailTokenFailed':
	case 'firstUserForm':
	case 'userForm':
		print libHTML::pageTitle(l_t('Register a webDiplomacy account'),l_t('Validate your email address -&gt; <strong>Enter your account settings</strong> -&gt; Play webDiplomacy!'));
}

// The exception is printed here so that it's below the title and easier to spot
if( !is_null($exception) )
{
	print '<p class="notice">'.$e->getMessage().'</p>';
	print '<p class="notice">Please contact <a href="mailto:admin@webdiplomacy.net">admin@webdiplomacy.net</a> if you are experiencing continuous issues registering an account.</p>';
	print '<div class="hr"></div>';
}

switch($page)
{
	case 'firstValidationForm':

		print '<h2>'.l_t('Welcome to webDiplomacy!').'</h2>';
		print '<p>'.l_t('We are a competitive community looking for fair and fun games; to ensure you are a human with a working email address, please fill out the registration form below. Help us keep the server free of spam and cheaters!').'</p>';

		/*
		print '<h2>'.l_t('Site User Agreement (We aren’t Apple™, so please read this.)').'</h2>';
		print '<p>'.l_t('I agree not to create more than one account.<br /> '.
		'I agree not to work around game communication rules.<br /> '.
		'I agree not to make alliances based on out-of-game relationships.<br /> '.
		'I agree not to play public games with family, or friends.<br /> '.
		'I agree to treat all members with respect regardless of race, religion, gender, or creed.<br /><br /> '.

		'If you can agree to these values and adhere to our site rules, you are welcome here!').'</p>';
		*/
		
	case 'validationForm':

		require_once(l_r('locales/English/validationForm.php'));

		break;

	case 'emailSent':

		print '<h3>'.l_t('Anti-bot Validation - Confirmed!').'</h3>';
		print "<p>".l_t("Now that we know you're a human, we need to check that you have a real email address.")."</p>";

		print '<div class="hr"></div>';
		print '<h3>'.l_t('Email Validation').'</h3>';
		print l_t("An email has been sent to the address you provided (<strong>%s</strong>) ".
			"with a link that you can click on to confirm that it's your real email address, and then you're ".
			"ready to go!",htmlentities($_REQUEST['emailValidate']))."</p>";

		print "<p>".l_t("The email may take a couple of minutes to arrive. If it doesn't appear, check your spam folder.")."</p>";

		print '<p>'.l_t('If you have problems, email this server\'s admin at %s',Config::$adminEMail).'</p>';

		break;

	case 'emailTokenFailed':
		print '<p>'.l_t('The email token you provided was not accepted; please go back to the email you were sent and '.
			'check that you visited the exact URL given.').'</p>';
		print '<p>'.l_t('If the email did not arrive, check your spam box. If you are sure you haven\'t received it and that '.
			'you have waited long enough for it try going through the registration process from the start.').'<br /><br />

			'.l_t('If it still fails email this server\'s admin at %s',Config::$adminEMail).'</p>';
		break;

	case 'firstUserForm':

		print '<h3 style = "margin-left: 0px;">'.l_t('Email address confirmed!').'</h3>';

		print "<p>".l_t("Thank you for verifying your email address!</p>
			<p>Enter the username, password, and any of the optional settings you want into the screen below to
			complete the registration process.")."</p>";
			//" </br></br><font color='darkred'>Your username is visible to other members and cannot be changed, so please make sure you're sure about it. Keep it appropriate and 
			//and avoid using your full name if you are concerned about privacy.</font>

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
