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

/* 
 * Helper functions for tutorial mode modal operations
*/

function indexForward(index, tutorial = true) {
    if (tutorial) {
        document.getElementsByClassName("tutorial")[index - 1].style["display"] = "none";
        document.getElementsByClassName("tutorial")[index].style["display"] = "block";
        document.getElementsByClassName("tutorial-header")[0].scrollIntoView();
    }
}

function endTutorial() {
    document.cookie.split("; ").forEach((c) => {
        if (c.includes("wD-Tutorial")) {
            unsetCookie(c.substring(0, c.indexOf("=")));
        }
    });
    hideHelp();
}

function unsetCookie(name) {
    var date = new Date();
    date.setTime(Date.now()+(-1 * 24 * 60 * 60 * 1000));
    var expiration = "; expires=" + date.toGMTString();
    document.cookie = name + "=" + expiration + "; path=/";
}

function hideHelp() {
    document.getElementsByClassName("tutorial-wrap")[0].style["display"] = "none";
}
