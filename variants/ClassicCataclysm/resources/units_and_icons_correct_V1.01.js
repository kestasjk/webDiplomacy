Array.prototype.inArray = function (value)	{
	var i;
	for (i=0; i < this.length; i++) {
		if (this[i] == value) {
			return true;
		}
	}
	return false;
};	

function IconsCorrect(SeaTerrs)
{
	MyOrders.map(function(OrderObj) {
			OrderObj.setUnitIconArea = function(newIcon) {
				if (SeaTerrs.inArray(this.Unit.Territory.name))
					newIcon='Fleet';
					
				if( newIcon == this.currentUnitIcon ) return;
		
				if( this.currentUnitIcon != false )
					$('orderID'+this.id).removeClassName(this.currentUnitIcon.toLowerCase());
		
				$('orderID'+this.id).addClassName(newIcon.toLowerCase());
				this.currentUnitIcon=newIcon;
		
				this.unitIconArea.update('<img src="images/'+newIcon+'.png" alt="'+newIcon+'" />');
			};
		});
}

function NewUnitNames(SeaTerrs)
{
	if( context.phase == 'Builds' ) {

		MyOrders.map(function(OrderObj) {
			OrderObj.updateTypeChoices = function () {
				switch(this.type)
				{
					case 'Build Army':
					case 'Build Fleet':
					case 'Wait':
						this.typeChoices = $H({'Build Army':'Build a unit',
									'Wait':'Wait/Postpone build'});
						break;
					case 'Destroy':
						this.typeChoices = $H({'Destroy':'Destroy a unit'});
				}
				
				return this.typeChoices;
			};
			OrderObj.updateTypeChoices();	
			OrderObj.reHTML('type');
		}, this);
		
	}
	else
	{	

		MyOrders.map(function(OrderObj) {
			OrderObj.beginHTML = function () {
				return 'The unit at '+this.Unit.Territory.name+' ';			
			};
			OrderObj.reHTML('orderBegin');			
			OrderObj.setSelectsGreen();
		}, this);
		
	}
	
}			



