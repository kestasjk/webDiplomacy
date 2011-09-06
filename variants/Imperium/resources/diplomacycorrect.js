function DiplomacyCorrect() {
			
	if( context.phase == 'Diplomacy' ) {
	
		UnitClass.addMethods( {
		
			// Remove the Id's for river-moves
			removeRiverMoves : function(terrID, moves) {
				if (this.type == 'Army') {
					if (/^(?:1|3|4|5|10|12|16|18|19|30|50|51)$/.test(terrID)) return moves.without(9,42,44,66,75,82,83,86,91);
					if (/^(?:9|42|44|66|75|82|83|86|91)$/.test(terrID)) return moves.without (1,3,4,5,10,12,16,18,19,30,50,51);
				}
				return moves;				
			},
	
			// Names of units territories I can support-hold
			getSupportHoldChoices : function() {
				return this.removeRiverMoves(this.Territory.id, this.getMovableUnits().pluck('Territory').pluck('coastParent').pluck('id').uniq())
			},
			
			// Names of territories I can support-move to (places I can support an attack to)
			getSupportMoveToChoices : function () {
				return this.removeRiverMoves(this.Territory.id, this.getMovableTerritories().pluck('coastParent').pluck('id'));
			}			
		});
	} else if( context.phase == 'Retreats' ) {
	
		UnitClass.addMethods( {

			// Territories I can move to, not including army convoy moves (in Retreats we have to remove the complete moves, because there
			// is no function for the choices hardcoded in the Unitclass for Retreats...)
			getMovableTerritories : function () {
			
				if( Object.isUndefined(this.getMovableTerritoriesCache) ) {
					this.getMovableTerritoriesCache = this.Territory.CoastalBorders
						.select(this.canCrossBorder,this).pluck('id').compact()
						.map(function(n){return Territories.get(n);},this);
						
					if (this.type == 'Army') {
						if (/^(?:9|42|44|66|75|82|83|86|91)$/.test(this.Territory.id) ) 
							this.getMovableTerritoriesCache = this.getMovableTerritoriesCache.without(
								Territories.get(1),Territories.get(3),Territories.get(4),Territories.get(5),
								Territories.get(10),Territories.get(12),Territories.get(16),Territories.get(18),
								Territories.get(19),Territories.get(30),Territories.get(50),Territories.get(51));
						if (/^(?:1|3|4|5|10|12|16|18|19|30|50|51)$/.test(this.Territory.id) )
							this.getMovableTerritoriesCache = this.getMovableTerritoriesCache.without(
								Territories.get(9),Territories.get(42),Territories.get(44),Territories.get(66),
								Territories.get(75),Territories.get(82),Territories.get(83),Territories.get(86),
								Territories.get(91));
					}
				}
				return this.getMovableTerritoriesCache;
			}

		});
	}
	
};