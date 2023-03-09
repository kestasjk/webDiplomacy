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
 * @subpackage Forms
 */

// Check whether we're in play-now mode:
define('IN_CODE',true);
require_once('config.php');
if( Config::isOnPlayNowDomain() ) define('PLAYNOW',true);

require_once('header.php');

global $User, $Misc, $DB;

if ( $Misc->Panic )
{
	libHTML::notice(l_t('Game creation disabled'), 
	l_t("Game creation has been temporarily disabled while we take care of an unexpected problem. Please try again later, sorry for the inconvenience."));
}

if( defined('PLAYNOW') )
{
	if( !isset($User) || $User->type['Guest'] || !$User->type['User'] )
	{
		// Make a User
		// Save their key, if present
		// Until no key
		// Set their new key their key
		
		//libAuth::keyWipe();
		// Generate user key
		$acct = 'diplonow_'.round(rand(0,100000));
		while( 0 != $DB->sql_row("SELECT COUNT(1) FROM wD_Users WHERE username='" . $acct . "'")[0] )
		{
			$acct = 'diplonow_'.round(rand(0,100000));
		}
		$pass = (string)(rand(0,1000000000)); 
		//$DB->sql_put("INSERT INTO wd_Users (username,type,email,points,comment,homepage,timejoined,timeLastSessionEnded,password) VALUES ('".$acct."', 'User', '".$acct."', 0, '', '', ".time().", ".time().", UNHEX('".$passHash."'));");
		$DB->sql_put("INSERT INTO wD_Users(
			`username`,`email`,`points`,`comment`,`homepage`,`hideEmail`,`timeJoined`,`locale`,`timeLastSessionEnded`,`lastMessageIDViewed`,`password`,`type`,`notifications`,`muteReports`,`silenceID`,`cdCount`,`nmrCount`,`cdTakenCount`,`phaseCount`,`gameCount`,`reliabilityRating`,`deletedCDs`,`tempBan`,`emergencyPauseDate`,`yearlyPhaseCount`,`tempBanReason`,`optInFeatures`
			)
			SELECT '".$acct."' `username`,'".$acct."' `email`, 100 `points`,`comment`,`homepage`,`hideEmail`,`timeJoined`,`locale`,".time()." `timeLastSessionEnded`,".time()."`lastMessageIDViewed`,UNHEX('".libAuth::pass_Hash($pass)."'),'User' `type`,`notifications`,`muteReports`,`silenceID`,`cdCount`,`nmrCount`,`cdTakenCount`,`phaseCount`,`gameCount`,`reliabilityRating`,`deletedCDs`,`tempBan`,`emergencyPauseDate`,`yearlyPhaseCount`,`tempBanReason`,1
			FROM wD_Users
			WHERE id = 1");
		list($newUserID) = $DB->sql_row("SELECT LAST_INSERT_ID()");
		
		//$NewUser = new User($newUserID);
		$key = libAuth::userPass_Key($acct, $pass); // Password is never uysed
		

		$cookieKey = $key;//libAuth::generateKey($newUserID, $pass);
		setcookie('wD-Key',$cookieKey,['expires'=>time()+365*24*60*60,'samesite'=>'Lax']);

		global $User;
		$User = new User($newUserID);
	}
	$_REQUEST['newGame'] = array('variantID'=>1, 'name'=>$User->username, 'countryID'=>0);
}

if( !$User->type['User'] )
{
	libHTML::notice(l_t('Not logged on'),l_t("Only a logged on user can create games. Please <a href='logon.php' class='light'>log on</a> to create your own games."));
}

// Limit users to 3 bot games at a time unless they are a moderator. 
if ($User->getBotGameCount() > 2)
{
    if (!$User->type['Moderator'])
    {
        libHTML::notice('3 bot games at a time.','Sorry, only 3 bot games at a time, please finish one of your current ones to start another!');
    }
}

libHTML::starthtml();

