<?php

class libReliability
{

	public static $grades = array (
		98=>'98+', 90=>'90+', 80=>'80+', 60=>'60+', 40=>'40+', 10=>'10+', 0=>'0'
	);
	
	static public function Grade($reliability)
	{
		foreach (self::$grades as $limit=>$grade)
			if ($reliability >= $limit)
				return $grade;
	}

	/**
	 * Calc a reliability rating.  Reliability rating is 100 minus phases missed / phases played * 200, not to be lower than 0
	 * Examples: If a user misses 5% of their games, rating would be 90, 15% would be 70, etc. 
	 * Certain features of the site (such as creating and joining games) will be restricted if the reliability rating is too low.
	 * @return reliability
	 */
	static public function calcReliability($missedMoves, $phasesPlayed, $gamesLeft, $leftBalanced)
	{
		if ( $phasesPlayed == 0 )
			$reliability = 100;
		else
			$reliability = ceil(100 - $missedMoves / $phasesPlayed * 200 - (10 * ($gamesLeft - $leftBalanced)));

		if ($reliability < 0) $reliability = 0;
		
		return $reliability;
	}
		
}

?>
