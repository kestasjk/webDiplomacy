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
					 * var ConvoyArmies;
					
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
					}*/
					
					this.convoyOptions=AgainstTerritory.ConvoyGroup.Armies.pluck('Territory').pluck('id');
					
					PossibleUnits=snapTogether(PossibleUnits,AgainstTerritory.ConvoyGroup.Armies);
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
			nodeInit: function() {
				this.BlockIDs = $H({});
				this.blockCount = 0;
			},
			isBlocked: function() { return ( Object.isUndefined(this.blockCount)||this.blockCount>0 ); },
			block: function(blockID) {
				var blockSet = this.BlockIDs.get(blockID);
				if( Object.isUndefined(blockSet)||!blockSet )
				{
					this.BlockIDs.set(blockID, true);
					this.blockCount++;
				}	
			},
			unblock: function(blockID) {
				var blockSet = this.BlockIDs.get(blockID);
				if( !Object.isUndefined(blockSet)&&blockSet )
				{
					this.BlockIDs.set(blockID, false);
					this.blockCount--;
				}
			}
		});
		
		var NodeSetClass = Class.create({
			initialize: function() {
				this.Nodes = $H({ });
				this.NodeIDChain=$A([ ]);
			},
			addNodes: function(Nodes) {
				Nodes.map(function(n){ this.addNode(n); }, this);
			},
			addNode: function(Node) {
				Node.nodeInit();
				this.Nodes.set(Node.id, Node);
			},
			block: function(blockID, blockFunc) {
				this.Nodes.findAll( blockFunc ).map( n.block(blockID) );
			},
			unblock: function(blockID) {
				this.Nodes.map(function(n) { n.unblock(blockID); });
			},
			setActive: function(Node) {
				Node.block('Searching');
				this.NodeIDChain.push(Node.id);
				return Node.getBorderTerritories().findAll(function(n){return !n.isBlocked();});
			},
			unsetActive: function(Node) {
				Node.unblock('Searching');
				this.NodeIDChain.pop();
			},
			
			routeSetLoad: function(ConvoyGroup) {
				ConvoyGroup.Fleets.pluck('Territory').map(function(t){this.addNode(t);},this);
				ConvoyGroup.Armies.pluck('Territory').map(function(t){this.addNode(t);},this);
				ConvoyGroup.Coasts.map(function(t){this.addNode(t);},this);
			},
			routeSetStart: function(StartTerr, fEndNode, fAllNode, fAnyNode) {
				this.AnyNodeMatched=false;
				
				this.temp = $H({});
				
				var StartNode = this.Nodes.get(StartTerr.id);
				
				var NextNodes = this.setActive(StartNode);
				var pathFound = NextNodes.map(function(n) { this.findPaths(n, fEndNode, fAllNode, fAnyNode); },this);
				this.unsetActive(StartNode);
				
				return pathFound;
			},
			findPaths: function( Node, fEndNode, fAllNode, fAnyNode ) {
				//if(this.temp.get(Node.id)) return;
				//else this.temp.set(Node.id,true);
				
				if( fEndNode(Node) )
				{
					if( this.AnyNodeMatched )
					{
						this.Path=this.NodeIDChain.clone();
						return true;
					}
				}
				else if( fAllNode(Node) )
				{
					var clearAnyNodeMatched=false;
					if( !this.AnyNodeMatched && fAnyNode(Node) )
					{
						clearAnyNodeMatched=true;
						this.AnyNodeMatched=true;
					}
					
					var NextNodes = this.setActive(Node);
					var pathFound = NextNodes.any(function(n) { this.findPaths(n, fEndNode, fAllNode, fAnyNode); },this);
					this.unsetActive(Node);
					
					if( clearAnyNodeMatched )
						this.AnyNodeMatched=false;

					if( pathFound ) return true;
				}
				
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