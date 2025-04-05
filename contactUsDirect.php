<?php

/**
 * @package Base
 */

require_once('header.php');
require_once(l_r('gamesearch/search.php'));
require_once(l_r('pager/pagergame.php'));
require_once(l_r('objects/game.php'));
require_once(l_r('gamepanel/game.php'));
require_once(l_r('objects/mailer.php'));
require_once(l_r('gamemaster/game.php'));

global $Mailer;
$Mailer = new Mailer();

$tab = '';
global $DB;

if( !$User->type['User'] )
{
	libHTML::error(l_t("This page is only for registered users."));
}

header('refresh: 4; url=modforum.php');

libHTML::notice('Redirecting to Mod forum', 'Redirecting you to the <a href="modforum.php">moderator forum</a> where you can submit a request to the mod team.');

$submitted = false;
$issueType = '';
$gamesValid = false;
$games = -1;
$postedGameName = '';
$postedGameIssue = '';
$postedOtherIssue = '';
$postedAdditionalInfo = '';
$postedEmergencyIssue = '';

$subject = '';
$actualProblem = '';

// Game Search Objects
class GameResultData
{
	public $gameID;
	public $gameName;
	public $gameOver; 				// enum('No','Won','Drawn')  
	public $processStatus; 		// enum('Not-processing','Processing','Crashed','Paused')  
	public $hasPassword; 			// is password set?
	public $phaseMinutes;
	public $anon; 						// yes/no 
	public $pressType; 				//enum('Regular','PublicPressOnly','NoPress','RulebookPress')
    public $directorUserID;
    public $processTime;
    public $phase;
}

// Game Search Variables
$GamesData = array();

