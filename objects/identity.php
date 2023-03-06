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

// No defined() check here; this may be called before header.php

/**
 * A collection of functions to help identify users.,
 *
 * @package Base
 */

 /*
 
// Verification score
// ------------------
// verificationUserType
// Admin 500
// Moderator 300

// verificationAccountDetail
// Facebook 200
// Paypal or Donor flag 200
// SMS 150
// Google 50
// Entered location 10

// verificationRelationships
// Relationships declared 5

// verificationUserActivity
// Forum messages (log(messageCount)-1)*5 (10 -> 14, 1000 -> 30) phpbb_users.webdip_user_id .user_posts
// Mod forum messages (log(messageCount)-1)*5 (10 -> 14, 1000 -> 30) phpbb_users.webdip_user_id .user_posts
// Non-bot games (log(gamesCount)-1)*20
// Game messages (log(messageCount)-1)*10 (10 -> 14, 1000 -> 30)
// Points (log(point)-1)
// Weeks joined (log((UNIX_TIMESTAMP() - timeJoined)/(7*24*60))-1)*50

*/

class userIdentity
{
    public $id;
    public $userID;
    public $identityType;
    public $systemVerified;
    public $systemVerifiedTime;
    public $timeCreated;
    public $identityText;
    public $identityNumber;
    public $modVerified;
    public $modVerifiedTime;
    public $modRequestedTime;
    public $modRequestedUserID;
    public $modUserID;
    public $timeSubmitted;
    public $modRequestedFromGroupID;
    public $isDirty;
    public $score;
    public $userComment;
    public $modComment;

    public function getToken() { return libAuth::generateToken('identity_'.$this->userID.'_'.$this->identityType); }
    public function validateToken() { return libAuth::validateToken('identity_'.$this->userID.'_'.$this->identityType); }

    public function getLabel() { return "No label"; }
    public function getDescription() { return "No description"; }
    public function getPrivacyDescription() { return null; }
    public function getForm() { return null; }
    public function getAvailableScore() { return 0; }
    public function getIsVisible() { return false; }
    public function getIsDataVisible() { return false; }
    public function getIsDataErasable() { return false; }
    
    public function trySystemVerify() { return false; }
    public function submitUserData($args) { return false; }

    public function eraseData() {
        global $DB;
        if( $this->getIsDataErasable() )
        {
            $DB->sql_put("UPDATE wD_UserIdentities SET identityText='', identityNumber=0, isDirty = 1, score = 0, userComment = CONCAT('(Erased) ',COALESCE(userComment,'')) WHERE id=".$this->id);
            $this->identityText = '';
            $this->identityNumber = 0;
        }
    }

    // All possible identity types
    public static $identityTypes = array('facebook','google','youtube','instagram','github','twitter','playdiplomacy',
        'backstabbr','vdiplomacy','webdiplomacyFork','paypal','sms','photo','relationshipDeclared',
        'relationshipModChecked','longTimePlayer','forumMember','location');

    // Identity types that a user can submit interactively
    public static $interactiveIdentityTypes = array('facebook','google','sms','location');
    public static $visibleIdentityTypes = array('facebook','google','sms','photo','paypal','location','relationshipDeclared','relationshipModChecked','longTimePlayer','forumMember','location');

    public static $identityTypeExplanations = array(
        'facebook' => 'A link to a Facebook account, by signing into Facebook from this site',
        'google' => 'A link to a Google accounnt, by signing into Google from this site',
        'youtube' => 'A link to a YouTube account, by signing into YouTube from this site',
        'instagram' => 'A link to an Instagram account, by signing into Instagram from this site',
        'github' => 'A link to a Github account, by signing into Github from this site',
        'twitter' => 'A link to a Twitter account, by signing into Twitter from this site',
        'playdiplomacy' => 'A link to a PlayDiplomacy account, by inserting some text into your PlayDiplomacy profile page',
        'backstabbr' => 'A link to a Backstabbr account, by inserting some text into your Backstabbr profile page',
        'vdiplomacy' => 'A link to a vDiplomacy account, by inserting some text into your vDiplomacy profile page',
        'webdiplomacyFork' => 'A link to a known webDiplomacy fork/translation account, by inserting some text into your profile page',
        'paypal' => 'A link to a PayPal account, by making a token payment or donation',
        'sms' => 'A link to a mobile phone by sending and validating an SMS message',
        'photo' => 'A link to a face photo containing written text, verified by a mod (The photo is not saved by/stored in this site, it can be removed after verification)',
        'relationshipDeclared' => 'The user has declared a relationship with another user and had it verified by a mod',
        'relationshipModChecked' => 'The user has had a suspicion checked by a mod',
        'longTimePlayer' => 'The user has been playing games for a long time',
        'forumMember' => 'The user participates in the forum',
        'location' => 'Your browser can send your location if permission is granted'
    );

