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

/**
 * Script runs on load of ghost ratings explanation page
 * See doc/javascript.txt for information on JavaScript in webDiplomacy
*/ 

var segments    = document.getElementsByClassName("gr-guide-detail");
var defaultOpen = document.getElementsByClassName("gr-guide-switch-default-open");
var switches    = document.getElementsByClassName("gr-guide-switch");

[].forEach.call(segments, function(e) {
    e.style.display = "none";
});

[].forEach.call(defaultOpen, function(e) {
    e.nextElementSibling.style.display = "block";
});

[].forEach.call(switches, function(e) {
    e.addEventListener("click", function() {
        this.classList.toggle("active");
        if (this.nextElementSibling.style.display === "block") {
            this.nextElementSibling.style.display = "none";
        } else if (this.nextElementSibling.style.display === "none") {
            this.nextElementSibling.style.display = "block";
        }
    })
});
