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

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * Code that calculates and alters Ghost Ratings
 *
 */
 class GhostRatings
 {  
   private $gameID;
   private $SCcounts;
   private $memberStatus;
   private $variantID;
   private $pressType;
   private $potType;
   private $gameTurns;
   private $gameStatus;
   private $phaseMinutes;
   private $victorySC;
   private $variantSC;
   private $winner;
   private $time;
   
   private $variantMod;
   private $pressMod;
   private $modValue;
   
   private $k; //This determines how much an individual game affects a players ranking in 1v1s.
   private $start; //This determines the starting ranking for a player in 1v1s.
   private $modMultiplier;

 	
 	public function __construct($gameID, $SCcounts, $memberStatus, $variantID, $pressType, $potType, $gameTurns, $gameStatus, $phaseMinutes, $victorySC, $variantSC, $winner, $botGame, $time)
   {
     $this->gameID = $gameID;
     $this->SCcounts = $SCcounts;
     $this->memberStatus = $memberStatus;
     $this->variantID = $variantID;
     $this->pressType = $pressType;
     $this->potType = $potType;
     $this->gameTurns = $gameTurns;
     $this->gameStatus = $gameStatus;
     $this->phaseMinutes = $phaseMinutes;
     if (isset(Config::$grVariantMods[$this->variantID]))
     {
       $this->variantMod = Config::$grVariantMods[$this->variantID];
     }
     else
     {
       $this->variantMod = 1;
     }
     $this->victorySC = $victorySC;
     $this->variantSC = $variantSC;
     $this->winner = $winner;
     $this->botGame = $botGame;
     $this->time = $time;
     $this->k = 32; //A higher k value means players will move faster up and down in rankings.
     $this->start = 100; //32 and 100 are arbitrary numbers used for scaling in 1v1 rankings.
     if (isset(Config::$grPressMods[$this->pressType]))
     {
       $this->pressMod = Config::$grPressMods[$this->pressType];
     }
     else
     {
       $this->pressMod = 1;
     }
     $this->modvalue = (17.5 * $this->variantMod * $this->pressMod); //17.5 is the 'k' value for non 1v1 games and is used for scaling.
   }
     
   public function processGR()
   {
     global $DB;
     //This first section checks to see if the game should be processed and iterates through each category, also making checks for whether to calculate or not
     if ($this->gameTurns > 3 and $this->botGame <> True)
     {
       foreach(Config::$grCategories as $categoryID => $categoryData)
       {
         $calculate = True;
         if(in_array($this->variantID,$categoryData["variants"]))
         {
           if ($categoryData["1v1"] <> "Yes")
           {
             if (!(in_array($this->pressType,$categoryData["presses"])))
             {
               $calculate = False;
             }
             else if (!(in_array($this->potType,$categoryData["scoring"])))
             {
               $calculate = False;
             }
             else
             {
               if ($this->phaseMinutes < 60)
               {
                 if (!(in_array("Live",$categoryData["phases"])))
                 {
                   $calculate = False;
                 }
               }
               else
               {
                 if (!(in_array("Nonlive",$categoryData["phases"])))
                 {
                   $calculate = False;
                 }
               }
             }
           }
         }
         else
         {
           $calculate = False;
         }
         if ($calculate)
         {
           //First we need to grab everyones GR for this category to use in the calculations
           $userGR = array();
           $peakGR = array();
           $monthYear = array();
           $grSum = 0;
           $first = True;
           $date = date('my',$this->time);
           foreach($this->SCcounts as $userID=>$scs)
           {
             $rating = $this->start;
             $peakRating = $rating;
             $userDate = $date;
             $sqlCount = "SELECT COUNT(1) FROM wD_GhostRatings WHERE categoryID=".$categoryID." AND userID=".$userID;
             $sql = "SELECT rating, peakRating, monthYear FROM wD_GhostRatings WHERE categoryID=".$categoryID." AND userID=".$userID;
             $inDB = 1;
             list($inDB) = $DB->sql_row($sqlCount);
             if ($inDB < 1)
             {
               $DB->sql_put("INSERT INTO wD_GhostRatings(userID, categoryID, rating, peakrating, monthYear) VALUES(".$userID.", ".$categoryID.", ".$rating.", ".$peakRating.", ".$date.")");
               $DB->sql_put("INSERT INTO wD_GhostRatingsHistory(userID, categoryID, monthYear, rating) VALUES(".$userID.", ".$categoryID.", ".$date.", ".$rating.")");
             }
             else
             {
               list($rating, $peakRating, $userDate) = $DB->sql_row($sql);
             }
             $userGR[$userID] = $rating;
             $peakGR[$userID] = $peakRating;
             $monthYear[$userID] = $userDate;
             $grSum += $rating;
           }
           $grAdjustment = array();
           //Next we divide up based on the scoring type
           switch($this->potType)
           {
             case "Points-per-supply-center":
               $expectedResult = array();
               $actualResult = array();
               $firstplace = array();
               $secondplace = array();
               $secondplaceSum = 0;
               $drawNum = 0;
               foreach($userGR as $userID => $rating)
               {
                 $firstplace[$userID] = $rating / $grSum;
                 $secondplace[$userID] = 0;
                 foreach($userGR as $nID => $nRating)
                 {
                   if ($userID <> $nID)
                   {
                     $secondplace[$userID] = $secondplace[$userID] + (($nRating*$rating)/(($grSum*$grSum)-($grSum*$nRating)));
                   }
                 }
                 $secondplace[$userID] = $secondplace[$userID] * (1 - $firstplace[$userID]);
                 $secondplaceSum += $secondplace[$userID];
                 if ($this->memberStatus[$userID] == 'Drawn')
                 {
                   $drawNum += 1;
                 }
               }
               foreach ($userGR as $userID => $rating)
               {
                 $expectedResult[$userID] = (($this->victorySC * $firstplace[$userID]) + ((($this->variantSC - $this->victorySC) * $secondplace[$userID]) / $secondplaceSum)) / $this->variantSC;
                 if ($this->gameStatus == "Drawn")
                 {
                   if($this->memberStatus[$userID] == 'Drawn')
                   {
                     $actualResult[$userID] = 1/$drawNum;
                   }
                   else
                   {
                     $actualResult[$userID] = 0;
                   }
                 }
                 else
                 {
                   $actualResult[$userID] = $this->SCcounts[$userID] / (float) $this->variantSC;
                 }
                 $grAdjustment[$userID] = (($grSum / $this->modvalue) * ($actualResult[$userID]-$expectedResult[$userID]));
               }
               break;
             case "Winner-takes-all":
               $expectedResult = array();
               $actualResult = array();
               $drawNum = 0;
               foreach($userGR as $userID => $rating)
               {
                 if ($this->memberStatus[$userID] == 'Drawn')
                 {
                   $drawNum += 1;
                 }
               }
               foreach ($userGR as $userID => $rating)
               {
                 $expectedResult[$userID] = $rating / $grSum;
                 if ($this->gameStatus == "Drawn")
                 {
                   if($this->memberStatus[$userID] == 'Drawn')
                   {
                     $actualResult[$userID] = 1/$drawNum;
                   }
                   else
                   {
                     $actualResult[$userID] = 0;
                   }
                 }
                 else
                 {
                   if($userID == $this->winner)
                   {
                     $actualResult[$userID] = 1;
                   }
                   else
                   {
                     $actualResult[$userID] = 0;
                   }
                 }
                 $grAdjustment[$userID] = (($grSum / $this->modvalue) * ($actualResult[$userID]-$expectedResult[$userID]));
               }
               
               break;
             case "Sum-of-squares":
               $expectedResult = array();
               $actualResult = array();
               $expectedSquare = array();
               $actualSquare = array();
               $expectedSum = 0;
               $actualSum = 0;
               $drawNum = 0;
               foreach($userGR as $userID => $rating)
               {
                 if ($this->memberStatus[$userID] == 'Drawn')
                 {
                   $drawNum += 1;
                 }
                   $actualSquare[$userID] = $this->SCcounts[$userID] * $this->SCcounts[$userID];
                   $expectedSquare[$userID] = $rating * $rating;
                   $actualSum += $actualSquare[$userID];
                   $expectedSum += $expectedSquare[$userID];
               }
               foreach ($userGR as $userID => $rating)
               {
                 $expectedResult[$userID] = $expectedSquare[$userID] / $expectedSum;
                 if ($this->gameStatus == "Drawn" && $this->memberStatus[$userID] == 'Drawn')
                 {
                   $actualResult[$userID] = $actualSquare[$userID] /$actualSum;
                 }
                 else
                 {
                   if($userID == $this->winner)
                   {
                     $actualResult[$userID] = 1;
                   }
                   else
                   {
                     $actualResult[$userID] = 0;
                   }
                 }
                 $grAdjustment[$userID] = (($grSum / $this->modvalue) * ($actualResult[$userID]-$expectedResult[$userID]));
               }
               break;
             //In this case we assume that all 1v1 games are unranked, and the previous code excludes unranked non-1v1 games from being calculated, so this is the 1v1 calculation 
             case "Unranked":
               $first = True;
               $r1 = 0;
               $r2 = 0;
               $id1 = 0;
               $id2 = 0;
               $result = 0;
               foreach ($userGR as $userID => $rating)
               {
                 if($first)
                 {
                   $r1 = $rating;
                   $id1 = $userID;
                   $first = False;
                 }
                 else
                 {
                   $r2 = $rating;
                   $id2 = $userID;
                 }
               }
               if ($this->gameStatus == "Drawn")
               {
                 $result = 0.5;
               }
               else if ($this->winner == $id1)
               {
                 $result = 1;
               }
               $R1 = pow(10,($r1/400));
               $R2 = pow(10,($r2/400));
               $E1 = $R1 / ($R1 + $R2);
               $E2 = $R2 / ($R1 + $R2);
               $grAdjustment[$id1] = $this->k * ($result - $E1);
               $grAdjustment[$id2] = $this->k * (1 - $result - $E2);
               break;
           }
           foreach ($grAdjustment as $userID=>$adjustment)
           {
             $newRating = $userGR[$userID] + $adjustment;
             $sqlUpdate = "UPDATE wD_GhostRatings SET rating=".$newRating;
             if ($newRating > $peakGR[$userID])
             {
               $sqlUpdate = $sqlUpdate . ", peakRating=".$newRating;
             }
             if ($date <> $monthYear[$userID])
             {
               $sqlUpdate = $sqlUpdate. ", monthYear=".$date;
               $DB->sql_put("INSERT INTO wD_GhostRatingsHistory(userID, categoryID, monthYear, rating) VALUES(".$userID.", ".$categoryID.", ".$date.", ".$newRating.")");
             }
             else
             {
               $DB->sql_put("UPDATE wD_GhostRatingsHistory SET rating=".$newRating." WHERE userID=".$userID." AND categoryID=".$categoryID." AND monthYear=".$date." LIMIT 1");
             }
             $sqlUpdate = $sqlUpdate . " WHERE categoryID=".$categoryID." AND userID=".$userID." LIMIT 1";
             $DB->sql_put($sqlUpdate);
             $DB->sql_put("INSERT INTO wD_GhostRatingsBackup(userID, categoryID, gameID, adjustment, timeFinished) VALUES(".$userID.", ".$categoryID.", ".$this->gameID.", ".$adjustment.", ".$this->time.")");
           }
         }
       }
     }
     $DB->sql_put("UPDATE wD_Games SET grCalculated=1 WHERE id=".$this->gameID." LIMIT 1");
   }
 }
?>