// Get values from posted contact requests. 
if(isset($_POST['submit'])) 
{
    libAuth::formToken_Valid();

    $submitted = true;
    if (isset($_POST['issueType']))
    {
        if ($_POST['issueType'] == 'gameIssue') { $issueType='gameIssue'; }
        else if ($_POST['issueType'] == 'otherIssue') { $issueType='otherIssue'; }
        else if ($_POST['issueType'] == 'emergencyIssue') { $issueType='emergencyIssue'; }
    }
    if ($issueType=='gameIssue')
    {
        $subject = 'WebDip Generated Game Support Task';
        if(isset($_POST['games'])) 
        { 
            $games = (int)$_POST["games"]; 
            if ($games == 0 || $games == 1) { $gamesValid = true;}
        }
        if(isset($_POST['gamesIssue'])) 
        {
            if ($_POST['gamesIssue'] == 'pause') 
            { 
                $postedGameIssue='pause';
                $actualProblem = 'user is requesting a pause';
            }
            else if ($_POST['gamesIssue'] == 'unpause') 
            { 
                $postedGameIssue='unpause'; 
                $actualProblem = 'user is requesting an un-pause';
            }
            else if ($_POST['gamesIssue'] == 'cheating') 
            { 
                $postedGameIssue='cheating'; 
                $actualProblem = 'user is requesting a cheating investigation';
            }
            else if ($_POST['gamesIssue'] == 'orders') 
            { 
                $postedGameIssue='orders'; 
                $actualProblem = 'user is requesting help with orders';
            }
            else if ($_POST['gamesIssue'] == 'replace') 
            { 
                $postedGameIssue='replace'; 
                $actualProblem = 'user is requesting to be replaced';
            }
            else if ($_POST['gamesIssue'] == 'stalemate') 
            { 
                $postedGameIssue='stalemate'; 
                $actualProblem = 'user is requesting a stalemate investigation';
            }
            else if ($_POST['gamesIssue'] == 'wfo') 
            { 
                $postedGameIssue='wfo'; 
                $actualProblem = 'user is requesting help with a wfo game';
            }
            else if ($_POST['gamesIssue'] == 'crash') 
            { 
                $postedGameIssue='crash'; 
                $actualProblem = 'user is requesting help with a crashed game';
            }
            else if ($_POST['gamesIssue'] == 'other') 
            { 
                $postedGameIssue='other'; 
                $actualProblem = 'user is requesting something else, check additional details';
            }
        }
    }
    else if ($issueType=='otherIssue')
    {
        $subject = 'WebDip Generated Other Support Task';
        if(isset($_POST['otherIssue'])) 
        {
            if ($_POST['otherIssue'] == 'rules') 
            { 
                $postedOtherIssue='rules'; 
                $actualProblem = 'user is requesting help with the rules';
            }
            else if ($_POST['otherIssue'] == 'otherGame') 
            { 
                $postedOtherIssue='otherGame'; 
                $actualProblem = 'user is requesting help with a game they are not in';
            }
            else if ($_POST['otherIssue'] == 'finishedGame') 
            { 
                $postedOtherIssue='finishedGame'; 
                $actualProblem = 'user is requesting help with a finished game';
            }
            else if ($_POST['otherIssue'] == 'bug') 
            { 
                $postedOtherIssue='bug'; 
                $actualProblem = 'user is requesting help with a bug';
            }
            else if ($_POST['otherIssue'] == 'other') 
            { 
                $postedOtherIssue='other'; 
                $actualProblem = 'user is requesting something else, check additional details';
            }
        }
    }

    else if ($issueType =='emergencyIssue')
    {
        $subject = 'WebDip Generated Emergency Pause';
        if(isset($_POST['emergencyIssue'])) 
        {
            if ($_POST['emergencyIssue'] == 'naturalDisaster') 
            { 
                $postedEmergencyIssue='naturalDisaster'; 
                $actualProblem = 'user paused all games for a natural disaster impacting them';
            }
            else if ($_POST['emergencyIssue'] == 'medical') 
            { 
                $postedEmergencyIssue='medical'; 
                $actualProblem = 'user paused all games for a medical emergency';
            }
            else if ($_POST['emergencyIssue'] == 'powerOutage') 
            { 
                $postedEmergencyIssue='powerOutage'; 
                $actualProblem = 'user paused all games due to a power outage';
            }
        }
    }

    // Use db escape to guard against special characters. 
    if(isset($_POST['additionalInfo']) && strlen($_POST['additionalInfo'])) 
    {
        $postedAdditionalInfo = $DB->escape($_POST['additionalInfo']);
        $postedAdditionalInfo = strip_tags(html_entity_decode(trim($postedAdditionalInfo)));
    }
}

// Print the header and standard php for the site that is required on every page. 
libHTML::starthtml();
print libHTML::pageTitle(l_t('Contact Us'),l_t('Directly submit a support request to the moderator team.'));
?>

<?php
$sql = "SELECT g.id, g.name, g.gameOver, g.processStatus, ( CASE WHEN g.password IS NULL THEN 'False' ELSE 'True' END ) AS password,
				g.phaseMinutes, g.anon, g.pressType, g.directorUserID, g.processTime, g.phase
                FROM wD_Games g 
                inner join wD_Members m on m.gameID = g.id
                WHERE g.gameOver = 'No' and g.phase <> 'Pre-Game' and m.status = 'Playing' and m.userID = ". $User->id;

$tablChecked = $DB->sql_tabl($sql);
$allGames = '';
$allRunningGames = '';

$numberOfGames = 0;

