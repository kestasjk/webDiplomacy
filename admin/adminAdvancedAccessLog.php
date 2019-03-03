<?php

defined('IN_CODE') or die('This script can not be run by itself.');

if (!$User->type['Moderator']) { die ('Only admins or mods can run this script'); }

class UserData
{
    public $userID;
    public $username;
    public $email;
    public $timeJoined;
    public $CookieCount;
    public $IPCount;
    public $DaysSinceChecked;
}

$UsersData = array();

$days = '';
$checkIPs = 'unchecked';
$chCookies = 'unchecked';
$chShowPrevious = 'unchecked';
$chReCheckAll = 'unchecked';

$CCcount = 0;
$IPcount = 0;

if ( isset($_REQUEST['days'])) { $days=(int)$_REQUEST['days']; }
if ( isset($_REQUEST['checkIPs'])) { $checkIPs='checked'; }
if ( isset($_REQUEST['chCookies'])) { $chCookies='checked'; }
if ( isset($_REQUEST['chShowPrevious'])) { $chShowPrevious='checked'; }
if ( isset($_REQUEST['chReCheckAll'])) { $chReCheckAll='checked'; }

print '<button class="modToolsCollapsible">See Page Details</button>';
print '<div class="modToolsContent">';
print '<p class="modTools">This tool checks a new Connections table to see if users have a record in it. If the user has a record in the table
it then checks to see if that record has been updated since the user joined the site because users who are first joining do not always have 
reliable access information. If a user has a records in the new table it will pull that information and display it. <br /> <br /> It is important to note
that this information will be old, and the number of days old will be displayed in the days column in the results table. If you want to see
updated information then check the "Rerun all checks" option. <br /> <br /> It is also important to note that this tool will check both the ip and cookie
data of all the new users regardless of if cookie or ip are checked. It is only the display that will be impacted. <br .> If this tool is being run for the first 
time in a while or over a large set of data it will be slow. Approx .13 seconds per user being checked. Subsequent runs will be faster as data is stored in the
new table.</p>';

print '<ui class="modTools"> Explaining Paramaters:
<li>days: This is used to check for users who joined after this number of days ago.</li>
<li>Check IP Matches: This is used to decide if users with IP connections should be displayed.</li>
<li>Check Cookie Matches: This is used to decide if users with cookie connection should be displayed.</li>
<li>Show Mod Checked Matches: This is used to decide if users with ip and cookie connection matches who have already been checked in the multi tool by a moderator should be displayed.</li>
<li>Rerun all Checks all: This is used to decide if users who have previously been checked in this tool should have their data rechecked.</li>
</ui>';
print '</div>';

// Print a form for selecting which users to check
print '<FORM class="modTools" method="get" action="admincp.php">
		<INPUT type="hidden" name="tab" value="AccessLog" />
		<HR><STRONG>New users from the last </STRONG><INPUT class="modTools" type="text" name="days"  value="'. $days .'" size="3" /> days.
         <p>Valid from 1-365 days.</p>
        <input class="modTools" type="checkbox" name="checkIPs" value="checkIPs">Check IP Matches</br>
        <input class="modTools" type="checkbox" name="chCookies" value="chCookies" checked="checked">Check Cookie Matches</br>
        <input class="modTools" type="checkbox" name="chShowPrevious" value="chShowPrevious">Show Mod Checked Matches</br>
        <input class="modTools" type="checkbox" name="chReCheckAll" value="chReCheckAll">Rerun all Checks all</br></br>
        <input class="modToolsform-submit" type="submit" name="Submit" class="form-submit" value="Check" /><HR></form>';

