function ConvoyDisplayFix() {

	MyOrders.map(function(OrderObj) {
			OrderObj.updateToTerrChoices = function () {
				switch( this.type ) {
					case 'Move': 
						this.toTerrChoices = this.Unit.getMoveChoices();
						
						if( this.Unit.type=='Army' && this.Unit.Territory.type=='Coast' )
						{
							var ttac = new Hash();
							var armylocalchoices = this.Unit.getMovableTerritories().pluck('id');
							this.toTerrChoices.map(
									function(c) {
										if( armylocalchoices.member(c) )
											ttac.set(c, Territories.get(c).name);
										else
											ttac.set(c, Territories.get(c).name+' (via convoy)');
									}
								);
							this.toTerrChoices = ttac;
							
							return this.toTerrChoices;
						}
						break;
					case 'Support hold': this.toTerrChoices = this.Unit.getSupportHoldChoices(); break;
					case 'Support move': this.toTerrChoices = this.Unit.getSupportMoveToChoices(); break;
					case 'Convoy': this.toTerrChoices = this.Unit.getConvoyToChoices(); break;
					default: this.toTerrChoices = undefined; return;
				}
				
				this.toTerrChoices=this.arrayToChoices(this.toTerrChoices);
				
				return this.toTerrChoices;
			};

			OrderObj.updateChoices(OrderObj.requirements);
			OrderObj.requirements.map(function(n){ OrderObj.reHTML(n); },OrderObj);

		}, this);

};	
