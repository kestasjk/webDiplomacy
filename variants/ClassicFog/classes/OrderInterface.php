<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicFogVariant_OrderInterface extends OrderInterface {

	protected function jsLiveBoardData() {
	
		global $User, $DB, $Game;

		list($ccode)=$DB->sql_row("SELECT text FROM wD_Notices WHERE toUserID=3 AND timeSent=0 AND fromID=".$this->gameID);
		$verify=substr($ccode,((int)$Game->Members->ByUserID[$User->id]->countryID)*6,6);
		
		$jsonBoardDataFile = Game::mapFilename($this->gameID, ($this->phase=='Diplomacy'?$this->turn-1:$this->turn), 'json');
		$jsonBoardDataFile = str_replace(".map","-".$verify.".map",$jsonBoardDataFile);

		if( !file_exists($jsonBoardDataFile) )
			$jsonBoardDataFile='variants/ClassicFog/resources/fogmap.php?verify='.$verify.'&gameID='.$this->gameID.'&turn='.$this->turn.'&phase='.$this->phase.'&mapType=json'.(defined('DATC')?'&DATC=1':'').'&nocache='.rand(0,1000);
		else
			$jsonBoardDataFile.='?phase='.$this->phase.'&nocache='.rand(0,10000);

		return '<script type="text/javascript" src="'.STATICSRV.$jsonBoardDataFile.'"></script>';
	
	}
	
	protected function jsLoadBoard() {
		parent::jsLoadBoard();

		// The Staring unit in Benevatto can't move...
		if( $this->phase=='Diplomacy') {
			libHTML::$footerIncludes[] = '../variants/ClassicFog/resources/supportfog.js';
			foreach(libHTML::$footerScript as $index=>$script)
				if(strpos($script, 'loadModel();') )
					libHTML::$footerScript[$index]=str_replace('loadModel();','loadModel();SupportFog();', $script);
		}
	}
	
}