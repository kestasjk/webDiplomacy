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
function OrdersHTMLFormClass() {
	this.finalized=false;
	this.ordersChanged=false;
	
	this.formInit=function(context, contextKey){
		this.context = context;
		this.contextKey = contextKey;
		
		this.finalized = context.orderStatus.match('Ready');
		
		this.orderDiv = $('orderDiv'+this.context.memberID);
		this.UpdateButton = $('UpdateButton'+this.context.memberID);
		this.FinalizeButton = $('FinalizeButton'+this.context.memberID);
		this.ordersNoticeArea = $('ordersNoticeArea'+this.context.memberID);
		
		if( this.finalized )
			this.setFinalized();
		else
			this.setUnfinalized();
		
		this.OrdersIndex=new Hash();
		MyOrders.map(function(o){ this.OrdersIndex.set(o.id,o); },this);
		
		if(MyOrders.length==0)
		{
			this.orderDiv.update('<p class="notice">'+l_t('You have no orders to fill for this phase.')+'</p>');
		}
		else
		{
			this.updateFormButtons();
			this.UpdateButton.observe('click', this.onSave.bindAsEventListener(this));
			this.FinalizeButton.observe('click', this.onLock.bindAsEventListener(this));
		}
	}
	
	this.asynchronous=true;
	
	this.sendUpdates = function(url) {
		var up=MyOrders.select(function(o){return o.isComplete;}).map(function(o){return o.getDataArray();},this);
		this.ordersNoticeArea.update('...');
		this.buttonOff('UpdateButton');
		this.buttonOff('FinalizeButton');
		this.ordersChanged=false;
		
		new Ajax.Request(url, 
			{
				method: 'post', asynchronous : this.asynchronous, 
				parameters: {'orderUpdates':up.toJSON(),'context':Object.toJSON(this.context),'contextKey':this.contextKey},
				onFailure: function(response) {
					document.write(response.responseText);
				},
				onSuccess: function(response) {
					OrdersHTML.onSuccess(response);
				}
			}
		);
	};
	
	this.onSave = function(event) {
		this.sendUpdates('ajax.php');
		
		return false;
	}
	
		
	this.onLock = function(event) {
		this.sendUpdates('ajax.php?'+(this.finalized?'notready':'ready')+'=on');
		
		return false;
	}
	
	this.setFinalized = function() {
		this.finalized=true;
		this.buttonOn('FinalizeButton');
		MyOrders.map(function(o){ o.setSelects(function(e){e.disable(); }); });
		this.FinalizeButton.value=l_t('Not ready');
	}
	this.setUnfinalized = function() {
		this.finalized=false;
		MyOrders.map(function(o){ o.setSelects(function(e){if( !e.hasClassName('orderDisabled') ) e.enable();}); });
		this.FinalizeButton.value=l_t('Ready');
	}
	
	this.onSuccess = function(response) {
		if( response.headerJSON == null )
		{
			document.write(response.responseText);
			return;
		}
		else
		{
			if( !Object.isUndefined(response.headerJSON.newContext) )
			{
				this.context = response.headerJSON.newContext;
				this.contextKey = response.headerJSON.newContextKey;
				
				var tmpFinalized = this.context.orderStatus.match('Ready');
				
				if( tmpFinalized && !this.finalized )
					this.setFinalized();
				else if( !tmpFinalized && this.finalized )
					this.setUnfinalized();
			}
			
			if( response.headerJSON.statusIcon.length>0 )
				$$('.member'+this.context.memberID+'StatusIcon').map(function(e){ e.update(response.headerJSON.statusIcon); });
			
			if( response.headerJSON.statusText.length>0 )
				$$('.member'+this.context.memberID+'StatusText').map(function(e){ e.update(response.headerJSON.statusText); });
			
			if( response.headerJSON.notice.length > 0 )
				this.ordersNoticeArea.update(response.headerJSON.statusIcon+' '+response.headerJSON.notice+'<br />');
			else
				this.ordersNoticeArea.update(response.headerJSON.statusIcon);
			
			var orderMessages = $H(response.headerJSON.orders);
			
			this.OrdersIndex.each(function(p){
				var orderID = p[0];
				var Order = p[1];

				Order.setResult(orderMessages.get(orderID));
			},this);
			
			this.updateFormButtons();
		}
	};
	
	this.buttonOff=function(buttonName){
		var Button=$(buttonName+this.context.memberID);

		Button.removeClassName('form-submit');
		Button.setStyle({color:'#777777', backgroundColor:'#F5F5F5', fontWeight:'bold'});
		Button.disable();
	};
	this.buttonOn=function(buttonName){
		var Button=$(buttonName+this.context.memberID);
		
		Button.addClassName('form-submit');
		Button.setStyle({color:'',backgroundColor:'', fontWeight:''});
		Button.enable();
	};
	
	this.updateFormButtons = function() {
		
			if( MyOrders.pluck('isChanged').any(function(c){return c;}) )
			{
				this.ordersChanged=true;
				this.buttonOn('UpdateButton');
			}
			else
			{
				this.ordersChanged=false;
				this.buttonOff('UpdateButton');
			}
	
			if( this.finalized || MyOrders.pluck('isComplete').all(function(c){return c;}) )
				this.buttonOn('FinalizeButton');
			else
				this.buttonOff('FinalizeButton');
	};
};

function loadOrdersForm() {
	OrdersHTML = new OrdersHTMLFormClass();
}
