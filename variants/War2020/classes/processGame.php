<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class CustomStart_processGame extends processGame {

	protected function changePhase() {
		if( $this->phase == 'Pre-game' )
		{
			// Builds first after the game starts
			$this->setPhase('Builds');

			// This gives the map some color to start with
			$this->archiveTerrStatus();

			return false;
		}
		elseif( $this->phase == 'Builds' && $this->turn==0 )
		{
			// The first Spring builds just finished, make sure we don't go to the next turn

			$this->phase='Pre-game'; // This prevents a turn being added on in setPhase, keeping it in Spring, 1901
			// (It won't activate twice because the next time it won't go into a Builds phase in Spring)

			$this->setPhase('Diplomacy'); // Diplomacy, Spring 1901, and from then on like nothing was different

			$this->archiveTerrStatus();
			return false;
		}
		else
			return parent::changePhase(); // Except those two phases above behave normally
	}
}

class War2020Variant_processGame extends CustomStart_processGame {}
