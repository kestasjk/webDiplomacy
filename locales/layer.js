/*
    Copyright (C) 2004-2012 Kestas J. Kuliukas

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

// Substitute text
function l_t(text) {
	
	var args = [];
	
	if( arguments.length > 1 ) {
		args = $A(arguments);
		
		args.shift();
	}
	
	return Locale.text(text, args);
}
// Substitute static file
function l_s(file) {
	return Locale.staticFile(file);
}

Locale = {
		// Run immidiately after the translation layer JS has been loaded
		initialize : function () {
			
		},
		
		// Run before all other webDip scripts
		onLoad : function () {
			
		},
		
		// Run after all other webDip scripts
		afterLoad : function () {
		},
		
		// Text substitution
		text : function (text, args) {
			if( this.textLookup.keys().include(text) ) {
				text = this.textLookup.get(text);
			} else {
				this.failedLookup(text);
			}
			
			return vsprintf(text, args);
		},
		
		// Static file substitution
		staticFile : function (file) {
			return file;
		},

		// The text lookup table, usually set via e.g. Italian/lookup.js
		textLookup : $H({}),
		
		// Report a failure, if in debug mode
		reportFailure: function(failure) {
			if( WEBDIP_DEBUG ) {
				$('jsLocalizationDebug').insert(text+'<br />');
			}
		},
		
		// Collected failed lookups
		failedLookups : $A(),
		
		// Reporting a failed lookup (log it if in debug mode)
		failedLookup : function(text) {
			if( WEBDIP_DEBUG && !this.failedLookups.include(text) ) {
				this.failedLookups.push(text);
			}
		}
};
