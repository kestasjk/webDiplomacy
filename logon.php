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
	print libHTML::pageTitle(l_t('Reset your password'),l_t('Resetting passwords using your e-mail account, in-case you forgot your password.'));

	try
	{
		if ( $_REQUEST['forgotPassword'] == 1 )
		{
			print '<p>'.l_t('Enter your username here, and an e-mail will be sent to the address you registered with, with an '.
			'activation link that will set a new password.').'</p>

			<form action="./logon.php?forgotPassword=2" method="post">
				<ul class="formlist">
				<li class="formlisttitle">'.l_t('Username').'</li>
				<li class="formlistfield"><input type="text" tabindex="1" maxlength=30 size=15 name="forgotUsername"></li>
				<li class="formlistdesc">'.l_t('The webDiplomacy username of the account which you can\'t log in to.').'</li>
				<li><input type="submit" class="form-submit" value="'.l_t('Send code').'"></li>
				</ul>
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

			require_once(l_r('objects/mailer.php'));
			$Mailer = new Mailer();
			$Mailer->Send(array($forgottenUser->email=>$forgottenUser->username), l_t('webDiplomacy forgotten password verification link'),
l_t("You can use this link to get a new password generated:")."<br>
".libAuth::email_validateURL($forgottenUser->email)."&forgotPassword=3<br><br>

".l_t("If you have any further problems contact the server's admin at %s.",Config::$adminEMail)."<br>");

			print '<p>'.l_t('An e-mail has been sent with a verification link, which will allow you to have your password reset. '.
				'If you can\'t find the e-mail in your inbox try your junk folder/spam-box.').'</p>';
		}
		elseif ( $_REQUEST['forgotPassword'] == 3 && isset($_REQUEST['emailToken']) )
		{
			$email = $DB->escape(libAuth::emailToken_email($_REQUEST['emailToken']));

			$userID = User::findEmail($email);

			$newPassword = base64_encode(rand(1000000000,2000000000));

			$DB->sql_put("UPDATE wD_Users
				SET password=UNHEX('".libAuth::pass_Hash($newPassword)."')
				WHERE id=".$userID." LIMIT 1");

			print '<p>'.l_t('Thanks for verifying your address, this is your new password, which you can '.
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



if( ! $User->type['User'] ) {
	print libHTML::pageTitle(l_t('Log on'),l_t('Enter your webDiplomacy account username and password to log into your account.'));
	print '
		<form action="./index.php" method="post">

		<ul class="formlist">

		<li class="formlisttitle">'.l_t('Username').'</li>
		<li class="formlistfield"><input type="text" tabindex="1" maxlength=30 size=15 name="loginuser"></li>
		<li class="formlistdesc">'.l_t('Your webDiplomacy username. If you don\'t have one please '.
			'<a href="register.php" class="light">register</a>.').'</li>

		<li class="formlisttitle">'.l_t('Password').'</li>
		<li class="formlistfield"><input type="password" tabindex="2" maxlength=30 size=15 name="loginpass"></li>
		<li class="formlistdesc">'.l_t('Your webDiplomacy password.').'</li>

		<li class="formlisttitle">'.l_t('Remember me').'</li>
		<li class="formlistfield"><input type="checkbox" /></li>
		<li class="formlistdesc">'.l_t('Do you want to stay logged in permanently? '.
			'If you are on a public computer you should not stay logged on permanently!').'</li>

		<li><input type="submit" class="form-submit" value="'.l_t('Log on').'"></li>
		</ul>
		</form>
		<p><a href="logon.php?forgotPassword=1" class="light">'.l_t('Forgot your password?').'</a></p>
		<p><a href="profile.php" class="light">'.l_t('Forgot your username?').'</a></p>';
} else {
	print libHTML::pageTitle('Log off','Log out of your webDiplomacy account, to prevent other users of this computer accessing it.');
	print '<form action="./logon.php" method="get">
		<p class="notice"><input type="hidden" name="logoff" value="on">
		<input type="submit" class="form-submit" value="'.l_t('Log off').'"></p>
		</form>';
}

print '</div>';
libHTML::footer();
?>
