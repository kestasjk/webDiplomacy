function NewUnitNames() {

	if( context.phase == 'Builds' ) {

		MyOrders.map(function(OrderObj) {
			OrderObj.updateTypeChoices = function () {
				switch(this.type)
				{
					case 'Build Army':
					case 'Build Fleet':
					case 'Wait':
						this.typeChoices = $H({'Build Army':'Engage patriot',
									'Wait':'Wait/Postpone engagement.'});
						break;
					case 'Destroy':
						this.typeChoices = $H({'Destroy':'Dismiss a patriot'});
				}
				
				return this.typeChoices;
			};
			OrderObj.updateTypeChoices();	
			OrderObj.reHTML('type');
		}, this);
		
	} else {	
	

		MyOrders.map(function(OrderObj) {
			OrderObj.beginHTML = function () {					
				return 'The patriot at '+this.Unit.Territory.name+' '; 
			};
			OrderObj.reHTML('orderBegin');			
		}, this);
		
	}			
}

