<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Haven variant for webDiplomacy

	The Haven variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Haven variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

class HavenVariant_panelGameBoard extends panelGameBoard
{

	function mapHTML() {
		$mapTurn = (($this->phase=='Pre-game'||$this->phase=='Diplomacy') ? $this->turn-1 : $this->turn);
		$smallmapLink = 'map.php?gameID='.$this->id.'&turn='.$mapTurn;

		$staticMAP=Game::mapFilename($this->id, $mapTurn, 'small');
		$staticPNG=str_replace('.map','.png',$staticMAP);
		
		if (( file_exists($staticPNG) ) or ( file_exists($staticMAP) ))
			$smallmapLink = STATICSRV.$staticPNG.'?nocache='.$this->processTime;

		if (( file_exists($staticMAP) ) and !( file_exists($staticPNG) ))
			copy ($staticMAP, $staticPNG);

		$html = parent::mapHTML();
		
		$old = '/img id="mapImage" src="(\S*)" alt=" " title="The small map for the current phase. If you are starting a new turn this will show the last turn\'s orders" \/>/';
		$new = 'iframe id="mapImage" src="'.$smallmapLink.'" alt=" " width="750" height="486"> </iframe>';
		
		$newHTML = preg_replace($old,$new,$html);
		
		return $newHTML;
	}

}

