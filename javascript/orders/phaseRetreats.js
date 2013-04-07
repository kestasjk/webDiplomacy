/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas
	
	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */
// See doc/javascript.txt for information on JavaScript in webDiplomacy

function loadOrdersPhase() {

	
	MyOrders.map(function(OrderObj) {
			OrderObj.updaterequirements = function () {
				var oldrequirements = this.requirements;
				
				if( this.type == 'Disband')
					this.requirements=['type'];
				else
					this.requirements=['type','toTerrID'];
				
				this.wipe(oldrequirements.reject(function(r){return this.requirements.member(r);},this));
				
			};
			
			OrderObj.updateTypeChoices = function () {
				this.typeChoices = $H({'Retreat':l_t('retreat'),'Disband':l_t('disband')});
				return this.typeChoices;
			};
			
			OrderObj.updateToTerrChoices = function () {
				if( this.type == 'Disband' )
				{
					this.toTerrChoices = undefined;
					return;
				}
				
				this.toTerrChoices = this.Unit.getMovableTerritories().select(function(t){
					
					if( !Object.isUndefined(t.coastParent.standoff) && t.coastParent.standoff )
						return false;
					else if ( !Object.isUndefined(t.coastParent.Unit) )
						return false;
					else if ( this.Unit.Territory.coastParent.occupiedFromTerrID == t.coastParent.id )
						return false;
					else
						return true;
				},this).pluck('id').uniq();
				
				this.toTerrChoices=this.arrayToChoices(this.toTerrChoices);
				
				return this.toTerrChoices;
			};
			
			OrderObj.beginHTML = function () {
				return l_t('The %s at %s ',l_t(this.Unit.type.toLowerCase()),l_t(this.Unit.Territory.name));
			};
			OrderObj.typeHTML = function () {
				return this.formDropDown('type',this.typeChoices,this.type);
			};
			OrderObj.toTerrHTML = function () {
				var toTerrID=this.formDropDown('toTerrID',this.toTerrChoices,this.toTerrID);
				
				if( toTerrID == '' ) return '';
				else return l_t(' to %s ',toTerrID); // toTerrID comes from the already translated choices.
			};
			
			OrderObj.updateFromTerrChoices = OrderObj.fNothing;
			OrderObj.updateViaConvoyChoices = OrderObj.fNothing;
			OrderObj.fromTerrHTML = OrderObj.fNothing;
			OrderObj.viaConvoyHTML = OrderObj.fNothing;

			OrderObj.load();
		});
};
