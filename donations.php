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

print "
<div class='donations'>
    <div class = 'donations_title'>Why do we need donations?</div>
    <div class = 'donations_content'>
        <p>
            We offer the game Diplomacy free of charge. Our goal is to be the best place for you to play the game of diplomacy online without ever bothering you with ads. 
            To do this we need occasional support from you. 
        </p>
    </div>

    <div class = 'donations_title'>What do we use your donations for?</div>
    <div class = 'donations_content'>
        <p>
           Donations are mainly used to pay for the cost of the server. Leftover funds are used for development of new features and functionality. The owners, 
           admins, and moderators do not take a salary and never will. Your contribution is only ever used for the site.
        </p>
    </div>

    <div class = 'donations_title'>What do you get for donating?</div>
    <div class = 'donations_content'>
        <p>
           We will give you a marker on your profile and on the forum to signifiy your support for the site. 
           However we never want our members to feel obligated to contribute so no functionality is locked behind a paywall. 
        </p>
    </div>

    <div class = 'donations_title'>How do you donate?</div>
    <div class = 'donations_content_show'>
        <p>
            If you would like to support the site, click the button below. After submitting a donation, please send an email to a Co-owner at <a href='mailto:".Config::$adminEMail."' class='light'>".Config::$adminEMail."</a> with your username to receive a donator marker. </br>
            <form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
                <input type='hidden' name='cmd' value='_s-xclick'>
                <input type='image' src='https://www.paypal.com/en_US/i/btn/x-click-but21.gif' border='0' name='submit' alt='Make payments with PayPal - it's fast, free and secure!'>
                <img alt='' border='0' src='https://www.paypal.com/en_AU/i/scr/pixel.gif' width='1' height='1'>
                <input type='hidden' name='encrypted' value='-----BEGIN PKCS7-----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-----END PKCS7-----
                '>
            </form>
        </p>
    </div>

</div></div>";
?>

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