if ((is_int($days)) && ($days > 0) && ($days < 366))
{
    $sTime = time() - $days * (86400); // 60*60*24 to get seconds per day, save 3 calcs on each user checked.

    if ($chReCheckAll == 'unchecked') {
        if ($chShowPrevious == 'checked') {
            $sql = "SELECT u.id, u.username, u.email, u.timeJoined, c.countMatchedIPUsers, c.countMatchedCookieUsers, c.matchesLastUpdatedOn
                    FROM wD_Users u
                    LEFT JOIN wD_UserConnections c on c.userID = u.id
                    WHERE u.timeJoined > ". $sTime ." and c.matchesLastUpdatedOn is not null 
                    ORDER BY u.id ASC";
        } else{
            $sql = "SELECT u.id, u.username, u.email, u.timeJoined, c.countMatchedIPUsers, c.countMatchedCookieUsers, c.matchesLastUpdatedOn
                    FROM wD_Users u
                    LEFT JOIN wD_UserConnections c on c.userID = u.id
                    WHERE u.timeJoined > ". $sTime ." and c.matchesLastUpdatedOn is not null and c.modLastCheckedOn is null
                    ORDER BY u.id ASC";
        }
        
        $tablChecked = $DB->sql_tabl($sql);

        /* Loop through all the users gathered from the query above who joined in the last X days and have already been checked. 
        * If the option to recheck is on, this list will be ignored. 
        */
        while (list($userID, $username, $email, $timeJoined, $countMatchedIPUsers, $countMatchedCookieUsers, $matchesLastUpdatedOn) = $DB->tabl_row($tablChecked))
        {   
            $myUser = new UserData();
            $myUser->userID = $userID;
            $myUser->username = $username;
            $myUser->email = $email;
            $myUser->timeJoined = $timeJoined;
            $myUser->CookieCount = $countMatchedCookieUsers;
            $myUser->IPCount = $countMatchedIPUsers;
            $myUser->DaysSinceChecked = round((time() - ($matchesLastUpdatedOn)) / (86400));
            array_push($UsersData,$myUser);
        }
    }

    if ($chReCheckAll == 'checked') {
        $sql = 'SELECT u.id, u.username, u.email, u.timeJoined
                FROM wD_Users u
                WHERE u.timeJoined > '. $sTime .' 
                ORDER BY u.id ASC';
    } else {
        $sql = 'SELECT u.id, u.username, u.email, u.timeJoined
                FROM wD_Users u
                LEFT JOIN wD_UserConnections c on c.userID = u.id
                WHERE u.timeJoined > '. $sTime .' and c.matchesLastUpdatedOn is null
                ORDER BY u.id ASC';
    }

    // Get all the users who need to be checked against wD_AccessLog
    
    $tabl = $DB->sql_tabl($sql);

    /* Loop through all the users gathered from the query above who joined in the last X days and check them for ip and cookie matches. 
     * Even though the user might only select IP or Cookie matches, we want to check users in this list for both and store the results
     * in the new wD_Connections table to speed up subsequent uses of this tool. Eventually all new users will get automatically checked
     * and this tool will be much faster then after the initial period. Smaller sites will have no speed problems, but larger ones with 
     * years of data in wD_Access will see initial performance problems for longer time periods. 
     */
    while (list($userID, $username, $email, $timeJoined) = $DB->tabl_row($tabl))
    {
        $CCcount = 0;
        $IPcount = 0;
        unset($IPs);
        unset($CCs);
        
        $sql_IPs = "SELECT ip FROM wD_AccessLog WHERE userID = ".$userID." GROUP BY ip";
        $tabl_IPs = $DB->sql_tabl($sql_IPs);
        $IPs=array();

        while ( list($IP) = $DB->tabl_row($tabl_IPs) )
        {
            $IPs[]=$IP;
        }

        if (count($IPs) > 0) {
            list($IPcount) = $DB->sql_row("
                SELECT COUNT(*) FROM (SELECT userID FROM wD_AccessLog WHERE ip IN ( ".implode(',',$IPs)." ) AND userID <> ".$userID." GROUP BY userID) AS IPmatch");
        }

        $sql_CCs = "SELECT cookieCode FROM wD_AccessLog WHERE userID = ".$userID." GROUP BY cookieCode";
        $tabl_CCs = $DB->sql_tabl($sql_CCs);
        $CCs=array();

        while ( list($CC) = $DB->tabl_row($tabl_CCs) )
        {
            $CCs[]=$CC;
        }
        
        if (count($CCs) > 0)
        {
            list($CCcount) = $DB->sql_row("
                SELECT COUNT(*) FROM (SELECT userID FROM wD_AccessLog WHERE cookieCode IN ( ".implode(',',$CCs)." ) AND userID <> ".$userID." GROUP BY userID) AS Cookiematch");
        }

        $myUser = new UserData();
        $myUser->userID = $userID;
        $myUser->username = $username;
        $myUser->email = $email;
        $myUser->timeJoined = $timeJoined;
        $myUser->CookieCount = $CCcount;
        $myUser->IPCount = $IPcount;
        $myUser->DaysSinceChecked = 0;
        array_push($UsersData,$myUser);

        // Insert or update the wD_UserConnections record here. 
        $DB->sql_put("INSERT INTO wD_UserConnections (userID, modLastCheckedBy, modLastCheckedOn, matchesLastUpdatedOn, countMatchedIPUsers, countMatchedCookieUsers) 
        VALUES (".$userID.", null, null, ".time().", ".$IPcount.", ".$CCcount.") ON DUPLICATE KEY UPDATE matchesLastUpdatedOn=VALUES(matchesLastUpdatedOn), 
        countMatchedIPUsers=VALUES(countMatchedIPUsers), countMatchedCookieUsers=VALUES(countMatchedCookieUsers)");
    }

    print "<TABLE class='modTools'>";
    print "<tr>";
    print '<th class= "modTools">User Profile:</th>';
    print '<th class= "modTools">email</th>';
    print '<th class= "modTools">Time Joined</th>';

    if ($checkIPs=='checked') { print '<th class= "modTools">IP Count</th>'; }
    if ($chCookies=='checked') { print '<th class= "modTools">Cookie Count</th>'; }

    print '<th class= "modTools">Days</th>';
    print '<th class= "modTools">Check User</th>';
    print "</tr>";
    
    foreach ($UsersData as $values)
    {   
        if (($checkIPs=='checked' and $values->IPCount > 0) or ($chCookies=='checked' and $values->CookieCount > 0)) {
            print '<TR><TD class= "modTools"><a href="profile.php?userID='.$values->userID.'">'.$values->username.'</a></TD>';
            print '<TD class= "modTools">'.$values->email.'</TD>';
            print '<TD class= "modTools">'.gmstrftime("%d %b / %I:%M %p",$values->timeJoined).'</TD>';

            if ($checkIPs=='checked') { print '<TD class= "modTools"> IP: '.$values->IPCount.'</TD>'; }
            if ($chCookies=='checked') { print '<TD class= "modTools"> Cookie: '.$values->CookieCount.'</TD>'; }
            
            print '<TD class= "modTools"> '.$values->DaysSinceChecked.'</TD>';
            print "<TD class= 'modTools'> <a href='admincp.php?tab=Multi-accounts&aUserID=".$values->userID."'>Check</a> </TD></TR>";
        }
    }
    print "</TABLE>";
} 
else { if ($days != '') { print '<p class = "modTools">'.$days.' is not valid. Please enter a number between 1 and 365.</p>'; } }
?>

<script>
var coll = document.getElementsByClassName("modToolsCollapsible");
var i;

for (i = 0; i < coll.length; i++) {
  coll[i].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
    if (content.style.display === "block") {
      content.style.display = "none";
    } else {
      content.style.display = "block";
    }
  });
}
</script>