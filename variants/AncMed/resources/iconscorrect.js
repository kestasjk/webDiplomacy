function IconsCorrect(VariantName) {
	MyOrders.map(
		function(OrderObj) {
			newIcon=OrderObj.currentUnitIcon
			OrderObj.unitIconArea.update('<img src="variants/'+VariantName+'/resources/'+newIcon.toLowerCase()+'.png" alt="'+newIcon+'" />');
		},this
	);
}

