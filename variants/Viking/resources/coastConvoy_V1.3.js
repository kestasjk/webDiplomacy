Array.prototype.inArray = function (value)	{
	var i;
	for (i=0; i < this.length; i++) {
		if (this[i] == value) {
			return true;
		}
	}
	return false;
};	

// Change 3 functions of the convoy-generation to allow some coasts too 
function coastConvoy_loadModel(Coasts)
{
	ConvoyGroupClass = Class.create(ConvoyGroupClass, {
		loadFleet : function(Fleet) {
			if( Fleet.convoyLink ) return false;
			if( (Fleet.Territory.type != 'Sea') && (!Coasts.inArray(Fleet.Territory.id)) ) return false;
			
			Fleet.convoyLink = true;
			Fleet.ConvoyGroup = this;
			this.Fleets.set(Fleet.Territory.id, Fleet);
			
			Fleet.Territory.getBorderTerritories().map(function(t) {
				if ( (t.type == 'Sea' || Coasts.inArray(t.id)) && !Object.isUndefined(t.Unit) && t.Unit.type == 'Fleet')
					this.loadFleet(t.Unit);
			},this);
		},

		// Only allow Convoy on borders that fleets can pass.
		loadCoasts : function() {
			this.Fleets.values().pluck('Territory').map(
				function (sea)
				{
					sea.Borders.select(function(a){return (a.f== true);}).pluck('id').compact().map(function(n){return Territories.get(n);},this).map(
						function(c)
						{
							if( c.type != 'Coast' ) return;
							c = c.coastParent;
							
							if( !Object.isUndefined(this.Coasts.get(c.id)) ) return;
							
							if( Object.isUndefined(c.ConvoyGroups) )
								c.ConvoyGroups = [ ];
							
							c.ConvoyGroups.push(this);
							
							this.Coasts.set(c.id, c);
							
							if( !Object.isUndefined(c.Unit) && c.Unit.type=='Army' )
								this.Armies.set(c.id, c.Unit);
							
						}, this);
				}, this);
		},
	
		pathArmyToCoast: function(StartTerr, EndTerr) {
			var ns=this.nodeSetClass();
			ns.routeSetStart(
				StartTerr, 
				function(EndNode) { return ( EndNode.id == EndTerr.id ); },
				function(AllNode) { 
					return (AllNode.type == 'Sea' ||
						(Coasts.inArray(AllNode.id) && 
							!Object.isUndefined(Units.find(function(p){return (p[1].terrID==AllNode.id && p[1].type=='Fleet');}))));
				},
				function(AnyNode) { return true; }
			);
			return ns.Path;
		},
		
		pathArmyToCoastWithFleet: function(StartTerr, EndTerr, WithFleetTerr) {
			var ns=this.nodeSetClass();
			ns.routeSetStart(
				StartTerr, 
				function(EndNode) { return ( EndNode.id == EndTerr.id ); },
				function(AllNode) { 
					return (AllNode.type == 'Sea' ||
						(Coasts.inArray(AllNode.id) && 
							!Object.isUndefined(Units.find(function(p){return (p[1].terrID==AllNode.id && p[1].type=='Fleet');}))));
				},
				function(AnyNode) { return ( AnyNode.id == WithFleetTerr.id ); }
			);
			return ns.Path;
		}
		
	});
}	
	
// Create a ConvoyGroup for fleets that start on a special-coast too.
function coastConvoy_loadBoard(Coasts)
{
	CGs=new Array();
	Units.values().map(function(f) {
		if( f.type == 'Fleet' && Coasts.inArray(f.Territory.id)  && !f.convoyLink ) 
		{
			var CG=new ConvoyGroupClass();
			CG.loadFleet(f);
			CG.loadCoasts();
			CGs.push(CG);
		}
	},this);
	CGs.map(function(CG){CG.linkGroups();},this);
	CGs.map(function(CG){CG.prepare();},this);
	
}

// Allow the convoy-command for the special Coasts too.
function coastConvoy_loadOrdersPhase(Coasts)
{	
	MyOrders.map(function(OrderObj)
	{
		OrderObj.updateTypeChoices = function () {
			this.typeChoices = {
				'Hold': 'hold', 'Move': 'move', 'Support hold': 'support hold', 'Support move': 'support move'
			};
			
			if( this.Unit.type == 'Fleet' && (this.Unit.Territory.type == 'Sea' || Coasts.inArray(this.Unit.Territory.id)) )
				this.typeChoices['Convoy']='convoy';
			
			return this.typeChoices;
		};
		
		OrderObj.updateTypeChoices(OrderObj.requirements);
		OrderObj.requirements.map(function(n){ OrderObj.reHTML(n); },OrderObj);
		OrderObj.setSelectsGreen();

	}, this);
}