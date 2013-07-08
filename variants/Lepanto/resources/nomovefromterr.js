function NoMoveFromTerrID() {

		MyOrders.map(function(OrderObj) {

			OrderObj.updateTypeChoices = function () {
				this.typeChoices = {
					'Hold': 'hold', 'Move': 'move', 'Support hold': 'support hold', 'Support move': 'support move'
				};

				if (this.Unit.terrID == 11 || this.Unit.terrID == 13 || this.Unit.terrID == 86 || this.Unit.terrID == 88 )
				{
					this.typeChoices = { 'Hold': 'hold', 'Support hold': 'support hold', 'Support move': 'support move' };
				}

				return this.typeChoices;
			};

			OrderObj.updateChoices(OrderObj.requirements);
			OrderObj.requirements.map(function(n){ OrderObj.reHTML(n); },OrderObj);

		}, this);

};