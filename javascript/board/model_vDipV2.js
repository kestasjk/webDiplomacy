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
		
		TerritoryClass.addMethods({
			nodeInit: function () {
				this.convoyNode = true;
				this.convoyPaths = new Array();
			},
			addPath: function (path) {
				this.convoyPaths.push(path);
			},
			clearPaths: function () {
				this.convoyPaths = new Array();
			},
			isConvoyNode: function () {
				return !Object.isUndefined(this.convoyNode) && this.convoyNode;
			}
		});

		var PathClass = Class.create({
			initialize: function (node, pathToNode) {
				this.node = node
				this.pathToNode = pathToNode;
				this.length = (pathToNode != null) ? pathToNode.length + 1 : 1;
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
			 * Checks if this path can be appended to path so a simple path is preserved.
			 * So this basically checks if both paths share any common nodes apart from
			 * path.lastNode and this.firstNode. 
			 * 
			 * Implemented recursivly.
			 */
			canBeAppendedTo: function (path) {
				if (this.pathToNode == null)
					// -> reached start of path
					return true;

				if (path.includes(this.node))
					return false;

				return this.pathToNode.canBeAppendedTo(path);
			},
			toArray: function (array) {
				if (Object.isUndefined(array))
					array = new Array();
				else
					//do not include last element of path in array representation
					array.push(this.node.id);

				if (this.pathToNode != null)
					return this.pathToNode.toArray(array);
				else
					// final array has to be reversed since we moved backwards
					return array.reverse();
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
				Node.nodeInit();
				this.Nodes.set(Node.id, Node);
			},
			resetNodePaths: function () {
				this.Nodes.values().map(function (n) {
					n.convoyPaths = new Array();
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
				// if fAnyNode describes one specific node -> split problem into two by searching
				// paths from StartTerr to AnyNode and AnyNode to EndNode and check if paths
				// exist with no shared nodes

				var AnyNodes = this.Nodes.values().select(fAnyNode);

				if (AnyNodes.length == 1) {
					// collect all minimal valid paths from startNode to anyNode and from anyNode to endNode
					var paths1 = this.findAllPaths(StartTerr, function (node) {
						return (node.id == AnyNodes[0].id);
					}, fAllNode);
					var paths2 = this.findAllPaths(AnyNodes[0], fEndNode, fAllNode);

					// check if there exists a combination of paths that form a simple path
					// (no shared nodes apart from anyNode)
					for(var i=0; i<paths1.length; i++)
						for(var j=0; j<paths2.length; j++)
							if (paths2[j].canBeAppendedTo(paths1[i])) {
								this.Path = paths1[i].toArray().concat(paths2[j].toArray());
								return true;
							}

					return false;

				} else if (AnyNodes.length == this.Nodes.keys().length) {
					var path = this.findPath(StartTerr, fEndNode, fAllNode);

					if (path == null)
						return false;

					this.Path = path.toArray();

					return true;

				} else
					return false;

			},
			/*
			 * This method find all paths from startNode to endNode that can not be reduced
			 * to shorter valid paths by removing nodes (so basically no unnecessary loops).
			 * 
			 * This is done in breadth-first search since this guarantees that tested nodes
			 * in search can only have already been reached via different paths that are 
			 * shorter or of equal length. So it just has to be tested if the current path
			 * with the new node can be reduced to the existing one and not vice versa.
			 * 
			 * if onePath==true, then just search for one path in simple breadth-first search
			 */
			findAllPaths: function (StartTerr, fEndNode, fAllNode, onePath) {
				if (Object.isUndefined(onePath))
					onePath = false;

				// first make sure, that no paths are stored for nodes from previous searches
				this.resetNodePaths();

				// start with initial path only containing StartTerr
				var testPaths = new Array(new PathClass(StartTerr, null));

				testPathLoop:
				while (testPaths.length > 0) {

					var testPath = testPaths.shift();

					// check if node was already visited by a shorter path that is a subpath of this one
					// (-> this path gets obsolete as additional loop)
					if (testPath.node.convoyPaths.length != 0) {

						//onePath: node already reached -> do not continue this path
						if (onePath)
							continue testPathLoop;

						for(var i=0; i<testPath.node.convoyPaths.length; i++){
							var pathAgainst = testPath.node.convoyPaths[i];
						
							if (pathAgainst.length < testPath.length && testPath.includes(pathAgainst.pathToNode.node))
								//pathAgainst reduced version of testPath
								continue testPathLoop;
						}
					}

					// add this path to the paths that contain node
					testPath.node.addPath(testPath);

					if (!fEndNode(testPath.node)) {
						// create new branches of the path, that reach to neighbored valid territories
						var NextNodes = testPath.node.getBorderTerritories().findAll(function (n) {
							return n.isConvoyNode() && n.id != StartTerr.id && (fAllNode(n) || fEndNode(n));
						});
						;

						// add new paths to testPaths	
						NextNodes.each(function(nextNode){
							testPaths.push(testPath.addNode(nextNode));
						});
					}else if(onePath){
						// one path is found
						break;
					}
				}



				// all paths that are not superpath of other paths that reach EndNode are found
				// return all paths that reached EndNode
				return this.Nodes.values().find(fEndNode).convoyPaths;
			},
			/*
			 * Find a path to endNode with simple breadth-first search (uses algorithm of findAllPaths)
			 */
			findPath: function (StartTerr, fEndNode, fAllNode) {
				var paths = this.findAllPaths(StartTerr, fEndNode, fAllNode, true)
				return (paths.length == 0) ? null : paths[0];
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
