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
 * Return a gif animation of the whole game
 *
 * @package Board
 */

require_once('board/info/gif/animate.php');

if ( $Game->phase != 'Finished' )
{
	libHTML::error(l_t("The game you selected is still ongoing. Animations are only available for finished games."));
}

print '<h3>'.l_t('GIF Animation').'</h3>';

$animation_url = 'cache/games/0/'.$Game->id.'/animation.gif';

if ( ! file_exists($animation_url) )
{
	$Game->create_webDip_animation();
}

print '<p style="text-align:center">
		<img src="'.$animation_url.'" title="'.l_t('Animation').'" />
		<br /><br /><br />
		</p>';

?>