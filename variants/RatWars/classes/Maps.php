<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Fog_Maps extends Maps
{
	// The verify-code for the map display
	protected $verify;
	
	function mapHTML($turn)
	{
		return str_replace("map.php?" ,"variants/RatWars/resources/fogmap.php?verify=".$this->verify."&" , parent::mapHTML($turn));
	}
	
	function __construct()
	{
		global $Game, $User, $DB;
		if ($Game->Members->isJoined()) {
			list($ccode)=$DB->sql_row("SELECT text FROM wD_Notices WHERE toUserID=3 AND timeSent=0 AND fromID=".$Game->id);
			$this->verify=substr($ccode,((int)$Game->Members->ByUserID[$User->id]->countryID)*6,6);
		} elseif ($User->type['Moderator']) {
			list($ccode)=$DB->sql_row("SELECT text FROM wD_Notices WHERE toUserID=3 AND timeSent=0 AND fromID=".$Game->id);
			$this->verify=substr($ccode,0,6);
		} else {
			$this->verify='fog';
		}
	}
}
 
class RatWarsVariant_Maps extends Fog_Maps {}
