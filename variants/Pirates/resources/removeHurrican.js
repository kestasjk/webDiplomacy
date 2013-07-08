function removeHurrican() {
	AA = Units.find(function(p){return (p[1].countryID == "14");});
	if (!Object.isUndefined(AA))
	{
		Units.unset(AA[0]);
		TerrStatus.map(function(TerrStat)
		{
			if (Object.isUndefined(Units.find(function(p){return (p[1].id==TerrStat.unitID);})))
				TerrStat.unitID = null;
		}, this);
	}
}