<?php
/*
    Copyright (C) 2004-2022 Kestas J. Kuliukas

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
 * Small library that sends SMS messages via twilio, used for account verification
 *
 * @package Base
 */

require_once('vendor/autoload.php'); 
use Twilio\Rest\Client; 

class libSMS
{
    public static function send($number, $message)
    {
        if( !isset(Config::$smsConfig) )
            throw new Exception("No SMS configuration present, cannot send SMS messages");
        if( !isset(Config::$smsConfig['isEnabled']) )
            throw new Exception("Attempting to send an SMS message but SMS messages not enabled in config.php");
        if( !isset(Config::$smsConfig['twilioSID']) || strlen(Config::$smsConfig['twilioSID']) === 0 )
            throw new Exception("Send SMS called but no Twilio SID configured in config.php");
        if( !isset(Config::$smsConfig['twilioToken']) || strlen(Config::$smsConfig['twilioToken']) === 0 )
            throw new Exception("Send SMS called but no Twilio SMS token configured in config.php");
        if( !isset(Config::$smsConfig['twilioServiceSID']) || strlen(Config::$smsConfig['twilioServiceSID']) === 0 )
            throw new Exception("Send SMS called but no Twilio SMS messaging service SID configured in config.php");

        $twilio = new Client(Config::$smsConfig['twilioSID'], Config::$smsConfig['twilioToken']); 
        
        $message = $twilio->messages->create($number, // to 
                array(  
                    "messagingServiceSid" => Config::$smsConfig['twilioServiceSID'],      
                    "body" => $message 
                ) 
        );

        return $message->sid;
    }
    public static function sendValidationText($countryCode, $number)
    {
        $fullNumber = self::combineCountryCodeAndNumber($countryCode, $number);
        $token = self::getSixDigitTokenForNumber($fullNumber);
        self::send($fullNumber, "Your webDiplomacy validation token is: " . $token);
    }
    public static function getSixDigitTokenForNumber($fullNumber)
    {
        // 0xfffff
        $numHash = substr(md5($fullNumber),0,5);
        // 000000 - 999999
        $numInt = hexdec("0x".strval($numHash)) % 1000000;
        return $numInt;
    }
    public static function filterNumber($number)
    {
        $number = str_replace('(','',$number);
        $number = str_replace(')','',$number);
        $number = str_replace(' ','',$number);
        $number = str_replace('-','',$number);
        if( substr($number,0,1) == '0' )
            $number = substr($number,1,strlen($number)-1);
        $number = strval(intval($number));
        return $number;
    }
    public static function combineCountryCodeAndNumber($countryCode, $number)
    {
        $number = self::filterNumber($number);

        return "+".$countryCode.$number;
    }
    /**
     * Get a HTML select dropdown of countryu prefixes
     */
    public static function getCountryCodeOptions($formField, $currentCode=null) 
    {
        $buf = '<select '.$formField.'>';
        foreach(self::$countryCodes as $code=>$name)
        {
            $buf .= '<option value="'.$code.'" '.($currentCode == $code ? 'selected' : '').'>'.$name.'</option>';
        }
        return $buf . '</select>';
    }
    private static $countryCodes = array(
        "1" => "USA (+1)",
        "61" => "Australia (+61)",
        "33" => "France (+33)",
        "44" => "UK (+44)",
        "213" => "Algeria (+213)",
        "376" => "Andorra (+376)",
        "244" => "Angola (+244)",
        "1264" => "Anguilla (+1264)",
        "1268" => "Antigua &amp; Barbuda (+1268)",
        "54" => "Argentina (+54)",
        "374" => "Armenia (+374)",
        "297" => "Aruba (+297)",
        "43" => "Austria (+43)",
        "994" => "Azerbaijan (+994)",
        "1242" => "Bahamas (+1242)",
        "973" => "Bahrain (+973)",
        "880" => "Bangladesh (+880)",
        "1246" => "Barbados (+1246)",
        "375" => "Belarus (+375)",
        "32" => "Belgium (+32)",
        "501" => "Belize (+501)",
        "229" => "Benin (+229)",
        "1441" => "Bermuda (+1441)",
        "975" => "Bhutan (+975)",
        "591" => "Bolivia (+591)",
        "387" => "Bosnia Herzegovina (+387)",
        "267" => "Botswana (+267)",
        "55" => "Brazil (+55)",
        "673" => "Brunei (+673)",
        "359" => "Bulgaria (+359)",
        "226" => "Burkina Faso (+226)",
        "257" => "Burundi (+257)",
        "855" => "Cambodia (+855)",
        "237" => "Cameroon (+237)",
        "1" => "Canada (+1)",
        "238" => "Cape Verde Islands (+238)",
        "1345" => "Cayman Islands (+1345)",
        "236" => "Central African Republic (+236)",
        "56" => "Chile (+56)",
        "86" => "China (+86)",
        "57" => "Colombia (+57)",
        "269" => "Comoros (+269)",
        "242" => "Congo (+242)",
        "682" => "Cook Islands (+682)",
        "506" => "Costa Rica (+506)",
        "385" => "Croatia (+385)",
        "53" => "Cuba (+53)",
        "90392" => "Cyprus North (+90392)",
        "357" => "Cyprus South (+357)",
        "42" => "Czech Republic (+42)",
        "45" => "Denmark (+45)",
        "253" => "Djibouti (+253)",
        "1809" => "Dominica (+1809)",
        "1809" => "Dominican Republic (+1809)",
        "593" => "Ecuador (+593)",
        "20" => "Egypt (+20)",
        "503" => "El Salvador (+503)",
        "240" => "Equatorial Guinea (+240)",
        "291" => "Eritrea (+291)",
        "372" => "Estonia (+372)",
        "251" => "Ethiopia (+251)",
        "500" => "Falkland Islands (+500)",
        "298" => "Faroe Islands (+298)",
        "679" => "Fiji (+679)",
        "358" => "Finland (+358)",
        "594" => "French Guiana (+594)",
        "689" => "French Polynesia (+689)",
        "241" => "Gabon (+241)",
        "220" => "Gambia (+220)",
        "7880" => "Georgia (+7880)",
        "49" => "Germany (+49)",
        "233" => "Ghana (+233)",
        "350" => "Gibraltar (+350)",
        "30" => "Greece (+30)",
        "299" => "Greenland (+299)",
        "1473" => "Grenada (+1473)",
        "590" => "Guadeloupe (+590)",
        "671" => "Guam (+671)",
        "502" => "Guatemala (+502)",
        "224" => "Guinea (+224)",
        "245" => "Guinea - Bissau (+245)",
        "592" => "Guyana (+592)",
        "509" => "Haiti (+509)",
        "504" => "Honduras (+504)",
        "852" => "Hong Kong (+852)",
        "36" => "Hungary (+36)",
        "354" => "Iceland (+354)",
        "91" => "India (+91)",
        "62" => "Indonesia (+62)",
        "98" => "Iran (+98)",
        "964" => "Iraq (+964)",
        "353" => "Ireland (+353)",
        "972" => "Israel (+972)",
        "39" => "Italy (+39)",
        "1876" => "Jamaica (+1876)",
        "81" => "Japan (+81)",
        "962" => "Jordan (+962)",
        "7" => "Kazakhstan (+7)",
        "254" => "Kenya (+254)",
        "686" => "Kiribati (+686)",
        "850" => "Korea North (+850)",
        "82" => "Korea South (+82)",
        "965" => "Kuwait (+965)",
        "996" => "Kyrgyzstan (+996)",
        "856" => "Laos (+856)",
        "371" => "Latvia (+371)",
        "961" => "Lebanon (+961)",
        "266" => "Lesotho (+266)",
        "231" => "Liberia (+231)",
        "218" => "Libya (+218)",
        "417" => "Liechtenstein (+417)",
        "370" => "Lithuania (+370)",
        "352" => "Luxembourg (+352)",
        "853" => "Macao (+853)",
        "389" => "Macedonia (+389)",
        "261" => "Madagascar (+261)",
        "265" => "Malawi (+265)",
        "60" => "Malaysia (+60)",
        "960" => "Maldives (+960)",
        "223" => "Mali (+223)",
        "356" => "Malta (+356)",
        "692" => "Marshall Islands (+692)",
        "596" => "Martinique (+596)",
        "222" => "Mauritania (+222)",
        "269" => "Mayotte (+269)",
        "52" => "Mexico (+52)",
        "691" => "Micronesia (+691)",
        "373" => "Moldova (+373)",
        "377" => "Monaco (+377)",
        "976" => "Mongolia (+976)",
        "1664" => "Montserrat (+1664)",
        "212" => "Morocco (+212)",
        "258" => "Mozambique (+258)",
        "95" => "Myanmar (+95)",
        "264" => "Namibia (+264)",
        "674" => "Nauru (+674)",
        "977" => "Nepal (+977)",
        "31" => "Netherlands (+31)",
        "687" => "New Caledonia (+687)",
        "64" => "New Zealand (+64)",
        "505" => "Nicaragua (+505)",
        "227" => "Niger (+227)",
        "234" => "Nigeria (+234)",
        "683" => "Niue (+683)",
        "672" => "Norfolk Islands (+672)",
        "670" => "Northern Marianas (+670)",
        "47" => "Norway (+47)",
        "968" => "Oman (+968)",
        "680" => "Palau (+680)",
        "507" => "Panama (+507)",
        "675" => "Papua New Guinea (+675)",
        "595" => "Paraguay (+595)",
        "51" => "Peru (+51)",
        "63" => "Philippines (+63)",
        "48" => "Poland (+48)",
        "351" => "Portugal (+351)",
        "1787" => "Puerto Rico (+1787)",
        "974" => "Qatar (+974)",
        "262" => "Reunion (+262)",
        "40" => "Romania (+40)",
        "7" => "Russia (+7)",
        "250" => "Rwanda (+250)",
        "378" => "San Marino (+378)",
        "239" => "Sao Tome &amp; Principe (+239)",
        "966" => "Saudi Arabia (+966)",
        "221" => "Senegal (+221)",
        "381" => "Serbia (+381)",
        "248" => "Seychelles (+248)",
        "232" => "Sierra Leone (+232)",
        "65" => "Singapore (+65)",
        "421" => "Slovak Republic (+421)",
        "386" => "Slovenia (+386)",
        "677" => "Solomon Islands (+677)",
        "252" => "Somalia (+252)",
        "27" => "South Africa (+27)",
        "34" => "Spain (+34)",
        "94" => "Sri Lanka (+94)",
        "290" => "St. Helena (+290)",
        "1869" => "St. Kitts (+1869)",
        "1758" => "St. Lucia (+1758)",
        "249" => "Sudan (+249)",
        "597" => "Suriname (+597)",
        "268" => "Swaziland (+268)",
        "46" => "Sweden (+46)",
        "41" => "Switzerland (+41)",
        "963" => "Syria (+963)",
        "886" => "Taiwan (+886)",
        "7" => "Tajikstan (+7)",
        "66" => "Thailand (+66)",
        "228" => "Togo (+228)",
        "676" => "Tonga (+676)",
        "1868" => "Trinidad &amp; Tobago (+1868)",
        "216" => "Tunisia (+216)",
        "90" => "Turkey (+90)",
        "7" => "Turkmenistan (+7)",
        "993" => "Turkmenistan (+993)",
        "1649" => "Turks &amp; Caicos Islands (+1649)",
        "688" => "Tuvalu (+688)",
        "256" => "Uganda (+256)",
        "380" => "Ukraine (+380)",
        "971" => "United Arab Emirates (+971)",
        "598" => "Uruguay (+598)",
        "7" => "Uzbekistan (+7)",
        "678" => "Vanuatu (+678)",
        "379" => "Vatican City (+379)",
        "58" => "Venezuela (+58)",
        "84" => "Vietnam (+84)",
        "84" => "Virgin Islands - British (+1284)",
        "84" => "Virgin Islands - US (+1340)",
        "681" => "Wallis &amp; Futuna (+681)",
        "969" => "Yemen (North)(+969)",
        "967" => "Yemen (South)(+967)",
        "260" => "Zambia (+260)",
        "263" => "Zimbabwe (+263)");
}
 
