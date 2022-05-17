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
 * @subpackage Static
 */

require_once('header.php');

libHTML::starthtml();
print libHTML::pageTitle(l_t('Donations'),l_t('Learn how to donate and what your donations are used for.'));

if( isset($_GET['thank-you']) )
{
	print "
<div class='donations'>
    <div class = 'donations_title'>Thank you!</div>
    <div class = 'donations_content'>
        <p>
            Thank you for your donation! Donations help to keep the server up and running for all users, and with fees of only hundreds of USD per year every donation helps keep the server running for free, for everyone.<br />
	    Thanks again! It may take some time for the PayPal receipt to be registered by the server, please contact the team if you do not receive a marker.
        </p>
    </div>
    </div>";
}
else
{
	print "
<div class='donations'>
    <div class = 'donations_title'>Why do you need donations?</div>
    <div class = 'donations_content'>
        <p>
            webDiplomacy offers you the chance to play Diplomacy online free of charge. Our goal is to always be the best place for you to play the game of diplomacy online without 
            ever bothering you with ads, charging you for extra features, or charging you to play games. To do this we need occasional support from you. 
        </p>
    </div>

    <div class = 'donations_title'>How do you use my donation?</div>
    <div class = 'donations_content'>
        <p>
           Donations are mainly used to pay for the cost of the server, domain, and maintenance costs. The owners, admins, and moderators do not take a salary and never will. 
           Leftover funds may be used for development of features such as our email service to confirm new members in bulk. 
           Your contribution is only ever used for the betterment of webDiplomacy.
        </p>
    </div>

    <div class = 'donations_title'>Why don't you get ad revenue, sell user data, or charge for premium features?</div>
    <div class = 'donations_content'>
        <p>
           We do not believe that it is right to distract you with obnoxious advertisements, sell your private data to third parties, or charge you for extra features. We are an 
           entirely free, safe-to-use site and always will be.
        </p>
    </div>

    <div class = 'donations_title'>What do I get for donating?</div>
    <div class = 'donations_content'>
        <p>
           We will give you a marker on your profile and on the forum to signify your support for the site. However we never want our members to feel obligated to contribute so no 
           functionality is locked behind a paywall, nor do we ever discriminate in any way against those who have not donated. 
        </p>
    </div>

    <div class = 'donations_title'>Do I have to donate?</div>
    <div class = 'donations_content'>
        <p>
           No, donations are optional. We will never require a user to donate in order to gain any perks, play more games, or enjoy our full site.
        </p>
    </div>

    <div class = 'donations_title'>How do I donate?</div>
    <div class = 'donations_content_show' style='display:block'>
        <p>
            If you would like to support the site, click the button below. You will receive a donation marker so that other users are aware that you are helping to keep the site we all enjoy up and running.</br>
		<form action='https://www.paypal.com/donate' method='post' target='_top'>
		<input type='hidden' name='hosted_button_id' value='EDEQ2R6ZJK5GW' />
		<input type='hidden' name='custom' value='".$User->id."' />
		<input type='image' src='https://www.paypalobjects.com/en_AU/i/btn/btn_donate_LG.gif' border='0' name='submit' title='PayPal - The safer, easier way to pay online!' alt='Donate with PayPal button' />
		<img alt='' border='0' src='https://www.paypal.com/en_AU/i/scr/pixel.gif' width='1' height='1' />
		</form>
        </p>
    </div>
</div>";
}
?>
</div>

<script type="text/javascript">
   var coll = document.getElementsByClassName("donations_title");
   var searchCounter;
   
   for (searchCounter = 0; searchCounter < coll.length; searchCounter++) {
     coll[searchCounter].addEventListener("click", function() {
       this.classList.toggle("active");
       var content = this.nextElementSibling;
   		if (content.style.display === "block") { content.style.display = "none"; } 
   		else { content.style.display = "block"; }
     });
   }
</script>

<?php
libHTML::footer();
?>
