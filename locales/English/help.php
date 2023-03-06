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

print libHTML::pageTitle('Information and Links','Links to pages with more information about webDiplomacy.');
?>

<div class='help'>
    
    <div class = 'help_title'>Get Help - Mod forum</div>
    <div class = 'help_content'>
        <p>
            If you need to get in touch with the mod team please submit a message to the <a href="modforum.php">moderator forum</a>!
        </p>
    </div>

    <div class = 'help_title'>DATC Adjudicator Tests</div>
    <div class = 'help_content'>
        <p>
            For experts; the <a href="datc.php">adjudicator tests</a> which show that webDiplomacy is true to the proper rules.
        </p>
    </div>

    <div class = 'help_title'>GNU Affero General License</div>
    <div class = 'help_content'>
        <p>
            The <a href="AGPL.txt">OSI approved license</a> which applies to the vast majority of webDiplomacy.
        </p>
    </div>

    <div class = 'help_title'>Developer Info</div>
    <div class = 'help_content'>
        <p>
            If you want to fix/improve/install webDiplomacy all the info you need to make it happen is <a href="developers.php">here</a>.
        </p>
    </div>
    <div class = 'help_title'>Privacy</div>
    <div class = 'help_content'>
        <p>
            At WebDiplomacy.net, the privacy of our visitors is of great importance to us. 
            This privacy policy outlines the types of information received and stored by WebDiplomacy.net and how it is used, plus our use of cookies. <br /><br />

            <strong>User information:</strong><br />
            We only store personal information which was entered by you, the user. This includes your chosen username, password, 
            and e-mail address, which is required at sign-up. This information is used purely for identification on the website. 
            Only your username will be published online on our website, your e-mail address is visible only to moderators.<br /><br />

            <strong>Log Files:</strong><br />
            Like many other websites, www.WebDiplomacy.net makes use of access logs for improving the website, analyzing trends, catching cheaters, etc. 
            The information inside the log files includes IP addresses, anonymized browser identifiers, timestamps. IP addresses, and other such information 
            are not linked to any information that is personally identifiable. <br /><br />

            <strong>Academic Research:</strong><br />
            We are pleased to be able to offer historic move data that is publically available for research purposes, as well as historic, 
            anonymized and redacted game message data to reputable research organizations under strict NDA provisions. Such data can result 
            in the development of AI models that can play Diplomacy, which we can then incorporate into WebDiplomacy.net to improve the system. 
            Such efforts also advance the field of computer science which the Diplomacy hobby has always benefited/contributed from/to.<br /><br />
            
            <strong>Cookies:</strong><br />
            WebDiplomacy.net, as with any interactive website, uses cookies to store authentication credentials and store information that persists 
            across requests. Cookies are simply information sent from the server to your web-browser that your web-browser is expected to repeat on 
            subsequent requests. Almost all web browsers will allow or deny storage of cookies depending on your preferences.<br />
            We use Google Analytics to monitor usage trends. Refer to Google's privacy policy. (Note that browsers set to reject third-party cookies, 
            which some do by default, will not repeat any cookies sent by Google Analytics on our behalf.)
        </p>
    </div>

    <div class = 'help_title'>Credits</div>
    <div class = 'help_content'>
        <p>
        <a href="credits.php">The Credits</a>. All the people who made this site possible. Includes a list of active moderators.
        </p>
    </div>

<p>Didn't find the help or information you were looking for? Post a message in the <a href="contrib/phpBB3/" class="light">forum</a>, or contact the moderators in the <a href="modforum.php">moderator forum</a>.</p>

</div>

<script type="text/javascript">
   var coll = document.getElementsByClassName("help_title");
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
