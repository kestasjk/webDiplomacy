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

require_once('header.php');

if ( ! defined('FACEBOOKSCRIPT') )
{
	libHTML::error('This page is Facebook-only.');
}

libHTML::starthtml();

print '<fb:request-form
action=""
method="POST"
invite="true"
type="Diplomacy"
content="webDiplomacy is based on the popular turn-based-strategy game of international relations. '.
	'Play with your friends and see if you can conquer Europe. '.
	'<fb:req-choice url=\''.DYNAMICSRV.'\' label=\'Add webDiplomacy\' />">

<fb:multi-friend-selector
showborder="false"
actiontext="Invite more friends to play webDiplomacy with you:">

</fb:request-form>';

print '</div>';
libHTML::footer();

?>