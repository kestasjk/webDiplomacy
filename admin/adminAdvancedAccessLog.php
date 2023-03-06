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
$checkFingerprintPro = 'unchecked';
$chShowPrevious = 'unchecked';
$chReCheckAll = 'unchecked';

$CCcount = 0;
$IPcount = 0;
$Fingerprintcount = 0;
$FingerprintProcount = 0;

if ( isset($_REQUEST['days'])) { $days=(int)$_REQUEST['days']; }
if ( isset($_REQUEST['checkIPs'])) { $checkIPs='checked'; }
if ( isset($_REQUEST['chCookies'])) { $chCookies='checked'; }
if ( isset($_REQUEST['checkFingerprint'])) { $checkFingerprint='checked'; }
if ( isset($_REQUEST['checkFingerprintPro'])) { $checkFingerprintPro='checked'; }
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
</ui>';
print '</div>';

// Print a form for selecting which users to check
print '<FORM class="modTools" method="get" action="admincp.php">
		<INPUT type="hidden" name="tab" value="Account Searcher" />
		<HR><STRONG>New users from the last </STRONG><INPUT class="modTools" type="text" name="days"  value="'. $days .'" size="3" /> days.
         <p>Valid from 1-1,000 days.</p>
        <input class="modTools" type="checkbox" name="checkIPs" value="checkIPs" '.(isset($_REQUEST['checkIPs']) ? 'checked' : '').'>Check IP Matches</br>
        <input class="modTools" type="checkbox" name="chCookies" value="chCookies" '.(isset($_REQUEST['chCookies']) ? 'checked' : '').'>Check Cookie Matches</br>
        <input class="modTools" type="checkbox" name="checkFingerprint" value="checkFingerprint" '.(isset($_REQUEST['checkFingerprint']) ? 'checked' : '').'>Check Fingerprint Matches</br>
        <input class="modTools" type="checkbox" name="checkFingerprintPro" value="checkFingerprintPro" '.(isset($_REQUEST['checkFingerprintPro']) ? 'checked' : '').'>Check FingerprintPro Matches</br>
        <input class="modTools" type="checkbox" name="chShowPrevious" value="chShowPrevious" '.(isset($_REQUEST['chShowPrevious']) ? 'checked' : '').'>Show Mod Checked Matches</br>
        <input type="submit" name="Submit" class="form-submit" value="Check" /><HR></form>';

