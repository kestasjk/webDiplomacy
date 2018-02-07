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
 * @package GameMaster
 * @subpackage Adjudicator
 */

/**
 * This class implements the Hungarian Algorithm, used to solve the assingment problem of
 * choosing which players play as which countries. The entry point is the method
 * hungarian, which takes a cost matrix and outputs an assignment vector.
 * This code is based on that found here, Assignment Problem and Hungarian Algorithm - 
 * topcoder: https://www.topcoder.com/community/data-science/data-science-tutorials/assignment-problem-and-hungarian-algorithm/
 */
class assignmentSolver
{
	/**
	 * The algorithm attempts to assign by maximising the weights. If instead we want to 
	 * minimise the weights, we first need to assign the weight matrix to be the result of
	 * this method.
	 */
    function makeMinAssignmentProblemMatrix($weights)
    {
        $maxVal = max(array_map('max', $weights));
        for($x = 0; $x < $this->n; $x++)
            for($y = 0; $y < $this->n; $y++)
                $weights[$x][$y] = $maxVal - $weights[$x][$y];
                
        return $weights;
    }
    
    function setInitialLabeling()
    {
        foreach($this->weights as $row)
        {
            $this->lx[] = max($row);
            $this->ly[] = 0;
        }
    }
    
    function addToTree($x, $prevx)
    {
        // x is the vertex we're adding and prevx is the X vertex before it.
        // Therefore we add (prevx, xy[x]) and (xy[x], x)
        $this->S[$x] = true;
        $this->prevXs[$x] = $prevx;
        // update slacks, as a new vertex could introduce a new more feasible edge.
        for ($y = 0; $y < $this->n; $y++)
        {
            $newSlack = $this->lx[$x] + $this->ly[$y] - $this->weights[$x][$y];
            if ($newSlack < $this->slack[$y])
            {
                $this->slack[$y] = $newSlack;
                $this->slackx[$y] = $x;
            }
        }
    }
    
    function updateLabels()
    {
        $delta = PHP_INT_MAX;
        // Find the smallest delta we can
        for ($y = 0; $y < $this->n; $y++)
            if (!$this->T[$y])
                $delta = min($delta, $this->slack[$y]);
        // update X labels 
        for ($x = 0; $x < $this->n; $x++)
            if ($this->S[$x])
                $this->lx[$x] -= $delta;
        // update Y labels and slacks 
        for ($y = 0; $y < $this->n; $y++)
            if ($this->T[$y])
                $this->ly[$y] += $delta;
            else 
                $this->slack[$y] -= $delta;
    }
    
    function augment($timeThrough)
    {
        // If we have matched every country, then stop.
        if ($this->matchedCountries == $this->n) return;
        
        $this->S = array_fill(0, $this->n, false);
        $this->T = array_fill(0, $this->n, false);
        $this->prevXs = array_fill(0, $this->n, -1);
        $this->q = array();
        
        // Find the root of our new augmenting tree.
        for($x=0; $x<$this->n; $x++)
        {
            if($this->xy[$x] == -1)
            {
                $root = $x;
                $this->q[] = $root;
                $this->prevXs[$x] = -2;
                $this->S[$x] = true;
                break;
            }
        }
        
        $this->slack = array();
        $this->slackx = array();
        for($y=0; $y<$this->n; $y++)
        {
            $this->slack[] = $this->lx[$root] + $this->ly[$y] - $this->weights[$root][$y];
            $this->slackx[] = $root;
        }
        
        $foundPath = false;
        while(true)
        {
            while(count($this->q) > 0)
            {
                $x = array_shift($this->q);
                for ($y = 0; $y < $this->n; $y++)
                {
                    if ($this->weights[$x][$y] == $this->lx[$x] + $this->ly[$y] and !$this->T[$y])
                    {
                        if ($this->yx[$y] == -1)
                        {
                            // We have an exposed vertex, and thus an augmenting path!
                            $foundPath = true;
                            break;
                        }
                        // We have found a tight edge not being considered
                        $this->T[$y] = true;
                        $this->q[] = $this->yx[$y];
                        $this->addToTree($this->yx[$y], $x);
                    }
                }
                if ($foundPath) break;
            }
            if ($foundPath) break;
            
            $this->updateLabels();
            $this->q = array();
            // reevaluate whether we have tight edges or augmenting paths now we've updated labels 
            for($y=0; $y < $this->n; $y++)
            {
                if (!$this->T[$y] and $this->slack[$y] == 0)
                {
                    if($this->yx[$y] == -1)
                    {
                        $foundPath = true;
                        $x = $this->slackx[$y];
                        break;
                    }
                    else
                    {
                        $this->T[$y] = true;
                        if (!$this->S[$this->yx[$y]])
                        {
                            $this->q[] = $this->yx[$y];
                            $this->addToTree($this->yx[$y], $this->slackx[$y]);
                        }
                    }
                }
            }
            
            if($foundPath) break;
        }
        
        if ($foundPath)
        {
            $this->matchedCountries++;
            // Invert the augmenting path!
            $cx = $x;
            $cy = $y;
            while($cx != -2)
            {
                $ty = $this->xy[$cx];
                $this->yx[$cy] = $cx;
                $this->xy[$cx] = $cy;
                $cx = $this->prevXs[$cx];
                $cy = $ty;
            }
        }
        
        $this->augment($timeThrough+1);
    }
    
    function makeWeights0Indexed($weights)
    {
    	$newWeights = array();
    	for ($userIndex = 0; $userIndex < $this->n; $userIndex++)
    	{
    		$newWeights[$userIndex] = array();
    		for ($newCountryIndex = 0; $newCountryIndex < $this->n; $newCountryIndex++)
    			$newWeights[$userIndex][$newCountryIndex] = $weights[$userIndex][$newCountryIndex + 1];
    	}
    	
    	return $newWeights;
    }
    
    function makeXY1Indexed($xy)
    {
    	for ($userIndex = 0; $userIndex < $this->n; $userIndex++)
    		$xy[$userIndex]++;
    		
    	return $xy;
    }
    
    function hungarian($weights, $isMaxAssignmentProblem)
    {
        $this->n = count($weights);
        $this->weights = $this->makeWeights0Indexed($weights);
        $this->weights = $isMaxAssignmentProblem ? $this->weights : $this->makeMinAssignmentProblemMatrix($this->weights);
        $this->matchedCountries = 0;
        $this->xy = array_fill(0,$this->n,-1);
        $this->yx = array_fill(0,$this->n,-1);
        $this->setInitialLabeling();
        $this->augment(1);
        return $this->makeXY1Indexed($this->xy);
    }
}

?>
