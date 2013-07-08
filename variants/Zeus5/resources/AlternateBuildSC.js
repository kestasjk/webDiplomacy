function SupplyCentersCorrect()
{
	tc = Territories.find(function(t){ return(t[1].id=='13' 
			&& t[1].coastParent.countryID == context.countryID
			&& t[1].coastParent.ownerCountryID == context.countryID
			&& Object.isUndefined(t[1].coastParent.Unit));});
	if ( !Object.isUndefined(tc)) SupplyCenters.push(tc[1]);
}
