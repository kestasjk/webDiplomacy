// Allow the convoy-command for the special Coasts too.
function OneWay_loadOrdersPhase()
{	
	MyOrders.map(function(OrderObj)
	{
		OrderObj.updateToTerrChoices = function () {
			switch( this.type ) {
				case 'Move': 
					this.toTerrChoices = this.Unit.getMoveChoices();
					if (this.Unit.Territory.id == 27 || this.Unit.Territory.id == 28 || this.Unit.Territory.id == 29)
					{
						this.toTerrChoices = ["27","28","29"];
						var index = this.toTerrChoices.indexOf(this.Unit.Territory.id);
						this.toTerrChoices.splice(index, 1);
					}
					else if( this.Unit.type=='Army' && this.Unit.Territory.type=='Coast')
					{
						var ttac = new Hash();
						var armylocalchoices = this.Unit.Territory.getBorderTerritories().pluck('id');
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
			
			if( this.Unit.Territory.id == 27 || this.Unit.Territory.id == 28 || this.Unit.Territory.id == 29 )
			{
				var index = this.toTerrChoices.indexOf("30");
				if (index != -1) this.toTerrChoices.splice(index, 1);
			}		

			this.toTerrChoices=this.arrayToChoices(this.toTerrChoices);
			
			return this.toTerrChoices;
		};
		
		OrderObj.updateFromTerrChoices = function () {
			if( Object.isUndefined(this.ToTerritory) )
			{
				this.fromTerrChoices = undefined;
			}
			else
			{
				switch( this.type ) {
					case 'Support move': this.fromTerrChoices = this.Unit.getSupportMoveFromChoices(this.ToTerritory); break;
					case 'Convoy': this.fromTerrChoices = this.Unit.getConvoyFromChoices(this.ToTerritory); break;
					default: this.fromTerrChoices = undefined; return;
				}
			}
			
			if( this.Unit.Territory.id == 7 || this.Unit.Territory.id == 8 || this.Unit.Territory.id == 9 || this.Unit.Territory.id == 10 )
			{
				var index = this.fromTerrChoices.indexOf("27");
				if (index != -1) this.fromTerrChoices.splice(index, 1);
				var index = this.fromTerrChoices.indexOf("28");
				if (index != -1) this.fromTerrChoices.splice(index, 1);
				var index = this.fromTerrChoices.indexOf("29");
				if (index != -1) this.fromTerrChoices.splice(index, 1);
			}
			
			this.fromTerrChoices=this.arrayToChoices(this.fromTerrChoices);
			
			return this.fromTerrChoices;
		};
		
		OrderObj.requirements.map(function(n){ OrderObj.reHTML(n); },OrderObj);
		
	}, this);
}