while (list($gameID, $gameName, $gameOver, $processStatus, $password, $phaseMinutes, $anon, $pressType, $directorUserID, $processTime, $phase) = $DB->tabl_row($tablChecked))
{   
    $myGame = new GameResultData();
    $myGame->gameID = $gameID;
    $myGame->gameName = $gameName;
    $myGame->gameOver = $gameOver;
    if ($password == 'True' ) {$myGame->password = true; } else {$myGame->password = false; };
    $myGame->phaseMinutes = $phaseMinutes;
    $myGame->anon = $anon;
    $myGame->pressType = $pressType;
    $myGame->directorUserID = $directorUserID;
    $myGame->processStatus = $processStatus;
    $myGame->processTime = $processTime;
    $myGame->phase = $phase;
    array_push($GamesData,$myGame);

    $allGames = $allGames.'https://www.webdiplomacy.net/board.php?gameID='.$gameID.'<br>';

    if($processStatus != 'Paused' )
    {
        $allRunningGames = $allRunningGames.'https://www.webdiplomacy.net/board.php?gameID='.$gameID.'<br>';
        $numberOfGames = $numberOfGames+1;
    }
    
    if ($gamesValid == false && $games == $gameID)
    {     
        $gamesValid = true;
        $postedGameName = $gameName;
    }
}

