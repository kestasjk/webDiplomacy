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

global $Mailer;
$Mailer = new Mailer();

$tab = '';
global $DB;

if( !$User->type['User'] )
{
	libHTML::error(l_t("This page is only for registered users."));
}

$submitted = false;
$issueType = '';
$gamesValid = false;
$games = -1;
$postedGameName = '';
$postedGameIssue = '';
$postedOtherIssue = '';
$postedAdditionalInfo = '';

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
}

// Game Search Variables
$GamesData = array();

// Get values from posted contact requests. 
if(isset($_POST['submit'])) 
{
    $submitted = true;
    if (isset($_POST['issueType']))
    {
        if ($_POST['issueType'] == 'gameIssue') { $issueType='gameIssue'; }
        else if ($_POST['issueType'] == 'otherIssue') { $issueType='otherIssue'; }
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
$sql = "SELECT g.id, g.name, g.pot, g.gameOver, g.processStatus, ( CASE WHEN g.password IS NULL THEN 'False' ELSE 'True' END ) AS password,
				g.phaseMinutes, g.anon, g.pressType, g.directorUserID 
                FROM wD_Games g 
                inner join wD_Members m on m.gameID = g.id
                WHERE g.gameOver = 'No' and m.status <> 'Defeated' and m.userID = ". $User->id;

$tablChecked = $DB->sql_tabl($sql);
$allGames = '';

while (list($gameID, $gameName, $gameOver, $processStatus, $password, $phaseMinutes, $anon, $pressType, $directorUserID) = $DB->tabl_row($tablChecked))
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
    array_push($GamesData,$myGame);

    $allGames = $allGames.'https://www.webdiplomacy.net/board.php?gameID='.$gameID.'<br>';
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
    try
	{
        $Mailer->Send(array($email=>$email), $subject.' '.$User->username,
        "
        This request is from <a href='https://www.webdiplomacy.net/profile.php?userID=".$User->id."' class = 'contactUs'>".$User->username. "</a>
        , and their registered email is: ".$User->email."<br><br>

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

    If you have a question about how the game works please read the <a href="faq.php" class="contactUs">FAQ</a> 
    and the <a href="intro.php" class="contactUs"> intro to webDiplomacy</a> before using this form. </p>

    <p> Need something else? Take a look at our <a href="contactUs.php" class="contactUs">Contact Info</a> 
    page to learn how to contact an owner and see all the problems moderators can help with!</p>
    
    </div>';
    print '<div class = "contactUsShow">';
    
    print '<form action="#" method="post">';
    print   '<p><strong>What do you need to contact us about?</strong></p>
            Issue with game(s) <input type="radio" value="gameIssue" onclick="javascript:gameIssueCheck();" name="issueType" id="gameIssue" required> 
            </br>Other issue <input type="radio" value="otherIssue" onclick="javascript:gameIssueCheck();" name="issueType" id="otherIssue"><br>';
            
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

    print '<p><font color="red">If you asked for a pause tell us for how long and why or your request will <strong>not</strong> be granted.</font></p>';
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
    
    print ' <p>Please give us any additional details
            <textarea name="additionalInfo" class = "contactUs"  rows="5"></textarea></p>';
    print '<p><input type="submit" class = "contactUs-submit" name="submit"/></p>';
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
    }
    else 
    {
        document.getElementById('ifGames').style.display = 'none';
        document.getElementById('ifOther').style.display = 'block';
    }
}
</script>

<?php
libHTML::footer();
?>