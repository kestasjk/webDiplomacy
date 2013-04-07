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
 * A paradox exception; has to collect decisions at each point that it's thrown,
 * so that it can tell when it has reached back to the beginning. If it has
 * reached back to the beginning the paradox can't be resolved via other routes,
 * and it has to be fixed directly
 * 
 * @package GameMaster
 * @subpackage Adjudicator
 */
class adjParadoxException extends Exception
{
	/**
	 * An array of 2 part arrays:
	 * array(array(dependencyNode, decisionName),array(dependencyNode, decisionName), [...]) 
	 *
	 * @var array
	 */
	private $dependencyChain;
	
	/**
	 * How many decisions are contained in this dependency chain? This is important
	 * because when considering paradoxes the shortest dependency chain is the one
	 * which must be considered.
	 * This is determined by counting the number of decisions above us in the stack
	 * before the chain would be completed.
	 *
	 * @var int
	 */
	public $length;
	
	/**
	 * If the paradoxException given is smaller than this one then this one will
	 * clone the paradoxException given. If this is greater than or equal to the
	 * paradoxException given then nothing will happen
	 *
	 * @param adjParadoxException $p
	 */
	public function downSizeTo( adjParadoxException $p )
	{
		/*
		 * If the chain given to us is smaller than this, 
		 * we will become the smaller chain
		 */
		
		if ( $p->length < $this->length)
		{
			// Become the smaller paradox
			foreach($this as $name=>&$value)
				$value = $p->{$name};
		}
	}
	
	/**
	 * Create the paradox exception. If we come back to the decision given here we will need to 
	 * resolve the paradox
	 *
	 * @param adjDependencyNode $start The node which we discovered the loop
	 * @param unknown_type $decision The decision in the node in which we discovered the loop
	 */
	public function __construct( adjDependencyNode $start, $decision )
	{
		/*
		 * The parent exception should also be constructed, as advised by the PHP manual
		 */
		parent::__construct('Paradox detected', 1234);
		$this->dependencyChain = array(array($start, $decision));
		
		$this->length = $this->measureLength($start->id, $decision);
	}
	
	/**
	 * Count upwards through the decision stack to find how many decisions were traversed down to 
	 * reach this potential paradox
	 * 
	 * @param int $startID The unit ID of the node from which the loop comes from
	 * @param string $decision The name of the decision which started the loop
	 * 
	 * @return int $length The number of decisions above us before reaching the end of the loop
	 */
	private function measureLength($startID, $decision)
	{
		/*
		 * Use the global decision stack to find out how many decisions have 
		 * been traversed to loop into a paradox. The shorter it is the more
		 * likely it'll be the paradox that will get resolved; longer paradoxes
		 * are more likely to contain decisions which don't need to be affected
		 * by the paradox resolution.
		 */
		$chain = array();
		do
		{
			$current = array_pop($GLOBALS['decisionStack']);
			array_push($chain, $current);
		}
		while( $current != $startID.'->'.$decision );
		
		$length = count($chain);
		
		/*
		 * Now that it's counter out push everything back on
		 */
		do
		{
			$current = array_pop($chain);
			array_push($GLOBALS['decisionStack'], $current);
		}
		while( count($chain) );
		
		return $length;
	}
	
	/**
	 * true if the paradox is a full loop, ready to be resolved, false otherwise
	 *
	 * @var bool
	 */
	public $complete = false;
	
	/**
	 * Add a dependencyNode to the paradox chain. Will set $this->complete to true
	 * if the node is already contained in the paradox
	 *
	 * @param adjDependencyNode $node The new node
	 * @param string $decision The type of decision which could not be resolved
	 */
	public function addDependency( adjDependencyNode $node, $decision )
	{
		list($startNode, $startDecision) = $this->dependencyChain[0];
		 
		if ( $startNode->id == $node->id and $startDecision == $decision)
		{
			// We have reached the end of the paradox chain, don't re-add the start node
			$this->complete = true;
		}
		else
		{
			$this->dependencyChain[] = array($node, $decision);
		}
	}
	
	/**
	 * Convert the chain of array(dependencyNode, decision) to a simple array of 
	 * dependencyNodes involved in the paradox, and return it 
	 *
	 * @return array The array of nodes involved
	 */
	private function dependencyChainToUnitChain()
	{
		$units = array();
		
		foreach($this->dependencyChain as $pair)
		{
			list($unit, $decision) = $pair;
			
			if ( ! isset($units[$unit->id]) )
				$units[$unit->id] = $unit;
		}
		
		return $units;
	}
	
