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

// Prevent infinite recursion if there is an error while dealing with an error
var inErrorCode=0;

// Submit JavaScript errors encountered to ajax.php for logging
window.onerror = function (msg, url, line) {

	if( msg=="Error loading script" )
		return false;

	// Filter out some URLs which are beyond my control to handle errors from
	switch( url ) {
		case "http://webdiplomacy.net/contrib/js/prototype.js":
		case "http://www.google-analytics.com/ga.js":
		case "http://webdiplomacy.net/cache/stats/onlineUsers.json":
			return false;
		default:
	}

	// Check if we're already handling an error
	if( inErrorCode==1 )
		return false;
	else
		inErrorCode=1;

	new Ajax.Request("ajax.php", {
		method: "post", 
		asynchronous : false, 	// Don't run in the background, to keep things 
								// simpler when dealing with possible unstable code
		parameters: { 
			errorLocation:document.location.href, 
			errorMessage:msg,
			errorURL:url, 
			errorLine:line
		}
	});
				
	inErrorCode=0;
	return false;
};

// If false it's okay to leave the page. makeFormsSafe sets this to true, and runs code which makes only
// certain forms safe.
window.leavepagedanger=false;

// When the window is about to change make sure there are no unsubmitted messages around
window.onbeforeunload = function (e) {
	// Don't give a warning dialog if we are submitting the text
	if( !window.leavepagedanger ) return;

	// Don't give a warning dialog if no large amount of text is at stake
	if( $$("textarea").all(function (t) { return ( t.value.length <= 10 ); }) )
		return;

	var str="You seem to have an unsubmitted message.";
	var e = e || window.event;
	
	// For IE and Firefox
	if (e) e.returnValue = str;
	
	//For Safari
	return str;
};

// Mark that the page shouldn't be left if there are unsent messages, and add form onsubmit handlers to disarm the 
// confirm dialog when submitting the message which the user would otherwise be warned about.
function makeFormsSafe() {
	window.leavepagedanger=true;
	
	// A function which disables the confirmation dialog
	var safeToSubmit = function() { 
		window.leavepagedanger=false; 
		return true;
	};
	
	$$(".safeForm").map( function(e) { e.onsubmit = safeToSubmit; } );
}