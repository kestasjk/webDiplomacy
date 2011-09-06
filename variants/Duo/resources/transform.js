function loadTransform() {

	MyOrders.map(function(OrderObj) {

		OrderObj.updateTypeChoices = function () {
			this.typeChoices = {
				'Hold': 'hold', 'Move': 'move', 'Support hold': 'support hold', 'Support move': 'support move'
			};
			
			if( this.Unit.type == 'Fleet' && this.Unit.Territory.type == 'Sea' )
				this.typeChoices['Convoy']='convoy';
			
			this.typeChoices['Transform']='transform';
			
			return this.typeChoices;
		};

		OrderObj.load();	
	}
}