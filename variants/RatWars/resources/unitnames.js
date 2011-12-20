function NewUnitNames() {

	if( context.phase == 'Builds' ) {

		MyOrders.map(function(OrderObj) {
			OrderObj.updateTypeChoices = function () {
				switch(this.type)
				{
					case 'Build Army':
					case 'Build Fleet':
					case 'Wait':
						this.typeChoices = $H({'Build Army':'Build a Spear Rat',
									'Build Fleet':'Build a Arrow Rat',
									'Wait':'Wait/Postpone build.'});
						break;
					case 'Destroy':
						this.typeChoices = $H({'Destroy':'Destroy a unit'});
				}
				
				return this.typeChoices;
			};
			OrderObj.updateTypeChoices();	
			OrderObj.reHTML('type');
			OrderObj.setSelectsGreen();
		}, this);
		
	} else {	
	
		MyOrders.map(function(OrderObj) {
			OrderObj.beginHTML = function () {
				if (this.Unit.type.toLowerCase() == 'army')
					return 'The Spear Rat at '+this.Unit.Territory.name+' ';
				else
					return 'The Arrow Rat at '+this.Unit.Territory.name+' ';			
			};
			OrderObj.reHTML('orderBegin');			
			OrderObj.setSelectsGreen();
		}, this);
		
	}			
}

