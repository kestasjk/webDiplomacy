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
 * The main class behind paradox detection and the non-trivial stuff in the adjudicator.
 *
 * Understanding how paradoxes are solved is key to understanding this adjudicator. Other than
 * the paradoxes it's all pretty simple.
 *
 *
 * This is the main class involved in Diplomacy phase adjudication. The DATC algorithm didn't
 * touch on implementation too much; they suggested a recursive algorithm like the one employed
 * here, but when it came to solving paradoxes it was suggested that the code for finding
 * dependencies be separate from the recursive code, despite the fact that both have similar
 * functionality.
 *
 * In webDip the paradox resolution code and decision code is the same; there is no Undecided
 * status for a decision. webDip instead uses exceptions to handle paradoxes.
 *
 * - When a decision detects that it has looped back onto itself it throws a paradox exception,
 *   this is what is meant when referring to "paradox" from here on: A chain of dependencies which
 *   loops back on itself creating a dependency cycle.
 *
 * - When a paradox is caught in a dependencyNode one of three things can happen:
 *
 *		- Re-throw/Ignore
 * 		  A paradox may be re-thrown, i.e. ignored.
 * 		  e.g. If decision A calls decision B which calls decision C which calls decision A, then
 * 		  a paradox is thrown to C. If C does not find an alternative method of coming to a decision
 * 		  without running into a paradox it will then pass the paradox on up to decision B. If decision
 * 		  B can't find an alternative way of dealing with the paradox it'll get passed up to decision A.
 * 		  If multiple paths throw different paradoxes the shortest paradox chain will always be
 * 		  chosen, and the longer one discarded.
 *
 * 		- Alternative non-paradox route
 * 		  An alternative way to solve the decision, which doesn't require_once solving the paradox, may
 * 		  be found. e.g. If there is a paradox in a convoy path there may be alternate convoy paths
 * 		  which do not result in the paradox, or e.g. If trying to determine if a unit has enough
 * 		  support one of the supports may result in a paradox, but knowing whether the support is
 * 		  successful may not be required.
 * 		  In the example above A calls B calls C calls A. C might be unable to handle the paradox, but
 * 		  B might be able to solve the decision without needing to call C, in which case the paradox
 * 		  exception will be discarded within B, B will return a normal value, and A will continue as if
 * 		  no paradox was ever triggered.
 *
 * 		- Explicitly resolve the paradox
 * 		  The paradox may get resolved.
 * 		  In the above example neither decision B nor C could be resolved without getting a paradox
 * 		  exception that loops through them and back to A. The paradox was thrown and re-thrown until it
 * 		  reached A.
 * 		  Once a paradox as come full loop two things are taken to be true:
 * 			- There is no way to resolve the decision without triggering a paradox
 * 			- The full, complete paradox which was caught is the shortest paradox chain that can be found
 * 		  So once a complete paradox chain is recieved the adjudicator can go no further without solving
 * 		  the paradox. At this point the exception code will look through the paradox chain to determine
 * 		  whether it is a move chain paradox or a convoy dislodge paradox, and resolve it accordingly, by
 * 		  forcing decisions within the paradox (e.g. setting moves to be successful in the event of a move chain)
 *
 * 		  Once the paradox is resolved the order is re-processed, to see if it can now be solved.
 *
 * This system has the advantage that it will find the shortest paradox chain without any extra dependency
 * searching code, no undecided state is needed, and even complex situations where a multiple distinct paradoxes
 * are found within the same decision any paradoxes which need to be solved will be solved without problem.
 *
 * To make this system work all decisions must be true/false (boolean) decisions. Any decision will either return
 * true, false, or throw a paradox.
 *
 * For this true/false based decision system the numeric "decisions" (hold strength, prevent strength,
 * attack strength and defend strength) must be true/false. For this to fit into the way webDip adjudicates
 * numeric "decisions" are replaced with comparison decisions. Instead of "what is holdStrengthMax" being a
 * decision, "is my holdStrength greater than his attackStrength" is a decision.
 * In this setup a paradox will only be created by a comparison decision if solving a comparison requires
 * solving the very same comparison.
 *
 * With numeric decisions adapted to be yes/no decisions all the decisions take the following form:
 * Can I give this decision true?
 * Can I give this decision false?
 * If neither then I must have run into a paradox, and since I am still undecided I will re-throw the paradox.
 *
 * The easiest way to see it is that "Undecided" decisions are only allowed when the decision can be undecided
 * and still allow a parent decision to be resolved.
 *
 *
 * adjDependencyNode contains the only code which will generate a new paradox exception; other code will only
 * re-throw it, never generate their own.
 * adjParadoxException is the exception object, which can collect adjDependencyNodes to be solved later, if
 * no way to find an alternative to the paradox is discovered. When the adjParadoxException has come around
 * to a complete paradox chain it must resolve the paradox.
 * The other objects can handle the adjParadoxExceptions, or ignore them, depending on whether there is a possibility
 * that the decision can still be resolved despite the paradox. All other objects *are*, however, responsible for
 * making sure that the shortest paradox found is the one which is re-thrown, if any. If a longer paradox is
 * re-thrown the paradox resolving code may interfere with decisions which don't need to be interfered with to resolve
 * the paradox.
 *
 *
 * adjDependencyNode also catches any paradox thrown from the decision, and will check if the paradox is complete,
 * before either solving it or re-throwing it. adjDependencyNode also provides an interface for adjParadoxException
 * to explicitly set decision values, so that when that decision is next called instead of being normally resolved
 * the decision will return the value which the paradox resolver explicitly set, thus avoiding the paradox.
 *
 * If the decision logic code itself, which is called from adjDependencyNode's __call(), has no code to handle a
 * paradox, then alternative solutions which do not involve the paradox will never be found; any paradox will cause
 * the decision to pass the paradox on.
 *
 *
 * Once you understand all of the above, and have flicked through DATC chapter 5, you should have a good idea of how
 * this adjudicator works.
 *
 * @package GameMaster
 * @subpackage Adjudicator
 */
