function loadTransform() {

	MyOrders.map(function(OrderObj) {
		OrderObj.updateTypeChoices = function () {
			this.typeChoices = {
				'Hold': 'hold', 'Move': 'move', 'Support hold': 'support hold', 'Support move': 'support move'
			};
			
			if( this.Unit.type == 'Fleet' && this.Unit.Territory.type == 'Sea' )
				this.typeChoices['Convoy']='convoy';
			
			if( this.Unit.Territory.coastParent.supply == true && this.Unit.Territory.coastParent.type == 'Coast' )
			{
				found=false;
				if (this.Unit.type == 'Army')
				{
					Territories.each(function(p)
					{
						var t=p[1];
						if(t.coastParent.id == this.Unit.Territory.id && t.coastParent.id != t.id)
						{
							found=true;
							name=t.name.substring(t.name.indexOf("(") + 1, t.name.indexOf(")"))
							this.typeChoices['Transform_' + (1000 + parseInt(t.id)) ] = 'transform to fleet -> ' + name;
						}
					},this);
				}
				if (found == false)
				{
					target = (this.Unit.type == 'Fleet') ? 'army' : 'fleet';
					this.typeChoices['Transform_' + (1000 + parseInt(this.Unit.Territory.coastParent.id))]='transform to ' + target;
				}
			}
			return this.typeChoices;
		};

		OrderObj.updateTypeChoices(OrderObj.requirements);
		OrderObj.requirements.map(function(n){ OrderObj.reHTML(n); },OrderObj);
		OrderObj.setSelectsGreen();

	}, this);
	
}