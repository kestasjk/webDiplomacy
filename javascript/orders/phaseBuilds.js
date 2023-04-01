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
				
				if( this.type == 'Wait')
					this.requirements=['type'];
				else
					this.requirements=['type','toTerrID'];
				
				this.wipe(oldrequirements.reject(function(r){return this.requirements.member(r);},this));
				
			};
			
			OrderObj.updateTypeChoices = function () {
				switch(this.type)
				{
					case 'Build Army':
					case 'Build Fleet':
					case 'Wait':
						this.typeChoices = $H({'Build Army':l_t('Build an army'),
									'Build Fleet':l_t('Build a fleet'),
									'Wait':l_t('Wait/Postpone build.')});
						break;
					case 'Destroy':
						this.typeChoices = $H({'Destroy':l_t('Destroy a unit')});
				}
				
				return this.typeChoices;
			};
			
			OrderObj.updateToTerrChoices = function () {
				switch( this.type )
				{
					case 'Wait':
						this.toTerrChoices = undefined;
						return;
					case 'Build Army':
					case 'Build Fleet':
						this.toTerrChoices = SupplyCenters.select(function(sc){
							if( ! (sc.countryID == this.countryID || sc.coastParent.countryID == this.countryID) ) return false;

							if( this.type=='Build Army' && ( sc.coast=='Parent'||sc.coast=='No') ) 
								return true;
							else if ( this.type=='Build Fleet' && ( sc.type != 'Land' && sc.coast!='Parent' ) )
								return true;
							else
								return false;
						},this).pluck('id');
						break;
					case 'Destroy':
						this.toTerrChoices = MyUnits.select(function(sc) {
							return ( sc.countryID == this.countryID ); // For sandbox mode filter this specific order by countryID
						},this).pluck('Territory').pluck('coastParent').pluck('id');
						break;
				}
				
				this.toTerrChoices=this.arrayToChoices(this.toTerrChoices);
				
				return this.toTerrChoices;
			};
			
			OrderObj.updateFromTerrChoices = OrderObj.fNothing;
			OrderObj.updateViaConvoyChoices = OrderObj.fNothing;
			
			OrderObj.beginHTML = OrderObj.fNothing;
			OrderObj.typeHTML = function () {
				return this.formDropDown('type',this.typeChoices,this.type);
			};
			OrderObj.toTerrHTML = function () {
				var toTerrID=this.formDropDown('toTerrID',this.toTerrChoices,this.toTerrID);
				if(toTerrID=='') return '';
				else return ' at '+toTerrID;
			};
			
			OrderObj.fromTerrHTML = OrderObj.fNothing;
			OrderObj.viaConvoyHTML = OrderObj.fNothing;
			
			OrderObj.load();
		});
};