class adjDependencyNode
{
	/**
	 * A cache; once a value is determined once it does not have to be determined again
	 *
	 * @var array
	 */
	private $cache = array();

	/**
	 * Check whether we are already evaluating this decision, and have come full circle.
	 * If so, throw a paradox, otherwise add it onto the decision stack.
	 *
	 * @param string $decision
	 */
	private function detectParadox($decision)
	{
		global $Game;

		/*
		 * Yes, save the decision that we were previously making so that we can
		 * come back to it after we have resolved the new one
		 *
		 * But first check that we haven't looped back to the same decision
		 */

		$decisionName = $this->id.'->'.$decision;

		if ( in_array($decisionName, $GLOBALS['decisionStack']) )
		{
			/*
			 * We are currently resolving this decision, and have come back to it
			 * while trying to resolve it; this is a move chain or convoy paradox.
			 * By returning an array we switch to dependency search mode, where we
			 * find the shortest dependency chain
			 */
			throw $Game->Variant->adjParadoxException($this, $decision);
		}

		array_push($GLOBALS['decisionStack'], $decisionName);
	}

	/**
	 * An interface to allow adjParadoxException to force a decision by editing the cache, so that paradoxes can be resolved
	 *
	 * @param string $name
	 * @param string $value
	 */
	function paradoxForce($name, $value)
	{
		$this->cache[$name] = $value;
	}

	/**
	 * An array of decisions which are numeric
	 *
	 * @static array
	 */
	private static $numericCalls = array('attackStrength',
			'preventStrength','holdStrength','defendStrength');

