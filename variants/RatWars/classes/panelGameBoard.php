<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class Fog_panelGameBoard extends panelGameBoard
{
	// Load our custom map.php that revales only parts of the map
	function mapHTML() {

		if ($this->phase == 'Finished') return parent::mapHTML();
	
		global $User, $DB;
		$map=parent::mapHTML();

		if ($this->Members->isJoined()) {
			list($ccode)=$DB->sql_row("SELECT text FROM wD_Notices WHERE toUserID=3 AND timeSent=0 AND fromID=".$this->id);
			$verify=substr($ccode,((int)$this->Members->ByUserID[$User->id]->countryID)*6,6);
		} elseif ($User->type['Moderator']) {
			list($ccode)=$DB->sql_row("SELECT text FROM wD_Notices WHERE toUserID=3 AND timeSent=0 AND fromID=".$this->id);
			$verify=substr($ccode,0,6);
		} else {
			$verify='fog';
		}
		$mapTurn = (($this->phase=='Pre-game'||$this->phase=='Diplomacy') ? $this->turn-1 : $this->turn);
		$newFile = $oldFile = Game::mapFilename($this->id, $mapTurn, 'small');
		$newFile = str_replace(".map","-".$verify.".map",$newFile);
		if (!(file_exists($newFile)))
			$newFile='variants/RatWars/resources/fogmap.php?&verify='.$verify.'&gameID='.$this->id.'&turn='.$mapTurn;
		$oldMap="map.php?";
		$newMap="variants/RatWars/resources/fogmap.php?verify=".$verify."&";
		$map = str_replace($oldMap ,$newMap ,$map);
		$map = str_replace($oldFile,$newFile,$map);

		$map = str_replace("loadMap(","loadMap('".$verify."',",$map);
		$map = str_replace("loadMapStep(","loadMapStep('".$verify."',",$map);
		
		return $map;
	}
	
	// Load out custom map with the Javascript history-buttons too...
	protected function mapJS($mapTurn) {

		if ($this->phase == 'Finished') return parent::mapJS($mapTurn);

		libHTML::$footerScript[] = 'turnToText='.$this->Variant->turnAsDateJS()."mapArrows($mapTurn,$mapTurn);";
		libHTML::$footerIncludes[] = '../variants/RatWars/resources/my_mapUI.js';
	}
	
}

class RatWarsVariant_panelGameBoard extends Fog_panelGameBoard {}
