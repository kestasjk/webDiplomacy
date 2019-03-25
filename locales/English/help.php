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

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * @package Base
 * @subpackage Static
 */

print libHTML::pageTitle('webDiplomacy Help and Links','Links to pages with more information about webDiplomacy and this installation.');
?>
<ul class="formlist">
<li> <strong><u>How to Donate</u></strong> </br>
webDiplomacy.net is run off of user donations. Because of this, we pledge never to show ads, or charge for features.
If you would like to support the site, click the button below. After submitting a donation, please send an email to a Co-owner at <a href="mailto:<?php print Config::$adminEMail; ?>" class="light"><?php print Config::$adminEMail; ?></a> with your username to receive a donator marker. </br>
<div style='text-align:left'>
<form action='https://www.paypal.com/cgi-bin/webscr' method='post'>
<input type='hidden' name='cmd' value='_s-xclick'>
<input type='image' src='https://www.paypal.com/en_US/i/btn/x-click-but21.gif' border='0' name='submit' alt='Make payments with PayPal - it's fast, free and secure!'>
<img alt='' border='0' src='https://www.paypal.com/en_AU/i/scr/pixel.gif' width='1' height='1'>
<input type='hidden' name='encrypted' value='-----BEGIN PKCS7-----MIIHPwYJKoZIhvcNAQcEoIIHMDCCBywCAQExggEwMIIBLAIBADCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwDQYJKoZIhvcNAQEBBQAEgYBi6sed9cshjepyWTUk4z8zoiXxuj4AB+OK8PbcKGh25OJatLEcze1trOsMMfPcPuZOooEA8b0u9GTCx/NHdAr8y8eGBUt3Kc+AbJ4X2Xw38k127Z+ALaNJLVQqGt40ZqvsB+3HDxIhuUrvmxfZzdFCy4K6p56H/H0u83mom4jX7DELMAkGBSsOAwIaBQAwgbwGCSqGSIb3DQEHATAUBggqhkiG9w0DBwQIi3YOupGPsg+AgZh46XEhxcGMM10w1teOBsoanqp8I/bFxZZVausZu2NAf8tfHHKZSgV/qs7qyiLcMkRYbcwgwAgOTtyni+XmHQACz5uPIjlu6/ogXGZTddOB6xygmGd2Wmb08W3Dv1BPknfUK1Oy4X6TKf7egXgYKAH68YD2hYyViYF/deOR+BZY2ULRLgra5hq7Tp90ss5kqWb+g1MGkjbiP6CCA4cwggODMIIC7KADAgECAgEAMA0GCSqGSIb3DQEBBQUAMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTAeFw0wNDAyMTMxMDEzMTVaFw0zNTAyMTMxMDEzMTVaMIGOMQswCQYDVQQGEwJVUzELMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxFDASBgNVBAoTC1BheVBhbCBJbmMuMRMwEQYDVQQLFApsaXZlX2NlcnRzMREwDwYDVQQDFAhsaXZlX2FwaTEcMBoGCSqGSIb3DQEJARYNcmVAcGF5cGFsLmNvbTCBnzANBgkqhkiG9w0BAQEFAAOBjQAwgYkCgYEAwUdO3fxEzEtcnI7ZKZL412XvZPugoni7i7D7prCe0AtaHTc97CYgm7NsAtJyxNLixmhLV8pyIEaiHXWAh8fPKW+R017+EmXrr9EaquPmsVvTywAAE1PMNOKqo2kl4Gxiz9zZqIajOm1fZGWcGS0f5JQ2kBqNbvbg2/Za+GJ/qwUCAwEAAaOB7jCB6zAdBgNVHQ4EFgQUlp98u8ZvF71ZP1LXChvsENZklGswgbsGA1UdIwSBszCBsIAUlp98u8ZvF71ZP1LXChvsENZklGuhgZSkgZEwgY4xCzAJBgNVBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEUMBIGA1UEChMLUGF5UGFsIEluYy4xEzARBgNVBAsUCmxpdmVfY2VydHMxETAPBgNVBAMUCGxpdmVfYXBpMRwwGgYJKoZIhvcNAQkBFg1yZUBwYXlwYWwuY29tggEAMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcNAQEFBQADgYEAgV86VpqAWuXvX6Oro4qJ1tYVIT5DgWpE692Ag422H7yRIr/9j/iKG4Thia/Oflx4TdL+IFJBAyPK9v6zZNZtBgPBynXb048hsP16l2vi0k5Q2JKiPDsEfBhGI+HnxLXEaUWAcVfCsQFvd2A1sxRr67ip5y2wwBelUecP3AjJ+YcxggGaMIIBlgIBATCBlDCBjjELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3VudGFpbiBWaWV3MRQwEgYDVQQKEwtQYXlQYWwgSW5jLjETMBEGA1UECxQKbGl2ZV9jZXJ0czERMA8GA1UEAxQIbGl2ZV9hcGkxHDAaBgkqhkiG9w0BCQEWDXJlQHBheXBhbC5jb20CAQAwCQYFKw4DAhoFAKBdMBgGCSqGSIb3DQEJAzELBgkqhkiG9w0BBwEwHAYJKoZIhvcNAQkFMQ8XDTA3MTAzMTAxMTQwM1owIwYJKoZIhvcNAQkEMRYEFEJoQbGsedBhJvJfw3plhkh6GQm2MA0GCSqGSIb3DQEBAQUABIGAljgakViNAh9zew4Nn/cpAwKHhDs8LxIbpNbrQRkvnfnyg4gPtkzp1ie5qi7DBMOT0pX26qM41oQ+sywaU/wmKX9sqwPYvqcESjU2B8ZKGJFxt5ZQyJD3FmgWieifuokWQUCNJSKvReuUVzT/jO49/lw4x6JJkNVJTRKn1BMw4Gs=-----END PKCS7-----
'>
</form></div> </br>
</li>

