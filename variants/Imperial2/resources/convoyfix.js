
function NewConvoyCode()
{
	MyOrders.map(function(OrderObj)
	{
		OrderObj.postUpdate=function() {
				
			if( false && this.autoFill ) {
					
				var thisOrder=this;
				var filterSet=function(fFilter,fSet) { MyOrders.select(fFilter).map(fSet); };
					
				if( Object.isUndefined(this.setAndShow) ) {
					MyOrders.map(function(o){ 
						o.setAndShow=function(n,v){ o.inputValue(n,v); o.reHTML(n); };
					});
				}
					
				switch( this.type ) {
					case "Support hold":
						if( this.isComplete ) {
							filterSet(function(o) {
								return( o.Unit.Territory.id==thisOrder.ToTerritory.id && o.type=='Move' );
							}, function(o){
								o.setAndShow('type','Hold');
							});
						}
						break;
						
					case "Support move":
						if( !Object.isUndefined(this.ToTerritory) ) {

							MyOrders.select(function(o) { 
								return( o.Unit.Territory.id==thisOrder.ToTerritory.id && o.type!='Move' );
							}).map(function(o){
								o.setAndShow('type','Move');
							});
						}
							
						if( !Object.isUndefined(this.FromTerritory) ) {
							// We have fromTerr, where we are supporting from
							MyOrders.map(function(o) {
							var convoyingArmyList=MyOrders.select(function(o) { 
								return( o.Unit.Territory.id==thisOrder.FromTerritory.id );
							});
							
							convoyingArmyList.map(function(o){
								o.setAndShow('type','Move');
							});
							
							convoyingArmyList.select(function(o) { 
									return( Object.isUndefined(o.ToTerritory)||o.ToTerritory.id!=thisOrder.ToTerritory.id );
								}).map(function(o){
									o.setAndShow('toTerrID',thisOrder.ToTerritory.id.toString());
								});
							},this);
						}
						break;
						
					case "Convoy":
						if( !Object.isUndefined(this.ToTerritory) ) {
							// We have toTerr, where we are convoying to
							
							// If it's one of ours it had better move (had it?)
							MyOrders.select(function(o) {
								return ( o.Unit.Territory.id==thisOrder.ToTerritory.id && o.type!='Move' );
							}).map(function(o){
								setVal(o,'type','Move');
							});
						}
						
						if( !Object.isUndefined(this.FromTerritory) ) {
							// We have fromTerr, where we are convoying from
								// If it's one of ours it had better move to where we're convoying it
							MyOrders.select(function(o){
								return (o.Unit.Territory.id==thisOrder.FromTerritory.id);
							}).map(function(o){
								setVal(o,'type','Move');
								setVal(o,'toTerrID',thisOrder.ToTerritory.id.toString());
								setVal(o,'viaConvoy','Yes');
							});
						}
						break;
				}
			}
		}
	}, this);
	
}