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

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * Return all the maps for this game. May use a lot of resources if the maps aren't
 * already cached.
 *
 * @package Board
 */

print '<h3>'.l_t('Maps').'</h3>';

for($i=$Game->turn;$i>=0;$i--)
{
	if($i<$Game->turn && ($i%2)!=0) print '<div class="hr"></div>';

	print '<h4>'.$Game->datetxt($i).'</h4>';
	print '<p style="text-align:center">
		<img src="map.php?gameID='.$Game->id.'&turn='.$i.'" title="'.l_t('Small map for this turn').'" /><br />
		'.l_t('Large map:').' <a href="map.php?gameID='.$Game->id.'&largemap=on&turn='.$i.'">
					<img src="'.l_s('images/historyicons/external.svg').'" alt="'.l_t('Large map').'"
						title="'.l_t('This button will open the large map in a new window. The large map shows all the moves, and is useful when the small map isn\'t clear enough').'."
					/></a>
		</p>';
}

?>