if( isset($_REQUEST['newGame']) and is_array($_REQUEST['newGame']) )
{
	try
	{
		$form = $_REQUEST['newGame']; // This makes $form look harmless when it is unsanitized; the parameters must all be sanitized

		$input = array();
		$required = array('variantID', 'name', 'countryID');

		if ( !isset($form['missingPlayerPolicy']) ) {$form['missingPlayerPolicy'] = 'Normal'; }
		
		foreach($required as $requiredName)
		{
			if ( isset($form[$requiredName]) ) { $input[$requiredName] = $form[$requiredName]; }
			else{ throw new Exception(l_t('The variable "%s" is needed to create a game, but was not entered.',$requiredName)); }
        }
        
		unset($required, $form);

		$input['variantID']=(int)$input['variantID'];
		$input['countryID']=(int)$input['countryID'];
		if( !in_array($input['variantID'],Config::$apiConfig['variantIDs']) ) { throw new Exception(l_t("Variant ID given (%s) doesn't represent a real variant.",$input['variantID'])); }

		// If the name isn't unique or is too long the database will stop it
		$input['name'] = $DB->escape($input['name']);
		if ( !$input['name'] ) { throw new Exception(l_t("No name entered.")); }

		list($countryCount) = $DB->sql_row("SELECT countryCount FROM wD_VariantInfo WHERE variantID=".$input['variantID']);
		
		if ($input['countryID'] < 0 or $input['countryID'] > $countryCount)
		{
			throw new Exception(l_t("%s is an invalid country ID.",(string)$input['countryID']));
		}

		// Prevent temp banned players from making new games.
		if ($User->userIsTempBanned())
		{
			libHTML::notice('Temporary block', 'You are blocked from creating new games. Please visit the <a href="modforum.php">mod forum</a> to speak to a moderator.');
		}

		// Create Game record & object
		require_once(l_r('gamemaster/game.php'));
		$phaseMinutes = defined('PLAYNOW') ? 24*60 : 3*24*60;
		$Game = processGame::create($input['variantID'],$input['name'],'',5,'Unranked', $phaseMinutes, -1, $phaseMinutes, -1, 60,'No','Regular','Normal','draw-votes-public',0,4,'MemberVsBots');

		// Create first Member record & object
		processMember::create($User->id, 5, $input['countryID']);

		//Add Bots
		$botNum = $countryCount - 1;
		// $tabl = $DB->sql_tabl("SELECT id FROM wD_Users WHERE type LIKE '%bot%' LIMIT ".$botNum);
		// Use a specific bot for a specific variant for now. A new Config:: function is needed to be able to flexibly map variants to certain specialized bots
		$tabl = $DB->sql_tabl("SELECT id FROM wD_Users WHERE type LIKE '%bot%' ".($input['variantID']==15?" AND username='FairBot' ":"")." LIMIT ".$botNum);
        
		$currCountry = 1;

		while (list($botID) = $DB->tabl_row($tabl))
		{
			if($currCountry == $input['countryID'])
			{
				$currCountry += 1;
			}
			if ($input['countryID'] == 0)
			{
				processMember::create($botID, 5, 0);
			}
			else
			{
				processMember::create($botID, 5, $currCountry);
			}
			$currCountry += 1;
		}
		// Get the game started straight away
		$DB->sql_put('UPDATE wD_Games SET processTime = ' . time() . ' WHERE id = ' . $Game->id);
		$MC->append('processHint',','.$Game->id);
		$Game->Members->joinedRedirect();
	}
	catch(Exception $e)
	{
		print '<div class="content">';
		print '<p class="notice">'.$e->getMessage().'</p>';
		print '</div>';
	}
}

if ( $User->points >= 5 ) { $defaultPoints = 5; }
else
{
	print l_t("You cannot create a new game because you have less than 5%s, you only have %s%s. ".
		"You will always have at least 100 points, including the points that you have bet into active games, so if you want ".
		"to start a new game just wait until your other games have finished (<a href='points.php#minpoints' class='light'>read more</a>).",libHTML::points(),$User->points,libHTML::points());

	print '</div>';
	libHTML::footer();
}

if( isset($input) && isset($input['points']) ) { $formPoints = $input['points']; }
else { $formPoints = $defaultPoints; }

print '<div class="content-bare content-board-header content-title-header">
	<div class="pageTitle barAlt1">Create a new game against bots</div>
	<div class="pageDescription">Start a new game of Diplomacy against bots.</div>
</div>
<div class="content content-follow-on">
	<p><a href="gamecreate.php">Play a game against humans</a></p>
	<div class = "gameCreateShow">
	<p>All games against bots are unranked, with 3 day phases and 4 excused missed turns. However, anytime you ready up your orders, the game will immediately move to the next phase.</p>
		<form method="post">
			<p>
				<strong>Game Name:</strong></br>
				<input class = "gameCreate" type="text" name="newGame[name]" value="" size="30">
			</p>
			
			<strong>Variant type (map choices):</strong>
			<select id="variantID" class = "gameCreate" name="newGame[variantID]" onChange="setExtOptions(this.value)">';
			
?>			
<script type="text/javascript">
	function setExtOptions(i){
		document.getElementById('countryID').options.length=0;
		switch(i)
		{
			<?php
			$checkboxes=array();
			$first='';
			foreach(Config::$variants as $variantID=>$variantName)
			{
				if (in_array($variantID, Config::$apiConfig['variantIDs']))
				{
					$Variant = libVariant::loadFromVariantName($variantName);
					$checkboxes[$Variant->fullName] = '<option value="'.$variantID.'"'.(($first=='')?' selected':'').'>'.$Variant->fullName.'</option>';
					if($first=='')
					{
						$first='"'.$variantID.'"';
						$defaultName=$Variant->fullName;
					}
					print "case \"".$variantID."\":\n";		
					print "document.getElementById('countryID').options[0]=new Option ('Random','0');";
					for ($i=1; $i<=count($Variant->countries); $i++)
						print "document.getElementById('countryID').options[".$i."]=new Option ('".$Variant->countries[($i -1)]."', '".$i."');";
					print "break;\n";		
				}	
				ksort($checkboxes);	
			}
			?>	
		}
	}
</script>
<?php		
			print implode($checkboxes);
			print '</select>';
			print '</br></br>

			<strong>Country: </strong>
			<select id="countryID" class="gameCreate" name="newGame[countryID]">
			</select>
			</br></br>
			<p class="notice">
				<input class = "green-Submit" type="submit"  value="Create">
			</p>';
			?>
<script type="text/javascript">
	setExtOptions(<?php print $first;?>);
</script>
			<?php
			print '</form>
	</div>';

print '</div>';
libHTML::footer();
?>
