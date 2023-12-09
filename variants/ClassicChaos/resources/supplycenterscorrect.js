function SupplyCentersCorrect() {
	SupplyCenters = new Array();
	
	/*
	 * In javascript/board/load.js SupplyCenters is created using similar code but only with home SCs, 
	 * replacing it here allows them to be selected. A modified OrderInterface calls this shortly after load.js.
	 */
	Territories.each(function(p){
		var t=p[1];
		if( t.coastParent.supply && ( t.coastParent.ownerCountryID == context.countryID || context.isSandboxMode ) && Object.isUndefined(t.coastParent.Unit) )
		{
			t.countryID = context.countryID; // Ensure this passes the extra countryID filter for sandbox games
			SupplyCenters.push(t);
		}
	},this);
}