if ((is_int($days)) && ($days > 0) && ($days < 1001))
{
    $sTime = time() - $days * 60*60*24;

    if ($chReCheckAll == 'unchecked') 
    {
      $sql = "SELECT u.id, u.username, u.email, u.timeJoined, 
                  c.matchedIPTotal, c.matchedCookieTotal, c.matchedFingerprintTotal, 
                  c.matchedFingerprintProTotal, UNIX_TIMESTAMP() matchesLastUpdatedOn,
                  c.totalHits
              FROM wD_Users u
              LEFT JOIN wD_UserConnections c on c.userID = u.id
              WHERE u.timeJoined > ". $sTime ." ".
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
            $myUser->userID = $row['id'];
            $myUser->username = $row['username'];
            $myUser->email = $row['email'];
            $myUser->timeJoined = $row['timeJoined'];
            $myUser->CookieCount = $row['matchedCookieTotal'];
            $myUser->FingerprintCount = $row['matchedFingerprintTotal'];
            $myUser->FingerprintProCount = $row['matchedFingerprintProTotal'];
            $myUser->IPCount = $row['matchedIPTotal'];
            $myUser->DaysSinceChecked = round((time() - ($row['matchesLastUpdatedOn'])) / (86400));
            //$myUser->loadTimedata($row);
            array_push($UsersData,$myUser);
        }
    }

    if ($chReCheckAll == 'checked') 
    {
        $sql = 'SELECT u.id, u.username, u.email, u.timeJoined, 
                  c.matchedIPTotal, c.matchedCookieTotal, c.matchedFingerprintTotal, 
                  c.matchedFingerprintProTotal, UNIX_TIMESTAMP() matchesLastUpdatedOn,
                  c.totalHits
                FROM wD_Users u
                INNER JOIN wD_UserConnections c ON c.userID = u.id
                WHERE u.timeJoined > '. $sTime .' and u.type not like "banned"
                ORDER BY u.id DESC';
    } 
    else 
    {
        $sql = 'SELECT u.id, u.username, u.email, u.timeJoined, 
                  c.matchedIPTotal, c.matchedCookieTotal, c.matchedFingerprintTotal, 
                  c.matchedFingerprintProTotal, UNIX_TIMESTAMP() matchesLastUpdatedOn,
                  c.totalHits
                FROM wD_Users u
                LEFT JOIN wD_UserConnections c on c.userID = u.id
                WHERE u.timeJoined > '. $sTime .' and c.matchesLastUpdatedOn is null and u.type not like "banned"
                ORDER BY u.id DESC';
    }

    // Get all the users who need to be checked against wD_AccessLog
    $tablChecked = $DB->sql_tabl($sql);
    /* Loop through all the users gathered from the query above who joined in the last X days and check them for matches. 
     */
    while ($row = $DB->tabl_hash($tablChecked))
    {
      $myUser = new UserData();
      $myUser->userID = $row['id'];
      $myUser->username = $row['username'];
      $myUser->email = $row['email'];
      $myUser->timeJoined = $row['timeJoined'];
      $myUser->CookieCount = $row['matchedCookieTotal'];
      $myUser->FingerprintCount = $row['matchedFingerprintTotal'];
      $myUser->FingerprintProCount = $row['matchedFingerprintProTotal'];
      $myUser->IPCount = $row['matchedIPTotal'];
      $myUser->DaysSinceChecked = round((time() - ($row['matchesLastUpdatedOn'])) / (86400));
      //$myUser->loadTimedata($row);
      array_push($UsersData,$myUser);
    }

    print "<TABLE class='modTools'>";
    print "<tr>";
    print '<th class= "modTools">User Profile:</th>';
    print '<th class= "modTools">email</th>';
    print '<th class= "modTools">Time Joined</th>';

    if ($checkIPs=='checked') { print '<th class= "modTools">IP Count</th>'; }
    if ($chCookies=='checked') { print '<th class= "modTools">Cookie Count</th>'; }
    if ($checkFingerprint=='checked') { print '<th class= "modTools">Fingerprint Count</th>'; }
    if ($checkFingerprintPro=='checked') { print '<th class= "modTools">FingerprintPro Count</th>'; }

    print '<th class= "modTools">Days</th>';
    print '<th class= "modTools">Check User</th>';
    print "</tr>";
    
    foreach ($UsersData as $values)
    {   
        if (($checkIPs=='checked' and $values->IPCount > 0) or ($chCookies=='checked' and $values->CookieCount > 0) or ($checkFingerprint=='checked' and $values->FingerprintCount > 0) or ($checkFingerprintPro=='checked' and $values->FingerprintProCount > 0)) 
        {
            print '<TR><TD class= "modTools"><a href="userprofile.php?userID='.$values->userID.'">'.$values->username.'</a></TD>';
            print '<TD class= "modTools">'.$values->email.'</TD>';
            print '<TD class= "modTools">'.libTime::text($values->timeJoined).'</TD>';

            if ($checkIPs=='checked') { print '<TD class= "modTools"> IP: '.$values->IPCount.'</TD>'; }
            if ($chCookies=='checked') { print '<TD class= "modTools"> Cookie: '.$values->CookieCount.'</TD>'; }
            if ($checkFingerprint=='checked') { print '<TD class= "modTools"> Fingerprint: '.$values->FingerprintCount.'</TD>'; }
            if ($checkFingerprintPro=='checked') { print '<TD class= "modTools"> FingerprintPro: '.$values->FingerprintProCount.'</TD>'; }
            
            print '<TD class= "modTools"> '.$values->DaysSinceChecked.'</TD>';
            print "<TD class= 'modTools'> <a href='admincp.php?tab=Account Analyzer&aUserID=".$values->userID."'>Check</a> </TD></TR>";
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