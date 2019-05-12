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
    
    <div class = 'help_title'>Mentor Program</div>
    <div class = 'help_content'>
        <p>
            <a href="https://docs.google.com/document/d/1dSq6zlizecb90F3OKSqyFUWGI32Or87ZNmkFmhCVz-c/edit">Mentor-Apprentice Program (External Link)</a>
            This is a program to allow new members to receive advice and guidance on the game from our more experienced members. </br>
            If you are a new member and want a mentor <a href="https://goo.gl/forms/tHxJMZQ7mKgjAtEO2">click here</a> to sign up. </br>
            If you would like to mentor a new member please <a href="https://goo.gl/forms/XdMDvxfxdQQQJjDI3">click here</a> to sign up. </br>
            Any questions on this program can be directed to webdipmentors@gmail.com.
        </p>
    </div>

    <div class = 'help_title'>Contact Information</div>
    <div class = 'help_content'>
        <p>
            Need to contact one of the site owners or see more about what the moderator team and owners can help you out with, see their 
            <a href="contactUs.php">Contact Information</a> here!
        </p>
    </div>

    <div class = 'help_title'>Hall of Fame!</div>
    <div class = 'help_content'>
        <p>
            The <a href="halloffame.php">Hall of fame</a>! See the pros of this server, the top 100 by points!
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

    <div class = 'help_title'>Credits</div>
    <div class = 'help_content'>
        <p>
        <a href="credits.php">The Credits</a>. All the people who made this site possible. Includes a list of active moderators.
        </p>
    </div>

<p>Didn't find the help or information you were looking for? Post a message in the <a href="contrib/phpBB3/" class="light">forum</a>, or or contact the moderators at <a href="mailto:<?php print (isset(Config::$modEMail) ? Config::$modEMail : Config::$adminEMail); ?>" class="light">
<?php print (isset(Config::$modEMail) ? Config::$modEMail : Config::$adminEMail); ?></a>.</p>

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
