function NoMove(unit_id) {

	MyOrders.map(function(OrderObj) {
	
		OrderObj.updateTypeChoices = function () {
			this.typeChoices = {
				'Hold': 'hold', 'Move': 'move', 'Support hold': 'support hold', 'Support move': 'support move'
			};
			
			if( this.Unit.type == 'Fleet' && this.Unit.Territory.type == 'Sea' )
				this.typeChoices['Convoy']='convoy';
			if( this.Unit.id == unit_id)
				this.typeChoices = { 'Hold': 'hold', 'Support hold': 'support hold', 'Support move': 'support move' };

			return this.typeChoices;
		};

		OrderObj.updateChoices(OrderObj.requirements);
		OrderObj.requirements.map(function(n){ OrderObj.reHTML(n); },OrderObj);

	}, this);

}