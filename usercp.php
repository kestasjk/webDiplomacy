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

// Staging test commit

require_once('header.php');

require_once(l_r('objects/mailer.php'));

// Test commit #2, on staging branch
global $Mailer;
$Mailer = new Mailer();

if(!$User->type['User'])
{
	libHTML::error(l_t("You can't use the user control panel, you're using a guest account."));
}

if( isset(Config::$auth0conf) )
{
	// Do login/logout before sending any other headers (though it does work anyway?)
	require_once('contrib/auth0.php');

	if( isset($_REQUEST['auth0Login']))
	{
		libOpenID::logIn();
	}
	else if( isset($_REQUEST['auth0Logout']) )
	{
		libOpenID::logOut();
	}
}

libHTML::starthtml();

if ( isset($_REQUEST['emailToken']))
{
	libAuth::formToken_Valid();

	if( !($email = libAuth::emailToken_email($_REQUEST['emailToken'])) )
		libHTML::notice(l_t("Email change validation"), l_t("A bad email token was given, please check the validation link try again"));

	$email = $DB->escape($email);

	if( User::findEmail($email) )
		libHTML::notice(l_t("Email change validation"), l_t("The email address '%s', is already in use. Please contact the moderators at %s for assistance.",$email, Config::$modEMail));

	$DB->sql_put("UPDATE wD_Users SET email='".$email."' WHERE id = ".$User->id);

	$User->email = $email;

	print '<div class="content"><p class="notice">'.l_t('Your e-mail address has been succesfully changed').'</p></div>';
}

