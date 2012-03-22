function NewUnitNames() {

	if( context.phase == 'Builds' ) {

		MyOrders.map(function(OrderObj) {
			OrderObj.updateTypeChoices = function () {
				switch(this.type)
				{
					case 'Build Army':
					case 'Build Fleet':
					case 'Wait':
						this.typeChoices = $H({'Build Army':'Build a frigate',
									'Build Fleet':'Build a clipper',
									'Wait':'Wait/Postpone build.'});
						break;
					case 'Destroy':
						this.typeChoices = $H({'Destroy':'Destroy a unit'});
				}
				
				return this.typeChoices;
			};
			OrderObj.updateTypeChoices();	
			OrderObj.reHTML('type');
		}, this);
		
	} else {	
	
		MyOrders.map(function(OrderObj) {
			OrderObj.beginHTML = function () {
				if (this.Unit.type.toLowerCase() == 'army')
					return 'The frigate at '+this.Unit.Territory.name+' ';
				else
					return 'The clipper at '+this.Unit.Territory.name+' ';			
			};
			OrderObj.reHTML('orderBegin');			
		}, this);
		
	}			
}

