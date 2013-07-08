function Bidding() {

	MyOrders.map(function(OrderObj)
	{
				
			OrderObj.updateTypeChoices = function () {
				this.typeChoices = {
					'Hold': 'keep', 'Move': 'bid'
				};
				return this.typeChoices;
			};
			
			OrderObj.beginHTML = function () {
				return '1 coin ';
			};
			
			OrderObj.toTerrHTML = function () {
				var toTerrID=this.formDropDown('toTerrID',this.toTerrChoices,this.toTerrID);
				
				switch(this.type) {					
					case 'Move': return ' on '+toTerrID;
					default:     return '';
				}
			};
			
			OrderObj.updateTypeChoices(OrderObj.requirements);
			OrderObj.unitIconArea.update('<img src="variants/GreekDip/resources/coin_stack_gold.png" alt="Coin" />');
			OrderObj.reHTML('orderBegin');
			OrderObj.requirements.map(function(n){ OrderObj.reHTML(n); },OrderObj);
			OrderObj.setSelectsGreen();

	}, this);
		
}