if ( isset($_REQUEST['userForm']) )
{
	libAuth::formToken_Valid();
	
	$formOutput = '';

	try
	{
		$errors = array();
		$SQLVars = User::processForm($_REQUEST['userForm'], $errors);

		if( count($errors) )
			throw new Exception(implode('. ',$errors));

		unset($errors);

		$allowed = array('E-mail'=>'email','E-mail hiding'=>'hideEmail', 'Homepage'=>'homepage','Comment'=>'comment');

		$User->options->set($_REQUEST['userForm']);
		$User->options->load();

		$set = '';
		foreach( $allowed as $name=>$SQLName )
		{
			if ( ! isset($SQLVars[$SQLName]) or $User->{$SQLName} == $SQLVars[$SQLName] ) continue;

			if ( $SQLName == 'email' )
			{
				if( User::findEmail($SQLVars['email']) )
					throw new Exception(l_t("The e-mail address '%s', is already in use. Please choose another.",$SQLVars['email']));

				$Mailer->Send(array($SQLVars['email']=>$User->username), l_t('Changing your e-mail address'),
					l_t("Hello %s",$User->username).",<br><br>

					".l_t("You can use this link to change your account's e-mail address to this one:")."<br>
					".libAuth::email_validateURL($SQLVars['email'])."<br><br>

					".l_t("If you have any further problems contact the server's admin at %s.",Config::$adminEMail)."<br>
					".l_t("Regards,<br>The webDiplomacy Gamemaster")."<br>
					");

				$formOutput .= l_t('A validation e-mail was sent to the new address, containing a link which will confirm '.
					'the e-mail change. If you don\'t see it after a few minutes check your spam folder.');

				unset($SQLVars['email']);
				continue;
			}
			elseif( $SQLName == 'comment' )
			{
				if ( $User->{$SQLName} == $DB->msg_escape($SQLVars[$SQLName]) ) continue;
			}

			if ( $set != '' ) $set .= ', ';

			$set .= $SQLName." = '".$SQLVars[$SQLName]."'";
			$formOutput .= l_t('%s updated successfully.',$name).' ';
		}

		// Check if there are any changes to the opt-in features:
		if( isset(Config::$enabledOptInFeatures) && Config::$enabledOptInFeatures > 0 )
		{
			$previousOptInFeatures = $User->optInFeatures;
			for( $featureFlag=1; pow(2, $featureFlag) < Config::$enabledOptInFeatures; $featureFlag *= 2 )
			{
				if( ( $featureFlag & Config::$enabledOptInFeatures ) == 0 ) continue;

				if( key_exists('optInFeature_' . $featureFlag, $_REQUEST['userForm']) )
				{
					if( $_REQUEST['userForm']['optInFeature_' . $featureFlag ] == "1" )
						$User->optInFeatures = $User->optInFeatures | $featureFlag;
					else
						$User->optInFeatures = $User->optInFeatures & ~$featureFlag;
				}
			}
			if( $previousOptInFeatures != $User->optInFeatures )
			{
				if ( $set != '' ) $set .= ', ';
				$set .= "optInFeatures = " . $User->optInFeatures;
				$formOutput .= l_t('Optional feature set selection updated.').' ';
			}
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

			$formOutput .= l_t('Password updated successfully. You have been logged out and will need to login with the new password.').' ';
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

// settings page tutorial
if (isset($_COOKIE['wD-Tutorial-Settings'])) 
{
	$tutorialMessage = l_t('
		These are your account settings. You can update your registered email here, change your password,
		and add a comment to your profile. You can also adjust the game board for different types of 
		colorblindness, set your site theme to a light theme or high-contrast dark theme, choose whether you
		display upcoming live games on your home screen, and more. Please set your registered email to one
		that you regularly check if you did not already when you registered, as your account can be banned 
		if the moderators attempt to contact you by email but do not receive a reply.
	');

	libHTML::help('Settings', $tutorialMessage);

	unset($_COOKIE['wD-Tutorial-Settings']);
	setcookie('wD-Tutorial-Settings', '', time()-3600);
}

print libHTML::pageTitle(l_t('User account settings'),l_t('Control settings for your account.'));

print '
<div class = "settings">
<div class = "settings">This page allows you to update your profile settings. Your email address will never be spammed or given out, and is only used 
by the moderator team to contact you. Please ensure your email is updated so that the moderators can contact you. </br></br> 
If you select "no" for "Hide email address" your email will be displayed to other site users in an image file to protect you from bots. 
If you leave the default of "yes" it is only visible to moderators.</div></br>';

print '<form method="post" class = "settings_show" autocomplete="off"><ul class="formlist">';

require_once(l_r('locales/English/user.php'));

print '</div>';

if( isset(Config::$auth0conf) )
{
	print libHTML::pageTitle(l_t('External authentication / verification providers (Experimental)'),l_t('Help fight cheaters and improve security by linking to your external accounts.'));
	print '<div class="settings">';
	print '<a name="externalAuth"></a>';
	print '<p>webDiplomacy is trialing support for external sources of user authentication / verification. By linking to ';
	print 'an external provider you are making things easier for the webDiplomacy moderator team, and harder for cheaters, as well ';
	print 'as allowing a more modern user registration/authentication experience.</p>';
	print '<p>The data a linked account provides is only your publically available information, and the relationship between your webDiplomacy ';
	print 'and external accounts is only viewable by the mod team for account verification purposes.</p>';
	print '<p>Currently Facebook and Google authentication is supported, with Apple and SMS support coming soon.</p>';

	$userInfo = libOpenID::getUserInfo();
	//print str_replace("\n","<br />", print_r($userInfo,true));
	
	if( $userInfo )
	{
		$sql = "INSERT INTO wD_UserOpenIDLinks ( userId, source, timeCreated, timeUpdated, ";

		$addedCols = array();
		$addedVals = array();
		foreach(libOpenID::$validColumns as $col)
		{
			if( isset($userInfo[$col]) )
			{
				$addedCols[] = $col;
				$addedVals[] = $DB->msg_escape($userInfo[$col]);
			}
		}

		if( count($addedCols) > 0 )
		{
			
			if( isset($userInfo['sub']) )
			{
				if( false!==strstr($userInfo['sub'], 'facebook') )
					$source = 'facebook';
				elseif( false!==strstr($userInfo['sub'], 'google') )
					$source = 'google';
				else
					$source = 'unknown';
			}

			if( $source == 'unknown' )
			{
				throw new Exception("Unknown source of authentication info; rejected.");
			}

			$sql .= "`".implode('`, `', $addedCols)."`";
			$sql .= ') VALUES (';
			$sql .= $User->id;
			$sql .= ", '";
			$sql .= $source;
			$sql .= "', ";
			$sql .= time();
			$sql .= ', ';
			$sql .= time();
			$sql .= ', ';
			$sql .= "'".implode("', '", $addedVals)."'";
			$sql .= ') ON DUPLICATE KEY UPDATE timeUpdated = VALUES(timeUpdated)';
			foreach($addedCols as $addedCol)
			{
				$sql .= ", `" . $addedCol . "` = VALUES(`" . $addedCol . "`)";
			}

			$DB->sql_put($sql);
			$DB->sql_put("COMMIT");
		}
	}

	$validSources = array('facebook'=>false, 'google'=>false, 'sms'=>false);

	$registeredSources = $DB->sql_tabl("SELECT source, sub FROM wD_UserOpenIDLinks WHERE userId = " . $User->id);
	while(list($source, $sub) = $DB->tabl_row($registeredSources) )
	{
		if( isset($validSources[$source]) )
		{
			$validSources[$source] = $sub;
		}
	}

	print '<h4>Current links:</h4>';
	print '<ul>';
	foreach($validSources as $source=>$sub)
	{
		print '<li><strong>'.$source.':</strong> ';
		if( $sub === false )
		{
			print 'Not linked';
		}
		else
		{
			print 'Linked, ID='.$sub;
		}
		print '</li>';
	}
	print '</ul>';
	
	print '<h4>Link an account:</h4>';
	print '<p>To link an external account simply use the links below to authenticate yourself, and the external provider will ';
	print 'return a token verifying that you have an account with that provider.<br />To link multiple accounts simply use the ';
	print 'log out button to log out of one external provider, then use the log in link to log into a secondary external provider.</p>';
	
	print '<p class="notice" style="text-align:center">';
	if( $userInfo )
	{
		print '<a href="usercp.php?auth0Logout=on">Log out from external provider</a>';
	}
	else
	{
		print '<a href="usercp.php?auth0Login=on">Log into an external provider</a>';
	}
	print '</p>';
	print '</div>';
}

libHTML::$footerIncludes[] = l_j('help.js');
libHTML::footer();

?>
