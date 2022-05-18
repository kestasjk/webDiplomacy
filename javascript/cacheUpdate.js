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

// Set user online green/blue icons to actual values, via "onlineUsers" array of online user IDs  
 function setUserOnlineIcons() {
 	if( !Object.isUndefined(onlineUsers) )
 		onlineUsers.map(function(userID) {
 			$$('img[userID="'+userID+'"].userOnlineImg').invoke('show');
 		});
 }

// Update new message icon for forum posts depending on stored cookie values
function setForumMessageIcons() {
	$$(".messageIconForum").map(function (e) {
		var messageID = e.getAttribute("messageID");
		var threadID = e.getAttribute("threadID");
		
		if( isPostNew(threadID, messageID) )
			e.show();

	});
}

function setForumParticipatedIcons() {
	if( !Object.isUndefined(participatedThreadIDs) ) {
		$$(".participatedIconForum").map(function (e) {
			var threadID = e.getAttribute("threadID");
			
			if( participatedThreadIDs.member(threadID) )
				e.show();
		});
	}
}

// Set messages sent by the current user to be italic
function setPostsItalicized() {
	$$('div[fromUserID="'+User.id+'"].message-contents').map(function(c) {
		c.setStyle({ fontStyle: "italic" });
	});
}

// Set a threadID as having been read, up to lastMessageID 
function readThread(threadID, lastMessageID) {
	createCookie("wD_Read_"+threadID, lastMessageID);
}

// Determine whether this user has seen this post before, based on session cookies and the User.lastMessageIDViewed
function isPostNew(threadID, messageID) {
	if( messageID <= User.lastMessageIDViewed )
		return false;
	
	var lastReadID = readCookie("wD_Read_"+threadID);

	if( Object.isUndefined(lastReadID) )
		return true;
	else
		return ( messageID > lastReadID );
}

// Cookie functions taken from http://www.quirksmode.org/js/cookies.html
// "This script was originally written by Scott Andrew. Copied and edited by permission."
function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}
