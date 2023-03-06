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

if(!$User->type['User'])
{
	libHTML::error(l_t("You can't use the user notifications panel, you're using a guest account."));
}

libHTML::starthtml();

print libHTML::pageTitle(l_t('Notifications setup'),l_t('Configure whether and how you want notifications to be sent to you.'));

require_once('objects/notifications.php');
//userIdentity::panel($PanelUser);

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
	if( $userInfo ) libOpenID::saveOpenIDData($userInfo);
	
	$validSources = libOpenID::getValidSources($userInfo);

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
	print '</div>';
}


libHTML::footer();
