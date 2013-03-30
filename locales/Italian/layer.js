
/*
 * 
 * The code below modifies the Italian JavaScript localization layer to detect when a 
 * translation is e.g. "the %s", where %s is an army, and changes the translation so 
 * that the right sex is used (le/la).
 */
// Keep a copy of the original text() function which we can use.
Locale._text = Locale.text;

// Replace the text function with a version which is sensitive to armies etc.
Locale.text = function(text, args) {
	
	if( args.length > 0 && typeof args[0] === 'string' ) {
		if( args[0].toLowerCase() == "armata")
		{
			// Army is the first word being fed into this
			
			switch(text) {
			case 'The %s at %s %s':
				text='L\'%s nel %s %s';
				break;
			case 'The %s at %s disband':
				text='L\'%s nel %s viene distrutta';
				break;
			case 'The %s at %s retreat to %s':
				text='L\'%s nel %s ritira in %s';
				break;
			case ' the %s in %s ':
				text=' l\'%s in %s ';
				break;
			case 'The %s at %s':
				text='L\'%s nel %s';
				break;
			case 'The %s at %s ':
				text='L\'%s nel %s ';
				break;
			}
		}
		else if( args[0].toLowerCase() == "flotta") 
		{
			// Fleet is the first word being fed into this
			
			switch(text) {
			case 'The %s at %s %s':
				text='La %s nella %s %s';
				break;
			case 'The %s at %s disband':
				text='La %s nella %s viene distrutta';
				break;
			case 'The %s at %s retreat to %s':
				text='La %s nella %s ritira in %s';
				break;
			case ' the %s in %s ':
				text=' la %s in %s ';
				break;
			case 'The %s at %s':
				text='La %s nella %s';
				break;
			case 'The %s at %s ':
				text='La %s nella %s ';
				break;
			}
		}
		else if ( text == ' to %s ' )
		{
			text = ' %s';

			Territories.each(function(p){
				var t=p[1];
				if( t.supply )
					args[0] = args[0].replace(t.name, ' a '+t.name);
				else
					args[0] = args[0].replace(t.name, ' in '+t.name);
			},this);
		}
	}

	return Locale._text(text, args);
}