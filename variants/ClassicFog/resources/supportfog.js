function SupportFog() {
	
		UnitClass.addMethods( {	
		
			// Can I cross a given Border
			canCrossBorder : function (b) {
				if( this.id < 1000 )
					return true;
				if( this.type == 'Army' && !b.a ) 
					return false;
				else if( this.type == 'Fleet' && !b.f ) 
					return false;
				else 
					return true;
			}
		
		});
};