if ($submitted == true)
{
    // validate and send email.
    $email = Config::$modEMail;
    $userPickedGame = 'no specific game';
    
    if ($issueType=='gameIssue' && $postedGameIssue == 'pause') { $subject = $subject.' URGENT-Pause';}
    
    if ($gamesValid == true && $games == 1) 
    { 
        $userPickedGame = 'all of their games';
    }
    else if ($gamesValid == true && $games > 1) 
    {
        $userPickedGame = '<a href="https://www.webdiplomacy.net/board.php?gameID='.$games.'" class="contactUs"> '.$postedGameName.'</a>';
    }

    $worked = true;
    $pausedGames = '';

    // Make it easier to respond to direct to the user
    $Mailer->SetReplyTo($User->email, $User->username);

    if ($issueType=='emergencyIssue')
    {
        if ($User->qualifiesForEmergency())
        {
            foreach ($GamesData as $values)
            {      
                // This is a reduced version of the toggle Pause function, altered so that it can only impact non-paused games. 
                if ($values->processStatus != 'Paused')
                {
                    if($values->phase != 'Pre-game' && $values->phase != 'Finished' && $values->processStatus == 'Not-processing')
                    {
                        // Use processTime to find pauseTimeRemaining
                        $pauseTimeRemaining = $values->processTime - time();
                        $processTime=false;
                        
                        $DB->sql_put(
                            "UPDATE wD_Games 
                            SET processStatus = 'Paused',
                                pauseTimeRemaining = ".(false===$pauseTimeRemaining ? "NULL" : $pauseTimeRemaining).",
                                processTime = ".(false===$processTime ? "NULL" : $processTime)."
                            WHERE id = ".$values->gameID);

                        // Any votes to toggle the pause are now void
                        $DB->sql_put("UPDATE wD_Members SET votes = REPLACE(votes,'Pause',''), votesChanged=UNIX_TIMESTAMP() WHERE gameID = ".$values->gameID);
                    }
                }
            }

            // Update the users emergency pause time to now.
            $User->updateEmergencyPauseDate(time());

            // Add a record to the admin log just in case the email fails.
            $DB->sql_put("
            INSERT INTO wD_AdminLog ( name, userID, time, details, params )
            VALUES ( 'Emergency Pause', ".$User->id.", ".time().", 'The following games were paused due to a player emergency ', '".$allRunningGames."' )");

            try
            {
                $Mailer->Send(array($email=>$email), $subject.' '.$User->username,
                "
                This request is from <a href='https://www.webdiplomacy.net/userprofile.php?userID=".$User->id."' class = 'contactUs'>".$User->username."</a>, 
                and their registered email is: ".$User->email."<br><br>

                <strong>An emergency pause was used because of ".$actualProblem."</strong>
                
                <br><br>
                All the games impacted will need a moderator to post in the global chat explaining why the game was paused. Simply say 'A user in this game
                needed an emergency pause', do <strong>not</strong> give the reason.
                The games that were paused as a result of this are: <br><br>".$allRunningGames. "
                
                <br><br>
                Please note that any games they are in that were already paused will NOT show up in the above list. Please check all this users games to make sure none are 
                about to be unpaused and then follow up with the user to see how long they need this pause for and determine if the reason was acceptable.

                <br><br>
                <strong>Additional Information:</strong> <br><br>
                ".$postedAdditionalInfo."</br></br>

                ");
            }
            catch(Exception $e)
            {
                print '<div class="contactUs"> Sorry, but there was a problem sending this message, contact the moderator team directly at '.Config::$modEMail;
                print '<p class="contactUs">'.$e->getMessage().'</p>';
                print '</div>';
                $worked = false;
            }
        }
    }

    else
    {
        try
        {
            $Mailer->Send(array($email=>$email), $subject.' '.$User->username,
            "
            This request is from <a href='https://www.webdiplomacy.net/userprofile.php?userID=".$User->id."' class = 'contactUs'>".$User->username."</a>, 
            and their registered email is: ".$User->email."<br><br>

            <strong>The user called out ".$userPickedGame. ".</strong><br><br>
            The ".$actualProblem." and their games are: <br><br>".$allGames. "
            
            <br><br>
            <strong>Additional Information:</strong> <br><br>
            ".$postedAdditionalInfo."</br></br>

            ");
        }
        catch(Exception $e)
        {
            print '<div class="contactUs"> Sorry, but there was a problem sending this message, if this is an emergency contact the moderator team directly at '.Config::$modEMail;
            print '<p class="contactUs">'.$e->getMessage().'</p>';
            print '</div>';
            $worked = false;
        }
    }

    if ($worked == true)
    {
        print '<p class = "contactUs">The moderator team will get back to you shortly';

        if ($gamesValid == true && $games == 1) 
        { 
            print' about all of your games';
        }

        else if ($gamesValid == true && $games > 1) 
        {
            print ' about <i>'.$postedGameName.'</i>';
        }
        else 
        {
            print ' about your issue';
        }

        print'.</br></br>
        The email address the moderators will reply to you at is '.$User->email.' if that is not accurate please update 
        it in <a href="usercp.php" class="contactUs"> settings</a>  asap!</p>';
    }
    
    print '<p class = "contactUs"> <a class="contactUs" href="contactUsDirect.php">Submit another request. </a>'; 
}

else
{
    print '<div class = "contactUs"><p>Fill out the following form to get assistance from the moderator team. We will do our
    best to get to your problem as soon as possible. </br></br>

    If you have a question about how the game works, please read the <a href="faq.php" class="contactUs">FAQ</a> 
    and the <a href="intro.php" class="contactUs"> intro to webDiplomacy</a> before using this form. </p>

    <p> Need something else? Take a look at our <a href="modforum.php" class="contactUs">Moderator forum</a> 
    page to get in contact with the moderator team!</p>
    
    </div>';
    print '<div class = "contactUsShow">';
    
    print '<form action="#" method="post">';
    print libAuth::formTokenHTML();
    print   '<p><strong>What do you need to contact us about?</strong></p>
            Issue with game(s) <input type="radio" value="gameIssue" onclick="javascript:gameIssueCheck();" name="issueType" id="gameIssue" required> 
            </br>Other issue <input type="radio" value="otherIssue" onclick="javascript:gameIssueCheck();" name="issueType" id="otherIssue">';
    if ($numberOfGames > 0 && $User->qualifiesForEmergency() )
    {
        print   '</br>Personal Emergency (Automatic Pause)
                <input type="radio" value="emergencyIssue" onclick="javascript:gameIssueCheck();" name="issueType" id="emergencyIssue"><br>';
    }
    else
    {
        print   '<input type="radio" value="emergencyIssue" style="display:none" name="issueType" id="emergencyIssue">';
    }
            
    print '<div id="ifGames" style="display:none">
            <p>Which game(s): </br>
                <select  class = "contactUs" name="games">
                <option selected="selected" value="0">None</option>
                <option value="1">All</option>';

    
    foreach ($GamesData as $values)
    {   
        print '<option value="'.$values->gameID.'">'.$values->gameName.'</option>';	
    }

    print '</select></p>';

    print ' What is the problem? </br>
            <select class="contactUs" name="gamesIssue">
                <option value="orders" selected="selected">Issue entering orders</option>
                <option value="pause">I need a Pause</option>
                <option value="unpause">Game is stuck paused</option>
                <option value="cheating">I think someone is cheating</option>
                <option value="replace">I need a replacement</option>
                <option value="stalemate">Game is stalemated</option>
                <option value="wfo">Game is stuck in Wait-for-Orders</option>
                <option value="crash">Game is crashed</option>
                <option value="other">Other</option>
            </select></p>';

    print '<p class="contact-us-desc">If you asked for a pause tell us for how long and why or your request will <strong>not</strong> be granted.</font></p>';
    print '</div>';

    print '<div id="ifOther" style="display:none">';
    print '<p> What is the problem? </br>
            <select class="contactUs" name="otherIssue">
                <option value="rules" selected="selected">Rules violation</option>
                <option value="otherGame">Issue with game I am not in</option>
                <option value="finishedGame">Issue with a finished game</option>
                <option value="bug">Report bug</option>
                <option value="other">Other</option>
            </select></p>';
    print '</div>';

    print '<div id="ifEmergency" style="display:none">';
    print '<p class="contact-us-desc">This is for personal emergencies only and will instantly pause all of your running games that you are not defeated in. The
    moderator team will give you 7 days to let us know when you expect to be back. If we do not hear back in 7 days we will look for a replacement. <br><br>
    <u>Vacations, business trips, or any other absence you know of ahead of time do not count as a personal emergency.</u> 
    This tool is intended for unexpected absences, such as a family emergency, widespread power outage, natural disaster, or other circumstance you cannot plan for. 
    If you can plan for your pause ahead of time, it is not an emergency. You should inform others in your game in advance that you will need a pause, 
    and if you cannot do so instead contact the moderators in the <a href="modforum.php">moderator forum</a>.</font></br></br>
    Abuse of the emergency pause will be punished with a 50% point dock and removal of your emergency pause privilege at minimum. <br><br>
    Using your emergency pause will instantly pause the following games: ';
    
    $counter = 1;
    foreach ($GamesData as $values)
    {      
        if ($values->processStatus != 'Paused')
        {
            print $values->gameName;	
            if ($counter < $numberOfGames) print ', ';
            $counter++;
        }
    }
    print'</br></br>To prevent abuse we need to know the reason for the emergency pause. We will follow up with this request later at '.$User->email.' to find 
    out how long you need your games to be paused.</br></br>
            <select class="contactUs" name="emergencyIssue">
                <option value="medical" selected="selected">Illness or injury to yourself/family</option>
                <option value="naturalDisaster">Natural Disaster</option>
                <option value="powerOutage">Power Outage</option>
            </select></p>';
    print '</div>';
    
    print ' <p class="contact-us-desc">Please give us any additional details below.</p>
            <textarea name="additionalInfo" class = "contactUs"  rows="5"></textarea>';
    print '<p><input type="submit" class = "green-Submit" name="submit"/></p>';
    print '</form>';
}
print '</div>';
print '</div>';

?>

<script type="text/javascript">
function gameIssueCheck() 
{
    if (document.getElementById('gameIssue').checked) 
    {
        document.getElementById('ifGames').style.display = 'block';
        document.getElementById('ifOther').style.display = 'none';
        document.getElementById('ifEmergency').style.display = 'none';
    }
    else if (document.getElementById('emergencyIssue').checked)
    {
        document.getElementById('ifGames').style.display = 'none';
        document.getElementById('ifOther').style.display = 'none';
        document.getElementById('ifEmergency').style.display = 'block';
    }
    else 
    {
        document.getElementById('ifGames').style.display = 'none';
        document.getElementById('ifOther').style.display = 'block';
        document.getElementById('ifEmergency').style.display = 'none';
    }
}
</script>

<?php
libHTML::footer();
?>