    public static function loadIdentityData($userID)
    {
        global $DB;
        $tabl = $DB->sql_tabl("SELECT * FROM wD_UserIdentity WHERE userID = " . $userID);

        $identityRecords = array();
        while($row = $DB->tabl_hash($tabl))
        {
            $i = new userIdentity($row);
            $identityRecords[$i->identityType] = $i;
        }

        return $identityRecords;
    }

    public static function modRequestIdentityData($userID, $identityType, $modRequestedFromGroupID)
    {
        
    }
    public function __construct($row)
    {
        foreach($row as $k=>$v)
            $this->{$k} = $v;
    }

    public static function eraseForm($identityType)
    {
        if( isset($_REQUEST['identityErase']) )
            return '<a href="usercp?identityErase='.$identityType.'&confirm=on#'.$identityType.'">Are you sure you want to erase this identity data? This will lower your identity rating, and cannot be reversed.</a>';
        else
            return '<a href="usercp?identityErase='.$identityType.'#'.$identityType.'">Erase</a>';
    }

    //'facebook','google','youtube','instagram','github','twitter','playdiplomacy','backstabbr','vdiplomacy','webdiplomacyFork',
    //'paypal','sms','photo','relationshipDeclared','relationshipModChecked','longTimePlayer','forumMember'
	public static function locationForm()
    {
        $buf = <<<REQUESTLOCATION
        <script type="text/javscript">
        var x = document.getElementById("demo");
        function getLocation(outputElement) {
          if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    outputElement.innerHTML = "Location: " + position.coords.latitude + "/" + position.coords.longitude + "/" + position.coords.accuracy + " ... Submitting location now.";
                    window.setTimeout(function(){
                        window.location = "usercp.php?identityProcess=location&locLat=" + position.coords.latitude + "&locLon=" + position.coords.longitude + "&locAcc=" + position.coords.accuracy + "#location";
                    }, 2000 )
                },
                (error) => {
                      switch(error.code) {
                        case error.PERMISSION_DENIED:
                            outputElement.innerHTML = "User denied the request for Geolocation."
                            break;
                        case error.POSITION_UNAVAILABLE:
                            outputElement.innerHTML = "Location information is unavailable."
                            break;
                        case error.TIMEOUT:
                            outputElement.innerHTML = "The request to get user location timed out."
                            break;
                        case error.UNKNOWN_ERROR:
                            outputElement.innerHTML = "An unknown error occurred."
                            break;
                  });
          } else {
            outputElement.innerHTML = "Geolocation is not supported by this browser.";
          }
        }
        </script>

        <a href="javascript:getLocation();">Provide and save my browser's location</a> <span id="locationResult"></span>
