/*
    Copyright (C) 2004-2011 Kestas J. Kuliukas

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
 * Filter out unwanted text from certain players and countries
 */

function muteAll() {
	muteUsers.map(function(m) {
		$$(".userID"+m).map(function(mt) {
			mt.hide();
		});
	});
	muteCountries.map(function(m) {
		$$(".gameID"+m[0]+"countryID"+m[1]).map(function(mt) {
			mt.hide();
		});
	});
}