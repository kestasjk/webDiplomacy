function AddStraitsBorders()
{

	ownerID = TerrStatus.findAll(function(t){return (t.id == "95");}).pluck("ownerCountryID")[0];

	this.Units.each(function(p){
		// ID=51 0 Black Sea
		if (p[1].terrID == 51 && p[1].countryID != ownerID)
		{
			Territories.get("51").Borders        = Territories.get("51").Borders.findAll(function(t){return (t.id != "4");});
			Territories.get("51").CoastalBorders = Territories.get("51").CoastalBorders.findAll(function(t){return (t.id != "4");});
		}
		// ID=4 = Aegaean Sea
		if (p[1].terrID == 4 && p[1].countryID != ownerID)
		{
			Territories.get("4").Borders        = Territories.get("4").Borders.findAll(function(t){return (t.id != "51");});
			Territories.get("4").CoastalBorders = Territories.get("4").CoastalBorders.findAll(function(t){return (t.id != "51");});
		}			
	},this);
	
}