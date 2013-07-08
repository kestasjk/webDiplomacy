<?php
/*
    Copyright (C) 2013 Oliver Auth

	This file is part of vDiplomacy.

    vDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    vDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

if ( $Misc->Panic )
	libHTML::error('Cannot update in a panic.');

unset($DB); // Prevent libHTML from trying to do anything fancy if the database is out of sync with the code

libHTML::error(
		"vDip-Database version ".($Misc->vDipVersion)." and code
		version ".(VDIPVERSION)." don't match, and no
		auto-update script is available for this version.
		Please wait while the admin applies the lates database-updates."
	);

print '</div>';
libHTML::footer();

?>