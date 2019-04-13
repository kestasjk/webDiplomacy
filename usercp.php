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

if(!$User->type['User'])
{
	libHTML::error(l_t("You can't use the user control panel, you're using a guest account."));
}

libHTML::starthtml();

if ( isset($_REQUEST['optout']) )
{
	if ( $_REQUEST['optout'] == 'on' && !$User->type['Donator'] )
	{
		libHTML::notice(l_t("Opt-out"), l_t("Are you sure you want to opt-out of Plura? It helps keep this place running and on ".
			"most modern computers is barely noticable.")."<br />
			<form><input type='submit' class='form-submit' name='optout' value='".l_t("Opt-out")."' /></form>");
	}
	elseif( $_REQUEST['optout'] == l_t('Opt-out') && !$User->type['Donator'] )
	{
		$DB->sql_put("UPDATE wD_Users SET type = CONCAT_WS(',',type,'Donator') WHERE id = ".$User->id);

		$User->type['Donator'] = true;

		libHTML::notice(l_t("Opt-out"), l_t("You've opted-out of running the Plura applet. If you decide to re-enable it ".
			"later the <a href='faq.php' class='light'>FAQ</a> has a link to do so."));
	}
	elseif( $_REQUEST['optout'] == 'off' && $User->type['Donator'] )
	{
		libHTML::notice(l_t("Opt-out"), l_t("Would you like to opt back into running the Plura Java applet?")."<br />
			<form><input type='submit' class='form-submit' name='optout' value='".l_t('Opt-in')."' /></form>");
	}
	elseif( $_REQUEST['optout'] == l_t('Opt-in') && $User->type['Donator'] )
	{
		$types = array();
		foreach($User->type as $type=>$isMember)
		{
			if ( $isMember && $type != 'Donator' ) $types[] = $type;
		}
		$types = implode(',',$types);

		$DB->sql_put("UPDATE wD_Users SET type = '".$types."' WHERE id = ".$User->id);

		$User->type['Donator'] = false;

		libHTML::notice(l_t("Opt-out"), l_t("You've decided to re-add the Plura applet, thanks! By running the Plura applet you ".
			"help keep this server running."));
	}
}

if ( isset($_REQUEST['emailToken']))
{
	if( !($email = libAuth::emailToken_email($_REQUEST['emailToken'])) )
		libHTML::notice(l_t("E-mail change validation"),
			l_t("A bad e-mail token was given, please check the validation link try again"));

	$email = $DB->escape($email);

	if( User::findEmail($email) )
		libHTML::notice(l_t("E-mail change validation"),
			l_t("The e-mail address '%s', is already in use. If this is your e-mail, please contact the moderators at %s for assistance.",$email, Config::$modEMail));

	$DB->sql_put("UPDATE wD_Users SET email='".$email."' WHERE id = ".$User->id);

	$User->email = $email;

	print '<div class="content"><p class="notice">'.l_t('Your e-mail address has been succesfully changed').'</p></div>';
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
				'Homepage'=>'homepage','Comment'=>'comment');

		$User->options->set($_REQUEST['userForm']);
		$User->options->load();

		$set = '';
		foreach( $allowed as $name=>$SQLName )
		{
			if ( ! isset($SQLVars[$SQLName]) or $User->{$SQLName} == $SQLVars[$SQLName] )
				continue;

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
				if ( $User->{$SQLName} == $DB->msg_escape($SQLVars[$SQLName]) )
					continue;
			}

			if ( $set != '' ) $set .= ', ';

			$set .= $SQLName." = '".$SQLVars[$SQLName]."'";

			$formOutput .= l_t('%s updated successfully.',$name).' ';
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

			$formOutput .= l_t('Password updated successfully; you have been logged out and '.
							'will need to logon with the new password.').' ';
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


print libHTML::pageTitle(l_t('User account settings'),l_t('Control settings for your account.'));

print '
<div class = "settings">
<div class = "settings">This page allows you to update your profile settings. Your email address will never be spammed or given out, and is only used 
by the moderator team to contact you. Please ensure your email is updated so that the moderators can contact you. </br></br> 
If you select "no" for "Hide email address" your email will be displayed to other site users in an image file to protect you from bots. 
If you leave the default of "yes" it is only visible to moderators.</div></br>
<form method="post" class = "settings_show" autocomplete="off">
<ul class="formlist">';

require_once(l_r('locales/English/user.php'));

print '</div>';
libHTML::footer();

?>
