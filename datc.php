<?php
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
 * @package DATC
 */

require_once('header.php');

libHTML::starthtml(l_t('DATC Tests'));

print libHTML::pageTitle(l_t('Diplomacy Adjudicator Test Cases'),l_t('The results of a set of automated tests which show webDiplomacy\'s compliance with the official Diplomacy rules.'));

if ( $Misc->Maintenance )
{
	require_once(l_r('datc/interactive.php'));

}

require_once(l_r('locales/English/datc.php'));

?>
