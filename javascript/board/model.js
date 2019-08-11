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
// See doc/javascript.txt for information on JavaScript in webDiplomacy

// Load classes which board data will be loaded into
function loadModel() {
	
	// Take two arrays, join them into one longer array and return it
	function snapTogether(startArr,snapOnArr)
	{
		for(var i=0; i<snapOnArr.length; i++)
			startArr.push(snapOnArr[i]);
		
		return startArr;
	};
	
	TerritoryClass = Class.create();
	TerritoryClass.prototype = {
		initialize:function() {
			// True if the territory is within a convoygroup
			this.convoyLink=false;
		},
		
		prepare:function() {
			this.supply = (this.supply=='Yes');
			
			if( this.coastParentID!=this.id )
			{
				this.coastParent = Territories.get(this.coastParentID);
				this.supply = this.coastParent.supply;
				this.Borders = this.coastParent.Borders;
			}
			else
			{
				this.coastParent = this;
			}
		},

		// Find territories which border this one using regular borders
		getBorderTerritories : function() {

			// Results cached for efficiency
			if( Object.isUndefined(this.getBorderTerritoriesCache) )
				this.getBorderTerritoriesCache = this.Borders.pluck('id').compact().map(function(n){return Territories.get(n);},this);
			
			return this.getBorderTerritoriesCache;
		},
		
		// Get units which are in territories bordering this one
		getBorderUnits : function() {

			// Results cached for efficiency
			if( Object.isUndefined(this.getBorderUnitsCache) )
				this.getBorderUnitsCache = this.coastParent.getBorderTerritories().pluck('coastParent').uniq().pluck('Unit').compact();
			
			return this.getBorderUnitsCache;
		}
	};
	
	UnitClass = Class.create();
	UnitClass.prototype = {
		initialize:function() {
			this.convoyLink=false; // True if the territory is within a convoygroup
		},
		
		// Can I move into given Territory, not including convoy territories
		canMoveInto : function (TryMoveTerritory) {
			if( this.getMovableTerritories().pluck('coastParent').pluck('id').member(TryMoveTerritory.coastParent.id) )
				return true;
			else
				return false;
		},
		
		// Can I move to a given Territory via convoy (must be an army on a coast)
		canConvoyTo : function (TryConvoyTerritory) {
			if( this.type == 'Army' )
			{
				// Can't get convoyed to our own territory
				if( TryConvoyTerritory.id == this.Territory.id ) return false;
				
				// We're in a convoy group, moving into a convoygroup territory which is in our convoygroup.
				if( this.convoyLink && TryConvoyTerritory.convoyLink && this.ConvoyGroup.Coasts.member(TryConvoyTerritory) )
					return true;
			}
			
			return false;
		},
		
		// Can I cross a given Border
		canCrossBorder : function (b) {
			if( this.type == 'Army' && !b.a ) 
				return false;
			else if( this.type == 'Fleet' && !b.f ) 
				return false;
			else 
				return true;
		},
		
		// Territories I can move to, not including army convoy moves
		getMovableTerritories : function () {
			
			if( Object.isUndefined(this.getMovableTerritoriesCache) )
				this.getMovableTerritoriesCache = this.Territory.CoastalBorders
					.select(this.canCrossBorder,this).pluck('id').compact()
					.map(function(n){return Territories.get(n);},this);
			
			return this.getMovableTerritoriesCache;
		},
		
		// Territories I can move to, including convoyable locations for an army
		getReachableTerritories : function () {
			
			if( Object.isUndefined(this.getReachableTerritoriesCache) )
			{
				this.getReachableTerritoriesCache = this.getMovableTerritories();
				
				if( this.convoyLink && this.type == 'Army' )
					this.getReachableTerritoriesCache = snapTogether(this.getReachableTerritoriesCache,
							this.ConvoyGroup.Coasts.select(this.canConvoyTo, this)).uniq();
			}
			
			return this.getMovableTerritoriesCache;
		},
		
		// Get units adjacent to this one
		getBorderUnits : function() {
			return this.Territory.getBorderUnits();
		},
		
		// Units in territories I can move to (not including army convoy movable units)
		getMovableUnits : function () {
			if( Object.isUndefined(this.getMovableUnitsCache) )
				this.getMovableUnitsCache = this.getMovableTerritories().pluck('coastParent').pluck('Unit').compact();
			
			return this.getMovableUnitsCache;
		}
	};
	
	if( context.phase == 'Diplomacy' )
	{
		// Load model class functions which are only useful in the Diplomacy phase
		
		UnitClass = Class.create(UnitClass, {
			
			// Names of territories I can move to, including army convoy locations
			getMoveChoices : function() { 
				var choices = this.getMovableTerritories().pluck('id');
				
				if( this.convoyLink && this.type == 'Army' )
				{
					this.convoyOptions=this.ConvoyGroup.Coasts.select(this.canConvoyTo, this).pluck('id');
					choices=snapTogether(choices,this.convoyOptions).uniq();
				}
				
				return choices;
			},
			
			// Names of units territories I can support-hold
			getSupportHoldChoices : function() {
				return this.getMovableUnits().pluck('Territory').pluck('coastParent').pluck('id').uniq();
			},
			
			// Names of territories I can support-move to (places I can support an attack to)
			getSupportMoveToChoices : function () {
				return this.getMovableTerritories().pluck('coastParent').pluck('id');
			},
			
			// Names of territories containing units which I can support into the given Territory (fromTerrID)
			getSupportMoveFromChoices : function (AgainstTerritory) {
				// Essentially a list of units which can move into the given territory
				
				// Units bordering the given territory which can move into it
				var PossibleUnits = AgainstTerritory.coastParent.getBorderUnits().select(function(u){
					return u.canMoveInto(AgainstTerritory);
				},this);
				
				// Armies that could be convoyed into the given territory
				if( AgainstTerritory.convoyLink )
				{
					/*
					 * Resource intensive extra check, unnecessary 99% of the time. Leaving this disabled 
					 * means when an invalid support move is selected as a fleet the choice is undone once 
					 * it is selected and put through the check below.
					 * 
					 */ var ConvoyArmies;
					
					if( this.convoyLink && this.type=='Fleet' && 
						this.ConvoyGroup.Coasts.pluck('id').member(AgainstTerritory.id) )
					{
						// Make sure ConvoyArmies contains no armies which can only reach AgainstTerritory 
						// via a convoy containing this fleet. 
						ConvoyArmies = AgainstTerritory.ConvoyGroup.Armies.select(function(ConvoyArmy) {
							var path=AgainstTerritory.ConvoyGroup.pathArmyToCoastWithoutFleet(ConvoyArmy.Territory, AgainstTerritory, this.Territory);
							if( Object.isUndefined(path) )
								return false;
							else
								return true;
						},this);
					}
					else
					{
						ConvoyArmies = AgainstTerritory.ConvoyGroup.Armies;
					}//*/
					
					this.convoyOptions=AgainstTerritory.ConvoyGroup.Armies.pluck('Territory').pluck('id');
					
					PossibleUnits=snapTogether(PossibleUnits,ConvoyArmies);
				}
				
				// Return names, excluding the current territory
				return PossibleUnits.pluck('Territory').pluck('coastParent').pluck('id').uniq().reject(
						function(n){return (n==this.Territory.coastParent.id||n==AgainstTerritory.id);
					},this);
			},
			
			// Coasts which a fleet could convoy to
			getConvoyToChoices : function() {
				if( this.convoyLink )
					return this.ConvoyGroup.Coasts.pluck('id');
				else
					return [ ];
			},
			
			// Coasts which a fleet could convoy from
			getConvoyFromChoices : function(ToTerritory) {
				if( this.convoyLink )
				{
					this.convoyOptions = this.ConvoyGroup.Armies.select(function(a){return a.Territory!=ToTerritory;}).pluck('Territory').pluck('id');
					return this.convoyOptions;
				}
				else
					return [ ];
			}
		});
		
		/*
		 * As big variants can have much more complex convoy groups the path 
		 * searching code was reworked by Tobias Florin.
		 *
		 * While the new code got a lot more complex it actually improves the 
		 * performance a lot. Even on the most extreme case that is currently 
		 * available, a test map of the variant WWIV sealanes with fleets in every
		 * sea territory (>260) one expensive path search to set a convoy only 
		 * takes a few 10 ms (browser was none responsive  even for cases with 
		 * much less fleets). But the algorithm might need some explanation which 
		 * is tried below.
		 * 
		 * While it is simple and more or less efficient to search a simple path
		 * from start to end node in a breadth search approach, searching for path
		 * that contains one middle node (the convoying fleet) turned out to be
		 * much more problematic. One minor improvement over the original code was
		 * to introduce such a simple breadth search for the first case. The other
		 * much more important was to develop an algorithm for the second case.
		 * 
		 * So how does this work? 
		 * First of all two paths from start and end to middle respectively are
		 * calculated. If they do not share any common nodes apart from the middle
		 * node, the problem is already solved as a complete simple path from start
		 * to end via middle was found. However, if this is not the case, alternative
		 * routes are searched for the shorter path. After all alternatives are
		 * found the second path is searched again. But this time there are additional
		 * conditions: The second path must not include any node of the first path
		 * for which no alternative route without that node exists (so this node
		 * must be included in the first path and therefor cannot be used in the
		 * second). The search for the second path ends early, if the search reaches
		 * a node of the first path, for which an alternative route exists without 
		 * that node. In this case the alternative path is simply used for the first
		 * path and the second path is build by the current search path and then
		 * just following the path fork of the first path that it reached to the 
		 * middle node. This way the second path can be found in a sufficiently 
		 * efficient way.
		 * 
		 * But the alternative routes have to be found in an efficient way, too.
		 * This is done systematically in the following way. Beginning from the
		 * last node before middle of the found first path, alternative forks to
		 * the middle node are searched. This alternative nodes can only reach
		 * the middle nodes via nodes that are not used yet or that are only used
		 * by a fork that has more alternatives (called path rank in the code)
		 * than the current fork. In the latter case, the old fork is removed and 
		 * its path is used by the new fork.
		 * 
		 * If a new fork is found, alternatives are searched for this fork, as well, 
		 * starting again with the last node. So one gets a very recoursive pattern
		 * overall. This pattern guarantees, that after the search for alternatives
		 * is done, only those forks are found, that fork as early as possible in
		 * the path. This way it is guaranteed that there is really no alternative
		 * route around a node if it is not found by this algorithm.
		 * 
		 * To further improve performance, each search from each node places a
		 * so called search-object on each node it visited. This object includes
		 * the information about the node the search started and the rank of the
		 * path at that point. If the search was unsuccessful and another search
		 * enters a node visited by the first search it can be quickly decided, 
		 * if it is worth continuing that search. If the rank of the path at the
		 * start node of the current search is not lower than the last one, no
		 * additional forks can be reused so it is impossible to find a new path 
		 * continuing at this point and more exponentionally growing execution 
		 * time is saved.
		 * 
		 * Apart from those main concepts most minor calculation steps that inlcude
		 * recursions over menu nodes are implemented in way trying to get as much
		 * efficiency as possible. However, there might even be more potential to
		 * improve the code, that I do not want to follow here as it is probably
		 * not worth the effort. If this situation changes at some point, one might
		 * for example consider reusing started search paths from a successful 
		 * search for new searches. Or of course scrap the whole concept as I have
		 * done myself several times.
		 */
		
		TerritoryClass.addMethods({
			nodeInit: function (search) {
				this.convoyNode = true;
				
				// contains the last search that went
				// through this node -> needed for breadth first search
				this.lastVisitedBy = null;
				
				// contains the last search that went through this node and was not success.
				// In case a new search passes this node with a same or higher rank, no
				// additional search has to be done  (as there won't be a success in this
				// case as well)
				// (will only be updated, when lastVisitedBy is updated or state is called
				// by internal functions!)
				this.lastSearchNotSuccess = null;
				
				// the current path that uses this node
				this.path = null;
				
				// a cache for the border territories that are important for the path search
				this.validBorderTerritoriesCache = null;
				
				// data of the current search (the overall one; for validBorderTerritories)
				this.search = search;
				
				// A mark only used for checking, if two paths are seperated or not
				this.inPath = null;
			},
			visited: function(node){
				return (this.lastVisitedBy === node);
			},
			setVisited: function(search){
				// update lastSuccessfulSearch so no information gets lost
				if(!this.lastVisitedBy === null && !this.lastVisitedBy.success)
					this.lastSearchNotSuccess = this.lastVisitedBy;
				
				this.lastVisitedBy = search;
			},
			/*
			 * Returns a number (rank) to decide if this node might lead to a 
			 * successfull search.
			 * 
			 * In case this node is part of a complete path, the rank (number of 
			 * alternative routes) of the path at this node is returned. Only if
			 * the rank is lower than that of the current search's starting territory
			 * this node can be part of the new path.
			 * 
			 * In case this node is not part of a complete path, the rank of the
			 * last unsuccessful search (i.e. the rank of the starting node) is 
			 * reaturned. Only if the rank of the current search is lower, it might
			 * lead to a successful search.
			 */
			getMaxRank: function(){
				if(this.hasPath())
					return this.path.getRank();
				else
					return (this.lastSearchNotSuccess !== null)? this.lastSearchNotSuccess.path.getNextNodeRank(): Infinity;
			},
			hasPath: function(){
				return !(this.path === null);
			},
			isConvoyNode: function () {
				return !Object.isUndefined(this.convoyNode) && this.convoyNode;
			},
			//add function to cache valid border territories with specific search params for efficiency
			getValidBorderTerritories: function(){
				if( this.validBorderTerritoriesCache === null )
					this.validBorderTerritoriesCache = this.Borders
						.select(function(b){return b.f;}).pluck('id').compact()
						.map(function(n){return Territories.get(n);},this)
						.findAll(function (n) {
							return n.isConvoyNode() && n.id != this.search.startTerr.id && (this.search.fAllNode(n) || this.search.fEndNode(n));
						},this);
				
				return this.validBorderTerritoriesCache;
			},
		});
		
		/*
		 * A class representing a simple path search from a start terr to one
		 * or more end terrs. 
		 */
		PathSearchClass = Class.create({
			initialize: function(startTerr, fEndNode, fAllNode){
				this.startTerr = startTerr;
				this.fEndNode = fEndNode;
				this.fAllNode = fAllNode;
				
				this.success = false; // is set to true, if a path is found
			},
			/*
			 * Find a path from start node to endNode with simple breadth-first search
			 * 
			 * Note that fEndNode and fAllNode might not be the ones
			 * of the general overall search, but search specific. Especially fAllNode 
			 * contains path specific restrictions.
			 * 
			 * Returns true, if a path was found
			 */
			findPath: function (forceInternalNode) {
				// at least one internal node can be enforced in case of direct
				// path searches from land to land that must include at least 
				// one fleet.
				if(Object.isUndefined(forceInternalNode)) 
					forceInternalNode = false;

				// start with initial path only containing StartTerr
				var start = new PathClass(this.startTerr, null);
				start.node.setVisited(this);
				
				var testPaths = start.node.getValidBorderTerritories().select(
					function(nextNode){
						// skip the end node as one starting node if an internal node should be enforced
						return !forceInternalNode || !this.fEndNode(nextNode);
					},this).map(function(nextNode){
						return start.addNode(nextNode);
					});

				while (testPaths.length > 0) {

					var testPath = testPaths.shift();
					
					// check if path is found
					if(this.fEndNode(testPath.node)){
						this.success = true;
						this.path = testPath;
						return true;
					}

					// check if node was already visited or fails fAllNode conditions
					if (testPath.node.visited(this) || !(Object.isUndefined(this.fAllNode) || this.fAllNode(testPath.node))) {
						continue;
					}
					// set the node visited
					testPath.node.setVisited(this);
					
					// create new branches of the path, that reach to neighbored valid territories
					var NextNodes = testPath.node.getValidBorderTerritories();
						
					// add new paths to testPaths	
					NextNodes.each(function(nextNode){
						testPaths.push(testPath.addNode(nextNode));
					});
				}

				// no path is found
				return false;
			},
		});

		/*
		 * A recoursive implementation of a path
		 */
		var PathClass = Class.create({
			initialize: function (node, pathToNode) {
				// the current node
				this.node = node;
				
				// the path to previous node
				this.pathToNode = pathToNode;
					
				// a complete path is a path, that goes from start to end terr 
				// of the current search
				this.complete = false;	
				
				// a tag that stores if this path node was already checked for 
				// alternative path branches to end terr
				this.alternativeChecked = false;
				
				// the fixed next nodes (might be more than one due to forks)
				this.pathNextNodes = new Array();
				
				// the rank symbolizes, how many alternative routes exist from this path to endNode 
				// (only relevant for complete paths)
				// don't call directly but use this.getRank() as this.rank 
				// does not contain the real rank for optimization reasons
				this.rank = /*(pathToNode !== null) ? pathToNode.rank :*/ 0;
			},
			includes: function (node) {
				if (this.node == node)
					return true;

				if (this.pathToNode == null)
					return false;

				return this.pathToNode.includes(node);
			},
			/*
			 * Creates a new path identical to this one apart from the fact, that a new
			 * node was added at the end.
			 */
			addNode: function (node) {
				return new PathClass(node, this);
			},
			/*
			 * removes the first element of the path and return the new first one
			 */
			removeFirst: function(){
				if(this.pathToNode == null) return;
				
				if(this.pathToNode.pathToNode != null)
					return this.pathToNode.removeFirst();
				
				return this.newPathToNode(null);
			},
			/*
			 * The path is a complete path from start node to end node. Set it is 
			 * fixed in each node for further searches.
			 */
			setComplete: function(nextNode){
				this.node.path = this;
				
				if(this.pathToNode === null)
					this.setRank(0); //assure, the rank is set to 0 if this node is the start
				
				if(!Object.isUndefined(nextNode) && this.pathNextNodes.indexOf(nextNode) === -1){
					// adjust the ranks / count of alternative routes
					this.pathNextNodes.invoke('changeRank',1);
					nextNode.setRank(/*this.rank +*/ this.pathNextNodes.length);
					
					this.pathNextNodes.push(nextNode);
				}
				
				// previous nodes of complete path are already set to complete
				if(!this.complete){
					this.complete = true;
					
					if(this.pathToNode !==null)
						this.pathToNode.setComplete(this);
				}
			},
			/*
			 * To avoid resource intensive rank updates for all following nodes 
			 * everytime the rank is changed, the (real) rank is only calculated on call
			 * by just summing up this rank and all the rank of the predecessors.
			 */
			getRank: function(){
				return this.rank + ((this.pathToNode !== null)?this.pathToNode.getRank(): 0);
			},
			/*
			 * Changes the rank for this path node and all following (in case of a complete path).
			 * 
			 * Positive change -> increase
			 * Negative change -> decrease
			 */
			changeRank: function(change){
				this.rank += change;
				/*if(this.complete)
					this.pathNextNodes.invoke('changeRank',change);*/
			},
			/*
			 * Sets this rank and adjust the rank of following nodes
			 */
			setRank: function(newRank){
				var diff = newRank - this.rank;
				
				this.changeRank(diff);
			},
			/*
			 * get the last node of the path (normally end node)
			 */
			getLastPathNode: function(){
				if(this.pathNextNodes.length == 0)
					// this is the last element of the path
					return this;
				else
					// (arbitrarily) choose the first of the next nodes instead
					return this.pathNextNodes[0].getLastPathNode();
			},
			removePathNextNode: function(path){
				if(!this.complete) return;
				
				var index = this.pathNextNodes.indexOf(path);
				
				if(index == -1) return;
				
				this.pathNextNodes.splice(this.pathNextNodes.indexOf(path),1);
				
				this.pathNextNodes.invoke('changeRank',-1); // decrease rank of all other forks (one alternative was just deleted
				
				if(this.pathNextNodes.length == 0){
					// path not part of an alternative path -> dissolve completely
					this.node.path = null;
					this.dissolvePathToNode();
				}
			},
			dissolvePathToNode: function(){
				if(this.pathToNode != null)
					this.pathToNode.removePathNextNode(this);
				
				this.complete = false;
			},
			/*
			 * Dissolves the previous path to this node and add new path to node
			 */
			newPathToNode: function(node){
				this.dissolvePathToNode();
				
				this.pathToNode = node;
				
				return this;
			},
			/*
			 * Attaches the current path to an existing path through the node. 
			 * 
			 * Returns the path node where the reconnection happened.
			 */
			attachToNodePath: function(){
				if(this.node.hasPath())
					return this.node.path.newPathToNode(this.pathToNode);
				else
					return this;
			},
			getNextNodeRank: function(){
				return this.getRank()+this.pathNextNodes.length-1;
			},
			/*
			 * check for this path, if alternative paths starting from this.node
			 * to end terr exist.
			 */
			searchAlternativeRoutes: function(fEndNode){
				if(!this.complete) return;
				
				/*
				 * Only search for alternative routes in relevant cases:
				 * - not already done
				 * - not end node
				 * - not next to end node (no alternative can be found, where this path is not part of)
				 */
				if(!this.alternativeChecked){
					if(fEndNode(this.node) || this.pathNextNodes.pluck("node").any(fEndNode)){
						this.alternativeChecked = true;
					}else{
						var nextNodeRank = this.getNextNodeRank();
						var alternativeRouteSearch = new PathSearchClass(this.node, // start at this node
							function(node){
								// final node is found or node with higher rank
								return fEndNode(node) || (node.hasPath() && nextNodeRank<node.getMaxRank());
							}, 
							function(node){
								/* additional condition an alternative route nodes have to fullfill
								 * for optimization:
								 * 
								 * node not already used in search of same rank or lower before
								 *		(=> if it is, there are no chances of finding an alternative route)
								 */
								return nextNodeRank<node.getMaxRank();
							});

						if(alternativeRouteSearch.findPath()){
							// there exists an alternativeRoute
							var alternativeRoute = alternativeRouteSearch.path;

							// first check if alternativeRoute reaches endNode or path with lower rank
							if(!fEndNode(alternativeRoute.node)){
								// not EndNode -> reconnect paths
								var alternativeRoute = alternativeRoute.attachToNodePath();
							}

							// append alternativeRoute to current path
							alternativeRoute.removeFirst().newPathToNode(this);

							// next set the route complete
							alternativeRoute.setComplete();

							// now search alternativeRoute for alternativeRoutes (beginning at the node next to end node)
							alternativeRoute.pathToNode.searchAlternativeRoutes(fEndNode);
							/*
							 * Note, that not extra check is needed for pathToNode:
							 * Even if the alternativeRoute heads directly into endNode,
							 * pathToNode is at least this. So the extreme case is that other
							 * paths from this node are checked (which is wanted)
							 */
						}else{
							// all alternative routes from this node are found
							this.alternativeChecked = true;
						}
					}
				}
				
				if(this.pathToNode !== null)
					this.pathToNode.searchAlternativeRoutes(fEndNode);
			},
			/* 
			 * Returns an alternative path that does not pass through this.node.
			 * Alternative routes have to be searched before-
			 */
			getAlternativeRoute: function(){
				if(!this.alternativeChecked) return null;
				
				if(this.pathToNode === null) return null;
				
				if(this.pathToNode.pathNextNodes.length > 1){
					// there is a fork in the path at node before
					// find a path to next node of previous node that is not this.
					// and return the complete path
					return this.pathToNode.pathNextNodes
							.find(function(path){return path !== this;}, this)
							.getLastPathNode();
				}else{
					return this.pathToNode.getAlternativeRoute();
				}
			},
			/*
			 * Checks if this path can be appended to path so a simple path is preserved.
			 * So this basically checks if both paths share any common nodes apart from
			 * path.lastNode and this.firstNode. 
			 * 
			 * Implemented recursivly
			 */
			canBeAppendedTo: function (path) {
				path.markPath(path);
				
				var retValue = true;
				
				if(this.node.inPath == path)
					retValue = false;
				else if(this.pathToNode !== null)
					retValue = this.pathToNode.canBeAppendedTo(path);
				
				return retValue;
			},
			markPath: function(path){
				if(this.node.inPath === path) return;
				
				this.node.inPath = path;
				
				if(this.pathToNode !== null)
					this.pathToNode.markPath(path);
			},
			toArray: function (array) {
				if (Object.isUndefined(array))
					array = new Array();
				
				//do not include last element of path in array representation
				array.push(this.node.id);

				if (this.pathToNode != null)
					return this.pathToNode.toArray(array);
				else
					return array
			},
			getLength: function(){
				return 1 +((this.pathToNode !== null)?this.pathToNode.getLength():0);
			},
			//DEBUGGING ONLY
			getFirst: function(){
				if(this.pathToNode === null) 
					return this;
				else
					return this.pathToNode.getFirst();
			},
			getAllPathsFromThis: function(){
				if(this.pathNextNodes.length === 0){
					return this;
				}else{
					return this.pathNextNodes.invoke('getAllPathsFromThis').flatten();
				}
			}
		});

		var NodeSetClass = Class.create({
			initialize: function () {
				this.Nodes = $H({});
			},
			addNodes: function (Nodes) {
				Nodes.map(function (n) {
					this.addNode(n);
				}, this);
			},
			addNode: function (Node) {
				this.Nodes.set(Node.id, Node);
			},
			initNodes: function (search) {
				this.Nodes.values().map(function (n) {
					n.nodeInit(search); 
				});
			},
			routeSetLoad: function (ConvoyGroup) {
				ConvoyGroup.Fleets.pluck('Territory').map(function (t) {
					this.addNode(t);
				}, this);
				ConvoyGroup.Armies.pluck('Territory').map(function (t) {
					this.addNode(t);
				}, this);
				ConvoyGroup.Coasts.map(function (t) {
					this.addNode(t);
				}, this);
			},
			routeSetStart: function (StartTerr, fEndNode, fAllNode, fAnyNode) {

				// initialize nodes and load valied border territories for this search
				this.initNodes(new PathSearchClass(StartTerr, fEndNode, fAllNode));

				var AnyNodes = this.Nodes.values().select(fAnyNode);

				if (AnyNodes.length == 1) {
					var EndTerr = this.Nodes.values().find(fEndNode);
					
					// The EndTerr might not be part of the Convoy group.
					// In this case no valid path can be found.
					if(Object.isUndefined(EndTerr))
						return false;
					
					// find path simple path to AnyNode (from now on middle node)
					var fMiddleNode = function (node) {
						return (node.id == AnyNodes[0].id);
					};
					
					/*
					 * Calculate two paths from start to middle and end to middle (which is works fast).
					 * 
					 * If those two paths are seperated ones, the problem is already
					 * solved and we can take a short cut. 
					 * 
					 * If not, take the shorter one to calculate alternatives
					 * (as shorter paths might have lesser alternatives from a 
					 * statistical point of view).
					 * 
					 */
					
					// first path
					var search = new PathSearchClass(StartTerr, fMiddleNode);
					
					if(!search.findPath())
						return false;
					
					// second path
					var search2 = new PathSearchClass(EndTerr, fMiddleNode);
					
					if(!search2.findPath())
						return false;

					// are the two found paths seperated (apart from middleNode)
					if(!search2.path.pathToNode.canBeAppendedTo(search.path)){
						// if paths are not already found
						
						// check which path is shorter to search for alternatives
						if(search.path.getLength() > search2.path.getLength()){
							search = search2;
							EndTerr = StartTerr; // we now started our search at end terr, so start terr is the new end terr
						}
						
						// set this path fixed as complete path for further search
						search.path.setComplete();

						// find all alternative routes to end node 
						// (beginning at the node next to end node) 
						search.path.pathToNode.searchAlternativeRoutes(fMiddleNode);

						// now see if there exists a second path from end node to middle node
						var search2 = new PathSearchClass(
								EndTerr, // start search at end node
								function(node){
									// end nodes are the middle node or a path node with a complete fork before (rank > 0)
									// (which can be used as alternative route from start to middle)
									return fMiddleNode(node) || (node.hasPath() && node.getMaxRank() > 0);
								},
								function(node){
									// ignore nodes with paths in general (those should only be end nodes if rank > 0)
									return !node.hasPath(); 
								});

						if(!search2.findPath())
							return false;
					}
					
					/*
					 * there exists a path start - middle - end
					 * But it has to be completed first:
					 * - a fitting path start - middle has to be found
					 * - path2 might be completed from a node where it connected to one
					 * of the first paths
					 */
					var path1; // the final chosen first part of the whole path
					var path2; // the final chosen second part of the whole path
					
					// first check if second search reaches middleNode (or just a part of an alternative route)
					if(fMiddleNode(search2.path.node)){
						// it does directly reach endNode
						// the initial path can be chosen as path1
						path1 = search.path;
						path2 = search2.path;
					} else {
						// not EndNode -> path2 connects to one path from first paths
						// -> find alternative first path and then reconnect path2
						path1 = search2.path.node.path.getAlternativeRoute();
						
						path2 = search2.path.attachToNodePath().getLastPathNode();
					}
					
					// now there exist to disjoint paths, one from start and one from end to middle
					// -> build the final path
					// toArray generates an element starting with the last element
					// -> path1 has to be reversed
					// middleNode should not be included twice
					// -> chose path2.pathToNode instead
					this.Path = path1.toArray().reverse().concat(path2.pathToNode.toArray());
					
					// the path might still be reversed at this point if a search
					// backwards was considered more efficient
					
					if(this.Path[0] != StartTerr.id)
						this.Path.reverse();
					
					// do not include endNode in the final -> remove last element
					this.Path.pop();
					
					return true;
					
					
				} else if (AnyNodes.length == this.Nodes.keys().length) {
					var search = new PathSearchClass(StartTerr, fEndNode);
					if(!search.findPath(true))
						return false;

					this.Path = search.path.toArray().reverse();
					this.Path.pop();

					return true;

				} else
					return false;

			}
		});
		
		/**
		 * A group of fleets, coasts and armies which are linked fleets at sea. A fleet at sea 
		 * starts it off and then recursively adds linked fleets, then once all fleets in a group are found 
		 * coasts and coastal armies are added.
		 * 
		 * A fleet can only be in one convoygroup, but an army or coast can be in 2 (e.g. an army in constantinople).
		 * In these cases new convoygroups are created for the army/coast which contain both convoygroups
		 */
		ConvoyGroupClass = Class.create({
			nodeSetClass: function() {
				var ns=new NodeSetClass();
				ns.routeSetLoad(this);
				return ns;
			},
			pathArmyToCoast: function(StartTerr, EndTerr) {
				var ns=this.nodeSetClass();
				ns.routeSetStart(
					StartTerr, 
					function(EndNode) { return ( EndNode.id == EndTerr.id ); },
					function(AllNode) { return ( AllNode.type=='Sea' ); },
					function(AnyNode) { return true; }
				);
				return ns.Path;
			},
			pathArmyToCoastWithoutFleet: function(StartTerr, EndTerr, WithoutFleetTerr) {
				var ns=this.nodeSetClass();
				ns.routeSetStart(
					StartTerr, 
					function(EndNode) { return ( EndNode.id == EndTerr.id ); },
					function(AllNode) { return ( AllNode.type == 'Sea' && AllNode.id != WithoutFleetTerr.id ); },
					function(AnyNode) { return true; }
				);
				return ns.Path;
			},
			pathArmyToCoastWithFleet: function(StartTerr, EndTerr, WithFleetTerr) {
				var ns=this.nodeSetClass();
				ns.routeSetStart(
					StartTerr, 
					function(EndNode) { return ( EndNode.id == EndTerr.id ); },
					function(AllNode) { return ( AllNode.type == 'Sea' ); },
					function(AnyNode) { return ( AnyNode.id == WithFleetTerr.id ); }
				);
				return ns.Path;
			},
			initialize : function() {
				// Once fully loaded these are converted to arrays, they start as hashes for easy checking of whether they are already loaded
				this.Coasts = new Hash();
				this.Fleets = new Hash();
				this.Armies = new Hash();
			},
			
			// Run after all convoy groups have been loaded, to convert hashes into arrays
			prepare : function() {
				this.Coasts=this.Coasts.values();
				this.Armies=this.Armies.values();
				this.Fleets=this.Fleets.values();
			},
			
			// First load fleets, then load coasts & armies
			loadFleet : function(Fleet) {
				if( Fleet.convoyLink ) return false;
				if( Fleet.Territory.type != 'Sea' ) return false;
				
				Fleet.convoyLink = true;
				Fleet.ConvoyGroup = this;
				this.Fleets.set(Fleet.Territory.id, Fleet);
				
				Fleet.Territory.getBorderTerritories().map(function(t) {
						if ( t.type == 'Sea' && !Object.isUndefined(t.Unit) )
							this.loadFleet(t.Unit);
					},this);
			},
			
			loadCoasts : function() {
				this.Fleets.values().pluck('Territory').map(
					function (sea)
					{
						sea.getBorderTerritories().map(
							function(c)
							{
								if( c.type != 'Coast' ) return;
								c = c.coastParent;
								
								if( !Object.isUndefined(this.Coasts.get(c.id)) ) return;
								
								if( Object.isUndefined(c.ConvoyGroups) )
									c.ConvoyGroups = [ ];
								
								c.ConvoyGroups.push(this);
								
								this.Coasts.set(c.id, c);
								
								if( !Object.isUndefined(c.Unit) && c.Unit.type=='Army' )
									this.Armies.set(c.id, c.Unit);
								
							}, this);
						}, this);
			},
			
			linkGroups : function() {
				this.Coasts.values().map(function(c) {
					
					if( !Object.isUndefined(c.convoyLink) && c.convoyLink ) return;
					
					if( c.ConvoyGroups.length == 1 )
					{
						c.ConvoyGroup = c.ConvoyGroups[0];
					}
					else
					{
						c.ConvoyGroup = new ConvoyGroupClass();
						
						c.ConvoyGroups.map(function(cg) {
							c.ConvoyGroup.Armies = c.ConvoyGroup.Armies.merge(cg.Armies);
							c.ConvoyGroup.Coasts = c.ConvoyGroup.Coasts.merge(cg.Coasts);
							c.ConvoyGroup.Fleets = c.ConvoyGroup.Fleets.merge(cg.Fleets);
						},this);
						
						c.ConvoyGroup.prepare();
					}
					
					c.ConvoyGroups = undefined;
					
					c.convoyLink=true;
					if( !Object.isUndefined(c.Unit) && c.Unit.type=='Army' )
					{
						c.Unit.convoyLink=true;
						c.Unit.ConvoyGroup = c.ConvoyGroup;
					}
				},this);
			}
		});
		
		
	}
};
