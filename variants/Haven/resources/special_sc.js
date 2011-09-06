function SupplyCentersCorrect(ids) {

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
		if( t.coastParent.supply && (t.coastParent.countryID == context.countryID || ids.inArray(t.coastParent.id) ) && t.coastParent.ownerCountryID == context.countryID && Object.isUndefined(t.coastParent.Unit) )
		{
			SupplyCenters.push(t);
		}
	},this);
	
}
