function NewNamesNoDestroy() {

	if( context.phase == 'Builds' ) {
	
		MyOrders.map(function(OrderObj) {
		
			OrderObj.updateTypeChoices = function () {
				switch(this.type)
				{
					case 'Build Army':
					case 'Wait':
						this.typeChoices = $H({'Build Army':'Build a galley',
									'Wait':'Wait/Postpone build.'});
						break;
					case 'Destroy':
						this.typeChoices = $H({'Destroy':'Destroy a unit'});
				}				
				return this.typeChoices;
			};
		
			OrderObj.updateToTerrChoices = function () {
				switch( this.type )
				{
					case 'Wait':
						this.toTerrChoices = undefined;
						return;
					case 'Build Army':
						this.toTerrChoices = SupplyCenters.pluck('id');
						break;
					case 'Destroy':
						this.toTerrChoices = MyUnits.pluck('Territory').pluck('coastParent').pluck('id').reject(function(id) {
							return (id == 11 || id == 13 || id == 86 || id == 88);
						});
						break;
				}
				
				this.toTerrChoices=this.arrayToChoices(this.toTerrChoices);
				return this.toTerrChoices;
			};
			
			OrderObj.updateTypeChoices(OrderObj.requirements);
			OrderObj.updateToTerrChoices(OrderObj.requirements);
			OrderObj.requirements.map(function(n){ OrderObj.reHTML(n); },OrderObj);

		}, this);
		
	} else {	
	
		MyOrders.map(function(OrderObj) {
			OrderObj.beginHTML = function () {
				if (this.Unit.terrID == 11 || this.Unit.terrID == 13 || this.Unit.terrID == 86 || this.Unit.terrID == 88 )
					return 'The flagship at '+this.Unit.Territory.name+' ';
				else if (this.Unit.type.toLowerCase() == 'army')
					return 'The galley at '+this.Unit.Territory.name+' ';
				else
					return 'The frigate at '+this.Unit.Territory.name+' ';			
			};
			OrderObj.reHTML('orderBegin');			
		}, this);
		
	}
	
}

