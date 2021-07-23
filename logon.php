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

// Log-offs and log-ons are handled within header; here the form is presented, and forgotten passwords recovered

libHTML::starthtml();

if( isset($_REQUEST['forgotPassword']) and $User->type['Guest'] )
{
	print libHTML::pageTitle(l_t('Reset your password/find lost username'),l_t('Get back into your account!'));

	try
	{
		if ( $_REQUEST['forgotPassword'] == 1 )
		{
			print '<p> <strong>Forgot your username?</strong></br> Search for it by the email you registered with <a href="profile.php">here.</a> If you cannot find it email 
			the moderator team at '.Config::$modEMail.' and they will help you get back into your account. <strong>Do not make a new account.</strong> </p>
			
			<p><strong>Forgot your password?</strong></br> Enter your username below and an e-mail will be sent to the email you registered containing an '.
			'activation link that will set a new password. If you no longer have access to that email account email the moderator team at '.Config::$modEMail.'</p>

			<form action="./logon.php?forgotPassword=2" method="post">
				<strong>'.l_t('Username').'</strong>
				<input type="text" tabindex="1" maxlength=30 size=15 class="login" name="forgotUsername"></br></br>
				<input type="submit" class="green-Submit" value="Reset Password">
			</form>';
		}
		elseif ( $_REQUEST['forgotPassword'] == 2 && isset($_REQUEST['forgotUsername']) )
		{
			try {
				$forgottenUser = new User(0,$DB->escape($_REQUEST['forgotUsername']));
			} catch(Exception $e) {
				throw new Exception(l_t("Cannot find an account for the given username, please ".
					"<a href='logon.php?forgotPassword=1' class='light'>go back</a> and check your spelling."));
			}

			if( $MC->get('forgot_'.$forgottenUser->id) !== false )
			{
				throw new Exception(l_t("To help prevent abuse please wait 5 minutes before resending forgotten e-mail recovery links. ".
					"In the meantime please check your spam folder for a missing recovery e-mail, or contact the moderator team."));
			} 
			
			$MC->set('forgot_'.$forgottenUser->id, 5*60); // Set a flag preventing resends for 5 minutes

			require_once(l_r('objects/mailer.php'));
			$Mailer = new Mailer();
			$Mailer->Send(array($forgottenUser->email=>$forgottenUser->username), l_t('webDiplomacy forgotten password verification link'),
			l_t("You can use this link to get a new password generated:")."<br>
			".libAuth::email_validateURL($forgottenUser->email)."&forgotPassword=3<br><br>

			".l_t("If you have any further problems contact the moderator team at %s.",Config::$modEMail)."<br>");

			print '<p>'.l_t('An email has been sent with a reset link, click that link and enter a new password. '.
				'If you do not see the email check your spam folder.').'</p>';
		}
		elseif ( $_REQUEST['forgotPassword'] == 3 && isset($_REQUEST['emailToken']) )
		{
			$validatedEmail = libAuth::emailToken_email($_REQUEST['emailToken']);
			if( $validatedEmail === false )
				throw new Exception(l_t("Account not found"));
			
			$email = $DB->escape();
			$userID = User::findEmail($email);
			if( $userID == 0 )
				throw new Exception(l_t("Account not found"));
				
			$newPassword = base64_encode(rand(1000000000,2000000000));

			$DB->sql_put("UPDATE wD_Users
				SET password=UNHEX('".libAuth::pass_Hash($newPassword)."')
				WHERE id=".$userID." LIMIT 1");

			print '<p>'.l_t('Thanks for verifying your email, this is your new password, which you can '.
					'change once you have logged back on:').'<br /><br />

				<strong>'.$newPassword.'</strong></p>

				<p><a href="logon.php" class="light">'.l_t('Back to log-on prompt').'</a></p>';
		}
	}
	catch(Exception $e)
	{
		print '<p class="notice">'.$e->getMessage().'</p>';
	}

	print '</div>';
	libHTML::footer();
}

if( ! $User->type['User'] ) 
{
	print libHTML::pageTitle(l_t('Log on'),l_t('Enter your webDiplomacy account username and password to log into your account.'));
	print '
		<div class = "login">
		<form action="./index.php" method="post">

			<strong>Username</strong>
			<input type="text" tabindex="1" maxlength=30 size=15 class="login" name="loginuser"></br></br>

			<strong>Password</strong>
			<input type="password" tabindex="2" maxlength=30 size=15 class="login" name="loginpass"></br></br>

			<strong>Remember me</strong>
			<input type="checkbox" />
			<div class="loginDesc">Do you want to stay logged in permanently? Do not use on a public computer!</div></br>

			<input type="submit" class="green-Submit" value="Log on">
		</form>
		<p><a href="logon.php?forgotPassword=1" class="light">Forgot your username or password?</a></p>
		<p><a href="register.php" class="light">Not a member? Register!</a></p>
		</div>';
} 
else 
{
	print libHTML::pageTitle('Log off','Log out of your webDiplomacy account, to prevent other users of this computer accessing it.');
	print '<form action="./logon.php" method="get">
		<p class="notice"><input type="hidden" name="logoff" value="on">
		<input type="submit" class="form-submit" value="'.l_t('Log off').'"></p>
		</form>';
}

print '</div>';
libHTML::footer();
?>
