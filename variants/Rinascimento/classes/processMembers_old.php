<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class RinascimentoVariant_processMembers extends processMembers {

	// Winner need to occupie ROME
	function checkForWinner()
	{
		global $DB, $Game;

		$win=parent::checkForWinner();
		if ($win == false) return false;
		
		list($rom_stat)=$DB->sql_row("SELECT countryID FROM wD_TerrStatus WHERE terrID=77 AND GameID=".$Game->id);
		if ($rom_stat == $win->countryID)
			return $win;
		else
			return false;
	}
	
	function setWon(Member $Winner)
	{
		$potShareRatios = $this->my_potShareRatios($Winner);

		foreach($potShareRatios as $countryID=>$ratio)
		{
			$Member = $this->ByCountryID[$countryID];

			$pointsWon = ceil($ratio * $this->Game->pot);

			if($Member->id == $Winner->id)
				$Winner->setWon($pointsWon);
			elseif($Member->status == 'Playing')
				$Member->setSurvived($pointsWon);
			elseif($Member->status == 'Left')
				$Member->setResigned();
			else
				trigger_error("Invalid member status type for points distribution.");
		}

	}

	function my_potShareRatios(Member $Winner)
	{
		$ratios=array();
		$all_points=0;
		$points=array();

		$i_percent = array (1 , 0.037, 0.074, 0.037, 0.074,
					0.111, 0.148, 0.037, 0.074,
					0.037, 0.148, 0.074, 0.148);
		
		// We need a number for all 'Playing' or 'Left' countries, even a 0.0 may trigger required supplement points
		foreach($this->ByStatus['Left'] as $Member)
			$ratios[$Member->countryID] = 0.0;

		if( $this->Game->potType == 'Winner-takes-all' ) {
			// WTA; easy
			$ratios[$Winner->countryID] = 1.0;
		} else {
			$all_value  = $this->Game->Members->supplyCenterCount('Playing')  + $this->Game->Members->unitCount('Playing');

			$all_lost = $all_gain = 0;
			$countryno=count($i_percent)-1;
			$bet=$this->Game->pot / $countryno;

			for($id=1; $id<=$countryno; $id++) {
				if ($this->ByCountryID[$id]->status=='Playing')
					$c_percent = ( $this->ByCountryID[$id]->unitNo + $this->ByCountryID[$id]->supplyCenterNo ) / $all_value;
				else
					$c_percent = 0;

				if ($c_percent < $i_percent[$id] ) {
					$all_lost+= ($i_percent[$id] - $c_percent ) / $i_percent[$id] * $bet;
				} elseif ($c_percent > $i_percent[$id] ) {
					$all_gain+= ($c_percent - $i_percent[$id]);
				}
			}

			for($id=1; $id<=$countryno; $id++) {
				if ($this->ByCountryID[$id]->status=='Playing')
					$id_percent = ( $this->ByCountryID[$id]->unitNo + $this->ByCountryID[$id]->supplyCenterNo ) / $all_value;
				else
					$id_percent = 0;

				if ( round($id_percent,3) < $i_percent[$id]) {
					$ratios[$id]=( $bet - ($i_percent[$id] - $id_percent) / $i_percent[$id] * $bet ) / $this->Game->pot;
				} elseif (round($id_percent,3) > $i_percent[$id]) {
					$ratios[$id]=( $bet + ($id_percent - $i_percent[$id]) / $all_gain * $all_lost ) / $this->Game->pot;
				} else {
					$ratios[$id]= $bet / $this->Game->pot;
				}
			}
		}
		return $ratios;
	}

	// Count the units each player owns.
	public function unitCount($forMemberStatus=false)
	{
		$count=0;

		if($forMemberStatus)
			$Members = $this->ByStatus[$forMemberStatus];
		else
			$Members = $this->ByID;

		foreach($Members as $Member)
		$count += $Member->unitNo;

		return $count;
	}
	
}
?>
