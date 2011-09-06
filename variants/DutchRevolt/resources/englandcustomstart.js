function CustomBuild(ids) {

	Array.prototype.inArray = function (value)	{
		var i;
		for (i=0; i < this.length; i++) {
			if (this[i] == value) {
				return true;
			}
		}
		return false;
	};

	SupplyCenters = new Array();

	Territories.each(function(p){
		var t=p[1];
		if( ids.inArray(t.coastParent.id))
		{
			SupplyCenters.push(t);
		}
	},this);
};	

function EnglandStartSC() {
	
	// No armies for England in the first Build pahse
	MyOrders.map(function(OrderObj) {
			OrderObj.updateToTerrChoices = function () {
				switch( this.type )
				{
					case 'Wait':
						this.toTerrChoices = undefined;
						return;
					case 'Build Army':
					case 'Build Fleet':
						this.toTerrChoices = SupplyCenters.select(function(sc){
							if( this.type=='Build Army' && (sc.coast=='Parent'||sc.coast=='No') && (sc.type != 'Sea' ) )
								return true;
							else if ( this.type=='Build Fleet' && ( sc.type != 'Land' && sc.coast!='Parent' ) )
								return true;
							else
								return false;
						},this).pluck('id');
						break;
					case 'Destroy':
						this.toTerrChoices = MyUnits.pluck('Territory').pluck('coastParent').pluck('id');
						break;
				}
				
				this.toTerrChoices=this.arrayToChoices(this.toTerrChoices);
				
				return this.toTerrChoices;
			};
	});
};	