	/**
	 * Wraps around calls to decisions, which all need features like caching and paradox detection
	 * and resolution taken care of
	 *
	 * @param string $decision The name of the decision
	 * @param array $args The args; what are we comparing to?
	 *
	 * @return bool|array True, false, or a paradox and a max/min for numeric paradoxes
	 */
	public function __call($decision, array $args)
	{
		/*
		 * This can be one of three things:
		 * - A compare call, to compare
		 *   the values of two strengths, or one strength and a value.
		 *   Comparisons cannot be cached.
		 *   These have count($args) == 3
		 *
		 * - A yes/no call, to decide whether a certain value is yes/no
		 *   or true/false, like path/success/dislodged
		 *   These can always be cached
		 *   These have count($args) == 0
		 *
		 * - A numeric value, like attackStrength. Returns an array
		 *   containing a maximum, minimum, and possibly a paradox. If
		 *   the maximum and minimum are equal there will be a paradox,
		 *   otherwise there won't.
		 *   These have count($args) == 0 too, but can be told apart
		 *   because they return arrays.
		 *   With this type care should be taken with cacheing: Numeric
		 *   values should only be cached if max = min, i.e. the value
		 *   does not rely on a paradox. Cacheing the paradox could result
		 *   in returning a paradox which has already been resolved.
		 *   Numeric values also differ from yes/no values in that they
		 *   do not throw paradoxes. They may return a paradox along with
		 *   their max/min numeric array, but it will never be thrown as
		 *   an exception, only returned as an object. This is because the
		 *   comparer might not need to worry about the paradox, depending
		 *   on the max/min values.
		 *   Finally, numeric values cannot initiate their own paradoxes.
		 *   This is because the comparer that completes the "paradox" might
		 *   not need a value as high as the comparer that began the
		 *   "paradox"
		 *
		 *   So in summary the only reason the numeric calls pass through
		 *   this function is for the cache check
		 */
		if ( count($args) == 0 )
		{
			/*
			 * It's a basic decision request, or a numeric value
			 */
			if ( isset($this->cache[$decision]) ) // The decision is cached, no need to resolve it
				return $this->cache[$decision];
		}
		else
		{
			/*
			 * It's a numeric comparison request
			 */
			assert($decision == 'compare');

			/*
			 * Create a decision name that can be placed on the stack, which will allow us
			 * to recognize that we are re-evaluating the same comparison if we loop back to it.
			 */
			$decision = $args[0].$args[1];
			if ( is_array($args[2]) )
				$decision .= $args[2][0]->id.'->'.$args[2][1];
			else
				$decision .= $args[2];
		}

		if ( ! in_array($decision, self::$numericCalls) )
		{
			// It's not a numeric call, this call could start a paradox
			$this->detectParadox($decision); // Throws an exception in the event of a paradox
		}

		try
		{
			if ( count($args) == 0 )
			{
				$result = $this->{'_'.$decision}();
			}
			elseif ( count($args) == 3 and ( $args[1] == '>' or $args[1] == '<' ) )
			{
				/*
				 * 0: What we are comparing against (from this node)
				 * 1: What comparison are we applying; > or <
				 * 2: Either an array containing the comparison node and value name,
				 * 		or a plain int value
				 */

				$result = $this->_compare($args[0], $args[1], $args[2]);
			}
			else
			{
				trigger_error(l_t("Invalid arguments passed: ").implode(',',$args));
			}
		}
		catch( adjParadoxException $paradox )
		{
			$paradox->addDependency($this, $decision);

			array_pop($GLOBALS['decisionStack']);

			if( $paradox->complete )
			{
				/*
				 * If we have come full circle we have a paradox chain, which is ready
				 * to be resolved
				 */
				$paradox->resolve();

				/*
				 * Now that it's resolved we can try and solve the decision again. I don't know
				 * if there could be more than one paradox blocking a certain decision, but if there
				 * is this will continue to find and resolve paradoxes until the decision can be
				 * solved.
				 */
				if ( count($args) == 0 )
				{
					return $this->{$decision}(); // Try it again
				}
				else
				{
					return $this->compare($args[0],$args[1],$args[2]);
				}
			}
			else
			{
				/*
				 * We can't resolve it because the paradox chain isn't yet
				 * complete, so re-throw the exception
				 */
				throw $paradox;
			}
		}

		if ( ! in_array($decision, self::$numericCalls) )
		{
			// We are no longer processing this decision so paradoxes cannot occur within it
			array_pop($GLOBALS['decisionStack']);
		}

		if ( count($args) == 0 )
		{
			// It is either a yes/no call or numeric
			if ( is_array($result) )
			{
				// It's numeric, check whether the value is final before cacheing it
				if ( $result['max'] == $result['min'] )
				{
					$this->cache[$decision] = $result;
				}
			}
			else
			{
				$this->cache[$decision] = $result;
			}
		}

		return $result;
	}

	/**
	 * Compares numeric values using the max and min possible values returned, and will
	 * throw a paradox if the comparison cannot be made. $hisValueSource can be a static integer
	 * value, or it can be an array of a dependencyNode, and the name of the value to be
	 * compared with from the dependencyNode.
	 *
	 * This function behaves like the __call wrapper function, except instead of wrapping
	 * true/false decisions it wraps numeric comparisons in such a way that they behave like
	 * true/false decisions
	 *
	 * @param string $myValueName What this node is comparing
	 * @param string $comparison Greater, smaller, equal, etc
	 * @param int|array $hisValueSource A max/min array, or a static value
	 * @return bool True or false (or else throw a paradox)
	 */
	private function _compare($myValueName, $comparison, $hisValueSource)
	{
		// Load the values which will be getting compared
		if ( is_array($hisValueSource) )
		{
			/*
			 * It's an object numeric value call
			 */
			list($him, $valueName) = $hisValueSource;

			// These functions are called directly without going through __call
			$hisValue = $him->{$valueName}();
		}
		else
		{
			/*
			 * It's a static value; max = min = value
			 */
			$hisValue = array('max'=>$hisValueSource,'min'=>$hisValueSource);
		}

		$myValue = $this->{$myValueName}();

		if ( $comparison == '>' )
		{
			// My min is larger than his max; myVal is larger
			if ( $myValue['min'] > $hisValue['max'] ) return true;
			// My max is smaller than or equal to his min; myVal is not larger
			if ( $myValue['max'] <= $hisValue['min'] ) return false;
		}
		else
		{
			// My max is smaller than his min; myVal is smaller
			if ( $myValue['max'] < $hisValue['min'] ) return true;
			// My min is not smaller than his max; myVal is not smaller
			if ( $myValue['min'] >= $hisValue['max'] ) return false;
		}

		/*
		 * There are only 4 ways to get a sure true or false decision, and we haven't got it
		 *
		 * The comparison couldn't be resolved due to a paradox; re-throw the smallest paradox
		 */
		if ( isset($myValue['paradox']) and isset($hisValue['paradox']) )
		{
			$p = $myValue['paradox'];
			$p->downSizeTo($hisValue['paradox']);
		}
		elseif ( isset($myValue['paradox']) )
		{
			$p = $myValue['paradox'];
		}
		elseif ( isset($hisValue['paradox']) )
		{
			$p = $hisValue['paradox'];
		}
		else
		{
			trigger_error(l_t("Comparison paradox code reached, without a paradox."));
		}

		throw $p; // The comparison failed
	}
}

?>