<li><a href="https://docs.google.com/document/d/1dSq6zlizecb90F3OKSqyFUWGI32Or87ZNmkFmhCVz-c/edit">Mentor-Apprentice Program (External Site)</a></li>
<li class="formlistdesc">This is a program to allow new members to receive advice and guidance on the game from our more experienced members. </br>
If you are a new member and want a mentor <a href="https://goo.gl/forms/tHxJMZQ7mKgjAtEO2">click here</a> to sign up. </br>
If you would like to mentor a new member please <a href="https://goo.gl/forms/XdMDvxfxdQQQJjDI3">click here</a> to sign up. </br>
Any questions on this program can be directed to webdipmentors@gmail.com.
</li>

<li><a href="rules.php">Rulebook/Moderator Policies</a></li>
<li class="formlistdesc">The webDiplomacy rulebook. Moderator policies.</li>

<li><a href="contactUs.php">Contacting the Mods</a></li>
<li class="formlistdesc">Need help? Moderator and Owner emails.</li>
 
<li><a href="intro.php">The intro to Diplomacy</a></li>
<li class="formlistdesc">An introduction to playing webDiplomacy. Gives details on unit types, move types, and the basic rules of webDiplomacy.</li>

<li><a href="faq.php">FAQ</a></li>
<li class="formlistdesc">The webDiplomacy FAQ (Frequently asked questions) and information on donating.</li>

<li><a href="profile.php">Find a user</a></li>
<li class="formlistdesc">Search for a user account registered on this server if you know their user ID number, username, or e-mail address.</li>

<li><a href="tournaments.php">Tournaments</a></li>
<li class="formlistdesc">Tournament and Special Game rules and how to start one.</li>

<li><a href="halloffame.php">Hall of fame</a></li>
<li class="formlistdesc">The pros of this server; the top 100 by points!</li>

<li><a href="points.php">webDiplomacy points</a></li>
<li class="formlistdesc">What points are for, how to win them, and how to get into the hall of fame.</li>

<li><a href="https://sites.google.com/view/webdipinfo/ghost-ratings">Ghost Ratings (External Site)</a></li>
<li class="formlistdesc">An external rating system often used as an alternate measure of player skill.</li>

<li><a href="variants.php">Variant information</a></li>
<li class="formlistdesc">A list of the variants available on this server, credits, and information on variant-specific rules.</li>

<li><a href="credits.php">Credits</a></li>
<li class="formlistdesc">The credits. Includes a list of active moderators.</li>

<li><a href="datc.php">DATC Adjudicator Tests</a></li>
<li class="formlistdesc">For experts; the adjudicator tests which show that webDiplomacy is true to the proper rules</li>

<li><a href="https://github.com/kestasjk/webDiplomacy">GitHub project page</a></li>
<li class="formlistdesc">Our github.com project page. From here you can make feature requests, inform us about bugs, or help improve the code.</li>

<li><a href="http://webdiplomacy.net/developers.php">Developer info</a></li>
<li class="formlistdesc">If you want to fix/improve/install webDiplomacy all the info you need to make it happen is here.</li>

<li><a href="http://sourceforge.net/projects/phpdiplomacy">Sourceforge.net project page</a></li>
<li class="formlistdesc">Last Updated: 2013-04-25.  Our old sourceforge.net project page.</li>

<li><a href="AGPL.txt">GNU Affero General License</a></li>
<li class="formlistdesc">The OSI approved license which applies to the vast majority of webDiplomacy.</li>

<li><a href="recentchanges.php">Recent changes</a></li>
<li class="formlistdesc">Recent changes to the webDiplomacy software.</li>

</ul>

<p>Didn't find the help or information you were looking for? Post a message in the <a href="contrib/phpBB3/" class="light">public forum</a>, or or contact the moderators at <a href="mailto:<?php print (isset(Config::$modEMail) ? Config::$modEMail : Config::$adminEMail); ?>" class="light">
<?php print (isset(Config::$modEMail) ? Config::$modEMail : Config::$adminEMail); ?></a>.</p>
