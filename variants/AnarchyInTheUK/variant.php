<?php
/*
	Copyright (C) 2012 Oliver Auth / Scordatura

	This file is part of the Anarchy in the UK variant for webDiplomacy

	The Anarchy in the UK variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Anarchy in the UK variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class AnarchyInTheUKVariant extends WDVariant {
   public $id         =79;
   public $mapID      =79;
   public $name       ='AnarchyInTheUK';
   public $fullName   ='Anarchy in the UK';
   public $author     ='amisond and Evansevern';
   public $adapter    ='amisond and Acquiesce';
   public $codeVersion='1.1.1';   
   
   public $countries=array('Merseyside', 'Up North', 'London', 'Bristol', 'Wales', 'East-Anglia');

   public function __construct() {
      parent::__construct();

      $this->variantClasses['drawMap']            = 'AnarchyInTheUK';
      $this->variantClasses['adjudicatorPreGame'] = 'AnarchyInTheUK';
      $this->variantClasses['OrderInterface']     = 'AnarchyInTheUK';

   }

   public function turnAsDate($turn) {
      if ( $turn==-1 ) return "Pre-game";
      else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 2000);
   }

   public function turnAsDateJS() {
      return 'function(turn) {
         if( turn==-1 ) return "Pre-game";
         else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 2000);
      };';
   }
}

?>