REQUESTLOCATION;

        return $buf;
    }
    public static function locationProcess()
    {
        if( isset($_REQUEST['locLat']) && isset($_REQUEST['locLon']) && isset($_REQUEST['locAcc']) )
        {
            $locLat = (double)$_REQUEST['locLat'];
            $locLon = (double)$_REQUEST['locLon'];
            $locAcc = (double)$_REQUEST['locAcc'];
            $locData = implode('|', array($locLat, $locLon, $locAcc));
        }
    }
    
    public static function smsForm()
    {
        $buf = '<form action="usercp.php#identitysms" action="post"><input type="hidden" name="identityProcess" value="sms" />';
        $buf .= '<strong>Mobile number:</strong> ';
		require_once('lib/sms.php');
		$buf .= libSMS::getCountryCodeOptions("name='identityMobileCountryCode'");
        $buf .= ' <input type="text" class="settings" name="identityMobileNumber" style="width:50% !important;" size="10" value="" /> ';
        $buf .= '<br />';
            
        $buf .= '<strong>Code received:</strong> ';
        $buf .= ' <input type="text" class="settings" name="identitySMSCodeReceived" style="width:50% !important;" size="10" value="" /> ';
        
        $buf .= '</form>';
/*

		if( isset($SQLVars['mobileCountryCode']) && isset($SQLVars['mobileNumber']) && $SQLVars['mobileCountryCode'] && $SQLVars['mobileNumber'] )
		{
			$isMobileChanged = ($SQLVars['mobileNumber'] != $User->mobileNumber || $SQLVars['mobileCountryCode'] != $User->mobileCountryCode);
			if( !$User->isMobileValidated || $isMobileChanged )
			{
				$isMobileValidated = false;
				if( isset($SQLVars['mobileValidationCode'] ) && $SQLVars['mobileValidationCode'] )
				{
					if( intval($SQLVars['mobileValidationCode']) === intval(libSMS::getSixDigitTokenForNumber(libSMS::combineCountryCodeAndNumber($SQLVars['mobileCountryCode'],$SQLVars['mobileNumber']))) )
					{
						$formOutput .= l_t('SMS code validated succesfully. ').' ';
						$isMobileValidated = true;
					}
					else
					{
						$formOutput .= l_t('SMS code could not be validated, please try again. ').' ';
					}
				}
				else
				{
					libSMS::sendValidationText($SQLVars['mobileCountryCode'], $SQLVars['mobileNumber']);
					$formOutput .= l_t('Phone validation SMS message sent. Please submit the verification code you receive below. ').' ';
				}
				
				if ( $set != '' ) $set .= ', ';
				$set .= ' isMobileValidated = '.($isMobileValidated? '1' : '0').' ';
			}
		}*/
        return $buf;
    }
    public static function smsProcess()
    {
        $formOutput = '';
        if ( isset($_REQUEST['identityMobileCountryCode'] ) && $_REQUEST['identityMobileCountryCode']   )
        {
            if( isset($_REQUEST['identitySMSCodeReceived']) )
            {
                if( intval($_REQUEST['identitySMSCodeReceived']) === intval(libSMS::getSixDigitTokenForNumber(libSMS::combineCountryCodeAndNumber($_REQUEST['identityMobileCountryCode'],$_REQUEST['identityMobileNumber']))) )
                {
                    $formOutput .= l_t('SMS code validated succesfully. ').' ';
                    $mobileNumber = $_REQUEST['identityMobileCountryCode'].$_REQUEST['identityMobileNumber'];
                }
                else
                {
                    $formOutput .= l_t('SMS code could not be validated, please try again. ').' ';
                }
            }
            else
            {

                libSMS::sendValidationText($_REQUEST['identityMobileCountryCode'], $_REQUEST['identityMobileNumber']);
                $formOutput .= l_t('Phone validation SMS message sent. Please submit the verification code you receive below. ').' ';
            }
        }
        return $formOutput;
    }
    private static $openIDUserInfo = null;
    private static function getAndSaveOpenIDUserInfo()
    {
        if( is_null(self::$openIDUserInfo))
        {
            $userInfo = libOpenID::getUserInfo();
            if( $userInfo ) libOpenID::saveOpenIDData($userInfo);
            self::$openIDUserInfo = $userInfo;
        }
        return self::$openIDUserInfo;
    }
    private static $openIDValidSources = null;
    private static function getOpenIDUserValidSources()
    {
        global $User;
        if( is_null(self::$openIDValidSources))
        {
            $validSources = libOpenID::getValidSources($User->id);
            self::$openIDValidSources = $validSources;
        }
        return self::$openIDValidSources;
    }
    public static function googleForm()
    {
        $validSources = self::getOpenIDUserValidSources();
        if( $validSources['google'] )
        {
            return 'You are linked to a google account.';
        }
        else
        {
            if( !is_null(self::$openIDUserInfo) )
            {
                return '<a href="usercp.php?auth0Logout=on">Log out of your current OpenID provider to log into Google</a>';        
            }
            else
            {
                return '<a href="usercp.php?auth0Login=on">Log into an external provider</a>';
            }
        }
    }
    public static function googleProcess()
    {
        $validSources = $this->getOpenIDUserValidSources();
        if( $validSources['google'] )
        {
            //$validSources['google'];
        }
    }
    public static function facebookForm()
    {
        global $User;

        $validSources = libOpenID::getValidSources($User->id);
        if( $validSources['facebook'] )
        {
            return 'You are linked to a Facebook account.';
        }
        else
        {
            if( !is_null($self::$openIDUserInfo) )
            {
                return '<a href="usercp.php?auth0Logout=on">Log out of your current OpenID provider to log into Facebook</a>';        
            }
            else
            {
                return '<a href="usercp.php?auth0Login=on">Log in</a>';
            }
        }
    }
    public static function facebookProcess()
    {
        global $User;
        $validSources = libOpenID::getValidSources($User->id);
        if( $validSources['facebook'] )
        {
            //$validSources['facebook'];
        }
    }
    public static function panel($PanelUser)
    {
        global $User;

        $isCurrentUser = ( $User->id == $PanelUser->id );
        $isModeratorUser = ( ($User->type['Moderator'] || $User->type['Admin']) && !$isCurrentUser );

        $identityData = self::loadIdentityData($PanelUser->id);

        $buf = '<div class="content-bare content-board-header content-title-header">
        <div class="pageTitle barAlt1">
            '.libHTML::pathToSVG(self::$identityIcon).' Identity rating information (beta)
        </div>
        <div class="pageDescription">
            Information that associates an account with an identity/individual, which helps to prevent cheating by multi-accounting '.
            '(using multiple accounts) or exploiting undisclosed relationships. This data is used to generate an identity rating, which helps '.
            'moderators identity cheaters, and allows players to exclude accounts from games based on whether they have no identity information.
        </div>
    </div>';
        $buf = '<div class="content content-follow-on">';
        $buf .= '<p class="notice">Current rating: '.$PanelUser->identityScore.'% - '.libHTML::identityIcon($PanelUser->identityScore).'</p>';

        $buf .= '<table class="rrInfo">';
        $buf .= '<tr>';
        $buf .= '<th>';
        $buf .= 'Type';
        $buf .= '</th>';
        $buf .= '<th>';
        $buf .= 'Rating +/-';
        $buf .= '</th>';
        if( $isCurrentUser || $isModeratorUser )
        {
            $buf .= '<th>';
            $buf .= 'Current data';
            $buf .= '</th>';
        }
        if( $isCurrentUser )
        {
            $buf .= '<th>';
            $buf .= 'Submit';
            $buf .= '</th>';
            //$buf .= '<th>';
            //$buf .= 'Comment';
            //$buf .= '</th>';
        }
        if( $isCurrentUser || $isModeratorUser )
        {
            //$buf .= '<th>';
            //$buf .= 'Mod comment';
            //$buf .= '</th>';
            //$buf .= '<th>';
            //$buf .= 'Mod comment';
            //$buf .= '</th>';
        }
        $buf .= '</tr>';
        
        foreach(self::$visibleIdentityTypes as $identityType)
        {
            $buf .= '<tr>';

            $buf .= '<td>';
            if( isset(self::$identityIcon[$identityType])) 
                $buf .= libHTML::pathToSVG(self::$identityIcon[$identityType]);
            else   
                $buf .= $identityType;
            $buf .= '</td>';
            
            $buf .= '<td>';
            if( !isset($identityRecords[$identityType]) )
                $buf .= '0';
            else
                $identityRecords[$identityType]->score;
            $buf .= '</td>';
            
            $buf .= '</tr>';
        }

        $buf .= '</table>';
        $buf .= '</div>';
        return $buf;
    }
}
