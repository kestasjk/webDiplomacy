function NoMove(unit_id) {
	
		function snapTogether(startArr,snapOnArr)
		{
			for(var i=0; i<snapOnArr.length; i++)
				startArr.push(snapOnArr[i]);
		
			return startArr;
		};

		UnitClass.addMethods( {	
		
			getMoveChoices : function() { 
				var choices = this.getMovableTerritories().pluck('id');
				
				if( this.convoyLink && this.type == 'Army' )
				{
					this.convoyOptions=this.ConvoyGroup.Coasts.select(this.canConvoyTo, this).pluck('id');
					choices=snapTogether(choices,this.convoyOptions).uniq();
				}
				
				if (this.id == unit_id)
				{
					choices = [];				
				}
				return choices;
			},		
		});
};