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

function Order(orderData)
{
	this.id = orderData.id;
	
	this.status = orderData.status;
	this.error = orderData.error;
	
	this.unitID = orderData.unitID;
	this.type = (orderData.type==null?'':orderData.type);
	this.toTerrID = (orderData.toTerrID==null?'':orderData.toTerrID);
	this.fromTerrID = (orderData.fromTerrID==null?'':orderData.fromTerrID);
	this.viaConvoy = (orderData.viaConvoy==null?'':orderData.viaConvoy);
	
	this.autoFill = true;
	
	this.requirements = [ 'type' ];
	
	this.getDataArray = function() {
		var da = { id:this.id, unitID:this.unitID, type:this.type, 
			toTerrID:this.toTerrID, fromTerrID:this.fromTerrID, 
			viaConvoy:this.viaConvoy };
		
		if( !Object.isUndefined(this.convoyPath) && this.convoyPath.length>0)
			da.convoyPath = this.convoyPath;
		
		return da;
	};
	
	this.load = function() {
		if( this.unitID != null )
		{
			this.Unit = Units.get(this.unitID);
			this.Unit.Order = this;
		}
		
		if( this.toTerrID != null ) this.ToTerritory = Territories.get(this.toTerrID);
		
		if( this.fromTerrID != null ) this.FromTerritory = Territories.get(this.fromTerrID);
		
		$('orderID'+this.id).replace(new Element('div', {id: 'orderID'+this.id }));//'<div id="orderID'+this.id+'"></div>');
		
		this.messageSpan = new Element('span', {'class': 'orderNotice'} );
		$('orderID'+this.id).appendChild(this.messageSpan);
		
		this.unitIconArea = $('orderID'+this.id+'UnitIconArea');
		
		this.orderSegs = new Hash();
		this.orderSegmentNames.map(
			function(n){
				var el = new Element('span', 
					{'class': 'orderSegment'}
					);
				
				el.addClassName(n);
				$('orderID'+this.id).appendChild(el);
				
				el.Order = this;
				
				this.orderSegs.set(n,el);
			},this);
		
		this.orderSegmentNames.map(
			function(n) {
				var el=this.orderSegs.get(n);
				
				if( n=='orderBegin' || this.requirements.member(n) )
				{
					this.updateChoice(n);
					el.update(this.orderSegmentHTML(n));
				}
			},this);
		
		this.updaterequirements();
		this.updateChoices(this.requirements);
		
		this.reHTML('orderBegin');
		this.requirements.map(function(n){ this.reHTML(n); },this);
		this.reHTML('orderEnd');
		
		if( !this.isChanged )
			this.setSelectsGreen();
		
		$('orderID'+this.id).select('select').map(function(e){
			e.observe('change', this.onChange.bindAsEventListener(this));
		},this);
		
		if( !Object.isUndefined(this.Unit) )
		{
			this.setUnitIconArea(this.Unit.type);
		}
		
		this.checkComplete();
	};
	
	this.setSelects = function(f) {
		$('orderID'+this.id).select('select').map(f);
	};
	
	this.setSelectsGreen = function() {
		this.isChanged=false;
		$('orderID'+this.id).select('select').map(function(e){ 
			e.setStyle({backgroundColor: ''})
		});
	};
	
	this.currentUnitIcon=false;
	this.setUnitIconArea = function(newIcon) {
		if( newIcon == this.currentUnitIcon ) return;
		
		if( this.currentUnitIcon != false )
			$('orderID'+this.id).removeClassName(this.currentUnitIcon.toLowerCase());
		
		$('orderID'+this.id).addClassName(newIcon.toLowerCase());
		this.currentUnitIcon=newIcon;
		
		this.unitIconArea.update('<img src="'+l_s('images/'+newIcon+'.png')+'" alt="'+l_t(newIcon)+'" />');
	};
	
	this.setMessageSpan = function(message) {
		this.messageSpan.update(message);
	};
	
	this.onChange = function(event) {
		
		var DropDown=event.findElement();
	
		var changedName = this.requirements.find( function(namae) {
			return ( DropDown.name == 'orderForm['+this.id+']['+namae+']' );
		},this);
		
		DropDown.setStyle({backgroundColor: '#ffd4c9'});
		
		this.inputValue(changedName, DropDown.getValue());
	};
	
	this.inputValue = function(name, value) {
		if( !this.isValid(name,value) ) return;
		
		this.updateValue(name,value);
		this.checkComplete();
		
		this.postUpdate(); // Do post-update functionality
		
		OrdersHTML.updateFormButtons();
	};
	
	// Extended by Diplomacy phase order
	this.postUpdate=function() { };
	
	this.isChanged=false;
	this.setChanged = function(is) {
		if( is == this.isChanged ) return false;
		
		this.isChanged=is;
		return true;
	}
	
	this.isComplete=false;
	this.checkComplete = function() {
		this.setComplete(this.requirements.all(
			function(r){
				return this[r]!='';
			}
		,this));
		return this.isComplete;
	}
	
	this.setComplete = function(is) {
		if( is == this.isComplete) return false;
		
		this.isComplete=is;
		this.alterOrderSegment('orderEnd', this.endHTML());
		
		return true;
	}
	
	this.endHTML = function() {
		return ( this.isComplete ? '.' : '...' );
	}
	
	this.wipe = function(toWipe) {
		toWipe.map(function(w) {
			switch(w) {
				case 'type': this.type=''; this.typeChoices=undefined; return;
				case 'toTerrID': this.toTerrID='';this.ToTerritory=undefined; this.toTerrChoices=undefined; return;
				case 'fromTerrID': this.fromTerrID='';this.FromTerritory=undefined;this.fromTerrChoices=undefined; return;
				case 'viaConvoy': this.viaConvoy='';this.viaConvoyChoices=undefined; 
			}
			
			}, this);
		
		toWipe.map(function(w) {this.reHTML(w);},this);
	}
	
	this.fromrequirements = function(arr) {
		return this.requirements.select(function(r){return arr.member(r);});
	}
	
	this.nextrequirement = function(name) {
		for(var i=0; i<this.requirements.length; i++)
		{
			if( this.requirements[i] == name && ((i+1)<this.requirements.length) )
				return this.requirements[i+1];
		}
		return false;
	};
	
	this.isValid = function(name, value) {
		var choices;
		
		switch(name)
		{
			case 'type': choices = $H(this.typeChoices).keys(); break;
			case 'toTerrID': choices = $H(this.toTerrChoices).keys(); break;
			case 'fromTerrID': choices = $H(this.fromTerrChoices).keys(); break;
			case 'viaConvoy': choices = $H(this.viaConvoyChoices).keys(); break;
		}
		
		if( Object.isUndefined(choices) || choices.length==0 || ! choices.member(value) )
			return false;
		else
			return true;
	};
	
	this.updateValue = function(name,newValue) {
		if( Object.isUndefined(newValue) ) return;
		
		var updatedChoices=[ ];
		
		this.setChanged(true);
		
		switch(name) {
			case 'type':
				this.type=newValue;
				this.wipe( ['toTerrID','fromTerrID','viaConvoy'] );
				this.updaterequirements();
				updatedChoices=this.updateChoices( this.fromrequirements(['toTerrID','viaConvoy']) );
				break;
			case 'toTerrID':
				this.toTerrID=newValue;
				this.ToTerritory = Territories.get(newValue); 
				this.wipe( this.fromrequirements(['fromTerrID','viaConvoy']) );
				updatedChoices=this.updateChoices( this.fromrequirements(['fromTerrID','viaConvoy']) );
				break;
			case 'fromTerrID':
				this.fromTerrID=newValue;
				this.FromTerritory = Territories.get(newValue); 
				break;
			case 'viaConvoy':
				this.viaConvoy = newValue;
				break;
		}
		
		updatedChoices.map(function(c){ this.reHTML(c); }, this);
	};
	
	this.orderSegmentNames = ['orderBegin', 'type', 'toTerrID', 'fromTerrID', 'viaConvoy', 'orderEnd' ];
	
	this.orderSegmentHTML = function(name) {
		switch(name) {
			case 'orderBegin': return this.beginHTML();
			case 'type': return this.typeHTML();
			case 'toTerrID': return this.toTerrHTML();
			case 'fromTerrID': return this.fromTerrHTML();
			case 'viaConvoy': return this.viaConvoyHTML();
			case 'orderEnd': return this.endHTML();
		}
	}
	this.reHTML = function(name) {
		this.alterOrderSegment(name, this.orderSegmentHTML(name));
	}
	this.alterOrderSegment = function (name, HTML) {
		var OrderSegment = this.orderSegs.get(name);
		
		OrderSegment.update(HTML);
		
		OrderSegment.select('select').map(function(e){ e.observe('change', this.onChange.bindAsEventListener(this));},this);
	};
	
	this.updateChoices = function(choices) {
		return choices.select(function(c) { return this.updateChoice(c); }, this);
	}
	this.updateChoice = function(name) {
		var newChoices;
		var currentValue;
		
		switch(name) {
			case 'type': currentValue=this.type; newChoices = this.updateTypeChoices(); break;
			case 'toTerrID': currentValue=this.toTerrID; newChoices = this.updateToTerrChoices(); break;
			case 'fromTerrID': currentValue=this.fromTerrID; newChoices = this.updateFromTerrChoices(); break;
			case 'viaConvoy': currentValue=this.viaConvoy; newChoices = this.updateViaConvoyChoices(); break;
			default: return false;
		}
		
		newChoices=$H(newChoices);
		
		if( newChoices.values().length == 1 )
		{
			var onlyValue=newChoices.keys()[0];
			if( Object.isUndefined(onlyValue) || onlyValue=='undefined' )
				this.updateValue(name, '');
			else if( onlyValue != currentValue )
				this.updateValue(name, onlyValue);
		}
		else if ( newChoices.values().length == 0 )
			this.updateValue(name, '');
		
		return true;
	}
	
	this.formDropDown=function(name, aoptions, value) {
		var elementName='orderForm['+this.id+']['+name+']';
		
		if( Object.isUndefined(aoptions) ){ return ''; }

		var optionsCount = aoptions.length;
		
		if( optionsCount == 0 ) return '<em>['+l_t('No options available!')+'</em>]';
		
		var options = $H(aoptions);
		if( optionsCount == 1 && !Object.isUndefined(options.get('undefined')) )
			 return '<em>['+l_t('No options available!')+'</em>]';
		
		if( OrdersHTML.finalized )
			return ' '+options.get(value)+' ';
		else
		{
			var isDisabled=(options.values().length == 1);
				
			var html=' <select orderType="'+name+'" class="orderDropDown '+(isDisabled?' orderDisabled':'')+'" name="'+
				elementName+'" style="background-color:#ffd4c9" '+(isDisabled?' disabled':'')+' >';
			
			if( !Object.isUndefined(value) && value != '' )
			{
				var valueName = options.get(value);
				html=html+'<option selected="selected" value="'+value+'">'+valueName+'</option>';
			}
			else
			{
				value = '';
				html=html+'<option selected="selected" value=""></option>';
			}
			
			var valueName = '';
			
			options.each(function(pair) {
				if( !( Object.isUndefined(pair[0]) || pair[0]=='undefined' ) && pair[0] != value )
					html=html+'<option value="'+pair[0]+'">'+pair[1]+'</option>';
			});
			
			html = html+'</select> ';
			
			return html;
		}
	};
	
	this.setResult = function(Result) {
		var icon = '';
		var message = '';
		
		if( Object.isUndefined(Result) || Result.changed == 'No' )
		{
			this.setMessageSpan('');
			return;
		}
		
		if( Result.status == 'Complete' )
		{
			if( !this.checkComplete() )
			{
				message = l_t('Incompatibility between order and requirements, please report via the forums!');
				icon = 'alert';
			}
			else
			{
				this.setSelectsGreen();
				if( OrdersHTML.finalized )
					icon = 'tick';
				else
					icon = 'tick_faded';
			}
		}
		else if( Result.status == 'Incomplete' )
		{
			if( this.checkComplete() )
			{
				message = l_t('Incompatibility between order and requirements, please report via the forums!');
				icon = 'alert';
			}
			else
			{
				this.setSelectsGreen();
				icon = 'cross';
			}
		}
		else if ( Result.status == 'Invalid' )
		{
			icon = 'alert';
			message = ' '+(Result.notice==null ? l_t('Undefined error') : l_t(Result.notice))+'<br />';
		}
		
		this.setMessageSpan('<img src="'+l_s('images/icons/'+icon+'.png')+'" alt="'+l_t(icon)+'" /> '+message);
		
	};
	
	this.arrayToChoices = function(arr) {
		if(Object.isUndefined(arr)) arr = [ ];
		
		var choices = new Hash();
		arr.map(function(c) { choices.set(c, l_t(Territories.get(c).name)); });
		
		return choices;
	};
	
	this.fNothing = function() { return; };
};

function loadOrdersModel() {
	MyOrders=new Array();
	for(i=0; i<ordersData.length; i++)
	{
		var OrderObj = new Order(ordersData[i]);
		
		MyOrders.push(OrderObj);
	}
}
