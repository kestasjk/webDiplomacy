import TerritoryClass from "./TerritoryClass";
import UnitClass from "./UnitClass";
import { IConvoyGroup, ITerritory, IUnit } from "./Interfaces";

export default class ConvoyGroupClass {
  armies: UnitClass[];

  coasts: TerritoryClass[];

  fleets: UnitClass[];

  constructor({ armies, coasts, fleets }: IConvoyGroup) {
    this.coasts = coasts;
    this.armies = armies;
    this.fleets = fleets;
  }
}

// if( context.phase == 'Diplomacy' )
// 	{
// 		// Load and initialize ConvoyGroups
// 		CGs=new Array();
// 		Units.values().map(function(f) {
// 			if( f.type == 'Fleet' && f.Territory.type=='Sea' )
// 			{
// 				var CG=new ConvoyGroupClass();
// 				CG.loadFleet(f);
// 				CG.loadCoasts();
// 				CGs.push(CG);
// 			}
// 		},this);
// 		CGs.map(function(CG){CG.linkGroups();},this);
// 		CGs.map(function(CG){CG.prepare();},this);
// 	}
// 	else if( context.phase == 'Retreats' )
// 	{
// 		// Find retreating units
// 		RetreatingUnits = new Array();

// 		Units.each(function(p) {
// 			var u=p[1];

// 			// Retreating units don't yet have any Territory set
// 			if( Object.isUndefined(u.Territory) )
// 			{
// 				var unit = Units.get(u.id);
// 				Object.extend(unit, ProtoUnit);

// 				unit.Territory = Territories.get(unit.terrID);

// 				RetreatingUnits.push(unit);

// 				if( unit.countryID == context.countryID )
// 					MyUnits.push(unit);
// 			}
// 		},this);
// 	}
// 	else if( context.phase == 'Builds' )
// 	{
// 		// Find supply centers belonging to the current user
// 		SupplyCenters = new Array();

// 		Territories.each(function(p){
// 			var t=p[1];
// 			if( t.coastParent.supply && t.coastParent.countryID == context.countryID && t.coastParent.ownerCountryID == context.countryID && Object.isUndefined(t.coastParent.Unit) )
// 			{
// 				SupplyCenters.push(t);
// 			}
// 		},this);
// 	}

// nodeSetClass: function() {
//     var ns=new NodeSetClass();
//     ns.routeSetLoad(this);
//     return ns;
// },
// pathArmyToCoast: function(StartTerr, EndTerr) {
//     var ns=this.nodeSetClass();
//     ns.routeSetStart(
//         StartTerr,
//         function(EndNode) { return ( EndNode.id == EndTerr.id ); },
//         function(AllNode) { return ( AllNode.type=='Sea' ); },
//         function(AnyNode) { return true; }
//     );
//     return ns.Path;
// },
// pathArmyToCoastWithoutFleet: function(StartTerr, EndTerr, WithoutFleetTerr) {
//     var ns=this.nodeSetClass();
//     ns.routeSetStart(
//         StartTerr,
//         function(EndNode) { return ( EndNode.id == EndTerr.id ); },
//         function(AllNode) { return ( AllNode.type == 'Sea' && AllNode.id != WithoutFleetTerr.id ); },
//         function(AnyNode) { return true; }
//     );
//     return ns.Path;
// },
// pathArmyToCoastWithFleet: function(StartTerr, EndTerr, WithFleetTerr) {
//     var ns=this.nodeSetClass();
//     ns.routeSetStart(
//         StartTerr,
//         function(EndNode) { return ( EndNode.id == EndTerr.id ); },
//         function(AllNode) { return ( AllNode.type == 'Sea' ); },
//         function(AnyNode) { return ( AnyNode.id == WithFleetTerr.id ); }
//     );
//     return ns.Path;
// },
// initialize : function() {
//     // Once fully loaded these are converted to arrays, they start as hashes for easy checking of whether they are already loaded
//     this.Coasts = new Hash();
//     this.Fleets = new Hash();
//     this.Armies = new Hash();
// },

// // Run after all convoy groups have been loaded, to convert hashes into arrays
// prepare : function() {
//     this.Coasts=this.Coasts.values();
//     this.Armies=this.Armies.values();
//     this.Fleets=this.Fleets.values();
// },

// // First load fleets, then load coasts & armies
// loadFleet : function(Fleet) {
//     if( Fleet.convoyLink ) return false;
//     if( Fleet.Territory.type != 'Sea' ) return false;

//     Fleet.convoyLink = true;
//     Fleet.ConvoyGroup = this;
//     this.Fleets.set(Fleet.Territory.id, Fleet);

//     Fleet.Territory.getBorderTerritories().map(function(t) {
//             if ( t.type == 'Sea' && !Object.isUndefined(t.Unit) )
//                 this.loadFleet(t.Unit);
//         },this);
// },

// loadCoasts : function() {
//     this.Fleets.values().pluck('Territory').map(
//         function (sea)
//         {
//             sea.getBorderTerritories().map(
//                 function(c)
//                 {
//                     if( c.type != 'Coast' ) return;
//                     c = c.coastParent;

//                     if( !Object.isUndefined(this.Coasts.get(c.id)) ) return;

//                     if( Object.isUndefined(c.ConvoyGroups) )
//                         c.ConvoyGroups = [ ];

//                     c.ConvoyGroups.push(this);

//                     this.Coasts.set(c.id, c);

//                     if( !Object.isUndefined(c.Unit) && c.Unit.type=='Army' )
//                         this.Armies.set(c.id, c.Unit);

//                 }, this);
//             }, this);
// },

// linkGroups : function() {
//     this.Coasts.values().map(function(c) {

//         if( !Object.isUndefined(c.convoyLink) && c.convoyLink ) return;

//         if( c.ConvoyGroups.length == 1 )
//         {
//             c.ConvoyGroup = c.ConvoyGroups[0];
//         }
//         else
//         {
//             c.ConvoyGroup = new ConvoyGroupClass();

//             c.ConvoyGroups.map(function(cg) {
//                 c.ConvoyGroup.Armies = c.ConvoyGroup.Armies.merge(cg.Armies);
//                 c.ConvoyGroup.Coasts = c.ConvoyGroup.Coasts.merge(cg.Coasts);
//                 c.ConvoyGroup.Fleets = c.ConvoyGroup.Fleets.merge(cg.Fleets);
//             },this);

//             c.ConvoyGroup.prepare();
//         }

//         c.ConvoyGroups = undefined;

//         c.convoyLink=true;
//         if( !Object.isUndefined(c.Unit) && c.Unit.type=='Army' )
//         {
//             c.Unit.convoyLink=true;
//             c.Unit.ConvoyGroup = c.ConvoyGroup;
//         }
//     },this);
// }
