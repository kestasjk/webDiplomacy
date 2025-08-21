function loadCoastConvoyOrders(convoyCoasts) {

        MyOrders.map(function(o) {
                var IA = o.interactiveMap;

                IA.setConvoy = function(terrID) {
                        if (Territories.get(terrID).type == "Coast" && !(convoyCoasts.include(terrID) && this.isUnitIn(terrID) && Territories.get(terrID).Unit.type == "Fleet"))
                                this.finishConvoy(terrID);
                        else {
                                if (!this.isUnitIn(terrID)) {
                                        interactiveMap.errorMessages.noUnit(terrID);
                                        return;
                                }
                                if (Territories.get(terrID).Unit.type != "Fleet") {
                                        interactiveMap.errorMessages.noFleet(terrID);
                                        return;
                                }
                                /*if(Territories.get(terrID).type != "Sea"){
                                 alert("Convoying " + interactiveMap.parameters.fleetName + " not in Sea-Territory");
                                 return;
                                 }*/
                                if (!Territories.get(terrID).Unit.getMovableTerritories().pluck('coastParentID').include(Object.isUndefined(this.convoyChain[0]) ? this.Order.Unit.terrID : this.convoyChain[this.convoyChain.length - 1])) {
                                        alert(interactiveMap.parameters.fleetName + " (" + Territories.get(terrID).name + ") not neighbor of " + (Object.isUndefined(this.convoyChain[0]) ? this.Order.Unit.Territory.name : Territories.get(this.convoyChain[this.convoyChain.length - 1]).name) + "!");
                                        return;
                                }
                                if (this.convoyChain.any(function(e) {
                                        return terrID == e;
                                })) {
                                        alert(interactiveMap.parameters.fleetName + " (" + Territories.get(terrID).name + ") already selected!");
                                        return;
                                }
                                if (!Object.isUndefined(this.convoyChain[0]))
                                        interactiveMap.insertMessage(", ");
                                else
                                        this.convoyChain = new Array();
                                this.convoyChain.push(terrID);
                                interactiveMap.insertMessage(Territories.get(terrID).name);

                                this.getTerrChoices(); 
                                interactiveMap.greyOut.draw(this.terrChoices);

                                if (convoyCoasts.include(terrID) && this.isUnitIn(terrID) && Territories.get(terrID).Unit.type == "Fleet" && this.convoyChain.length > 1)
                                    interactiveMap.interface.orderMenu.showElement($("imgConvoy")); // use convoy button to finish convoys on ConvoyCoasts with fleets
								else
									interactiveMap.interface.orderMenu.hideElement($("imgConvoy"));
                        }
                };

                IA.finishCoastConvoy = function(terrID) {
					this.finishConvoy(terrID);
                };
				
				
				/*
				 * As Convoy button is used to end convoy on Convoy Coast with 
				 * fleet, different actions have to be taken, if setConvoy is 
				 * called via the Convoy-Button.
				 */
				var origSetOrder = IA.setOrder;
				
				IA.setOrder = function(value){
					var terrID = this.convoyChain.last();
					if(this.orderType == "Convoy" && value == "Convoy" && !Object.isUndefined(terrID) && Territories.get(terrID).type == "Coast" && convoyCoasts.include(terrID) && this.convoyChain.length > 1)
						this.finishCoastConvoy(this.convoyChain.pop());
					else
						(origSetOrder.bind(this))(value);
				}
				
				/*
				 * Add CoastConvoys to terrChoices for convoys
				 */
				IA.getTerrChoices = function() {
					switch (this.orderType) {
						case "Move":
							this.terrChoices = o.Unit.getMovableTerritories().pluck('id');
							break;
						case "Support hold":
							this.terrChoices = o.Unit.getSupportHoldChoices();
							break;
						case "Support move":
							this.terrChoices = o.Unit.getSupportMoveToChoices().select(function(c) {
								return (o.Unit.getSupportMoveFromChoices(Territories.get(c)).length != 0);
							});
							break;
						case "Support move to":
							this.terrChoices = o.Unit.getSupportMoveFromChoices(o.ToTerritory);
							break;
						case "Convoy":
							var currentUnit = Object.isUndefined(this.convoyChain[0]) ? this.Order.Unit : Territories.get(this.convoyChain[this.convoyChain.length - 1]).Unit;
							this.terrChoices = currentUnit.getBorderUnits().select(function(u) {
								return u.Territory.type == "Sea" || convoyCoasts.include(u.terrID);   //adjacent fleets <---- include Convoy coasts
							}).pluck("terrID");
							if (currentUnit.type == "Fleet")
								this.terrChoices = this.terrChoices.concat(currentUnit.Territory.getBorderTerritories().select(function(t) {
									return t.type == "Coast";       //adjacent coast if at least one fleet in convoyChain
								}).pluck('id'));
							this.terrChoices = this.terrChoices.select(function(terrID) {
								return (this.convoyChain.indexOf(terrID) == -1) && (terrID != this.Order.Unit.terrID);   //remove already used fleets and army's origin
							}, this);
							break;

						case "Retreat":
							this.terrChoices = o.toTerrChoices.keys();
					}
				};
        });

}
