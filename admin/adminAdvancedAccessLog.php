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
    public $FingerprintCount;
    public $FingerprintProCount;
    public $DaysSinceChecked;

    public $TotalActivity = 0;
    public $HourlyActivity = array();
    public $DailyActivity = array();
    public $DailyByHourlyActivity = array();
    public function loadTimedata($row)
    {
        $this->TotalActivity = $row['totalHits'];
        for($d = 0; $d < 7; $d++)
        {
            $this->DailyActivity[$d] = 0;
            $this->DailyByHourlyActivity[$d] = array();
            for($h = 0; $h < 24; $h++)
            {
                if( $d == 0 ) $this->HourlyActivity[$h] = 0;
                
                $activity = $row['day'.$d.'hour'.$h];
                $this->DailyByHourlyActivity[$d][$h] = $activity;
                $this->DailyActivity[$d] += $activity;
                $this->HourlyActivity[$h] += $activity;
            }
        }
    }
}

$UsersData = array();

$days = '';
$checkIPs = 'unchecked';
$chCookies = 'unchecked';
$checkFingerprint = 'unchecked';
$chShowPrevious = 'unchecked';
$chReCheckAll = 'unchecked';

$CCcount = 0;
$IPcount = 0;
$Fingerprintcount = 0;

if ( isset($_REQUEST['days'])) { $days=(int)$_REQUEST['days']; }
if ( isset($_REQUEST['checkIPs'])) { $checkIPs='checked'; }
if ( isset($_REQUEST['chCookies'])) { $chCookies='checked'; }
if ( isset($_REQUEST['checkFingerprint'])) { $checkFingerprint='checked'; }
if ( isset($_REQUEST['chShowPrevious'])) { $chShowPrevious='checked'; }
if ( isset($_REQUEST['chReCheckAll'])) { $chReCheckAll='checked'; }

print '<button class="modToolsCollapsible">See Page Details</button>';
print '<div class="modToolsContent">';
print '<p class="modTools">This tool queries the summary data for user connections to highlight the most suspicious users that should be investigated.</p>';

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
         <p>Valid from 1-1,000 days.</p>
        <input class="modTools" type="checkbox" name="checkIPs" value="checkIPs">Check IP Matches</br>
        <input class="modTools" type="checkbox" name="chCookies" value="chCookies" checked="checked">Check Cookie Matches</br>
        <input class="modTools" type="checkbox" name="checkFingerprint" value="checkFingerprint">Check Fingerprint Matches</br>
        <input class="modTools" type="checkbox" name="chShowPrevious" value="chShowPrevious">Show Mod Checked Matches</br>
        <input class="modTools" type="checkbox" name="chReCheckAll" value="chReCheckAll">Rerun all Checks all</br></br>
        <input type="submit" name="Submit" class="form-submit" value="Check" /><HR></form>';

