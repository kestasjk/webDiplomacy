function SupplyCentersCorrect(ids) {
	
	/*
	 * In javascript/board/load.js SupplyCenters is created using similar code but only with home SCs, 
	 * replacing it here allows them to be selected. A modified OrderInterface calls this shortly after load.js.
	 */
	SupplyCenters = new Array();
	
	Array.prototype.inArray = function (value)	{
		var i;
		for (i=0; i < this.length; i++) {
			if (this[i] == value) {
				return true;
			}
		}
		return false;
	};

	/* If we have a list of valid SC's use the list, else build anywhere... */
	if (ids.length > 0) {	
		Territories.each(function(p){
			var t=p[1];
			if( t.coastParent.supply && ids.inArray(t.coastParent.id)  && t.coastParent.ownerCountryID == context.countryID && Object.isUndefined(t.coastParent.Unit))
			{
				SupplyCenters.push(t);
			}
		},this);
	} else {
		Territories.each(function(p){
			var t=p[1];
			if( t.coastParent.supply && t.coastParent.ownerCountryID == context.countryID && Object.isUndefined(t.coastParent.Unit) )
			{
				SupplyCenters.push(t);
			}
		},this);

	}
}