	/**
	 * Determine whether the array of dependencyNodes passed to this 
	 * function consititutes a move chain paradox.
	 *
	 * @param array $units The nodes being checked
	 * @return bool True means it is a move chain
	 */
	private function isMoveChain(array $units)
	{
		foreach($units as $unit)
		{
			if ( ! ( $unit instanceof adjMove ) )
			{
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Determine whether the array of dependencyNodes passed to this function
	 * constitures a convoy paradox
	 *
	 * @param array $units The nodes being checked
	 * @return bool True means it is a convoy paradox
	 */
	private function isConvoyParadox(array $units)
	{
		/* 
		 * It must contain one unit which is being convoyed, and
		 * one unit which is holding, for it to be a convoy paradox
		 * 
		 * Ideally it should be clear that it's a convoy paradox based only
		 * on whether it's a move chain or not; it should be one or the other.
		 * This is here for error checking.
		 */
		$convoyed=0;
		$convoying=0;
		foreach($units as $unit)
		{
			if ( $unit instanceof adjConvoyMove )
			{
				$convoyed++;
			}
			elseif ( $unit instanceof adjHold )
			{
				$convoying++;
			}
		}
		return ( $convoyed and $convoying);
	}
	
	/**
	 * Resolve the paradox, by applying special logic. Move chains all result
	 * in a successful move, convoy paradoxes are resolved using the Szykman 
	 * rule, which states that the unit being convoyed is unsuccessful, and 
	 * has no influence over the territory it is being convoyed to.
	 * 
	 * Once this function has been performed the decision which initially 
	 * triggered the paradox can be re-tried.
	 */
	public function resolve()
	{
		$units = $this->dependencyChainToUnitChain();
		
		if ( $this->isMoveChain($units) )
		{
			/*
			 * It's just a move chain: Move all the units forward successfully
			 */
			
			foreach( $units as $unit )
			{
				if ( $unit instanceof adjMove )
				{
					$unit->paradoxForce('success', true);
				}
			}
		}
		elseif( $this->isConvoyParadox($units) )
		{
			/*
			 * It seems to be a convoy paradox
			 */

			/*
			 * http://web.inter.nl.net/users/L.B.Kruijswijk/#5.B.9
			 * 
			 * 5.B.9. CIRCULAR MOVEMENT AND PARADOXES (excerpt on convoy paradoxes)
			 * 
			 * In case of convoy disruption paradox, a convoy paradox rule must be applied on the dependency list. 
			 * Note that the MOVE decision of the army that convoys is not in the dependency list, since for the
			 * paradox only the cutting of support is essential. Therefore only the ATTACK STRENGTH decision of 
			 * the army that convoys appears in the dependency list. This is important when applying the Szykman 
			 * rule or the 'All Hold' rule.
			 * 			
			 * When the Szykman rule is applied, all ATTACK STRENGTH decisions in the dependency list are set to 
			 * zero for both minimum as maximum. The corresponding MOVE decision is set to failed and the 
			 * corresponding PREVENT STRENGTH is also to zero for both minimum as maximum.
			 * 			
			 * If you interpret the 2000 rulebook in such that in some very rare cases the attacked unit is 
			 * dislodged by the convoying army (see discussion in issue 4.A.2, and test case 6.F.17), then first 
			 * the dependency list must be searched for a SUPPORT decision of a support order of an attack on a 
			 * convoying fleet that convoys an army to the area of the supporting unit. That SUPPORT decision must 
			 * be set to 'given'. If no such decision could be found, then the 2000 rulebook has no resolution and 
			 * a fallback rule must be used such as the Szykman rule or the 'All Hold' rule.
			 */

			foreach( $units as $unit )
			{
				if ( $unit instanceof adjConvoyMove )
				{
					$unit->paradoxForce('attackStrength', array('max'=>0,'min'=>0));
					$unit->paradoxForce('success', false);
					$unit->paradoxForce('preventStrength', array('max'=>0,'min'=>0));
				}
			}
			
		}
		else
		{
			/*
			 * The only valid types of paradoxes are move chains or convoy dislodgement paradoxes. 
			 * This paradox doesn't seem to be either, so there is a problem. 
			 */
			if( defined('DATC') ) die(l_t("Paradox caught which could not be dealt with"));
			
			trigger_error(l_t("Paradox caught which could not be dealt with"));
		}
	}
}

?>