if ((is_int($days)) && ($days > 0) && ($days < 1001))
{
    $sTime = time() - $days * (86400); // 60*60*24 to get seconds per day, save 3 calcs on each user checked.

    if ($chReCheckAll == 'unchecked') 
    {
      $sql = "SELECT u.id, u.username, u.email, u.timeJoined, c.countMatchedIPUsers, c.countMatchedCookieUsers, c.countMatchedFingerprintUsers, c.countMatchedFingerprintProUsers, c.matchesLastUpdatedOn,
                  c.totalHits
              ,c.day0hour0
              ,c.day0hour1
              ,c.day0hour2
              ,c.day0hour3
              ,c.day0hour4
              ,c.day0hour5
              ,c.day0hour6
              ,c.day0hour7
              ,c.day0hour8
              ,c.day0hour9
              ,c.day0hour10
              ,c.day0hour11
              ,c.day0hour12
              ,c.day0hour13
              ,c.day0hour14
              ,c.day0hour15
              ,c.day0hour16
              ,c.day0hour17
              ,c.day0hour18
              ,c.day0hour19
              ,c.day0hour20
              ,c.day0hour21
              ,c.day0hour22
              ,c.day0hour23
              ,c.day1hour0
              ,c.day1hour1
              ,c.day1hour2
              ,c.day1hour3
              ,c.day1hour4
              ,c.day1hour5
              ,c.day1hour6
              ,c.day1hour7
              ,c.day1hour8
              ,c.day1hour9
              ,c.day1hour10
              ,c.day1hour11
              ,c.day1hour12
              ,c.day1hour13
              ,c.day1hour14
              ,c.day1hour15
              ,c.day1hour16
              ,c.day1hour17
              ,c.day1hour18
              ,c.day1hour19
              ,c.day1hour20
              ,c.day1hour21
              ,c.day1hour22
              ,c.day1hour23
              ,c.day2hour0
              ,c.day2hour1
              ,c.day2hour2
              ,c.day2hour3
              ,c.day2hour4
              ,c.day2hour5
              ,c.day2hour6
              ,c.day2hour7
              ,c.day2hour8
              ,c.day2hour9
              ,c.day2hour10
              ,c.day2hour11
              ,c.day2hour12
              ,c.day2hour13
              ,c.day2hour14
              ,c.day2hour15
              ,c.day2hour16
              ,c.day2hour17
              ,c.day2hour18
              ,c.day2hour19
              ,c.day2hour20
              ,c.day2hour21
              ,c.day2hour22
              ,c.day2hour23
              ,c.day3hour0
              ,c.day3hour1
              ,c.day3hour2
              ,c.day3hour3
              ,c.day3hour4
              ,c.day3hour5
              ,c.day3hour6
              ,c.day3hour7
              ,c.day3hour8
              ,c.day3hour9
              ,c.day3hour10
              ,c.day3hour11
              ,c.day3hour12
              ,c.day3hour13
              ,c.day3hour14
              ,c.day3hour15
              ,c.day3hour16
              ,c.day3hour17
              ,c.day3hour18
              ,c.day3hour19
              ,c.day3hour20
              ,c.day3hour21
              ,c.day3hour22
              ,c.day3hour23
              ,c.day4hour0
              ,c.day4hour1
              ,c.day4hour2
              ,c.day4hour3
              ,c.day4hour4
              ,c.day4hour5
              ,c.day4hour6
              ,c.day4hour7
              ,c.day4hour8
              ,c.day4hour9
              ,c.day4hour10
              ,c.day4hour11
              ,c.day4hour12
              ,c.day4hour13
              ,c.day4hour14
              ,c.day4hour15
              ,c.day4hour16
              ,c.day4hour17
              ,c.day4hour18
              ,c.day4hour19
              ,c.day4hour20
              ,c.day4hour21
              ,c.day4hour22
              ,c.day4hour23
              ,c.day5hour0
              ,c.day5hour1
              ,c.day5hour2
              ,c.day5hour3
              ,c.day5hour4
              ,c.day5hour5
              ,c.day5hour6
              ,c.day5hour7
              ,c.day5hour8
              ,c.day5hour9
              ,c.day5hour10
              ,c.day5hour11
              ,c.day5hour12
              ,c.day5hour13
              ,c.day5hour14
              ,c.day5hour15
              ,c.day5hour16
              ,c.day5hour17
              ,c.day5hour18
              ,c.day5hour19
              ,c.day5hour20
              ,c.day5hour21
              ,c.day5hour22
              ,c.day5hour23
              ,c.day6hour0
              ,c.day6hour1
              ,c.day6hour2
              ,c.day6hour3
              ,c.day6hour4
              ,c.day6hour5
              ,c.day6hour6
              ,c.day6hour7
              ,c.day6hour8
              ,c.day6hour9
              ,c.day6hour10
              ,c.day6hour11
              ,c.day6hour12
              ,c.day6hour13
              ,c.day6hour14
              ,c.day6hour15
              ,c.day6hour16
              ,c.day6hour17
              ,c.day6hour18
              ,c.day6hour19
              ,c.day6hour20
              ,c.day6hour21
              ,c.day6hour22
              ,c.day6hour23
              FROM wD_Users u
              LEFT JOIN wD_UserConnections c on c.userID = u.id
              WHERE u.timeJoined > ". $sTime ." and c.matchesLastUpdatedOn is not null ".
                ($chShowPrevious == 'checked' ? '' : ' and c.modLastCheckedOn is null ' ).
                "and u.type not like 'banned'
                ORDER BY u.id DESC";
        
        $tablChecked = $DB->sql_tabl($sql);

        /* Loop through all the users gathered from the query above who joined in the last X days and have already been checked. 
        * If the option to recheck is on, this list will be ignored. 
        */
        while ($row = $DB->tabl_hash($tablChecked))
        {   
            $myUser = new UserData();
            $myUser->userID = $row['userID'];
            $myUser->username = $row['username'];
            $myUser->email = $row['email'];
            $myUser->timeJoined = $row['timeJoined'];
            $myUser->CookieCount = $row['countMatchedCookieUsers'];
            $myUser->FingerprintCount = $row['countMatchedFingerprintUsers'];
            $myUser->FingerprintProCount = $row['countMatchedFingerprintProUsers'];
            $myUser->IPCount = $row['countMatchedIPUsers'];
            $myUser->DaysSinceChecked = round((time() - ($row['matchesLastUpdatedOn'])) / (86400));
            $myUser->loadTimedata($row);
            array_push($UsersData,$myUser);
        }
    }

    if ($chReCheckAll == 'checked') 
    {
        $sql = 'SELECT u.id, u.username, u.email, u.timeJoined
                FROM wD_Users u
                WHERE u.timeJoined > '. $sTime .' and u.type not like "banned"
                ORDER BY u.id DESC';
    } 
    else 
    {
        $sql = 'SELECT u.id, u.username, u.email, u.timeJoined
                FROM wD_Users u
                LEFT JOIN wD_UserConnections c on c.userID = u.id
                WHERE u.timeJoined > '. $sTime .' and c.matchesLastUpdatedOn is null and u.type not like "banned"
                ORDER BY u.id DESC';
    }

    print "<TABLE class='modTools'>";
    print "<tr>";
    print '<th class= "modTools">User Profile:</th>';
    print '<th class= "modTools">email</th>';
    print '<th class= "modTools">Time Joined</th>';

    if ($checkIPs=='checked') { print '<th class= "modTools">IP Count</th>'; }
    if ($chCookies=='checked') { print '<th class= "modTools">Cookie Count</th>'; }
    if ($checkFingerprint=='checked') { print '<th class= "modTools">Fingerprint Count</th>'; }

    print '<th class= "modTools">Days</th>';
    print '<th class= "modTools">Check User</th>';
    print "</tr>";
    
    foreach ($UsersData as $values)
    {   
        if (($checkIPs=='checked' and $values->IPCount > 0) or ($chCookies=='checked' and $values->CookieCount > 0) or ($checkFingerprint=='checked' and $values->FingerprintCount > 0)) 
        {
            print '<TR><TD class= "modTools"><a href="userprofile.php?userID='.$values->userID.'">'.$values->username.'</a></TD>';
            print '<TD class= "modTools">'.$values->email.'</TD>';
            print '<TD class= "modTools">'.gmstrftime("%d %b / %I:%M %p",$values->timeJoined).'</TD>';

            if ($checkIPs=='checked') { print '<TD class= "modTools"> IP: '.$values->IPCount.'</TD>'; }
            if ($chCookies=='checked') { print '<TD class= "modTools"> Cookie: '.$values->CookieCount.'</TD>'; }
            if ($checkFingerprint=='checked') { print '<TD class= "modTools"> Cookie: '.$values->FingerprintCount.'</TD>'; }
            
            print '<TD class= "modTools"> '.$values->DaysSinceChecked.'</TD>';
            print "<TD class= 'modTools'> <a href='admincp.php?tab=Multi-accounts&aUserID=".$values->userID."'>Check</a> </TD></TR>";
        }
    }
    print "</TABLE>";
} 
else { if ($days != '') { print '<p class = "modTools">'.$days.' is not valid. Please enter a number between 1 and 1,000.</p>'; } }
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